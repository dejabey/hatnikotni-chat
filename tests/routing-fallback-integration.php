<?php
/**
 * WordPress + MySQL integration test for the Round Robin fallback (0.1.29).
 *
 * Before 0.1.29, a lock or cursor failure returned null and the visitor was
 * silently redirected to the home page. This test holds the routing advisory
 * lock from a second database connection, so GET_LOCK genuinely times out,
 * and checks that the visitor still gets an active contact, that the cursor
 * does not move, that the failure is counted without personal data, and that
 * `hatnch_routing_failure` fires. It then checks that normal rotation resumes.
 *
 * Runs after tests/round-robin-concurrency-setup.php in CI (two active
 * contacts, routing_method = round_robin). Disposable database only.
 *
 * Run with: wp eval-file tests/routing-fallback-integration.php
 *
 * @package Hatnikotni_Chat
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "Run this test through WP-CLI.\n" );
	exit( 1 );
}

$hatnch_fail = static function ( string $message ): void {
	fwrite( STDERR, 'FAIL: ' . $message . "\n" );
	exit( 1 );
};

global $wpdb;

$hatnch_read_cursor = static function () use ( $wpdb ): int {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test reads the persisted cursor, bypassing caches.
	$raw   = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'hatnch_routing_state' ) );
	$state = null === $raw ? array() : maybe_unserialize( $raw );
	return is_array( $state ) ? absint( $state['last_contact_id'] ?? 0 ) : 0;
};

$hatnch_active = HATNCH_Contacts::get_active();
if ( count( $hatnch_active ) < 2 ) {
	$hatnch_fail( 'Expected at least two active fixture contacts; found ' . count( $hatnch_active ) . '.' );
}
if ( 'round_robin' !== HATNCH_Settings::get( 'routing_method', '' ) ) {
	$hatnch_fail( 'Fixture must use round_robin routing.' );
}

$hatnch_ids             = array_map( 'intval', wp_list_pluck( $hatnch_active, 'id' ) );
$hatnch_settings_before = HATNCH_Settings::all();

// Prefer the second active contact as default, to prove the fallback honours it.
$hatnch_default_id                 = $hatnch_ids[1];
$hatnch_settings                   = $hatnch_settings_before;
$hatnch_settings['default_contact'] = $hatnch_default_id;
update_option( 'hatnch_settings', $hatnch_settings, false );
// Literal option name so this file runs (and fails meaningfully) against pre-0.1.29 code.
delete_option( 'hatnch_routing_failures' );

$hatnch_events = array();
add_action(
	'hatnch_routing_failure',
	static function ( $reason, $lane, $fallback_id ) use ( &$hatnch_events ): void {
		$hatnch_events[] = array( $reason, $lane, (int) $fallback_id );
	},
	10,
	3
);

// 1. Hold the lock from a second connection. The name must match HATNCH_Routing.
$hatnch_lock = 'hatnch_rr_' . substr( hash( 'sha256', DB_NAME . '|' . $wpdb->prefix . '|hatnch_routing_state' ), 0, 48 );
$hatnch_other = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test holds the advisory lock from a second session.
if ( 1 !== (int) $hatnch_other->get_var( $hatnch_other->prepare( 'SELECT GET_LOCK( %s, 1 )', $hatnch_lock ) ) ) {
	$hatnch_fail( 'Second connection could not take the routing lock.' );
}

$hatnch_cursor_before = $hatnch_read_cursor();
$hatnch_started       = microtime( true );
$hatnch_contact       = ( new HATNCH_Routing() )->resolve();
$hatnch_elapsed       = microtime( true ) - $hatnch_started;
$hatnch_cursor_after  = $hatnch_read_cursor();

if ( ! is_array( $hatnch_contact ) ) {
	$hatnch_fail( 'Lock timeout still returned no contact (visitor would be sent to the home page).' );
}
if ( $hatnch_default_id !== (int) $hatnch_contact['id'] ) {
	$hatnch_fail( 'Fallback did not use the active default contact; got ID ' . (int) $hatnch_contact['id'] . '.' );
}
if ( $hatnch_elapsed < 4.0 ) {
	$hatnch_fail( sprintf( 'resolve() returned after %.2fs; the lock wait did not happen, so the lock path was not exercised.', $hatnch_elapsed ) );
}
if ( $hatnch_cursor_before !== $hatnch_cursor_after ) {
	$hatnch_fail( "Cursor moved during a fallback ({$hatnch_cursor_before} -> {$hatnch_cursor_after})." );
}

$hatnch_summary = HATNCH_Routing::failure_summary();
if ( 1 !== $hatnch_summary['total'] || array( 'lock_unavailable' => 1 ) !== $hatnch_summary['reasons'] ) {
	$hatnch_fail( 'Failure counter wrong: ' . wp_json_encode( $hatnch_summary ) );
}
if ( array( array( 'lock_unavailable', 'global', $hatnch_default_id ) ) !== $hatnch_events ) {
	$hatnch_fail( 'hatnch_routing_failure did not fire as expected: ' . wp_json_encode( $hatnch_events ) );
}

// The stored counter holds only counts and timestamps.
$hatnch_stored = get_option( HATNCH_Routing::FAILURE_OPTION );
if ( array( 'total', 'reasons', 'first_at', 'last_at' ) !== array_keys( $hatnch_stored ) ) {
	$hatnch_fail( 'Failure option has unexpected keys: ' . wp_json_encode( array_keys( $hatnch_stored ) ) );
}
foreach ( $hatnch_active as $hatnch_fixture ) {
	if ( false !== strpos( maybe_serialize( $hatnch_stored ), (string) $hatnch_fixture['phone'] ) ) {
		$hatnch_fail( 'Failure option contains a phone number.' );
	}
}

// 2. A filter may choose another eligible contact, but never one outside the set.
add_filter(
	'hatnch_routing_fallback_contact',
	static function () {
		return array( 'id' => 999999, 'phone' => '60000000000' );
	}
);
$hatnch_contact = ( new HATNCH_Routing() )->resolve();
if ( ! is_array( $hatnch_contact ) || ! in_array( (int) $hatnch_contact['id'], $hatnch_ids, true ) ) {
	$hatnch_fail( 'Fallback filter could inject a contact outside the active set.' );
}
remove_all_filters( 'hatnch_routing_fallback_contact' );

// 3. Release the lock: normal rotation resumes, nothing more is counted.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test releases its advisory lock.
$hatnch_other->get_var( $hatnch_other->prepare( 'SELECT RELEASE_LOCK( %s )', $hatnch_lock ) );
$hatnch_other->close();

$hatnch_total_before = HATNCH_Routing::failure_summary()['total'];
$hatnch_first        = ( new HATNCH_Routing() )->resolve();
$hatnch_second       = ( new HATNCH_Routing() )->resolve();
if ( ! is_array( $hatnch_first ) || ! is_array( $hatnch_second ) || (int) $hatnch_first['id'] === (int) $hatnch_second['id'] ) {
	$hatnch_fail( 'Normal rotation did not resume after the lock was released.' );
}
if ( (int) $hatnch_second['id'] !== $hatnch_read_cursor() ) {
	$hatnch_fail( 'Cursor does not match the last normal selection.' );
}
if ( HATNCH_Routing::failure_summary()['total'] !== $hatnch_total_before ) {
	$hatnch_fail( 'Normal routing was counted as a failure.' );
}

// 4. Unknown reasons are ignored; no_contact is counted.
HATNCH_Routing::report_failure( 'phone=60123456789', 'global', 0 );
HATNCH_Routing::report_failure( 'no_contact', 'global', 0 );
$hatnch_summary = HATNCH_Routing::failure_summary();
if ( 1 !== ( $hatnch_summary['reasons']['no_contact'] ?? 0 ) || isset( $hatnch_summary['reasons']['phone=60123456789'] ) ) {
	$hatnch_fail( 'Reason allow-list not enforced: ' . wp_json_encode( $hatnch_summary ) );
}

// Restore fixture state for later tests in the same job.
update_option( 'hatnch_settings', $hatnch_settings_before, false );
delete_option( HATNCH_Routing::FAILURE_OPTION );

echo sprintf( "PASS: Round Robin fallback (lock wait %.2fs, fallback ID %d, cursor unchanged, counter and hook correct, rotation resumed).\n", $hatnch_elapsed, $hatnch_default_id );
