<?php
/**
 * WhatsApp shortcode interface.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Shortcode {

	public static function init(): void {
		add_shortcode( 'hatnikotni_chat', array( __CLASS__, 'render' ) );
	}

	public static function render( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'label'   => HATNCH_Settings::get( 'button_label', 'Contact Us' ),
				'message' => HATNCH_Settings::get( 'default_message', '' ),
			),
			$atts,
			'hatnikotni_chat'
		);

		$label = sanitize_text_field( (string) $atts['label'] );
		if ( 'Contact Us' === $label ) {
			$label = __( 'Contact Us', 'hatnikotni-chat' );
		}
		$message   = sanitize_text_field( (string) $atts['message'] );
		$page_id   = get_queried_object_id();
		$page_type = $page_id ? (string) get_post_type( $page_id ) : '';
		$url       = HATNCH_WhatsApp::action_url( $message, (int) $page_id, $page_type );

		return sprintf(
			'<a class="hatnch-button hatnch-inline" href="%1$s" aria-label="%2$s">%2$s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}
