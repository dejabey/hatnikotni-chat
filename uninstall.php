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

$hatnch_contacts_table = $wpdb->prefix . 'hatnch_contacts';
$hatnch_events_table   = $wpdb->prefix . 'hatnch_events';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall must drop plugin-owned tables; table identifiers are passed through wpdb::prepare with the identifier placeholder.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $hatnch_contacts_table ) );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall must drop plugin-owned tables; table identifiers are passed through wpdb::prepare with the identifier placeholder.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $hatnch_events_table ) );

$hatnch_legacy_prefix         = sprintf( '%s%s', 'hk', 'c_' );
$hatnch_legacy_contacts_table = $wpdb->prefix . $hatnch_legacy_prefix . 'contacts';
$hatnch_legacy_events_table   = $wpdb->prefix . $hatnch_legacy_prefix . 'events';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall must drop plugin-owned tables; table identifiers are passed through wpdb::prepare with the identifier placeholder.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $hatnch_legacy_contacts_table ) );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall must drop plugin-owned tables; table identifiers are passed through wpdb::prepare with the identifier placeholder.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $hatnch_legacy_events_table ) );

delete_option( 'hatnch_settings' );
delete_option( 'hatnch_db_version' );
delete_option( 'hatnch_routing_state' );
delete_option( $hatnch_legacy_prefix . 'settings' );
delete_option( $hatnch_legacy_prefix . 'db_version' );
delete_option( $hatnch_legacy_prefix . 'routing_state' );
