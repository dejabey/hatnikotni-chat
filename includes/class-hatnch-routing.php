<?php
/**
 * Contact routing engine.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Routing {

	private const STATE_OPTION = 'hatnch_routing_state';

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
		$routes = apply_filters( 'hatnch_purpose_role_routes', array(
			'general_enquiries' => 'general_enquiries',
			'listing_assistance' => 'business_support',
			'business_partnerships' => 'partnerships',
			'technical_question' => 'business_support',
		) );
		if ( ! is_array( $routes ) ) { return $this->resolve(); }
		$routes = array_filter( $routes, 'is_string' );
		if ( ! isset( $routes[ $purpose ] ) ) {
			return $this->resolve();
		}
		$map = HATNCH_Settings::get( 'purpose_roles', array() );
		$role = is_array( $map ) && isset( $map[ $purpose ] ) ? sanitize_key( $map[ $purpose ] ) : $routes[ $purpose ];
		$allowed_roles = apply_filters( 'hatnch_allowed_contact_roles', array( 'general_enquiries', 'business_support', 'partnerships' ) );
		if ( ! is_array( $allowed_roles ) ) { $allowed_roles = array(); }
		$allowed_roles = array_map( 'sanitize_key', $allowed_roles );
		if ( ! in_array( $role, $allowed_roles, true ) ) {
			return $this->resolve();
		}
		$eligible = array_values( array_filter( HATNCH_Contacts::get_active(), static function ( $contact ) use ( $role ) {
			return isset( $contact['role'] ) && $role === $contact['role'];
		} ) );
		if ( empty( $eligible ) ) {
			return $this->resolve();
		}
		$method = HATNCH_Settings::get( 'routing_method', 'direct' );
		if ( 'random' === $method ) {
			return $eligible[ wp_rand( 0, count( $eligible ) - 1 ) ];
		}
		if ( 'round_robin' === $method ) {
			return $this->resolve_round_robin_from_set( $eligible, 'hatnch_rr_' . $role, false );
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
	 * Lock failure fails closed. Requires MySQL/MariaDB advisory lock support.
	 *
	 * @param array  $contacts Active contacts eligible for this lane.
	 * @param string $state_key Cursor option.
	 * @param bool   $structured_state Whether the option uses the legacy array shape.
	 * @return array|null
	 */
	private function resolve_round_robin_from_set( array $contacts, string $state_key, bool $structured_state ): ?array {
		global $wpdb;

		if ( empty( $contacts ) ) {
			error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'initial_contacts_empty', 'state_key' => $state_key ) ) );
			return null;
		}

		$lock_name = 'hatnch_rr_' . substr( hash( 'sha256', DB_NAME . '|' . $wpdb->prefix . '|' . $state_key ), 0, 48 );
		$acquired  = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 5 )', $lock_name ) );
		if ( 1 !== (int) $acquired ) {
			error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'lock_failed', 'state_key' => $state_key, 'acquired' => $acquired, 'db_error' => $wpdb->last_error ) ) );
			return null;
		}

		try {
			// Refresh eligibility inside the serialized critical section.
			if ( self::STATE_OPTION === $state_key ) {
				$contacts = HATNCH_Contacts::get_active();
			} else {
				$role = substr( $state_key, strlen( 'hatnch_rr_' ) );
				$contacts = array_values( array_filter( HATNCH_Contacts::get_active(), static function ( $contact ) use ( $role ) {
					return isset( $contact['role'] ) && $role === $contact['role'];
				} ) );
			}
			if ( empty( $contacts ) ) {
				error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'refreshed_contacts_empty', 'state_key' => $state_key ) ) );
				return null;
			}

			$state = get_option( $state_key, $structured_state ? array() : 0 );
			$last_id = $structured_state
				? ( is_array( $state ) ? absint( $state['last_contact_id'] ?? 0 ) : 0 )
				: absint( $state );
			$next = $contacts[0];
			foreach ( $contacts as $index => $contact ) {
				if ( $last_id === (int) $contact['id'] ) {
					$next = $contacts[ ( $index + 1 ) % count( $contacts ) ];
					break;
				}
			}

			$saved = $structured_state
				? array( 'last_contact_id' => (int) $next['id'] )
				: (int) $next['id'];
			update_option( $state_key, $saved, false );

			$persisted = get_option( $state_key, $structured_state ? array() : 0 );
			$persisted_id = $structured_state
				? ( is_array( $persisted ) ? absint( $persisted['last_contact_id'] ?? 0 ) : 0 )
				: absint( $persisted );
			if ( $persisted_id !== (int) $next['id'] ) {
				error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'state_verify_failed', 'state_key' => $state_key, 'selected_id' => (int) $next['id'], 'persisted_id' => $persisted_id, 'db_error' => $wpdb->last_error ) ) );
				return null;
			}
			error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'resolved', 'state_key' => $state_key, 'selected_id' => (int) $next['id'], 'previous_id' => $last_id ) ) );
			return $next;
		} finally {
			$release_result = $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', $lock_name ) );
			if ( 1 !== (int) $release_result ) {
				error_log( '[HATNCH_QA322] ' . wp_json_encode( array( 'stage' => 'release_unexpected', 'state_key' => $state_key, 'released' => $release_result, 'db_error' => $wpdb->last_error ) ) );
			}
		}
	}
}
