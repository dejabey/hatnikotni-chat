<?php
/**
 * WhatsApp interaction analytics.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Analytics {

	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'hkc_events';
	}

	public static function install_schema(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_type varchar(40) NOT NULL,
			created_at datetime NOT NULL,
			contact_id bigint(20) unsigned NULL,
			page_id bigint(20) unsigned NULL,
			page_type varchar(40) NULL,
			device varchar(20) NULL,
			utm_source varchar(100) NULL,
			utm_medium varchar(100) NULL,
			utm_campaign varchar(150) NULL,
			utm_term varchar(150) NULL,
			utm_content varchar(150) NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY contact_date (contact_id, created_at),
			KEY page_date (page_id, page_type, created_at),
			KEY campaign_date (utm_campaign, created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
