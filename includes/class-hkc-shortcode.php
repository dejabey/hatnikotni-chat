<?php
/**
 * WhatsApp shortcode interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Shortcode {

	public static function init(): void {
		add_shortcode( 'hatnikotni_chat', array( __CLASS__, 'render' ) );
	}

	public static function render( array $atts = array() ): string {
		return '';
	}
}
