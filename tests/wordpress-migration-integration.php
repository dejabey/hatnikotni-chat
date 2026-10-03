<?php
/**
 * WordPress-backed migration and dbDelta repair tests.
 *
 * This file is executed with WP-CLI against an ephemeral CI WordPress/MySQL install.
 */

function hatnch_wp_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
		exit( 1 );
	}
}

global $wpdb;

$contacts_table = $wpdb->prefix . 'hatnch_contacts';
$events_table   = $wpdb->prefix . 'hatnch_events';
$legacy_prefix  = 'hkc_';
$legacy_contacts = $wpdb->prefix . $legacy_prefix . 'contacts';
$legacy_events   = $wpdb->prefix . $legacy_prefix . 'events';

// Exercise the real activation callback and WordPress option/database APIs.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hatnch_routing_state' );
delete_option( 'hkc_settings' );
delete_option( 'hkc_routing_state' );

$wpdb->query( "CREATE TABLE {$legacy_contacts} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->query( "CREATE TABLE {$legacy_events} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	contact_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->insert( $legacy_contacts, array( 'name' => 'WordPress integration contact' ) );
$wpdb->insert( $legacy_events, array( 'contact_id' => 1 ) );
update_option( 'hkc_settings', array( 'enabled' => 1 ) );
update_option( 'hkc_routing_state', array( 'last_id' => 9 ) );

HATNCH_Plugin::activate();

hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$contacts_table}'" ) === $contacts_table, 'activation should create/migrate contacts table' );
hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$events_table}'" ) === $events_table, 'activation should create/migrate events table' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table} WHERE name = 'WordPress integration contact'" ) === 1, 'legacy contact row should survive activation migration' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table} WHERE contact_id = 1" ) === 1, 'legacy event row should survive activation migration' );
hatnch_wp_test_assert( get_option( 'hatnch_settings' ) === array( 'enabled' => 1 ), 'legacy settings should migrate through WordPress options API' );
hatnch_wp_test_assert( get_option( 'hatnch_routing_state' ) === array( 'last_id' => 9 ), 'legacy routing state should migrate through WordPress options API' );
hatnch_wp_test_assert( false === get_option( 'hkc_settings', false ), 'legacy settings should be removed after verified copy' );
hatnch_wp_test_assert( false === get_option( 'hkc_routing_state', false ), 'legacy routing state should be removed after verified copy' );
hatnch_wp_test_assert( HATNCH_DB_VERSION === get_option( 'hatnch_db_version' ), 'activation should record the verified schema version' );
echo "PASS: real WordPress activation migrates legacy tables and options" . PHP_EOL;

// Damage a current table, lower the version, and run the actual init upgrade path.
// WordPress dbDelta must restore the missing column before the version is advanced.
$wpdb->query( "ALTER TABLE {$contacts_table} DROP COLUMN updated_at" );
update_option( 'hatnch_db_version', '1.0.0' );
HATNCH_Plugin::instance()->init();

$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$contacts_table}", 0 );
hatnch_wp_test_assert( in_array( 'updated_at', $columns, true ), 'init upgrade should restore a missing contacts column using dbDelta' );
hatnch_wp_test_assert( HATNCH_DB_VERSION === get_option( 'hatnch_db_version' ), 'schema version should advance only after dbDelta repair passes verification' );
echo "PASS: real WordPress init/dbDelta repairs a missing column before version advancement" . PHP_EOL;

// Repeat against a missing index to exercise dbDelta index repair and the schema gate.
$wpdb->query( "ALTER TABLE {$contacts_table} DROP INDEX status" );
update_option( 'hatnch_db_version', '1.0.0' );
HATNCH_Plugin::instance()->init();

$indexes = $wpdb->get_results( "SHOW INDEX FROM {$contacts_table}", ARRAY_A );
$status_index_found = false;
foreach ( $indexes as $index ) {
	if ( 'status' === $index['Key_name'] && 'status' === $index['Column_name'] ) {
		$status_index_found = true;
		break;
	}
}
hatnch_wp_test_assert( $status_index_found, 'init upgrade should restore a missing index using dbDelta' );
hatnch_wp_test_assert( HATNCH_DB_VERSION === get_option( 'hatnch_db_version' ), 'version should advance after required index is restored and verified' );
echo "PASS: real WordPress init/dbDelta repairs a missing index before version advancement" . PHP_EOL;



