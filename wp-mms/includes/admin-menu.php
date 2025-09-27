<?php
/**
 * Admin Menu Setup
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add the main admin menu for the plugin.
 */
function wp_mms_add_admin_menu() {
    // Add top-level menu page
    add_menu_page(
        __( 'Manufacturing Management', 'wp-mms' ),
        __( 'MMS', 'wp-mms' ),
        'manage_options', // Capability
        'wp_mms', // Menu slug
        'wp_mms_dashboard_page_html', // Function to display the page
        'dashicons-admin-generic', // Icon
        20 // Position
    );

    // Add sub-menus for the CPTs
    add_submenu_page(
        'wp_mms', // Parent slug
        __( 'Dashboard', 'wp-mms' ),
        __( 'Dashboard', 'wp-mms' ),
        'manage_options',
        'wp_mms', // Same slug as parent to make it the default page
        'wp_mms_dashboard_page_html'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Suppliers', 'wp-mms' ),
        __( 'Suppliers', 'wp-mms' ),
        'manage_options',
        'edit.php?post_type=wp_mms_supplier'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Products', 'wp-mms' ),
        __( 'Products', 'wp-mms' ),
        'manage_options',
        'edit.php?post_type=wp_mms_product'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Purchase Orders', 'wp-mms' ),
        __( 'Purchase Orders', 'wp-mms' ),
        'manage_options',
        'edit.php?post_type=wp_mms_purchase_order'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Bills of Materials', 'wp-mms' ),
        __( 'Bills of Materials', 'wp-mms' ),
        'manage_options',
        'edit.php?post_type=wp_mms_bom'
    );
}
add_action( 'admin_menu', 'wp_mms_add_admin_menu' );

/**
 * Display the dashboard page HTML.
 */
function wp_mms_dashboard_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e( 'Welcome to the Manufacturing Management System dashboard. Here you will find an overview of your operations.', 'wp-mms' ); ?></p>
        <!-- Dashboard widgets will go here -->
    </div>
    <?php
}