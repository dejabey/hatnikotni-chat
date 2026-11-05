<?php
/**
 * Privacy and analytics consent integration.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Privacy {

	private const CONSENT_COOKIE = 'hatnch_analytics_consent';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_policy_content' ) );
	}

	/**
	 * Determine whether visitor analytics consent has been granted.
	 *
	 * The native frontend panel stores an explicit yes/no choice in a
	 * first-party cookie. The default remains false when no choice exists.
	 * Integrations may override the resulting value with this filter.
	 */
	public static function has_analytics_consent(): bool {
		$consent = isset( $_COOKIE[ self::CONSENT_COOKIE ] )
			&& is_string( $_COOKIE[ self::CONSENT_COOKIE ] )
			&& 'yes' === sanitize_key( wp_unslash( $_COOKIE[ self::CONSENT_COOKIE ] ) );

		return (bool) apply_filters( 'hatnch_has_analytics_consent', $consent );
	}

	/**
	 * Add suggested privacy-policy text to the WordPress Privacy Policy Guide.
	 */
	public static function add_privacy_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$policy = '<p>' . esc_html__(
			'Hatnikotni Chat provides a WhatsApp contact button and optional first-party interaction analytics. Analytics is disabled until a visitor explicitly allows it using the plugin privacy choices. Visitors can change their choice at any time using the privacy choices control.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'When analytics consent is granted, the plugin may record a WhatsApp click event containing the selected contact, the WordPress page ID and page type, a broad device category (mobile, tablet or desktop), and supported UTM campaign values. The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history or WhatsApp conversation content.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'Analytics events are stored in the site WordPress database and are automatically deleted after 180 days. With analytics consent, the plugin may use the first-party hatnch_campaign cookie for up to 30 days to retain the latest supported UTM campaign attribution.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'Analytics data is not sent by Hatnikotni Chat to an external analytics service. When a visitor chooses to contact the site through WhatsApp, the browser is redirected to WhatsApp and that service receives information according to its own privacy policy and terms.',
			'hatnikotni-chat'
		) . '</p>';

		wp_add_privacy_policy_content(
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			$policy
		);
	}
}
