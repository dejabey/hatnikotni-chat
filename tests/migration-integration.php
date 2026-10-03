<?php
/**
 * Database-backed migration integration tests. Uses only the disposable CI MySQL service.
 */
define( 'ABSPATH', __DIR__ );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'HATNCH_DB_VERSION', '1.1.0' );
$GLOBALS['hatnch_test_options'] = array();
$GLOBALS['hatnch_test_fail_rename_to'] = '';
$GLOBALS['hatnch_test_fail_update_option_key'] = '';

function get_option( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['hatnch_test_options'] ) ? $GLOBALS['hatnch_test_options'][ $key ] : $default;
}
function add_option( $key, $value, $deprecated = '', $autoload = 'yes' ) {
	if ( array_key_exists( $key, $GLOBALS['hatnch_test_options'] ) ) { return false; }
	$GLOBALS['hatnch_test_options'][ $key ] = $value;
	return true;
}
function delete_option( $key ) { unset( $GLOBALS['hatnch_test_options'][ $key ] ); return true; }
function update_option( $key, $value, $autoload = null ) {
	if ( $key === $GLOBALS['hatnch_test_fail_update_option_key'] ) { return false; }
	if ( get_option( $key, null ) === $value ) { return false; }
	$GLOBALS['hatnch_test_options'][ $key ] = $value;
	return true;
}
function absint( $value ) { return abs( (int) $value ); }
function wp_clear_scheduled_hook( $hook ) { return 0; }
function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return $text; }

class HATNCH_Test_WPDB {
	public $prefix = 'wp_';
	public $last_error = '';
	private $connection;
	public function __construct( mysqli $connection ) { $this->connection = $connection; }
	public function esc_like( $text ) { return addcslashes( $text, '_%\\\\' ); }
	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			$identifier = strpos( $query, '%i' );
			$string = strpos( $query, '%s' );
			if ( false !== $identifier && ( false === $string || $identifier < $string ) ) {
				$quoted = chr( 96 ) . str_replace( chr( 96 ), chr( 96 ) . chr( 96 ), (string) $arg ) . chr( 96 );
				$query = substr_replace( $query, $quoted, $identifier, 2 );
			} elseif ( false !== $string ) {
				$quoted = "'" . $this->connection->real_escape_string( (string) $arg ) . "'";
				$query = substr_replace( $query, $quoted, $string, 2 );
			}
		}
		return $query;
	}
	public function get_col( $query, $column = 0 ) {
		$this->last_error = '';
		$result = $this->connection->query( $query );
		if ( false === $result ) { $this->last_error = $this->connection->error; return null; }
		$values = array();
		while ( $row = $result->fetch_row() ) { $values[] = $row[ $column ] ?? null; }
		$result->free();
		return $values;
	}
	public function get_results( $query, $output = ARRAY_A ) {
		$this->last_error = '';
		$result = $this->connection->query( $query );
		if ( false === $result ) { $this->last_error = $this->connection->error; return null; }
		$rows = array();
		while ( $row = $result->fetch_assoc() ) { $rows[] = $row; }
		$result->free();
		return $rows;
	}
	public function get_var( $query ) {
		$this->last_error = '';
		$result = $this->connection->query( $query );
		if ( false === $result ) { $this->last_error = $this->connection->error; return null; }
		$row = $result->fetch_row();
		$result->free();
		return $row[0] ?? null;
	}
	public function query( $query ) {
		$this->last_error = '';
		if ( '' !== $GLOBALS['hatnch_test_fail_rename_to'] && false !== strpos( $query, 'RENAME TABLE' ) && false !== strpos( $query, $GLOBALS['hatnch_test_fail_rename_to'] ) ) {
			$this->last_error = 'Injected rename failure for integration test';
			return false;
		}
		$result = $this->connection->query( $query );
		if ( false === $result ) { $this->last_error = $this->connection->error; return false; }
		return true;
	}
}
function hatnch_test_assert( $condition, $message ) {
	if ( ! $condition ) { fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL ); exit( 1 ); }
}
function hatnch_test_reset( mysqli $connection ) {
	foreach ( array( 'wp_hkc_contacts', 'wp_hkc_events', 'wp_hatnch_contacts', 'wp_hatnch_events' ) as $table ) {
		$connection->query( 'DROP TABLE IF EXISTS ' . chr( 96 ) . $table . chr( 96 ) );
	}
	$GLOBALS['hatnch_test_options'] = array();
	$GLOBALS['hatnch_test_fail_rename_to'] = '';
	$GLOBALS['hatnch_test_fail_update_option_key'] = '';
}
function hatnch_test_create_legacy_tables( mysqli $connection ) {
	$connection->query( 'CREATE TABLE wp_hkc_contacts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)' );
	$connection->query( 'CREATE TABLE wp_hkc_events (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, contact_id BIGINT UNSIGNED NOT NULL)' );
	$connection->query( "INSERT INTO wp_hkc_contacts (name) VALUES ('Legacy contact')" );
	$connection->query( 'INSERT INTO wp_hkc_events (contact_id) VALUES (1)' );
}

