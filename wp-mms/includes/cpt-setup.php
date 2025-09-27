<?php
/**
 * Custom Post Type Setup
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register all CPTs for the plugin.
 */
function wp_mms_register_cpts() {
    // Supplier CPT
    $supplier_labels = array(
        'name'                  => _x( 'Suppliers', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Supplier', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Suppliers', 'wp-mms' ),
        'name_admin_bar'        => __( 'Supplier', 'wp-mms' ),
        'archives'              => __( 'Supplier Archives', 'wp-mms' ),
        'attributes'            => __( 'Supplier Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent Supplier:', 'wp-mms' ),
        'all_items'             => __( 'All Suppliers', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Supplier', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Supplier', 'wp-mms' ),
        'edit_item'             => __( 'Edit Supplier', 'wp-mms' ),
        'update_item'           => __( 'Update Supplier', 'wp-mms' ),
        'view_item'             => __( 'View Supplier', 'wp-mms' ),
        'view_items'            => __( 'View Suppliers', 'wp-mms' ),
        'search_items'          => __( 'Search Supplier', 'wp-mms' ),
    );
    $supplier_args = array(
        'label'                 => __( 'Supplier', 'wp-mms' ),
        'description'           => __( 'For storing supplier information', 'wp-mms' ),
        'labels'                => $supplier_labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false, // Will be added to our custom menu page later
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-store',
    );
    register_post_type( 'wp_mms_supplier', $supplier_args );

    // Product CPT
    $product_labels = array(
        'name'                  => _x( 'Products', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Product', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Products', 'wp-mms' ),
        'name_admin_bar'        => __( 'Product', 'wp-mms' ),
        'archives'              => __( 'Product Archives', 'wp-mms' ),
        'attributes'            => __( 'Product Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent Product:', 'wp-mms' ),
        'all_items'             => __( 'All Products', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Product', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Product', 'wp-mms' ),
        'edit_item'             => __( 'Edit Product', 'wp-mms' ),
        'update_item'           => __( 'Update Product', 'wp-mms' ),
        'view_item'             => __( 'View Product', 'wp-mms' ),
        'view_items'            => __( 'View Products', 'wp-mms' ),
        'search_items'          => __( 'Search Product', 'wp-mms' ),
    );
    $product_args = array(
        'label'                 => __( 'Product', 'wp-mms' ),
        'description'           => __( 'For all inventory items', 'wp-mms' ),
        'labels'                => $product_labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-cart',
    );
    register_post_type( 'wp_mms_product', $product_args );

    // Purchase Order CPT
    $po_labels = array(
        'name'                  => _x( 'Purchase Orders', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Purchase Order', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Purchase Orders', 'wp-mms' ),
        'name_admin_bar'        => __( 'Purchase Order', 'wp-mms' ),
        'archives'              => __( 'Purchase Order Archives', 'wp-mms' ),
        'attributes'            => __( 'Purchase Order Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent PO:', 'wp-mms' ),
        'all_items'             => __( 'All Purchase Orders', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Purchase Order', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Purchase Order', 'wp-mms' ),
        'edit_item'             => __( 'Edit Purchase Order', 'wp-mms' ),
        'update_item'           => __( 'Update Purchase Order', 'wp-mms' ),
        'view_item'             => __( 'View Purchase Order', 'wp-mms' ),
        'view_items'            => __( 'View Purchase Orders', 'wp-mms' ),
        'search_items'          => __( 'Search Purchase Order', 'wp-mms' ),
    );
    $po_args = array(
        'label'                 => __( 'Purchase Order', 'wp-mms' ),
        'description'           => __( 'For managing purchase orders', 'wp-mms' ),
        'labels'                => $po_labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-list-view',
    );
    register_post_type( 'wp_mms_purchase_order', $po_args );

    // Bill of Materials (BOM) CPT
    $bom_labels = array(
        'name'                  => _x( 'Bills of Materials', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Bill of Materials', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Bills of Materials', 'wp-mms' ),
        'name_admin_bar'        => __( 'Bill of Materials', 'wp-mms' ),
        'archives'              => __( 'BOM Archives', 'wp-mms' ),
        'attributes'            => __( 'BOM Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent BOM:', 'wp-mms' ),
        'all_items'             => __( 'All Bills of Materials', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Bill of Materials', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Bill of Materials', 'wp-mms' ),
        'edit_item'             => __( 'Edit Bill of Materials', 'wp-mms' ),
        'update_item'           => __( 'Update Bill of Materials', 'wp-mms' ),
        'view_item'             => __( 'View Bill of Materials', 'wp-mms' ),
        'view_items'            => __( 'View Bills of Materials', 'wp-mms' ),
        'search_items'          => __( 'Search Bill of Materials', 'wp-mms' ),
    );
    $bom_args = array(
        'label'                 => __( 'Bill of Materials', 'wp-mms' ),
        'description'           => __( 'For defining the components of a finished product', 'wp-mms' ),
        'labels'                => $bom_labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false, // Will be added to our custom menu page
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-hammer',
    );
    register_post_type( 'wp_mms_bom', $bom_args );
}
add_action( 'init', 'wp_mms_register_cpts', 0 );