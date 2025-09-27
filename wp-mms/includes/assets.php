<?php
/**
 * Asset Enqueuing
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Enqueue scripts and styles for the admin area.
 *
 * @param string $hook The current admin page.
 */
function wp_mms_admin_enqueue_assets( $hook ) {
    global $post;

    // Only load on the post edit screens for our CPT
    if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type ) && 'wp_mms_purchase_order' === $post->post_type ) {
        wp_enqueue_script(
            'wp-mms-admin-script', // Handle
            WP_MMS_PLUGIN_URL . 'assets/js/admin-scripts.js', // Path
            array( 'jquery' ), // Dependencies
            WP_MMS_VERSION, // Version
            true // Load in footer
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wp_mms_admin_enqueue_assets' );