function hatnch_test_create_contacts_schema( mysqli $connection, string $id_type = 'BIGINT UNSIGNED', string $status_index = 'KEY status (status)', bool $include_updated_at = true, string $id_definition = 'NOT NULL AUTO_INCREMENT' ) {
	$updated_at = $include_updated_at ? ', updated_at DATETIME NOT NULL' : '';
	$sql = "CREATE TABLE wp_hatnch_contacts (
		id {$id_type} {$id_definition},
		name VARCHAR(100) NOT NULL,
		phone VARCHAR(30) NOT NULL,
		role VARCHAR(50) NOT NULL DEFAULT '',
		description TEXT NOT NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'active',
		weight INT NOT NULL DEFAULT 1,
		sort_order INT NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL" . $updated_at . ",
		PRIMARY KEY (id),
		{$status_index},
		KEY sort_order (sort_order)
	)";
	$connection->query( $sql );
}

function hatnch_test_create_events_schema( mysqli $connection, string $contact_date_index = 'KEY contact_date (contact_id, created_at)' ) {
	$connection->query( "CREATE TABLE wp_hatnch_events (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		event_type VARCHAR(50) NOT NULL,
		created_at DATETIME NOT NULL,
		contact_id BIGINT UNSIGNED NULL,
		page_id BIGINT UNSIGNED NULL,
		page_type VARCHAR(50) NULL,
		device VARCHAR(50) NULL,
		utm_source VARCHAR(255) NULL,
		utm_medium VARCHAR(255) NULL,
		utm_campaign VARCHAR(255) NULL,
		utm_term VARCHAR(255) NULL,
		utm_content VARCHAR(255) NULL,
		PRIMARY KEY (id),
		KEY created_at (created_at),
		{$contact_date_index},
		KEY page_date (page_id, page_type, created_at),
		KEY campaign_date (utm_campaign, created_at)
	)" );
}

$connection = new mysqli( getenv( 'MYSQL_HOST' ) ?: '127.0.0.1', getenv( 'MYSQL_USER' ) ?: 'root', getenv( 'MYSQL_PASSWORD' ) ?: '', getenv( 'MYSQL_DATABASE' ) ?: 'hatnch_test', (int) ( getenv( 'MYSQL_PORT' ) ?: 3306 ) );
if ( $connection->connect_error ) { fwrite( STDERR, 'MySQL connection failed: ' . $connection->connect_error . PHP_EOL ); exit( 1 ); }
$connection->set_charset( 'utf8mb4' );
$GLOBALS['wpdb'] = new HATNCH_Test_WPDB( $connection );
class HATNCH_Contacts { public static function install_schema() {} }
class HATNCH_Analytics { public static function install_schema() {} }
require_once __DIR__ . '/../includes/class-hatnch-plugin.php';
$migration = new ReflectionMethod( 'HATNCH_Plugin', 'maybe_migrate_legacy_data' );
$migration->setAccessible( true );

// Legacy-only tables/options: rename tables and preserve row/option values.
hatnch_test_reset( $connection );
hatnch_test_create_legacy_tables( $connection );
$GLOBALS['hatnch_test_options']['hkc_settings'] = array( 'enabled' => 1 );
$GLOBALS['hatnch_test_options']['hkc_routing_state'] = array( 'last_id' => 7 );
hatnch_test_assert( true === $migration->invoke( null ), 'legacy-only migration should succeed' );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'contacts table should be renamed' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'events table should be renamed' );
$row = $connection->query( 'SELECT name FROM wp_hatnch_contacts WHERE id = 1' )->fetch_row();
hatnch_test_assert( 'Legacy contact' === $row[0], 'contact row should survive migration' );
hatnch_test_assert( array( 'enabled' => 1 ) === get_option( 'hatnch_settings' ), 'settings should be copied' );
hatnch_test_assert( false === get_option( 'hkc_settings' ), 'legacy settings should be deleted after verification' );
echo "PASS: legacy tables/options migrate with data preserved" . PHP_EOL;

