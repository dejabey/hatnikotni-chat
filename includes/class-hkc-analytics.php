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

		$table           = self::table_name();
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

	public static function record_click( array $data = array() ): bool {
		global $wpdb;

		$attribution = HKC_Campaign::get_attribution();
		$page_id     = absint( $data['page_id'] ?? 0 );
		$page_type   = isset( $data['page_type'] ) ? sanitize_key( $data['page_type'] ) : '';
		$device      = self::detect_device();
		$contact_id  = absint( $data['contact_id'] ?? 0 );

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'event_type'   => 'whatsapp_click',
				'created_at'   => current_time( 'mysql' ),
				'contact_id'  => $contact_id > 0 ? $contact_id : null,
				'page_id'     => $page_id > 0 ? $page_id : null,
				'page_type'   => '' !== $page_type ? $page_type : null,
				'device'      => $device,
				'utm_source'  => $attribution['utm_source'],
				'utm_medium'  => $attribution['utm_medium'],
				'utm_campaign'=> $attribution['utm_campaign'],
				'utm_term'    => $attribution['utm_term'],
				'utm_content' => $attribution['utm_content'],
			),
			array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $inserted;
	}

	private static function detect_device(): string {
		if ( ! wp_is_mobile() ) {
			return 'desktop';
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] )
			? strtolower( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
			: '';

		if ( preg_match( '/ipad|tablet|android(?!.*mobile)/i', $user_agent ) ) {
			return 'tablet';
		}

		return 'mobile';
	}
}
