<?php
/**
 * Contact storage and validation.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Contacts {

	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'hkc_contacts';
	}

	public static function install_schema(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			phone varchar(20) NOT NULL,
			role varchar(100) NOT NULL DEFAULT '',
			description varchar(255) NOT NULL DEFAULT '',
			status tinyint(1) unsigned NOT NULL DEFAULT 1,
			weight smallint(5) unsigned NOT NULL DEFAULT 1,
			sort_order int(10) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY sort_order (sort_order)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	public static function normalize_phone( string $phone ): string {
		return preg_replace( '/\D+/', '', $phone ) ?? '';
	}
}
