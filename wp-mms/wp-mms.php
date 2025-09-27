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

// Include the autoloader
// require_once WP_MMS_PLUGIN_DIR . 'includes/autoloader.php';

// Include core plugin files
require_once WP_MMS_PLUGIN_DIR . 'includes/cpt-setup.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/admin-menu.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once WP_MMS_PLUGIN_DIR . 'includes/assets.php';

// Initialize the plugin
function wp_mms_run() {
    // To be filled in later
}
// add_action( 'plugins_loaded', 'wp_mms_run' );