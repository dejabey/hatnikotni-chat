<?php
/**
 * WhatsApp interaction analytics.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Analytics {

	private const RETENTION_DAYS = 180;

	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'hatnch_events';
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
			contact_reference bigint(20) unsigned NULL,
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
			KEY contact_reference_date (contact_reference, created_at),
			KEY page_date (page_id, page_type, created_at),
			KEY campaign_date (utm_campaign, created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	public static function record_click( array $data = array() ): bool {
		if ( ! HATNCH_Privacy::has_analytics_consent() ) {
			return false;
		}

		global $wpdb;

		$attribution = HATNCH_Campaign::get_attribution();
		$page_id     = absint( $data['page_id'] ?? 0 );
		$page_type   = isset( $data['page_type'] ) ? sanitize_key( $data['page_type'] ) : '';
		$device      = self::detect_device();
		$contact_id  = absint( $data['contact_id'] ?? 0 );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'event_type'   => 'whatsapp_click',
				'created_at'   => current_time( 'mysql' ),
				'contact_id'        => $contact_id > 0 ? $contact_id : null,
				'contact_reference' => $contact_id > 0 ? $contact_id : null,
				'page_id'      => $page_id > 0 ? $page_id : null,
				'page_type'    => '' !== $page_type ? $page_type : null,
				'device'       => $device,
				'utm_source'   => $attribution['utm_source'],
				'utm_medium'   => $attribution['utm_medium'],
				'utm_campaign' => $attribution['utm_campaign'],
				'utm_term'     => $attribution['utm_term'],
				'utm_content'  => $attribution['utm_content'],
			),
			array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return false !== $inserted;
	}

	public static function get_summary( int $days = 30 ): array {
		global $wpdb;

		$days  = min( self::RETENTION_DAYS, max( 1, absint( $days ) ) );
		$since = wp_date( 'Y-m-d H:i:s', time() - ( DAY_IN_SECONDS * $days ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE event_type = %s AND created_at >= %s',
				self::table_name(),
				'whatsapp_click',
				$since
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$by_device = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT device, COUNT(*) AS total FROM %i WHERE event_type = %s AND created_at >= %s GROUP BY device ORDER BY total DESC',
				self::table_name(),
				'whatsapp_click',
				$since
			),
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$by_contact = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT COALESCE(contact_reference, contact_id) AS contact_reference, COUNT(*) AS total FROM %i WHERE event_type = %s AND created_at >= %s GROUP BY COALESCE(contact_reference, contact_id) ORDER BY total DESC',
				self::table_name(),
				'whatsapp_click',
				$since
			),
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$by_campaign = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT utm_campaign, COUNT(*) AS total FROM %i WHERE event_type = %s AND created_at >= %s AND utm_campaign IS NOT NULL AND utm_campaign <> %s GROUP BY utm_campaign ORDER BY total DESC',
				self::table_name(),
				'whatsapp_click',
				$since,
				''
			),
			ARRAY_A
		);

		return array(
			'days'        => $days,
			'total'       => $total,
			'by_device'   => is_array( $by_device ) ? $by_device : array(),
			'by_contact'  => is_array( $by_contact ) ? $by_contact : array(),
			'by_campaign' => is_array( $by_campaign ) ? $by_campaign : array(),
		);
	}

	/** Return a validated inclusive date range as local SQL boundaries. */
	public static function validate_date_range( string $start, string $end ): array|WP_Error {
		$start_date = DateTimeImmutable::createFromFormat( '!Y-m-d', $start, wp_timezone() );
		$end_date   = DateTimeImmutable::createFromFormat( '!Y-m-d', $end, wp_timezone() );
		$errors     = DateTimeImmutable::getLastErrors();

		if ( ! $start_date || ! $end_date || ( is_array( $errors ) && ( $errors['warning_count'] || $errors['error_count'] ) ) || $start_date->format( 'Y-m-d' ) !== $start || $end_date->format( 'Y-m-d' ) !== $end ) {
			return new WP_Error( 'invalid_date_range', __( 'Choose valid start and end dates.', 'hatnikotni-chat' ) );
		}

		if ( $start_date > $end_date ) {
			return new WP_Error( 'invalid_date_range', __( 'The start date must not be later than the end date.', 'hatnikotni-chat' ) );
		}

		$tomorrow = $end_date->modify( '+1 day' );
		return array(
			'start' => $start_date->format( 'Y-m-d H:i:s' ),
			'end'   => $tomorrow->format( 'Y-m-d H:i:s' ),
		);
	}

	/** Count retained click events within a validated range. */
	public static function count_events( ?array $range = null ): int|WP_Error {
		global $wpdb;
		$where = '1=1';
		$args  = array();
		if ( null !== $range ) {
			$where .= ' AND created_at >= %s AND created_at < %s';
			$args[] = $range['start'];
			$args[] = $range['end'];
		}
		$sql = $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ' . $where, array_merge( array( self::table_name() ), $args ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Count plugin-owned analytics events for the explicit data-management operation.
		$count = $wpdb->get_var( $sql );
		return '' !== (string) $wpdb->last_error ? new WP_Error( 'analytics_count_failed', __( 'Could not count analytics records.', 'hatnikotni-chat' ) ) : (int) $count;
	}

	/** Delete click events by inclusive local dates, or all event types when range is null. */
	public static function delete_events( ?array $range = null ): int|WP_Error {
		global $wpdb;
		if ( null === $range ) {
			$sql = $wpdb->prepare( 'DELETE FROM %i', self::table_name() );
		} else {
			$sql = $wpdb->prepare( 'DELETE FROM %i WHERE created_at >= %s AND created_at < %s', self::table_name(), $range['start'], $range['end'] );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional deletion of plugin-owned analytics rows after admin capability, nonce and confirmation checks.
		$deleted = $wpdb->query( $sql );
		return false === $deleted ? new WP_Error( 'analytics_delete_failed', __( 'Analytics records could not be deleted.', 'hatnikotni-chat' ) ) : (int) $deleted;
	}

	/** Return safe aggregate rows for a report export. */
	public static function get_export_rows( int $days ): array {
		$summary = self::get_summary( $days );
		$rows    = array(
			array( 'section' => 'Report', 'value' => sprintf( __( 'Last %d days', 'hatnikotni-chat' ), $days ), 'clicks' => '' ),
			array( 'section' => 'Summary', 'value' => 'WhatsApp clicks', 'clicks' => $summary['total'] ),
		);
		foreach ( $summary['by_device'] as $row ) {
			$rows[] = array( 'section' => 'By Device', 'value' => (string) ( $row['device'] ?? __( 'Unattributed', 'hatnikotni-chat' ) ), 'clicks' => (int) $row['total'] );
		}
		foreach ( $summary['by_contact'] as $row ) {
			$ref     = absint( $row['contact_reference'] ?? 0 );
			$contact = $ref ? HATNCH_Contacts::get( $ref ) : null;
			$label   = $contact['name'] ?? ( $ref ? sprintf( __( 'Removed contact #%d', 'hatnikotni-chat' ), $ref ) : __( 'Unknown / removed', 'hatnikotni-chat' ) );
			$rows[]  = array( 'section' => 'By Contact', 'value' => $label, 'clicks' => (int) $row['total'] );
		}
		foreach ( $summary['by_campaign'] as $row ) {
			$rows[] = array( 'section' => 'By Campaign', 'value' => (string) $row['utm_campaign'], 'clicks' => (int) $row['total'] );
		}
		return $rows;
	}

	public static function cleanup(): int {
		global $wpdb;

		$cutoff = wp_date( 'Y-m-d H:i:s', time() - ( DAY_IN_SECONDS * self::RETENTION_DAYS ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on the plugin-owned analytics table; values are prepared or passed through wpdb CRUD APIs.
		$result = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM %i WHERE created_at < %s',
				self::table_name(),
				$cutoff
			)
		);

		return false === $result ? 0 : (int) $result;
	}

	private static function detect_device(): string {
		if ( ! wp_is_mobile() ) {
			return 'desktop';
		}

		$user_agent = '';
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );
		}

		if ( preg_match( '/ipad|tablet|android(?!.*mobile)/i', $user_agent ) ) {
			return 'tablet';
		}

		return 'mobile';
	}
}
