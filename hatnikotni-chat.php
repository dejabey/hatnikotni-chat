<?php
/**
 * Plugin Name: Hatnikotni Chat
 * Plugin URI: https://github.com/dejabey/hatnikotni-chat
 * Description: Lightweight WhatsApp contact routing and consent-aware first-party interaction analytics for WordPress.
 * Version: 0.1.8
 * Author: Hatnikotni
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hatnikotni-chat
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.1
 *
 * @package Hatnikotni_Chat
 */

defined( 'ABSPATH' ) || exit;

define( 'HATNCH_VERSION', '0.1.8' );
define( 'HATNCH_DB_VERSION', '1.1.0' );
define( 'HATNCH_PLUGIN_FILE', __FILE__ );
define( 'HATNCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HATNCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-settings.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-contacts.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-routing.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-analytics.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-analytics-admin.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-campaign.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-privacy.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-whatsapp.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-shortcode.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-admin.php';
require_once HATNCH_PLUGIN_DIR . 'includes/class-hatnch-plugin.php';

register_activation_hook( HATNCH_PLUGIN_FILE, array( 'HATNCH_Plugin', 'activate' ) );
register_deactivation_hook( HATNCH_PLUGIN_FILE, array( 'HATNCH_Plugin', 'deactivate' ) );

HATNCH_Plugin::instance();