// A current/legacy table collision must pause activation without dropping either table.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hkc_settings' );

$wpdb->query( "CREATE TABLE {$legacy_contacts} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->query( "CREATE TABLE {$contacts_table} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->insert( $legacy_contacts, array( 'name' => 'Legacy collision row' ) );
$wpdb->insert( $contacts_table, array( 'name' => 'Current collision row' ) );
update_option( 'hkc_settings', array( 'source' => 'legacy' ) );

HATNCH_Plugin::activate();

hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$legacy_contacts} WHERE name = 'Legacy collision row'" ) === 1, 'legacy collision table row must remain untouched' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table} WHERE name = 'Current collision row'" ) === 1, 'current collision table row must remain untouched' );
hatnch_wp_test_assert( array( 'source' => 'legacy' ) === get_option( 'hkc_settings' ), 'legacy option must remain when table collision pauses migration' );
hatnch_wp_test_assert( false === get_option( 'hatnch_db_version', false ), 'table collision must not advance the database version' );
echo "PASS: real WordPress activation preserves both tables and legacy options on table collision" . PHP_EOL;

// A conflicting legacy/current option pair must pause activation before table changes.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hkc_settings' );
update_option( 'hkc_settings', array( 'source' => 'legacy' ) );
update_option( 'hatnch_settings', array( 'source' => 'current' ) );

HATNCH_Plugin::activate();

hatnch_wp_test_assert( array( 'source' => 'legacy' ) === get_option( 'hkc_settings' ), 'legacy option must remain on option conflict' );
hatnch_wp_test_assert( array( 'source' => 'current' ) === get_option( 'hatnch_settings' ), 'current option must remain on option conflict' );
hatnch_wp_test_assert( false === get_option( 'hatnch_db_version', false ), 'option conflict must not advance the database version' );
hatnch_wp_test_assert( null === $wpdb->get_var( "SHOW TABLES LIKE '{$contacts_table}'" ), 'option conflict must stop before creating current tables' );
echo "PASS: real WordPress activation preserves conflicting options and stops before table changes" . PHP_EOL;

// Inject a real WordPress $wpdb rename failure, then retry without the fault.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hatnch_routing_state' );
delete_option( 'hkc_settings' );
delete_option( 'hkc_routing_state' );

$wpdb->query( "CREATE TABLE {$legacy_contacts} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->query( "CREATE TABLE {$legacy_events} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	contact_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->insert( $legacy_contacts, array( 'name' => 'Retry-path contact' ) );
$wpdb->insert( $legacy_events, array( 'contact_id' => 1 ) );
$GLOBALS['hatnch_wp_test_fail_events_rename'] = true;

$rename_failure_filter = static function ( $query ) use ( $legacy_events ) {
	if ( ! empty( $GLOBALS['hatnch_wp_test_fail_events_rename'] ) && 0 === strpos( ltrim( $query ), 'RENAME TABLE' ) && false !== strpos( $query, $legacy_events ) ) {
		$GLOBALS['hatnch_wp_test_fail_events_rename'] = false;
		return str_replace( $legacy_events, $GLOBALS['wpdb']->prefix . 'missing_hkc_events', $query );
	}
	return $query;
};
add_filter( 'query', $rename_failure_filter );

// This query failure is deliberate; suppress its expected WordPress error output.
$previous_suppress_errors = $wpdb->suppress_errors();
HATNCH_Plugin::activate();
$wpdb->suppress_errors( $previous_suppress_errors );
remove_filter( 'query', $rename_failure_filter );

hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$contacts_table}'" ) === $contacts_table, 'first table rename should survive the injected second-rename failure' );
hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$legacy_events}'" ) === $legacy_events, 'failed events rename should leave the legacy events table intact' );
hatnch_wp_test_assert( false === get_option( 'hatnch_db_version', false ), 'failed real $wpdb rename must not advance the database version' );

HATNCH_Plugin::activate();

hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$events_table}'" ) === $events_table, 'retry should complete the events table rename' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table} WHERE name = 'Retry-path contact'" ) === 1, 'contact row should survive actual $wpdb rename failure and retry' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table} WHERE contact_id = 1" ) === 1, 'event row should survive actual $wpdb rename failure and retry' );
hatnch_wp_test_assert( HATNCH_DB_VERSION === get_option( 'hatnch_db_version' ), 'retry should advance the schema version only after successful verification' );
echo 'PASS: real WordPress $wpdb rename failure preserves data and recovers on retry' . PHP_EOL;

// An empty current table must still be treated as a collision when legacy rows exist.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hatnch_routing_state' );
delete_option( 'hkc_settings' );
delete_option( 'hkc_routing_state' );

$wpdb->query( "CREATE TABLE {$legacy_contacts} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->query( "CREATE TABLE {$contacts_table} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->insert( $legacy_contacts, array( 'name' => 'Legacy row beside empty current table' ) );
update_option( 'hkc_settings', array( 'source' => 'legacy-empty-collision' ) );

HATNCH_Plugin::activate();

hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$legacy_contacts} WHERE name = 'Legacy row beside empty current table'" ) === 1, 'legacy data must remain when the current table exists but is empty' );
hatnch_wp_test_assert( 0 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table}" ), 'empty current table must remain unchanged on collision' );
hatnch_wp_test_assert( array( 'source' => 'legacy-empty-collision' ) === get_option( 'hkc_settings' ), 'legacy settings must remain when an empty current table collides' );
hatnch_wp_test_assert( false === get_option( 'hatnch_db_version', false ), 'empty-current-table collision must not advance the database version' );
echo "PASS: real WordPress activation preserves legacy rows when the colliding current table is empty" . PHP_EOL;

// Deactivate/reactivate an old installation before migration; legacy settings must be migrated before defaults.
$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_contacts}" );
$wpdb->query( "DROP TABLE IF EXISTS {$legacy_events}" );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_settings' );
delete_option( 'hatnch_routing_state' );
delete_option( 'hkc_settings' );
delete_option( 'hkc_routing_state' );

$wpdb->query( "CREATE TABLE {$legacy_contacts} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->query( "CREATE TABLE {$legacy_events} (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	contact_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}" );
$wpdb->insert( $legacy_contacts, array( 'name' => 'Deactivate-reactivate contact' ) );
$wpdb->insert( $legacy_events, array( 'contact_id' => 1 ) );
update_option( 'hkc_settings', array( 'source' => 'pre-reactivation-legacy' ) );
update_option( 'hkc_routing_state', array( 'last_id' => 17 ) );

HATNCH_Plugin::deactivate();
HATNCH_Plugin::activate();

hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$contacts_table}'" ) === $contacts_table, 'reactivation should migrate legacy contacts before creating new tables' );
hatnch_wp_test_assert( $wpdb->get_var( "SHOW TABLES LIKE '{$events_table}'" ) === $events_table, 'reactivation should migrate legacy events before creating new tables' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$contacts_table} WHERE name = 'Deactivate-reactivate contact'" ) === 1, 'reactivation must preserve the legacy contact row' );
hatnch_wp_test_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table} WHERE contact_id = 1" ) === 1, 'reactivation must preserve the legacy event row' );
hatnch_wp_test_assert( array( 'source' => 'pre-reactivation-legacy' ) === get_option( 'hatnch_settings' ), 'legacy settings must migrate before defaults during reactivation' );
hatnch_wp_test_assert( array( 'last_id' => 17 ) === get_option( 'hatnch_routing_state' ), 'legacy routing state must survive reactivation' );
hatnch_wp_test_assert( false === get_option( 'hkc_settings', false ), 'legacy settings should be deleted only after verified reactivation migration' );
hatnch_wp_test_assert( HATNCH_DB_VERSION === get_option( 'hatnch_db_version' ), 'reactivation should advance the version only after verified migration' );
echo "PASS: deactivate/reactivate migrates legacy tables and options before defaults/schema installation" . PHP_EOL;

echo "PASS: all WordPress-backed migration lifecycle tests" . PHP_EOL;
