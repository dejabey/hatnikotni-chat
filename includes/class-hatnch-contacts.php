<?php
/**
 * Contact storage, validation and CRUD.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Contacts {

	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'hatnch_contacts';
	}

	public static function install_schema(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table_name();
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

	public static function get( int $id ): ?array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE id = %d LIMIT 1',
				self::table_name(),
				$id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	public static function get_active(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE status = 1 ORDER BY sort_order ASC, id ASC',
				self::table_name()
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	public static function get_all(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY sort_order ASC, id ASC',
				self::table_name()
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	public static function save( array $data, int $id = 0 ): int|WP_Error {
		global $wpdb;

		$name        = sanitize_text_field( $data['name'] ?? '' );
		$raw_phone   = (string) ( $data['phone'] ?? '' );
		$phone       = self::normalize_phone( $raw_phone );
		$role        = sanitize_text_field( $data['role'] ?? '' );
		$description = sanitize_text_field( $data['description'] ?? '' );
		$status      = ! empty( $data['status'] ) ? 1 : 0;
		$weight      = min( 65535, max( 1, absint( $data['weight'] ?? 1 ) ) );
		$sort_order  = min( 4294967295, absint( $data['sort_order'] ?? 0 ) );

		if ( '' === $name ) {
			return new WP_Error( 'missing_name', __( 'Contact name is required.', 'hatnikotni-chat' ) );
		}

		if ( ! preg_match( '/^[0-9]{8,20}$/', $raw_phone ) || $phone !== $raw_phone ) {
			return new WP_Error( 'invalid_phone', __( 'Enter a valid WhatsApp number using international digits only, without +, spaces or hyphens.', 'hatnikotni-chat' ) );
		}

		$now   = current_time( 'mysql' );
		$table = self::table_name();

		if ( $id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
			$updated = $wpdb->update(
				$table,
				array(
					'name'        => $name,
					'phone'       => $phone,
					'role'        => $role,
					'description' => $description,
					'status'      => $status,
					'weight'      => $weight,
					'sort_order'  => $sort_order,
					'updated_at'  => $now,
				),
				array( 'id' => $id ),
				array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s' ),
				array( '%d' )
			);

			if ( false === $updated ) {
				return new WP_Error( 'db_update_failed', __( 'The contact could not be updated.', 'hatnikotni-chat' ) );
			}

			return $id;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
		$inserted = $wpdb->insert(
			$table,
			array(
				'name'        => $name,
				'phone'       => $phone,
				'role'        => $role,
				'description' => $description,
				'status'      => $status,
				'weight'      => $weight,
				'sort_order'  => $sort_order,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_insert_failed', __( 'The contact could not be created.', 'hatnikotni-chat' ) );
		}

		return (int) $wpdb->insert_id;
	}

	public static function set_status( int $id, bool $active ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional operation on plugin-owned custom tables; queries are prepared or use wpdb CRUD APIs.
		return false !== $wpdb->update(
			self::table_name(),
			array(
				'status'     => $active ? 1 : 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%d', '%s' ),
			array( '%d' )
		);
	}
}
