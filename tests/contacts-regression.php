<?php
/**
 * Focused regression tests for contact persistence.
 *
 * Standalone test double: exercises plugin CRUD result handling, not MySQL locking.
 *
 * @package Hatnikotni_Chat
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'ARRAY_A', 'ARRAY_A' );

class WP_Error {
	private $code;
	public function __construct( $code, $message = '' ) { $this->code = $code; }
	public function get_error_code() { return $this->code; }
}
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function current_time( $type ) { return '2026-11-01 00:00:00'; }
function __( $text, $domain = '' ) { return $text; }

class HATNCH_Test_DB {
	public $prefix = 'wp_';
	public $rows = array();
	public $insert_id = 0;
	public $fail_insert = false;
	public $fail_update = false;
	public $delete_during_update = 0;
	public function prepare( $query, ...$args ) { return array( $query, $args ); }
	public function get_charset_collate() { return ''; }
	public function get_row( $prepared, $format = null ) {
		$id = (int) $prepared[1][1];
		return isset( $this->rows[$id] ) ? $this->rows[$id] : null;
	}
	public function get_results( $prepared, $format = null ) {
		$rows = array_values( array_filter( $this->rows, static fn( $row ) => 1 === (int) $row['status'] ) );
		usort( $rows, static fn( $a, $b ) => ( (int) $a['sort_order'] <=> (int) $b['sort_order'] ) ?: ( (int) $a['id'] <=> (int) $b['id'] ) );
		return $rows;
	}
	public function insert( $table, $data, $formats ) {
		if ( $this->fail_insert ) { return false; }
		$id = ++$this->insert_id;
		$this->rows[$id] = array_merge( array( 'id' => $id ), $data );
		return 1;
	}
	public function update( $table, $data, $where, $formats, $where_formats ) {
		$id = (int) $where['id'];
		if ( $this->fail_update ) { return false; }
		if ( $this->delete_during_update === $id ) {
			unset( $this->rows[$id] );
			$this->delete_during_update = 0;
			return 0;
		}
		if ( ! isset( $this->rows[$id] ) ) { return 0; }
		$changed = 0;
		foreach ( $data as $key => $value ) {
			if ( (string) $this->rows[$id][$key] !== (string) $value ) { $changed++; }
			$this->rows[$id][$key] = $value;
		}
		return $changed ? 1 : 0;
	}
}
$wpdb = new HATNCH_Test_DB();
require_once __DIR__ . '/../includes/class-hatnch-contacts.php';

function expect( $condition, $message ) {
	if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
	echo "PASS: {$message}\n";
}
$data = array( 'name' => 'Support', 'phone' => '601234567890', 'role' => 'Help', 'description' => '', 'status' => 1, 'weight' => 1, 'sort_order' => 0 );
$id = HATNCH_Contacts::save( $data );
expect( 1 === $id && null !== HATNCH_Contacts::get( $id ), 'insert persists and returns new ID' );

$updated_data = $data;
$updated_data['name'] = 'Support Team';
expect( $id === HATNCH_Contacts::save( $updated_data, $id ), 'update persists and returns existing ID' );
expect( 'Support Team' === HATNCH_Contacts::get( $id )['name'], 'updated fields are readable' );
expect( HATNCH_Contacts::save( $updated_data, $id ) === $id, 'unchanged/no-op update is accepted' );
expect( HATNCH_Contacts::set_status( $id, true ), 'setting existing status to same value is accepted' );
expect( HATNCH_Contacts::set_status( $id, false ), 'status mutation succeeds' );
expect( 0 === (int) HATNCH_Contacts::get( $id )['status'], 'status mutation persists' );
expect( ! HATNCH_Contacts::set_status( 999, true ), 'missing contact status mutation fails' );

$wpdb->fail_insert = true;
$failed_insert = HATNCH_Contacts::save( $data );
expect( $failed_insert instanceof WP_Error && 'db_insert_failed' === $failed_insert->get_error_code(), 'database insert failure returns WP_Error' );
$wpdb->fail_insert = false;

$wpdb->fail_update = true;
$failed_update = HATNCH_Contacts::save( $data, $id );
expect( $failed_update instanceof WP_Error && 'db_update_failed' === $failed_update->get_error_code(), 'database update failure returns WP_Error' );
$wpdb->fail_update = false;

$wpdb->delete_during_update = $id;
$vanished = HATNCH_Contacts::save( $updated_data, $id );
expect( $vanished instanceof WP_Error && 'contact_not_found' === $vanished->get_error_code(), 'concurrent deletion during update is detected' );
expect( HATNCH_Contacts::save( $data, 999 ) instanceof WP_Error, 'edit of missing contact is rejected before write' );

$invalid = $data;
$invalid['name'] = '';
expect( HATNCH_Contacts::save( $invalid ) instanceof WP_Error, 'validation rejects missing name' );
echo "PASS: Contact persistence regression suite\n";
