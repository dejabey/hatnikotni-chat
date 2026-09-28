<?php
/**
 * WhatsApp action and URL generation.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_WhatsApp {

	public static function init(): void {
		add_action( 'admin_post_nopriv_hatnch_whatsapp_click', array( __CLASS__, 'handle_click' ) );
		add_action( 'admin_post_hatnch_whatsapp_click', array( __CLASS__, 'handle_click' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render_floating_button' ) );
	}

	public static function action_url( string $message = '', int $page_id = 0, string $page_type = '' ): string {
		$args = array( 'action' => 'hatnch_whatsapp_click' );

		if ( $page_id > 0 ) {
			$args['hatnch_page_id'] = $page_id;
		}

		if ( '' !== $page_type ) {
			$args['hatnch_page_type'] = sanitize_key( $page_type );
		}

		if ( '' !== $message ) {
			$args['hatnch_message'] = $message;
		}

		return add_query_arg( $args, admin_url( 'admin-post.php' ) );
	}

	public static function handle_click(): void {
		nocache_headers();

		$contact = ( new HATNCH_Routing() )->resolve();

		if ( ! is_array( $contact ) || empty( $contact['phone'] ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public action endpoint intentionally accepts anonymous GET parameters.
		$page_id = isset( $_GET['hatnch_page_id'] ) ? absint( $_GET['hatnch_page_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public action endpoint intentionally accepts anonymous GET parameters.
		$page_type = isset( $_GET['hatnch_page_type'] ) ? sanitize_key( wp_unslash( $_GET['hatnch_page_type'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public action endpoint intentionally accepts anonymous GET parameters.
		$message = isset( $_GET['hatnch_message'] ) && is_scalar( $_GET['hatnch_message'] )
			? sanitize_text_field( wp_unslash( $_GET['hatnch_message'] ) )
			: (string) HATNCH_Settings::get( 'default_message', '' );

		HATNCH_Analytics::record_click(
			array(
				'contact_id' => (int) $contact['id'],
				'page_id'    => $page_id,
				'page_type'  => $page_type,
			)
		);

		$url = self::build_url( (string) $contact['phone'], $message );

		if ( '' === $url ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Destination is a locally constructed wa.me URL from a normalized phone number.
		wp_redirect( $url, 302, 'Hatnikotni Chat' );
		exit;
	}

	public static function build_url( string $phone, string $message = '' ): string {
		$phone = HATNCH_Contacts::normalize_phone( $phone );

		if ( '' === $phone ) {
			return '';
		}

		$url = 'https://wa.me/' . $phone;

		if ( '' !== $message ) {
			$url = add_query_arg( 'text', $message, $url );
		}

		return $url;
	}

	public static function enqueue_assets(): void {
		if ( ! self::frontend_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'hatnch-frontend',
			HATNCH_PLUGIN_URL . 'assets/css/hatnikotni-chat.css',
			array(),
			HATNCH_VERSION
		);

		wp_enqueue_script(
			'hatnch-privacy',
			HATNCH_PLUGIN_URL . 'assets/js/hatnikotni-chat-privacy.js',
			array(),
			HATNCH_VERSION,
			true
		);

		wp_localize_script(
			'hatnch-privacy',
			'hatnchPrivacyConfig',
			array(
				'cookiePath'   => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'cookieDomain' => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			)
		);

		wp_localize_script(
			'hatnch-privacy',
			'hatnchPrivacyConfig',
			array(
				'cookiePath'   => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'cookieDomain' => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			)
		);
	}

	public static function render_floating_button(): void {
		if ( ! self::frontend_enabled() ) {
			return;
		}

		$label        = (string) HATNCH_Settings::get( 'button_label', 'WhatsApp Kami' );
		$position     = 'left' === HATNCH_Settings::get( 'button_position', 'right' ) ? 'left' : 'right';
		$show_desktop = (bool) HATNCH_Settings::get( 'show_desktop', true );
		$show_mobile  = (bool) HATNCH_Settings::get( 'show_mobile', true );
		$page_id      = get_queried_object_id();
		$page_type    = $page_id ? (string) get_post_type( $page_id ) : '';

		if ( ! $page_id && is_front_page() ) {
			$page_type = 'page';
		}

		if ( ! $show_desktop && ! $show_mobile ) {
			return;
		}

		$classes = array( 'hatnch-widget', 'hatnch-widget--' . $position );

		if ( ! $show_desktop ) {
			$classes[] = 'hatnch-hide-desktop';
		}

		if ( ! $show_mobile ) {
			$classes[] = 'hatnch-hide-mobile';
		}

		$url                = self::action_url( '', (int) $page_id, $page_type );
		$privacy_policy_url = get_privacy_policy_url();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<a class="hatnch-button hatnch-floating hatnch-floating--<?php echo esc_attr( $position ); ?>" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
				<svg class="hatnch-button__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="M20.52 3.48A11.83 11.83 0 0 0 12.08 0C5.54 0 .22 5.31.22 11.86c0 2.09.55 4.13 1.59 5.93L.12 24l6.36-1.67a11.86 11.86 0 0 0 5.6 1.43h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.23-6.15-3.43-8.42Zm-8.44 18.26h-.01a9.84 9.84 0 0 1-5.01-1.37l-.36-.21-3.77.99 1.01-3.67-.23-.38a9.82 9.82 0 0 1-1.51-5.24C2.2 6.42 6.62 2 12.08 2a9.87 9.87 0 0 1 7.02 2.92 9.85 9.85 0 0 1 2.9 7.01c0 5.46-4.45 9.81-9.92 9.81Zm5.41-7.36c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-1.76-.88-2.91-1.57-4.07-3.55-.31-.53.31-.49.88-1.63.1-.2.05-.37-.03-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49 0 1.47 1.07 2.88 1.22 3.08.15.2 2.11 3.22 5.11 4.52.71.31 1.27.49 1.7.63.72.23 1.38.2 1.9.12.58-.09 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
				</svg>
				<span class="hatnch-button__label"><?php echo esc_html( $label ); ?></span>
			</a>
			<button class="hatnch-privacy-toggle" type="button" aria-expanded="false" aria-controls="hatnch-privacy-panel"><?php echo esc_html__( 'Privacy choices', 'hatnikotni-chat' ); ?></button>
			<section class="hatnch-privacy-panel" id="hatnch-privacy-panel" hidden aria-label="<?php echo esc_attr__( 'Privacy and analytics choices', 'hatnikotni-chat' ); ?>">
				<h2><?php echo esc_html__( 'Privacy and analytics', 'hatnikotni-chat' ); ?></h2>
				<p><?php echo esc_html__( 'Allow optional analytics to help the site understand WhatsApp button usage. Your choice does not affect your ability to contact us on WhatsApp.', 'hatnikotni-chat' ); ?></p>
				<details class="hatnch-privacy-details">
					<summary><?php echo esc_html__( 'What does analytics record?', 'hatnikotni-chat' ); ?></summary>
					<p><?php echo esc_html__( 'If allowed, analytics records the selected contact, page, broad device category and supported campaign parameters. It does not record your WhatsApp messages or intentionally store your IP address.', 'hatnikotni-chat' ); ?></p>
				</details>
				<div class="hatnch-privacy-actions">
					<button type="button" class="hatnch-privacy-allow"><?php echo esc_html__( 'Allow analytics', 'hatnikotni-chat' ); ?></button>
					<button type="button" class="hatnch-privacy-reject"><?php echo esc_html__( 'Reject analytics', 'hatnikotni-chat' ); ?></button>
				</div>
				<p class="hatnch-privacy-status" role="status" aria-live="polite"></p>
				<?php if ( $privacy_policy_url ) : ?>
					<a class="hatnch-privacy-policy-link" href="<?php echo esc_url( $privacy_policy_url ); ?>"><?php echo esc_html__( 'Read our Privacy Policy', 'hatnikotni-chat' ); ?></a>
				<?php endif; ?>
			</section>
		</div>
		<?php
	}

	private static function frontend_enabled(): bool {
		return (bool) HATNCH_Settings::get( 'enabled', true );
	}
}
