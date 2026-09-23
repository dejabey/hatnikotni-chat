<?php
/**
 * Plugin bootstrap and lifecycle coordinator.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Plugin {

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

		HKC_Privacy::init();
		HKC_Campaign::init();
		HKC_WhatsApp::init();
		HKC_Shortcode::init();
		HKC_Admin::init();
		HKC_Analytics_Admin::init();

		if ( ! wp_next_scheduled( 'hkc_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hkc_daily_cleanup' );
		}

		add_action( 'hkc_daily_cleanup', array( 'HKC_Analytics', 'cleanup' ) );

		do_action( 'hkc_loaded' );
	}

	public static function activate(): void {
		HKC_Settings::install_defaults();
		HKC_Contacts::install_schema();
		HKC_Analytics::install_schema();

		if ( ! wp_next_scheduled( 'hkc_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hkc_daily_cleanup' );
		}

		update_option( 'hkc_db_version', HKC_DB_VERSION );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'hkc_daily_cleanup' );
		// Deactivation intentionally preserves plugin data.
	}

	private static function maybe_upgrade(): void {
		$installed_version = get_option( 'hkc_db_version', '' );

		if ( HKC_DB_VERSION === $installed_version ) {
			return;
		}

		HKC_Contacts::install_schema();
		HKC_Analytics::install_schema();
		update_option( 'hkc_db_version', HKC_DB_VERSION );
	}
}
