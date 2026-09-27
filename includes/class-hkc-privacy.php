<?php
/**
 * Privacy and analytics consent integration.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATC_Privacy {

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_policy_content' ) );
	}

	/**
	 * Determine whether visitor analytics consent has been granted.
	 *
	 * Hatnikotni Chat does not provide its own consent banner. The default is
	 * deliberately false so analytics and campaign cookies are opt-in.
	 * Consent-management plugins or site code can integrate through the filter.
	 */
	public static function has_analytics_consent(): bool {
		return (bool) apply_filters( 'hatc_has_analytics_consent', false );
	}

	/**
	 * Add suggested privacy-policy text to the WordPress Privacy Policy Guide.
	 */
	public static function add_privacy_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$policy = '<p>' . esc_html__(
			'Hatnikotni Chat can provide a WhatsApp contact button, contact routing and optional first-party interaction analytics. When visitor analytics consent is not available, the plugin does not record WhatsApp click analytics and does not set its campaign attribution cookie.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'When analytics consent is granted, the plugin may record a WhatsApp click event containing the selected contact, the WordPress page ID and page type, a broad device category (mobile, tablet or desktop), and supported UTM campaign values. The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history or WhatsApp conversation content.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'Analytics events are stored in the site WordPress database and are automatically deleted after 180 days. With analytics consent, the plugin may use the first-party hatc_campaign cookie for up to 30 days to retain the latest supported UTM campaign attribution.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p>' . esc_html__(
			'Analytics data is not sent by Hatnikotni Chat to an external analytics service. When a visitor chooses to contact the site through WhatsApp, the browser is redirected to WhatsApp and that service receives information according to its own privacy policy and terms.',
			'hatnikotni-chat'
		) . '</p>';

		$policy .= '<p class="privacy-policy-tutorial">' . esc_html__(
			'Site administrators should document the consent mechanism used by their site and ensure that the hatc_has_analytics_consent filter reflects an explicit visitor choice before analytics is enabled.',
			'hatnikotni-chat'
		) . '</p>';

		wp_add_privacy_policy_content(
			__( 'Hatnikotni Chat', 'hatnikotni-chat' ),
			$policy
		);
	}
}
