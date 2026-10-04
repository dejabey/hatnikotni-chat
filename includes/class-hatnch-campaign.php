<?php
/**
 * UTM campaign attribution.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HATNCH_Campaign {

	private const COOKIE_NAME  = 'hatnch_campaign';
	private const COOKIE_DAYS  = 30;
	private const FIELDS       = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' );
	private const FIELD_LIMITS = array(
		'utm_source'   => 100,
		'utm_medium'   => 100,
		'utm_campaign' => 150,
		'utm_term'     => 150,
		'utm_content'  => 150,
	);

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'capture' ), 1 );
		add_action( 'admin_post_nopriv_hatnch_revoke_campaign', array( __CLASS__, 'revoke_campaign' ) );
		add_action( 'admin_post_hatnch_revoke_campaign', array( __CLASS__, 'revoke_campaign' ) );
	}

	/**
	 * Expire the HttpOnly campaign cookie immediately after consent is withdrawn.
	 *
	 * This public endpoint only clears the requesting visitor's own attribution cookie.
	 */
	public static function revoke_campaign(): void {
		nocache_headers();
		self::clear_cookie();
		status_header( 204 );
		exit;
	}

	public static function capture(): void {
		if ( ! HATNCH_Privacy::has_analytics_consent() ) {
			self::clear_cookie();
			return;
		}

		$attribution = array();

		// External UTM campaign URLs are public, read-only attribution inputs, not form submissions.
		// Requiring a nonce would break ordinary external campaign links; the consent gate controls capture.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( self::FIELDS as $field ) {
			if ( isset( $_GET[ $field ] ) && is_scalar( $_GET[ $field ] ) ) {
				$value = self::limit_text(
					sanitize_text_field( wp_unslash( $_GET[ $field ] ) ),
					self::FIELD_LIMITS[ $field ]
				);

				if ( '' !== $value ) {
					$attribution[ $field ] = $value;
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( empty( $attribution ) || headers_sent() ) {
			return;
		}

		$encoded = wp_json_encode( $attribution );

		if ( false === $encoded ) {
			return;
		}

		$value = rawurlencode( $encoded );

		setcookie(
			self::COOKIE_NAME,
			$value,
			array(
				'expires'  => time() + ( DAY_IN_SECONDS * self::COOKIE_DAYS ),
				'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		$_COOKIE[ self::COOKIE_NAME ] = $value;
	}

	public static function get_attribution(): array {
		if ( ! HATNCH_Privacy::has_analytics_consent() || empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return self::empty_attribution();
		}

		$encoded = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
		$decoded = rawurldecode( $encoded );
		$data    = json_decode( $decoded, true );

		if ( ! is_array( $data ) ) {
			return self::empty_attribution();
		}

		$attribution = self::empty_attribution();

		foreach ( self::FIELDS as $field ) {
			if ( isset( $data[ $field ] ) && is_scalar( $data[ $field ] ) ) {
				$attribution[ $field ] = self::limit_text(
					sanitize_text_field( (string) $data[ $field ] ),
					self::FIELD_LIMITS[ $field ]
				);
			}
		}

		return $attribution;
	}

	private static function clear_cookie(): void {
		if ( empty( $_COOKIE[ self::COOKIE_NAME ] ) || headers_sent() ) {
			return;
		}

		setcookie(
			self::COOKIE_NAME,
			'',
			array(
				'expires'  => time() - HOUR_IN_SECONDS,
				'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		unset( $_COOKIE[ self::COOKIE_NAME ] );
	}

	/**
	 * Limit attribution text to the matching database column length.
	 *
	 * @param string $value Sanitized attribution value.
	 * @param int    $limit Maximum number of Unicode characters.
	 * @return string Value bounded to the requested character limit.
	 */
	private static function limit_text( string $value, int $limit ): string {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $limit, 'UTF-8' );
		}

		$characters = preg_split( '//u', $value, -1, PREG_SPLIT_NO_EMPTY );

		if ( ! is_array( $characters ) ) {
			return substr( $value, 0, $limit );
		}

		return implode( '', array_slice( $characters, 0, $limit ) );
	}

	private static function empty_attribution(): array {
		return array_fill_keys( self::FIELDS, null );
	}
}
