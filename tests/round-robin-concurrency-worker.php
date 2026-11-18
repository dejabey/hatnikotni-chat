<?php
/**
 * Resolve one Round Robin contact for a concurrent integration-test worker.
 *
 * @package Hatnikotni_Chat
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "Run this test through WP-CLI.\n" );
	exit( 1 );
}

$routing = new HATNCH_Routing();
$contact = $routing->resolve();

if ( ! is_array( $contact ) || empty( $contact['id'] ) ) {
	fwrite( STDERR, "Round Robin did not return a contact.\n" );
	exit( 1 );
}

echo absint( $contact['id'] ) . "\n";
