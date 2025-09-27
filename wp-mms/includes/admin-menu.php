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
 * Add the main admin menu for the plugin, checking for capabilities.
 */
function wp_mms_add_admin_menu() {
    // Add top-level menu page
    add_menu_page(
        __( 'Manufacturing Management', 'wp-mms' ),
        __( 'MMS', 'wp-mms' ),
        'view_mms_reports', // A base capability for viewing the dashboard.
        'wp_mms',
        'wp_mms_dashboard_page_html',
        'dashicons-admin-generic',
        20
    );

    // Add sub-menus for the CPTs
    add_submenu_page(
        'wp_mms',
        __( 'Dashboard', 'wp-mms' ),
        __( 'Dashboard', 'wp-mms' ),
        'view_mms_reports',
        'wp_mms',
        'wp_mms_dashboard_page_html'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Suppliers', 'wp-mms' ),
        __( 'Suppliers', 'wp-mms' ),
        'edit_mms_suppliers', // Capability to view the list table
        'edit.php?post_type=wp_mms_supplier'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Products', 'wp-mms' ),
        __( 'Products', 'wp-mms' ),
        'edit_mms_products',
        'edit.php?post_type=wp_mms_product'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Purchase Orders', 'wp-mms' ),
        __( 'Purchase Orders', 'wp-mms' ),
        'edit_mms_purchase_orders',
        'edit.php?post_type=wp_mms_purchase_order'
    );

    add_submenu_page(
        'edit.php?post_type=wp_mms_purchase_order', // Child of Purchase Orders
        __( 'Purchase Requisitions', 'wp-mms' ),
        __( 'Purchase Requisitions', 'wp-mms' ),
        'edit_mms_requisitions',
        'edit.php?post_type=wp_mms_requisition'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Bills of Materials', 'wp-mms' ),
        __( 'Bills of Materials', 'wp-mms' ),
        'edit_mms_boms',
        'edit.php?post_type=wp_mms_bom'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Production Orders', 'wp-mms' ),
        __( 'Production Orders', 'wp-mms' ),
        'edit_mms_production_orders',
        'edit.php?post_type=wp_mms_production_order'
    );

    add_submenu_page(
        'edit.php?post_type=wp_mms_production_order', // Child of Production Orders
        __( 'Production Pipeline', 'wp-mms' ),
        __( 'Production Pipeline', 'wp-mms' ),
        'edit_mms_production_orders',
        'wp_mms_kanban_board',
        'wp_mms_kanban_page_html'
    );

    add_submenu_page(
        'wp_mms',
        __( 'Reports', 'wp-mms' ),
        __( 'Reports', 'wp-mms' ),
        'view_mms_reports',
        'wp_mms_reports',
        'wp_mms_reports_page_html'
    );

    add_submenu_page(
        'wp_mms_reports', // Child of Reports page
        __( 'Product Profitability', 'wp-mms' ),
        __( 'Product Profitability', 'wp-mms' ),
        'view_mms_reports',
        'wp_mms_profitability_report',
        'wp_mms_profitability_report_html'
    );

    add_submenu_page(
        'wp_mms_reports', // Child of Reports page
        __( 'Production Efficiency', 'wp-mms' ),
        __( 'Production Efficiency', 'wp-mms' ),
        'view_mms_reports',
        'wp_mms_efficiency_report',
        'wp_mms_efficiency_report_html'
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