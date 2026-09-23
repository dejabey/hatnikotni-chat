<?php
/**
 * Plugin settings storage.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Settings {

	private const OPTION_KEY = 'hkc_settings';

	public static function install_defaults(): void {
		if ( false !== get_option( self::OPTION_KEY, false ) ) {
			return;
		}

		add_option(
			self::OPTION_KEY,
			array(
				'enabled'         => true,
				'default_contact' => 0,
				'default_message' => '',
				'button_label'    => 'WhatsApp Kami',
				'button_position' => 'right',
				'show_desktop'   => true,
				'show_mobile'    => true,
				'routing_method' => 'direct',
			)
		);
	}

	public static function get( string $key, mixed $default = null ): mixed {
		$settings = get_option( self::OPTION_KEY, array() );

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	public static function all(): array {
		$settings = get_option( self::OPTION_KEY, array() );

		return is_array( $settings ) ? $settings : array();
	}
}
