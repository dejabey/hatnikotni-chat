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

$contacts_table = $wpdb->prefix . 'hatnch_contacts';
$events_table   = $wpdb->prefix . 'hatnch_events';

$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $contacts_table ) );
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $events_table ) );

$legacy_prefix         = sprintf( '%s%s', 'hk', 'c_' );
$legacy_contacts_table = $wpdb->prefix . $legacy_prefix . 'contacts';
$legacy_events_table   = $wpdb->prefix . $legacy_prefix . 'events';

$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $legacy_contacts_table ) );
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $legacy_events_table ) );

delete_option( 'hatnch_settings' );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_routing_state' );
delete_option( $legacy_prefix . 'settings' );
delete_option( $legacy_prefix . 'db_version' );
delete_option( $legacy_prefix . 'routing_state' );
