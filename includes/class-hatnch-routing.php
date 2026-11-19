<?php
/**
 * Contact routing engine.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Routing {

	private const STATE_OPTION = 'hatnch_routing_state';

	/** PII-free failure counter (counts and timestamps only). */
	public const FAILURE_OPTION = 'hatnch_routing_failures';

	/** Reasons that may be recorded. Anything else is ignored. */
	public const FAILURE_REASONS = array(
		'lock_unavailable',
		'state_read_failed',
		'state_write_failed',
		'state_verify_failed',
		'no_contact',
	);

	public function resolve(): ?array {
		$method = HATNCH_Settings::get( 'routing_method', 'direct' );

		switch ( $method ) {
			case 'random':
				$contact = $this->resolve_random();
				break;

			case 'round_robin':
				$contact = $this->resolve_round_robin();
				break;

			case 'direct':
			default:
				$contact = $this->resolve_direct();
				break;
		}

		return apply_filters( 'hatnch_resolved_contact', $contact, $method );
	}

	/** Resolve an allow-listed support role, preserving the global fallback. */
	public function resolve_purpose( string $purpose ): ?array {
		$routes = apply_filters(
			'hatnch_purpose_role_routes',
			array(
				'general_enquiries'     => 'general_enquiries',
				'listing_assistance'    => 'business_support',
				'business_partnerships' => 'partnerships',
				'technical_question'    => 'business_support',
			)
		);

		if ( ! is_array( $routes ) ) {
			return $this->resolve();
		}

		$routes = array_filter( $routes, 'is_string' );
		if ( ! isset( $routes[ $purpose ] ) ) {
			return $this->resolve();
		}

		$map  = HATNCH_Settings::get( 'purpose_roles', array() );
		$role = is_array( $map ) && isset( $map[ $purpose ] ) ? sanitize_key( $map[ $purpose ] ) : $routes[ $purpose ];

		$allowed_roles = apply_filters(
			'hatnch_allowed_contact_roles',
			array( 'general_enquiries', 'business_support', 'partnerships' )
		);
		if ( ! is_array( $allowed_roles ) ) {
			$allowed_roles = array();
		}

		$allowed_roles = array_map( 'sanitize_key', $allowed_roles );
		if ( ! in_array( $role, $allowed_roles, true ) ) {
			return $this->resolve();
		}

		$eligible = array_values(
			array_filter(
				HATNCH_Contacts::get_active(),
				static function ( $contact ) use ( $role ) {
					return isset( $contact['role'] ) && $role === $contact['role'];
				}
			)
		);

		if ( empty( $eligible ) ) {
			return $this->resolve();
		}

		$method = HATNCH_Settings::get( 'routing_method', 'direct' );
		if ( 'random' === $method ) {
			return $eligible[ wp_rand( 0, count( $eligible ) - 1 ) ];
		}

		if ( 'round_robin' === $method ) {
			// A lane that empties inside the lock falls back to global routing.
			return $this->resolve_round_robin_from_set( $eligible, 'hatnch_rr_' . $role, false ) ?? $this->resolve();
		}

		$default_id = absint( HATNCH_Settings::get( 'default_contact', 0 ) );
		foreach ( $eligible as $contact ) {
			if ( $default_id === (int) $contact['id'] ) {
				return $contact;
			}
		}

		return $eligible[0];
	}

	private function resolve_direct(): ?array {
		$contact_id = absint( HATNCH_Settings::get( 'default_contact', 0 ) );

		if ( $contact_id < 1 ) {
			return null;
		}

		$contact = HATNCH_Contacts::get( $contact_id );

		return $contact && 1 === (int) $contact['status'] ? $contact : null;
	}

	private function resolve_random(): ?array {
		$contacts = HATNCH_Contacts::get_active();

		if ( empty( $contacts ) ) {
			return null;
		}

		return $contacts[ wp_rand( 0, count( $contacts ) - 1 ) ];
	}

	private function resolve_round_robin(): ?array {
		return $this->resolve_round_robin_from_set( HATNCH_Contacts::get_active(), self::STATE_OPTION, true );
	}

	/**
	 * Serialize selection and cursor persistence for one routing lane.
	 *
	 * Requires MySQL/MariaDB advisory lock support. When the lock, the cursor
	 * read, the cursor write or the read-back verification fails, the visitor
	 * is still routed: a fallback contact from the same eligible set is
	 * returned without moving the cursor, the failure is counted and
	 * `hatnch_routing_failure` fires. Null is returned only when no contact is
	 * eligible.
	 *
	 * @param array  $contacts Active contacts eligible for this lane.
	 * @param string $state_key Cursor option.
	 * @param bool   $structured_state Whether the option uses the legacy array shape.
	 * @return array|null
	 */
	private function resolve_round_robin_from_set( array $contacts, string $state_key, bool $structured_state ): ?array {
		global $wpdb;

		if ( empty( $contacts ) ) {
			return null;
		}

		$lock_name = 'hatnch_rr_' . substr( hash( 'sha256', DB_NAME . '|' . $wpdb->prefix . '|' . $state_key ), 0, 48 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- MySQL advisory lock; no WordPress API exists.
		$acquired = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 5 )', $lock_name ) );
		if ( 1 !== (int) $acquired ) {
			return $this->fallback( $contacts, 'lock_unavailable', $state_key );
		}

		try {
			// Refresh eligibility inside the serialized critical section.
			if ( self::STATE_OPTION === $state_key ) {
				$contacts = HATNCH_Contacts::get_active();
			} else {
				$role     = substr( $state_key, strlen( 'hatnch_rr_' ) );
				$contacts = array_values(
					array_filter(
						HATNCH_Contacts::get_active(),
						static function ( $contact ) use ( $role ) {
							return isset( $contact['role'] ) && $role === $contact['role'];
						}
					)
				);
			}
			if ( empty( $contacts ) ) {
				return null;
			}

			// Read the cursor directly from the database. The Options API may serve a
			// stale value from a persistent object cache across concurrent requests.
			$wpdb->last_error = '';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cursor must bypass the options/object cache under concurrency; see comment above.
			$state_row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
					$state_key
				),
				ARRAY_A
			);
			if ( null === $state_row && '' !== $wpdb->last_error ) {
				return $this->fallback( $contacts, 'state_read_failed', $state_key );
			}
			$state   = null !== $state_row
				? maybe_unserialize( $state_row['option_value'] )
				: ( $structured_state ? array() : 0 );
			$last_id = $structured_state
				? ( is_array( $state ) ? absint( $state['last_contact_id'] ?? 0 ) : 0 )
				: absint( $state );
			$next    = $contacts[0];
			foreach ( $contacts as $index => $contact ) {
				if ( $last_id === (int) $contact['id'] ) {
					$next = $contacts[ ( $index + 1 ) % count( $contacts ) ];
					break;
				}
			}

			$saved = $structured_state
				? array( 'last_contact_id' => (int) $next['id'] )
				: (int) $next['id'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Atomic upsert of the cursor; option caches are invalidated immediately below.
			$write_result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->options} ( option_name, option_value, autoload ) VALUES ( %s, %s, 'no' ) ON DUPLICATE KEY UPDATE option_value = %s, autoload = 'no'",
					$state_key,
					maybe_serialize( $saved ),
					maybe_serialize( $saved )
				)
			);
			if ( false === $write_result ) {
				return $this->fallback( $contacts, 'state_write_failed', $state_key );
			}

			// Keep WordPress' option caches coherent after the direct database write.
			wp_cache_delete( $state_key, 'options' );
			wp_cache_delete( 'alloptions', 'options' );
			wp_cache_delete( 'notoptions', 'options' );

			$wpdb->last_error = '';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read-back verification of the persisted cursor must bypass caches.
			$persisted_row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
					$state_key
				),
				ARRAY_A
			);
			$persisted     = null !== $persisted_row ? maybe_unserialize( $persisted_row['option_value'] ) : null;
			$persisted_id  = $structured_state
				? ( is_array( $persisted ) ? absint( $persisted['last_contact_id'] ?? 0 ) : 0 )
				: absint( $persisted );
			if ( $persisted_id !== (int) $next['id'] ) {
				return $this->fallback( $contacts, 'state_verify_failed', $state_key );
			}
			return $next;
		} finally {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Releases the advisory lock acquired above; no WordPress API exists.
			$release_result = $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', $lock_name ) );
			if ( 1 !== (int) $release_result ) {
				do_action( 'hatnch_routing_lock_release_failed', $state_key, $release_result );
			}
		}
	}

	/**
	 * Choose a safe contact when Round Robin bookkeeping fails.
	 *
	 * Prefers the configured default contact when it is in the eligible set,
	 * otherwise the first eligible contact. The cursor is not touched, so normal
	 * rotation resumes from where it was once the failure clears.
	 *
	 * @param array  $contacts  Eligible active contacts for the lane.
	 * @param string $reason    One of self::FAILURE_REASONS.
	 * @param string $state_key Cursor option of the lane.
	 * @return array|null
	 */
	private function fallback( array $contacts, string $reason, string $state_key ): ?array {
		$contacts = array_values( array_filter( $contacts, 'is_array' ) );
		$lane     = self::STATE_OPTION === $state_key ? 'global' : substr( $state_key, strlen( 'hatnch_rr_' ) );

		if ( empty( $contacts ) ) {
			self::report_failure( $reason, $lane, 0 );
			return null;
		}

		$chosen     = $contacts[0];
		$default_id = absint( HATNCH_Settings::get( 'default_contact', 0 ) );
		foreach ( $contacts as $contact ) {
			$contact_id = (int) ( $contact['id'] ?? 0 );
			if ( $default_id > 0 && $contact_id === $default_id ) {
				$chosen = $contact;
				break;
			}
		}

		/**
		 * Filters the fallback contact used when Round Robin bookkeeping fails.
		 *
		 * The returned contact must be one of the eligible contacts; anything else is ignored.
		 *
		 * @param array  $chosen   Proposed fallback contact.
		 * @param array  $contacts Eligible active contacts.
		 * @param string $reason   Failure reason.
		 * @param string $lane     'global' or the purpose role.
		 */
		$filtered = apply_filters( 'hatnch_routing_fallback_contact', $chosen, $contacts, $reason, $lane );
		if ( is_array( $filtered ) && isset( $filtered['id'] ) ) {
			$filtered_id = (int) $filtered['id'];
			foreach ( $contacts as $contact ) {
				$contact_id = (int) ( $contact['id'] ?? 0 );
				if ( $contact_id === $filtered_id ) {
					$chosen = $contact;
					break;
				}
			}
		}

		self::report_failure( $reason, $lane, (int) $chosen['id'] );
		return $chosen;
	}

	/**
	 * Count a routing failure and fire `hatnch_routing_failure`.
	 *
	 * Stores only counts per reason and two timestamps: no phone numbers,
	 * names, IP addresses or request data. Increments are best effort and may
	 * under-count when failures race or when the database itself is failing.
	 *
	 * @param string $reason              One of self::FAILURE_REASONS.
	 * @param string $lane                'global' or a purpose role key.
	 * @param int    $fallback_contact_id Contact used instead, or 0 when none.
	 */
	public static function report_failure( string $reason, string $lane = 'global', int $fallback_contact_id = 0 ): void {
		if ( ! in_array( $reason, self::FAILURE_REASONS, true ) ) {
			return;
		}

		$lane  = sanitize_key( $lane );
		$state = get_option( self::FAILURE_OPTION, array() );
		if ( ! is_array( $state ) ) {
			$state = array();
		}

		$now     = time();
		$reasons = isset( $state['reasons'] ) && is_array( $state['reasons'] ) ? $state['reasons'] : array();

		$reasons[ $reason ] = absint( $reasons[ $reason ] ?? 0 ) + 1;

		$state = array(
			'total'    => absint( $state['total'] ?? 0 ) + 1,
			'reasons'  => array_intersect_key( $reasons, array_flip( self::FAILURE_REASONS ) ),
			'first_at' => absint( $state['first_at'] ?? 0 ) > 0 ? absint( $state['first_at'] ) : $now,
			'last_at'  => $now,
		);

		if ( false === get_option( self::FAILURE_OPTION, false ) ) {
			add_option( self::FAILURE_OPTION, $state, '', false );
		} else {
			update_option( self::FAILURE_OPTION, $state, false );
		}

		/**
		 * Fires when routing could not use its normal path.
		 *
		 * @param string $reason              One of HATNCH_Routing::FAILURE_REASONS.
		 * @param string $lane                'global' or the purpose role.
		 * @param int    $fallback_contact_id Contact used instead, or 0 when the visitor got no contact.
		 */
		do_action( 'hatnch_routing_failure', $reason, $lane, $fallback_contact_id );
	}

	/**
	 * Read the failure counter for display.
	 *
	 * @return array{total:int,reasons:array<string,int>,first_at:int,last_at:int}
	 */
	public static function failure_summary(): array {
		$state = get_option( self::FAILURE_OPTION, array() );
		$state = is_array( $state ) ? $state : array();

		$reasons = array();
		foreach ( self::FAILURE_REASONS as $reason ) {
			$count = absint( $state['reasons'][ $reason ] ?? 0 );
			if ( $count > 0 ) {
				$reasons[ $reason ] = $count;
			}
		}

		return array(
			'total'    => absint( $state['total'] ?? 0 ),
			'reasons'  => $reasons,
			'first_at' => absint( $state['first_at'] ?? 0 ),
			'last_at'  => absint( $state['last_at'] ?? 0 ),
		);
	}
}
