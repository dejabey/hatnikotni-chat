<?php
/**
 * UTM campaign attribution.
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

final class HKC_Campaign {

	private const COOKIE_NAME = 'hkc_campaign';
	private const COOKIE_DAYS = 30;
	private const FIELDS      = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' );

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'capture' ), 1 );
	}

	public static function capture(): void {
		if ( ! HKC_Privacy::has_analytics_consent() ) {
			self::clear_cookie();
			return;
		}

		$attribution = array();

		foreach ( self::FIELDS as $field ) {
			if ( isset( $_GET[ $field ] ) && is_scalar( $_GET[ $field ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_GET[ $field ] ) );

				if ( '' !== $value ) {
					$attribution[ $field ] = $value;
				}
			}
		}

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
		if ( ! HKC_Privacy::has_analytics_consent() || empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
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
				$attribution[ $field ] = sanitize_text_field( (string) $data[ $field ] );
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

	private static function empty_attribution(): array {
		return array_fill_keys( self::FIELDS, null );
	}
}
