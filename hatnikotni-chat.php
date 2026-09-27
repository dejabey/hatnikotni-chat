<?php
/**
 * Plugin Name: Hatnikotni Chat
 * Description: Lightweight WhatsApp contact routing and consent-aware first-party interaction analytics for WordPress.
 * Version: 0.1.1
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

define( 'HATC_VERSION', '0.1.1' );
define( 'HATC_DB_VERSION', '1.0.0' );
define( 'HATC_PLUGIN_FILE', __FILE__ );
define( 'HATC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HATC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once HATC_PLUGIN_DIR . 'includes/class-hatc-settings.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-contacts.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-routing.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-analytics.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-analytics-admin.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-campaign.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-privacy.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-whatsapp.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-shortcode.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-admin.php';
require_once HATC_PLUGIN_DIR . 'includes/class-hatc-plugin.php';

register_activation_hook( HATC_PLUGIN_FILE, array( 'HATC_Plugin', 'activate' ) );
register_deactivation_hook( HATC_PLUGIN_FILE, array( 'HATC_Plugin', 'deactivate' ) );

HATC_Plugin::instance();
