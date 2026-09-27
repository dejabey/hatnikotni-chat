<?php
/**
 * Plugin bootstrap and lifecycle coordinator.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATC_Plugin {

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

		HATC_Privacy::init();
		HATC_Campaign::init();
		HATC_WhatsApp::init();
		HATC_Shortcode::init();
		HATC_Admin::init();
		HATC_Analytics_Admin::init();

		if ( ! wp_next_scheduled( 'hatc_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatc_daily_cleanup' );
		}

		add_action( 'hatc_daily_cleanup', array( 'HATC_Analytics', 'cleanup' ) );

		do_action( 'hatc_loaded' );
	}

	public static function activate(): void {
		HATC_Settings::install_defaults();
		HATC_Contacts::install_schema();
		HATC_Analytics::install_schema();

		if ( ! wp_next_scheduled( 'hatc_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatc_daily_cleanup' );
		}

		update_option( 'hatc_db_version', HATC_DB_VERSION );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'hatc_daily_cleanup' );
		// Deactivation intentionally preserves plugin data.
	}

	private static function maybe_upgrade(): void {
		$installed_version = get_option( 'hatc_db_version', '' );

		if ( HATC_DB_VERSION === $installed_version ) {
			return;
		}

		HATC_Contacts::install_schema();
		HATC_Analytics::install_schema();
		update_option( 'hatc_db_version', HATC_DB_VERSION );
	}
}
