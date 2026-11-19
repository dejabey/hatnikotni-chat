<?php
/**
 * WordPress-backed regression test for UTM campaign attribution.
 *
 * Reproduces the real request sequence: a consented landing request with UTM
 * parameters stores the attribution cookie; a later request reads it back and
 * records a click. Before 0.1.28, sanitize_text_field() stripped the
 * percent-encoded cookie payload, so every click was stored without UTM values.
 *
 * Run with: wp eval-file tests/campaign-attribution-integration.php
 *
 * @package Hatnikotni_Chat
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "Run this test through WP-CLI.\n" );
	exit( 1 );
}

$hatnch_fail = static function ( string $message ): void {
	fwrite( STDERR, 'FAIL: ' . $message . "\n" );
	exit( 1 );
};

global $wpdb;
$hatnch_table = $wpdb->prefix . 'hatnch_events';

// Request 1: consented landing page with UTM parameters. WordPress slashes superglobals.
$_COOKIE['hatnch_analytics_consent'] = 'yes';
unset( $_COOKIE['hatnch_campaign'] );
$_GET = wp_slash(
	array(
		'utm_source'   => 'ci-source',
		'utm_medium'   => 'ci-medium',
		'utm_campaign' => 'ci "quoted" & <b>c0128</b>',
	)
);
HATNCH_Campaign::capture();

if ( empty( $_COOKIE['hatnch_campaign'] ) ) {
	$hatnch_fail( 'capture() did not store the campaign cookie for a consented UTM request.' );
}

// Request 2: a later request carries the cookie as PHP decodes it once, slashed by WordPress.
$hatnch_cookie = (string) $_COOKIE['hatnch_campaign'];
$_GET          = array();
$_COOKIE       = wp_slash(
	array(
		'hatnch_analytics_consent' => 'yes',
		'hatnch_campaign'          => $hatnch_cookie,
	)
);

$hatnch_attr = HATNCH_Campaign::get_attribution();
if ( 'ci-source' !== $hatnch_attr['utm_source'] || 'ci-medium' !== $hatnch_attr['utm_medium'] ) {
	$hatnch_fail( 'get_attribution() lost utm_source/utm_medium: ' . wp_json_encode( $hatnch_attr ) );
}
if ( 'ci "quoted" & c0128' !== $hatnch_attr['utm_campaign'] ) {
	$hatnch_fail( 'utm_campaign was not decoded and sanitized as expected: ' . wp_json_encode( $hatnch_attr['utm_campaign'] ) );
}

$hatnch_before = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $hatnch_table ) );
if ( ! HATNCH_Analytics::record_click( array( 'contact_id' => 0 ) ) ) {
	$hatnch_fail( 'record_click() returned false with consent granted.' );
}
$hatnch_row = $wpdb->get_row( $wpdb->prepare( 'SELECT utm_source, utm_medium, utm_campaign FROM %i ORDER BY id DESC LIMIT 1', $hatnch_table ), ARRAY_A );
if ( (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $hatnch_table ) ) !== $hatnch_before + 1 ) {
	$hatnch_fail( 'record_click() did not insert exactly one event.' );
}
if ( ! is_array( $hatnch_row ) || 'ci-source' !== $hatnch_row['utm_source'] || 'ci "quoted" & c0128' !== $hatnch_row['utm_campaign'] ) {
	$hatnch_fail( 'Stored event lacks the campaign attribution: ' . wp_json_encode( $hatnch_row ) );
}

// Malformed or non-string cookies must yield empty attribution, not errors.
foreach ( array( '%7Bnot-json', 'plain', '' ) as $hatnch_bad ) {
	$_COOKIE['hatnch_campaign'] = $hatnch_bad;
	$hatnch_attr                = HATNCH_Campaign::get_attribution();
	if ( '' !== (string) $hatnch_attr['utm_campaign'] ) {
		$hatnch_fail( 'Malformed cookie produced attribution: ' . wp_json_encode( $hatnch_bad ) );
	}
}
$_COOKIE['hatnch_campaign'] = array( 'x' );
HATNCH_Campaign::get_attribution();

// Without consent, attribution is never returned.
$_COOKIE = wp_slash(
	array(
		'hatnch_analytics_consent' => 'no',
		'hatnch_campaign'          => $hatnch_cookie,
	)
);
$hatnch_attr = HATNCH_Campaign::get_attribution();
if ( '' !== (string) $hatnch_attr['utm_campaign'] ) {
	$hatnch_fail( 'Attribution returned without analytics consent.' );
}

// Clean up the test event.
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE utm_source = %s', $hatnch_table, 'ci-source' ) );

echo "PASS: campaign attribution survives cookie round-trip and is stored on click; malformed and unconsented cookies yield none.\n";
