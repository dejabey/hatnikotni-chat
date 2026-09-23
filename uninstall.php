<?php
/**
 * Hatnikotni Chat uninstall handler.
 *
 * Data is removed only when WordPress explicitly requests uninstall.
 *
 * @package Hatnikotni_Chat
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$contacts_table = $wpdb->prefix . 'hkc_contacts';
$events_table   = $wpdb->prefix . 'hkc_events';

$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $contacts_table ) );
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $events_table ) );

delete_option( 'hkc_settings' );
delete_option( 'hkc_db_version' );
delete_option( 'hkc_routing_state' );