// Conflicting legacy/current options: pause before any table changes and preserve both values.
hatnch_test_reset( $connection );
$GLOBALS['hatnch_test_options']['hkc_settings'] = array( 'legacy' => 1 );
$GLOBALS['hatnch_test_options']['hatnch_settings'] = array( 'current' => 1 );
hatnch_test_assert( false === $migration->invoke( null ), 'conflicting options should pause migration' );
hatnch_test_assert( array( 'legacy' => 1 ) === get_option( 'hkc_settings' ), 'legacy option must remain after conflict' );
hatnch_test_assert( array( 'current' => 1 ) === get_option( 'hatnch_settings' ), 'current option must remain after conflict' );
hatnch_test_assert( null === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'option conflict must stop before table changes' );
echo "PASS: option conflict preserves both values" . PHP_EOL;

// Coexisting old/new tables: stop without deleting either table.
hatnch_test_reset( $connection );
hatnch_test_create_legacy_tables( $connection );
$connection->query( 'CREATE TABLE wp_hatnch_contacts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)' );
$GLOBALS['hatnch_test_options']['hkc_settings'] = array( 'legacy' => 1 );
hatnch_test_assert( false === $migration->invoke( null ), 'table collision should pause migration' );
hatnch_test_assert( 'wp_hkc_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hkc_contacts'" ), 'legacy table must remain' );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'current table must remain' );
hatnch_test_assert( array( 'legacy' => 1 ) === get_option( 'hkc_settings' ), 'legacy option must remain' );
echo "PASS: table collision pauses without data deletion" . PHP_EOL;

// Inject failure on the second rename, then verify a retry completes without data loss.
hatnch_test_reset( $connection );
hatnch_test_create_legacy_tables( $connection );
$GLOBALS['hatnch_test_fail_rename_to'] = 'wp_hatnch_events';
hatnch_test_assert( false === $migration->invoke( null ), 'injected rename failure should pause migration' );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'first rename should remain completed' );
hatnch_test_assert( 'wp_hkc_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hkc_events'" ), 'failed table should remain legacy' );
$GLOBALS['hatnch_test_fail_rename_to'] = '';
hatnch_test_assert( true === $migration->invoke( null ), 'retry should finish migration' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'retry should rename events table' );
$row = $connection->query( 'SELECT COUNT(*) FROM wp_hatnch_contacts' )->fetch_row();
hatnch_test_assert( 1 === (int) $row[0], 'contact row should survive retry' );
$row = $connection->query( 'SELECT COUNT(*) FROM wp_hatnch_events' )->fetch_row();
hatnch_test_assert( 1 === (int) $row[0], 'event row should survive retry' );
echo "PASS: partial rename failure recovers on retry" . PHP_EOL;

// Schema gate: a missing required column must prevent DB-version advancement.
hatnch_test_reset( $connection );
$connection->query( 'CREATE TABLE wp_hatnch_contacts (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	phone VARCHAR(30) NOT NULL,
	role VARCHAR(50) NOT NULL DEFAULT \'\',
	description TEXT NOT NULL,
	status VARCHAR(20) NOT NULL DEFAULT \'active\',
	weight INT NOT NULL DEFAULT 1,
	sort_order INT NOT NULL DEFAULT 0,
	created_at DATETIME NOT NULL,
	PRIMARY KEY (id),
	KEY status (status),
	KEY sort_order (sort_order)
)' );
$connection->query( 'CREATE TABLE wp_hatnch_events (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	event_type VARCHAR(50) NOT NULL,
	created_at DATETIME NOT NULL,
	contact_id BIGINT UNSIGNED NULL,
	page_id BIGINT UNSIGNED NULL,
	page_type VARCHAR(50) NULL,
	device VARCHAR(50) NULL,
	utm_source VARCHAR(255) NULL,
	utm_medium VARCHAR(255) NULL,
	utm_campaign VARCHAR(255) NULL,
	utm_term VARCHAR(255) NULL,
	utm_content VARCHAR(255) NULL,
	PRIMARY KEY (id),
	KEY created_at (created_at),
	KEY contact_date (contact_id, created_at),
	KEY page_date (page_id, page_type, created_at),
	KEY campaign_date (utm_campaign, created_at)
)' );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'incomplete contacts fixture must exist' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'events fixture must exist' );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
$upgrade = new ReflectionMethod( 'HATNCH_Plugin', 'maybe_upgrade' );
$upgrade->setAccessible( true );
hatnch_test_assert( false === $upgrade->invoke( null ), 'missing required column should fail schema gate' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'schema version must not advance when schema verification fails' );
echo "PASS: incomplete schema blocks database-version advancement" . PHP_EOL;

// A malformed primary ID must also block the version bump.
hatnch_test_reset( $connection );
hatnch_test_create_contacts_schema( $connection, 'INT UNSIGNED' );
hatnch_test_create_events_schema( $connection );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'wrong-ID contacts fixture must exist' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'events fixture must exist for ID test' );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
hatnch_test_assert( false === $upgrade->invoke( null ), 'wrong primary ID type should fail schema gate' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'wrong primary ID must not advance schema version' );
echo "PASS: invalid primary ID blocks database-version advancement" . PHP_EOL;

