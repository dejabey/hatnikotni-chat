<?php
/**
 * Prepare disposable WordPress contacts for the concurrent routing integration test.
 *
 * @package Hatnikotni_Chat
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "Run this test through WP-CLI.\n" );
	exit( 1 );
}

$contacts = array(
	array(
		'name'        => 'Round Robin CI A',
		'phone'       => '60111111111',
		'role'        => 'general_enquiries',
		'description' => 'Disposable concurrency fixture',
		'status'      => 1,
		'weight'      => 1,
		'sort_order'  => 1,
	),
	array(
		'name'        => 'Round Robin CI B',
		'phone'       => '60111111112',
		'role'        => 'general_enquiries',
		'description' => 'Disposable concurrency fixture',
		'status'      => 1,
		'weight'      => 1,
		'sort_order'  => 2,
	),
);

foreach ( $contacts as $contact_data ) {
	$result = HATNCH_Contacts::save( $contact_data );
	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, $result->get_error_message() . "\n" );
		exit( 1 );
	}
}

$settings                   = HATNCH_Settings::all();
$settings['routing_method'] = 'round_robin';
update_option( 'hatnch_settings', $settings, false );

// This reset is confined to the disposable GitHub Actions WordPress database.
delete_option( 'hatnch_routing_state' );

echo "Prepared two disposable active contacts for Round Robin concurrency test.\n";
