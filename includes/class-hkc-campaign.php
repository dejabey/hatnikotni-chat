<?php
/**
 * UTM campaign attribution.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Campaign {

	private const COOKIE_NAME = 'hkc_campaign';
	private const COOKIE_DAYS = 30;

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'capture' ), 1 );
	}

	public static function capture(): void {
		// UTM capture will be implemented after the attribution contract is finalized.
	}

	public static function get_attribution(): array {
		return array();
	}
}
