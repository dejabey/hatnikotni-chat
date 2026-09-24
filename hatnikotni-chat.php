<?php
/**
 * Plugin Name: Hatnikotni Chat
 * Plugin URI: https://github.com/dejabey/hatnikotni-chat
 * Description: Lightweight WhatsApp contact routing and consent-aware first-party interaction analytics for WordPress.
 * Version: 0.1.0
 * Author: Hatnikotni
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hatnikotni-chat
 * Requires at least: 6.6
 * Requires PHP: 8.1
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

define( 'HKC_VERSION', '0.1.0' );
define( 'HKC_DB_VERSION', '1.0.0' );
define( 'HKC_PLUGIN_FILE', __FILE__ );
define( 'HKC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HKC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HKC_PLUGIN_DIR . 'includes/class-hkc-settings.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-contacts.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-routing.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-analytics.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-analytics-admin.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-campaign.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-privacy.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-whatsapp.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-shortcode.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-admin.php';
require_once HKC_PLUGIN_DIR . 'includes/class-hkc-plugin.php';

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
\trequire_once HKC_PLUGIN_DIR . 'tests/class-hkc-consent-test-harness.php';
\tHKC_Consent_Test_Harness::init();
}

register_activation_hook( HKC_PLUGIN_FILE, array( 'HKC_Plugin', 'activate' ) );
register_deactivation_hook( HKC_PLUGIN_FILE, array( 'HKC_Plugin', 'deactivate' ) );

HKC_Plugin::instance();
