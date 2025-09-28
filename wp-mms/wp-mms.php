<?php
/**
 * Plugin Name:       Manufacturing Management System
 * Plugin URI:        https://example.com/
 * Description:       A complete Manufacturing Management System for WordPress.
 * Version:           1.0.0
 * Author:            Jules
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-mms
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
define( 'WP_MMS_VERSION', '1.0.0' );
define( 'WP_MMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_MMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include core plugin files
require_once WP_MMS_PLUGIN_DIR . 'includes/cpt-setup.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/admin-menu.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/assets.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/reports.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/roles.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/kanban-board.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/requisitions.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/lots.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/dashboard-widgets.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/boms.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/audit-trail.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/settings.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/api.php';

// Activation and deactivation hooks
register_activation_hook( __FILE__, 'wp_mms_add_roles_and_caps' );
register_deactivation_hook( __FILE__, 'wp_mms_remove_roles' );

// Initialize the plugin
function wp_mms_run() {
    // Main plugin logic can go here if needed in the future.
}
add_action( 'plugins_loaded', 'wp_mms_run' );