<?php
/**
 * Temporary consent integration test harness.
 *
 * Loaded only when WP_DEBUG is enabled. Remove before stable release.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Consent_Test_Harness {

	public static function init(): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'hkc',
			__( 'Consent Test', 'hatnikotni-chat' ),
			__( 'Consent Test', 'hatnikotni-chat' ),
			'manage_options',
			'hkc-consent-test',
			array( __CLASS__, 'render' )
		);

		add_action( 'admin_post_hkc_consent_test', array( __CLASS__, 'handle' ) );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$actions = array(
			'no_consent' => __( 'Test without consent', 'hatnikotni-chat' ),
			'with_consent' => __( 'Test with consent', 'hatnikotni-chat' ),
			'with_consent_utm' => __( 'Test consent + UTM', 'hatnikotni-chat' ),
		);

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Hatnikotni Chat Consent Test', 'hatnikotni-chat' ) . '</h1>';
		echo '<p>' . esc_html__( 'Temporary development-only harness. It is available only while WP_DEBUG is enabled and only to administrators.', 'hatnikotni-chat' ) . '</p>';

		foreach ( $actions as $action => $label ) {
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'hkc_consent_test',
						'test'   => $action,
					),
					admin_url( 'admin-post.php' )
				),
				'hkc_consent_test_' . $action
			);

			echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></p>';
		}

		echo '</div>';
	}

	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'hatnikotni-chat' ) );
		}

		$test = isset( $_GET['test'] ) ? sanitize_key( wp_unslash( $_GET['test'] ) ) : '';

		if ( ! in_array( $test, array( 'no_consent', 'with_consent', 'with_consent_utm' ), true ) ) {
			wp_die( esc_html__( 'Invalid consent test.', 'hatnikotni-chat' ) );
		}

		check_admin_referer( 'hkc_consent_test_' . $test );

		if ( 'no_consent' !== $test ) {
			add_filter(
				'hkc_has_analytics_consent',
				static function (): bool {
					return true;
				}
			);
		}

		if ( 'with_consent_utm' === $test ) {
			$_GET['utm_source']   = 'hkc-test';
			$_GET['utm_medium']   = 'manual';
			$_GET['utm_campaign'] = 'consent-test';
			HKC_Campaign::capture();
		}

		$before = self::event_count();
		$recorded = HKC_Analytics::record_click(
			array(
				'contact_id' => 1,
				'page_id'    => get_queried_object_id(),
				'page_type'  => 'consent-test',
			)
		);
		$after = self::event_count();

		wp_die(
			'<h1>' . esc_html__( 'Consent test result', 'hatnikotni-chat' ) . '</h1>' .
			'<p>' . esc_html( sprintf( 'Test: %s', $test ) ) . '</p>' .
			'<p>' . esc_html( sprintf( 'Consent: %s', HKC_Privacy::has_analytics_consent() ? 'true' : 'false' ) ) . '</p>' .
			'<p>' . esc_html( sprintf( 'record_click(): %s', $recorded ? 'true' : 'false' ) ) . '</p>' .
			'<p>' . esc_html( sprintf( 'Event count: %d → %d', $before, $after ) ) . '</p>' .
			'<p><a href="' . esc_url( admin_url( 'admin.php?page=hkc-consent-test' ) ) . '">' . esc_html__( 'Back to consent tests', 'hatnikotni-chat' ) . '</a></p>'
		);
	}

	private static function event_count(): int {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE event_type = %s',
				HKC_Analytics::table_name(),
				'whatsapp_click'
			)
		);
	}
}
