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

	private function resolve_direct(): ?array {
		$contact_id = absint( HATNCH_Settings::get( 'default_contact', 0 ) );

		if ( $contact_id < 1 ) {
			return null;
		}

		$contact = HATNCH_Contacts::get( $contact_id );

		return $contact && 1 === (int) $contact['status'] ? $contact : null;
	}

	/**
	 * Select an active contact using its configured relative weight.
	 *
	 * @return array|null Selected contact, or null when no active contacts exist.
	 */
	private function resolve_random(): ?array {
		$contacts = HATNCH_Contacts::get_active();

		if ( empty( $contacts ) ) {
			return null;
		}

		$total_weight = 0;
		foreach ( $contacts as $contact ) {
			$weight        = max( 1, absint( $contact['weight'] ?? 1 ) );
			$total_weight += $weight;
		}

		// Stored weights are bounded by the Contacts validation layer (1–65535).
		// Avoid an invalid random range if a malformed/custom data set overflows.
		if ( $total_weight < 1 || $total_weight > PHP_INT_MAX ) {
			return $contacts[ wp_rand( 0, count( $contacts ) - 1 ) ];
		}

		$ticket = wp_rand( 1, $total_weight );
		foreach ( $contacts as $contact ) {
			$ticket -= max( 1, absint( $contact['weight'] ?? 1 ) );
			if ( $ticket <= 0 ) {
				return $contact;
			}
		}

		// Defensive fallback for unexpected data.
		return $contacts[ count( $contacts ) - 1 ];
	}

	/**
	 * Select and persist the next contact under a database advisory lock.
	 *
	 * MySQL/MariaDB named locks serialize routing requests across PHP workers.
	 * If the lock cannot be acquired, fail closed instead of silently breaking
	 * the configured round-robin order.
	 *
	 * @return array|null Selected active contact, or null when unavailable.
	 */
	private function resolve_round_robin(): ?array {
		global $wpdb;

		// GET_LOCK names are limited to 64 characters on supported MySQL versions.
		$lock_name = 'hatnch_rr_' . substr( hash( 'sha256', DB_NAME . '|' . $wpdb->prefix ), 0, 48 );
		$acquired  = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 5 )', $lock_name ) );

		if ( 1 !== (int) $acquired ) {
			return null;
		}

		try {
			// Read active contacts only after acquiring the lock so the selection
			// and cursor advancement share one serialized critical section.
			$contacts = HATNCH_Contacts::get_active();
			if ( empty( $contacts ) ) {
				return null;
			}

			$state   = get_option( self::STATE_OPTION, array() );
			$last_id = is_array( $state ) ? absint( $state['last_contact_id'] ?? 0 ) : 0;
			$next    = $contacts[0];

			if ( $last_id > 0 ) {
				foreach ( $contacts as $index => $contact ) {
					if ( $last_id === (int) $contact['id'] ) {
						$next = $contacts[ ( $index + 1 ) % count( $contacts ) ];
						break;
					}
				}
			}

			update_option(
				self::STATE_OPTION,
				array( 'last_contact_id' => (int) $next['id'] ),
				false
			);

			// Verify persisted state before returning a routing decision.
			$saved_state = get_option( self::STATE_OPTION, array() );
			if ( ! is_array( $saved_state ) || (int) ( $saved_state['last_contact_id'] ?? 0 ) !== (int) $next['id'] ) {
				return null;
			}

			return $next;
		} finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', $lock_name ) );
		}
	}
}
