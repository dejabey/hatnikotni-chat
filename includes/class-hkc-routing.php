<?php
/**
 * Contact routing engine.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Routing {

	public function resolve(): ?array {
		$method = HKC_Settings::get( 'routing_method', 'direct' );

		return apply_filters( 'hkc_resolve_contact', null, $method );
	}
}
