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

define( 'HATNCH_VERSION', '0.1.0' );
define( 'HATNCH_DB_VERSION', '1.0.0' );
define( 'HATNCH_PLUGIN_FILE', __FILE__ );
define( 'HATNCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HATNCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-settings.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-contacts.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-routing.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-analytics.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-analytics-admin.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-campaign.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-privacy.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-whatsapp.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-shortcode.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-admin.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hkc-plugin.php';

register_activation_hook( HATNCH_PLUGIN_FILE, array( 'HATNCH_Plugin', 'activate' ) );
register_deactivation_hook( HATNCH_PLUGIN_FILE, array( 'HATNCH_Plugin', 'deactivate' ) );

HATNCH_Plugin::instance();
