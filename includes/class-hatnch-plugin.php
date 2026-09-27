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
