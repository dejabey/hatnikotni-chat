<?php
/**
 * WordPress admin interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Admin {

	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		// Admin UI will be implemented after the data contracts are finalized.
	}
}