// An ID without AUTO_INCREMENT must not be accepted as the plugin's primary ID contract.
hatnch_test_reset( $connection );
hatnch_test_create_contacts_schema( $connection, 'BIGINT UNSIGNED', 'KEY status (status)', true, 'NOT NULL' );
hatnch_test_create_events_schema( $connection );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'non-auto-increment contacts fixture must exist' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'events fixture must exist for auto-increment test' );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
hatnch_test_assert( false === $upgrade->invoke( null ), 'missing AUTO_INCREMENT should fail schema gate' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'missing AUTO_INCREMENT must not advance schema version' );
echo "PASS: missing AUTO_INCREMENT blocks database-version advancement" . PHP_EOL;

// A required secondary index with misordered columns must be rejected.
hatnch_test_reset( $connection );
hatnch_test_create_contacts_schema( $connection );
hatnch_test_create_events_schema( $connection, 'KEY contact_date (created_at, contact_id)' );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'contacts fixture must exist for index-order test' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'misordered-index events fixture must exist' );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
hatnch_test_assert( false === $upgrade->invoke( null ), 'misordered required index should fail schema gate' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'misordered index must not advance schema version' );
echo "PASS: misordered index blocks database-version advancement" . PHP_EOL;

// A required secondary index with the wrong uniqueness must be rejected.
hatnch_test_reset( $connection );
hatnch_test_create_contacts_schema( $connection, 'BIGINT UNSIGNED', 'UNIQUE KEY status (status)' );
hatnch_test_create_events_schema( $connection );
hatnch_test_assert( 'wp_hatnch_contacts' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_contacts'" ), 'wrong-index contacts fixture must exist' );
hatnch_test_assert( 'wp_hatnch_events' === $GLOBALS['wpdb']->get_var( "SHOW TABLES LIKE 'wp_hatnch_events'" ), 'events fixture must exist for index test' );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
hatnch_test_assert( false === $upgrade->invoke( null ), 'unique secondary index should fail schema gate' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'wrong index uniqueness must not advance schema version' );
echo "PASS: invalid index uniqueness blocks database-version advancement" . PHP_EOL;

// A failed DB-version option write must leave the old version in place and report upgrade failure.
hatnch_test_reset( $connection );
hatnch_test_create_contacts_schema( $connection );
hatnch_test_create_events_schema( $connection );
$GLOBALS['hatnch_test_options']['hatnch_db_version'] = '1.0.0';
$GLOBALS['hatnch_test_fail_update_option_key'] = 'hatnch_db_version';
hatnch_test_assert( false === $upgrade->invoke( null ), 'failed DB-version write should fail upgrade' );
hatnch_test_assert( '1.0.0' === get_option( 'hatnch_db_version' ), 'failed DB-version write must preserve old version' );
echo "PASS: failed database-version write does not advance version" . PHP_EOL;

hatnch_test_reset( $connection );
$connection->close();
echo "PASS: all migration integration tests" . PHP_EOL;
