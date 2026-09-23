<?php
/**
 * Contact routing engine.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Routing {

	private const STATE_OPTION = 'hkc_routing_state';

	public function resolve(): ?array {
		$method = HKC_Settings::get( 'routing_method', 'direct' );

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

		return apply_filters( 'hkc_resolved_contact', $contact, $method );
	}

	private function resolve_direct(): ?array {
		$contact_id = absint( HKC_Settings::get( 'default_contact', 0 ) );

		if ( $contact_id < 1 ) {
			return null;
		}

		$contact = HKC_Contacts::get( $contact_id );

		return $contact && 1 === (int) $contact['status'] ? $contact : null;
	}

	private function resolve_random(): ?array {
		$contacts = HKC_Contacts::get_active();

		if ( empty( $contacts ) ) {
			return null;
		}

		return $contacts[ wp_rand( 0, count( $contacts ) - 1 ) ];
	}

	private function resolve_round_robin(): ?array {
		$contacts = HKC_Contacts::get_active();

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

		return $next;
	}
}
