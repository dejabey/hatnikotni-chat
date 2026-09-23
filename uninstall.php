<?php
/**
 * Hatnikotni Chat uninstall handler.
 *
 * Data is removed only when WordPress explicitly requests uninstall.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$contacts_table = $wpdb->prefix . 'hkc_contacts';
$events_table = $wpdb->prefix . 'hkc_events';

$wpdb->query( "DROP TABLE IF EXISTS {$contacts_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$events_table}" );

delete_option( 'hkc_settings' );
delete_option( 'hkc_db_version' );
