<?php
/**
 * Plugin bootstrap and lifecycle coordinator.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Migration failure message for the current request.
	 *
	 * @var string
	 */
	private static string $migration_error = '';

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
		if ( ! self::maybe_migrate_legacy_data() ) {
			add_action( 'admin_notices', array( __CLASS__, 'migration_notice' ) );
			return;
		}

		if ( ! self::maybe_upgrade() ) {
			add_action( 'admin_notices', array( __CLASS__, 'migration_notice' ) );
			return;
		}

		HATNCH_Privacy::init();
		HATNCH_Campaign::init();
		HATNCH_WhatsApp::init();
		HATNCH_Shortcode::init();
		HATNCH_Admin::init();
		HATNCH_Analytics_Admin::init();

		if ( ! wp_next_scheduled( 'hatnch_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatnch_daily_cleanup' );
		}

		add_action( 'hatnch_daily_cleanup', array( 'HATNCH_Analytics', 'cleanup' ) );

		do_action( 'hatnch_loaded' );
	}

	public static function activate(): void {
		// Migration must run before defaults or new tables can mask legacy data.
		if ( ! self::maybe_migrate_legacy_data() ) {
			return;
		}

		HATNCH_Settings::install_defaults();
		HATNCH_Contacts::install_schema();
		HATNCH_Analytics::install_schema();

		if ( ! self::plugin_tables_exist() ) {
			self::set_migration_error(
				__( 'Hatnikotni Chat could not verify its database tables after installation. The database version was not advanced; check the database error log and try again.', 'hatnikotni-chat' )
			);
			return;
		}

		if ( ! update_option( 'hatnch_db_version', HATNCH_DB_VERSION ) && HATNCH_DB_VERSION !== get_option( 'hatnch_db_version', '' ) ) {
			self::set_migration_error(
				__( 'Hatnikotni Chat could not save its database version. The database version was not advanced; check the database permissions and try again.', 'hatnikotni-chat' )
			);
			return;
		}

		if ( ! wp_next_scheduled( 'hatnch_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'hatnch_daily_cleanup' );
		}
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'hatnch_daily_cleanup' );
		// Deactivation intentionally preserves plugin data.
	}

	/**
	 * Show a non-destructive migration/upgrade failure notice to administrators.
	 */
	public static function migration_notice(): void {
		if ( '' === self::$migration_error || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html__( 'Hatnikotni Chat database migration paused:', 'hatnikotni-chat' ),
			esc_html( self::$migration_error )
		);
	}

	/**
	 * Migrate legacy options and tables without deleting data on conflicts.
	 *
	 * @return bool True when migration is safe to continue.
	 */
	private static function maybe_migrate_legacy_data(): bool {
		global $wpdb;

		$legacy_prefix = sprintf( '%s%s', 'hk', 'c_' );
		$option_pairs  = array(
			$legacy_prefix . 'settings'      => 'hatnch_settings',
			$legacy_prefix . 'routing_state' => 'hatnch_routing_state',
		);

		// Preflight option conflicts before making any changes.
		foreach ( $option_pairs as $legacy_key => $new_key ) {
			$legacy_value = get_option( $legacy_key, false );
			$new_value    = get_option( $new_key, false );

			if ( false !== $legacy_value && false !== $new_value && $legacy_value !== $new_value ) {
				self::set_migration_error(
					sprintf(
						/* translators: 1: legacy option name, 2: current option name. */
						__( 'Both legacy option "%1$s" and current option "%2$s" exist with different values. Neither value was deleted or overwritten. Back up the database, reconcile the values, then retry.', 'hatnikotni-chat' ),
						$legacy_key,
						$new_key
					)
				);
				return false;
			}
		}

		$legacy_contacts = $wpdb->prefix . $legacy_prefix . 'contacts';
		$new_contacts    = $wpdb->prefix . 'hatnch_contacts';
		$legacy_events   = $wpdb->prefix . $legacy_prefix . 'events';
		$new_events      = $wpdb->prefix . 'hatnch_events';
		$table_pairs     = array(
			array( $legacy_contacts, $new_contacts ),
			array( $legacy_events, $new_events ),
		);
		$table_states    = array();

		// Preflight every table pair first so known collisions cause no writes.
		foreach ( $table_pairs as $tables ) {
			$legacy_exists = self::table_exists( $tables[0] );
			$new_exists    = self::table_exists( $tables[1] );

			if ( null === $legacy_exists || null === $new_exists ) {
				self::set_migration_error(
					__( 'The database could not determine whether legacy tables exist. No legacy data was deleted. Check database permissions/connectivity and retry.', 'hatnikotni-chat' )
				);
				return false;
			}

			if ( $legacy_exists && $new_exists ) {
				self::set_migration_error(
					sprintf(
						/* translators: 1: legacy table name, 2: current table name. */
						__( 'Both legacy table "%1$s" and current table "%2$s" exist. Automatic merging is paused to avoid losing rows or breaking event-to-contact references. Back up both tables and reconcile them before retrying.', 'hatnikotni-chat' ),
						$tables[0],
						$tables[1]
					)
				);
				return false;
			}

			$table_states[] = array(
				'legacy' => $tables[0],
				'new'    => $tables[1],
				'rename' => $legacy_exists && ! $new_exists,
			);
		}

		// Rename only when the destination is absent; check every DB result.
		foreach ( $table_states as $state ) {
			if ( ! $state['rename'] ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Intentional migration of plugin-owned tables; identifiers are prepared.
			$result = $wpdb->query( $wpdb->prepare( 'RENAME TABLE %i TO %i', $state['legacy'], $state['new'] ) );

			if ( false === $result || self::table_exists( $state['new'] ) !== true || self::table_exists( $state['legacy'] ) !== false ) {
				self::set_migration_error(
					sprintf(
						/* translators: 1: legacy table name, 2: current table name. */
						__( 'Could not safely rename "%1$s" to "%2$s". The database version was not advanced and remaining legacy data was preserved. Correct the database error and retry.', 'hatnikotni-chat' ),
						$state['legacy'],
						$state['new']
					)
				);
				return false;
			}
		}

		// Copy options only after table migration has succeeded; verify before cleanup.
		foreach ( $option_pairs as $legacy_key => $new_key ) {
			$legacy_value = get_option( $legacy_key, false );
			if ( false === $legacy_value ) {
				continue;
			}

			if ( false === get_option( $new_key, false ) ) {
				add_option( $new_key, $legacy_value, '', false );
			}

			if ( get_option( $new_key, false ) !== $legacy_value ) {
				self::set_migration_error(
					sprintf(
						/* translators: 1: legacy option name, 2: current option name. */
						__( 'Could not verify the copied value from "%1$s" to "%2$s". The legacy option was preserved; resolve the conflict and retry.', 'hatnikotni-chat' ),
						$legacy_key,
						$new_key
					)
				);
				return false;
			}
		}

		// Delete legacy options only after their replacements have been verified.
		foreach ( $option_pairs as $legacy_key => $new_key ) {
			if ( false !== get_option( $legacy_key, false ) && get_option( $new_key, false ) === get_option( $legacy_key, false ) ) {
				delete_option( $legacy_key );
			}
		}

		$legacy_db_version = get_option( $legacy_prefix . 'db_version', false );
		if ( false !== $legacy_db_version ) {
			delete_option( $legacy_prefix . 'db_version' );
		}

		wp_clear_scheduled_hook( $legacy_prefix . 'daily_cleanup' );

		return true;
	}

	/**
	 * Determine whether a plugin-owned table exists.
	 *
	 * @param string $table Table name.
	 * @return bool|null True when present, false when absent, null on query error.
	 */
	private static function table_exists( string $table ): ?bool {
		global $wpdb;

		$wpdb->last_error = '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional schema inspection for plugin-owned tables.
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );

		if ( '' !== $wpdb->last_error ) {
			return null;
		}

		return $found === $table;
	}

	/**
	 * Verify both tables required by the current plugin version.
	 */
	private static function plugin_tables_exist(): bool {
		global $wpdb;

		$contacts_columns = array(
			'id',
			'name',
			'phone',
			'role',
			'description',
			'status',
			'weight',
			'sort_order',
			'created_at',
			'updated_at',
		);
		$events_columns   = array(
			'id',
			'event_type',
			'created_at',
			'contact_id',
			'page_id',
			'page_type',
			'device',
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_term',
			'utm_content',
		);

		$contacts_table = $wpdb->prefix . 'hatnch_contacts';
		$events_table   = $wpdb->prefix . 'hatnch_events';

		$contacts_indexes = array(
			'PRIMARY'    => array( 'id' ),
			'status'     => array( 'status' ),
			'sort_order' => array( 'sort_order' ),
		);
		$events_indexes   = array(
			'PRIMARY'       => array( 'id' ),
			'created_at'    => array( 'created_at' ),
			'contact_date'  => array( 'contact_id', 'created_at' ),
			'page_date'     => array( 'page_id', 'page_type', 'created_at' ),
			'campaign_date' => array( 'utm_campaign', 'created_at' ),
		);

		return self::table_has_columns( $contacts_table, $contacts_columns )
			&& self::table_has_columns( $events_table, $events_columns )
			&& self::table_has_auto_increment_primary_id( $contacts_table )
			&& self::table_has_auto_increment_primary_id( $events_table )
			&& self::table_has_required_indexes( $contacts_table, $contacts_indexes )
			&& self::table_has_required_indexes( $events_table, $events_indexes );
	}

	/**
	 * Verify the table ID is an unsigned bigint AUTO_INCREMENT primary-key column.
	 *
	 * @param string $table Plugin-owned table name.
	 * @return bool True when the ID column has the required definition.
	 */
	private static function table_has_auto_increment_primary_id( string $table ): bool {
		global $wpdb;

		$wpdb->last_error = '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional schema verification for plugin-owned tables.
		$columns = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ), ARRAY_A );

		if ( '' !== $wpdb->last_error || ! is_array( $columns ) ) {
			return false;
		}

		foreach ( $columns as $column ) {
			if ( ! isset( $column['Field'], $column['Type'], $column['Null'], $column['Extra'] ) || 'id' !== $column['Field'] ) {
				continue;
			}

			$type = strtolower( (string) $column['Type'] );
			return (bool) preg_match( '/^bigint(?:\\(\\d+\\))? unsigned$/', $type )
				&& 'NO' === $column['Null']
				&& false !== stripos( (string) $column['Extra'], 'auto_increment' );
		}

		return false;
	}

	/**
	 * Verify that a plugin-owned table contains every required column.
	 *
	 * @param string   $table            Table name.
	 * @param string[] $required_columns Required column names.
	 * @return bool True when every required column is present.
	 */
	private static function table_has_columns( string $table, array $required_columns ): bool {
		global $wpdb;

		$wpdb->last_error = '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional schema verification for plugin-owned tables.
		$columns = $wpdb->get_col( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ), 0 );

		if ( '' !== $wpdb->last_error || ! is_array( $columns ) ) {
			return false;
		}

		return array() === array_diff( $required_columns, $columns );
	}

	/**
	 * Verify required indexes and their ordered columns.
	 *
	 * @param string                  $table            Table name.
	 * @param array<string, string[]> $required_indexes Required index names and ordered columns.
	 * @return bool True when every required index matches.
	 */
	private static function table_has_required_indexes( string $table, array $required_indexes ): bool {
		global $wpdb;

		$wpdb->last_error = '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional schema verification for plugin-owned tables.
		$rows = $wpdb->get_results( $wpdb->prepare( 'SHOW INDEX FROM %i', $table ), ARRAY_A );

		if ( '' !== $wpdb->last_error || ! is_array( $rows ) ) {
			return false;
		}

		$actual_indexes = array();
		foreach ( $rows as $row ) {
			if ( ! isset( $row['Key_name'], $row['Column_name'], $row['Seq_in_index'] ) ) {
				return false;
			}

			$key_name = (string) $row['Key_name'];
			$position = absint( $row['Seq_in_index'] );
			if ( '' === $key_name || $position < 1 ) {
				return false;
			}

			$actual_indexes[ $key_name ][ $position ] = (string) $row['Column_name'];
		}

		foreach ( $required_indexes as $key_name => $required_columns ) {
			if ( ! isset( $actual_indexes[ $key_name ] ) ) {
				return false;
			}

			ksort( $actual_indexes[ $key_name ] );
			if ( array_values( $actual_indexes[ $key_name ] ) !== $required_columns ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Store a safe failure message for the current request.
	 *
	 * @param string $message Administrator-facing explanation.
	 */
	private static function set_migration_error( string $message ): void {
		self::$migration_error = $message;
	}

	/**
	 * Apply the current schema only after legacy migration has completed.
	 *
	 * @return bool True when the current schema is present.
	 */
	private static function maybe_upgrade(): bool {
		$installed_version = get_option( 'hatnch_db_version', '' );

		if ( HATNCH_DB_VERSION === $installed_version && self::plugin_tables_exist() ) {
			return true;
		}

		HATNCH_Contacts::install_schema();
		HATNCH_Analytics::install_schema();

		if ( ! self::plugin_tables_exist() ) {
			self::set_migration_error(
				__( 'Hatnikotni Chat could not verify its database tables after an upgrade. The database version was not advanced; check database errors and retry.', 'hatnikotni-chat' )
			);
			return false;
		}

		if ( ! update_option( 'hatnch_db_version', HATNCH_DB_VERSION ) && HATNCH_DB_VERSION !== get_option( 'hatnch_db_version', '' ) ) {
			self::set_migration_error(
				__( 'Hatnikotni Chat could not save its database version. The database version was not advanced; check database permissions and retry.', 'hatnikotni-chat' )
			);
			return false;
		}

		return true;
	}
}
