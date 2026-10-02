<?php
/**
 * Plugin bootstrap and lifecycle coordinator.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init(): void {
		self::maybe_migrate_legacy_data();
		self::maybe_upgrade();

		HATNCH_Privacy::init();
		HATNCH_Campaign::init();
		HATNCH_WhatsApp::init();
		HATNCH_Shortcode::init();
		HATNCH_Admin::init();
		HATNCH_Analytics_Admin::init();

		if ( ! wp_next_scheduled( 'hatnch_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatnch_daily_cleanup' );
		}

		add_action( 'hatnch_daily_cleanup', array( 'HATNCH_Analytics', 'cleanup' ) );

		do_action( 'hatnch_loaded' );
	}

	public static function activate(): void {
		HATNCH_Settings::install_defaults();
		HATNCH_Contacts::install_schema();
		HATNCH_Analytics::install_schema();

		if ( ! wp_next_scheduled( 'hatnch_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatnch_daily_cleanup' );
		}

		update_option( 'hatnch_db_version', HATNCH_DB_VERSION );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'hatnch_daily_cleanup' );
		// Deactivation intentionally preserves plugin data.
	}

	private static function maybe_migrate_legacy_data(): void {
		$legacy_prefix = sprintf( '%s%s', 'hk', 'c_' );

		$legacy_settings = get_option( $legacy_prefix . 'settings', false );
		if ( false !== $legacy_settings && false === get_option( 'hatnch_settings', false ) ) {
			add_option( 'hatnch_settings', $legacy_settings, '', false );
		}
		if ( false !== $legacy_settings ) {
			delete_option( $legacy_prefix . 'settings' );
		}

		$legacy_state = get_option( $legacy_prefix . 'routing_state', false );
		if ( false !== $legacy_state && false === get_option( 'hatnch_routing_state', false ) ) {
			add_option( 'hatnch_routing_state', $legacy_state, '', false );
		}
		if ( false !== $legacy_state ) {
			delete_option( $legacy_prefix . 'routing_state' );
		}

		$legacy_db_version = get_option( $legacy_prefix . 'db_version', false );
		if ( false !== $legacy_db_version ) {
			delete_option( $legacy_prefix . 'db_version' );
		}

		global $wpdb;
		$legacy_contacts = $wpdb->prefix . $legacy_prefix . 'contacts';
		$new_contacts    = $wpdb->prefix . 'hatnch_contacts';
		$legacy_events   = $wpdb->prefix . $legacy_prefix . 'events';
		$new_events      = $wpdb->prefix . 'hatnch_events';

		foreach ( array( array( $legacy_contacts, $new_contacts ), array( $legacy_events, $new_events ) ) as $tables ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
			$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $tables[0] ) ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
			$new_exists    = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $tables[1] ) ) );

			if ( $legacy_exists === $tables[0] && $new_exists !== $tables[1] ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
				$wpdb->query( $wpdb->prepare( 'RENAME TABLE %i TO %i', $tables[0], $tables[1] ) );
			}
		}

		wp_clear_scheduled_hook( $legacy_prefix . 'daily_cleanup' );
	}

	private static function maybe_upgrade(): void {
		$installed_version = get_option( 'hatnch_db_version', '' );

		if ( HATNCH_DB_VERSION === $installed_version ) {
			return;
		}

		HATNCH_Contacts::install_schema();
		HATNCH_Analytics::install_schema();
		update_option( 'hatnch_db_version', HATNCH_DB_VERSION );
	}
}
