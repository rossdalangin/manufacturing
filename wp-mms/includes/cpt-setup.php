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
        'capability_type'       => 'mms_supplier',
        'capabilities' => [
            'edit_post'          => 'edit_mms_supplier',
            'read_post'          => 'read_mms_supplier',
            'delete_post'        => 'delete_mms_supplier',
            'edit_posts'         => 'edit_mms_suppliers',
            'edit_others_posts'  => 'edit_others_mms_suppliers',
            'publish_posts'      => 'publish_mms_suppliers',
            'read_private_posts' => 'read_private_mms_suppliers',
            'delete_posts'       => 'delete_mms_suppliers',
        ],
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
        'capability_type'       => 'mms_product',
        'capabilities' => [
            'edit_post'          => 'edit_mms_product',
            'read_post'          => 'read_mms_product',
            'delete_post'        => 'delete_mms_product',
            'edit_posts'         => 'edit_mms_products',
            'edit_others_posts'  => 'edit_others_mms_products',
            'publish_posts'      => 'publish_mms_products',
            'read_private_posts' => 'read_private_mms_products',
            'delete_posts'       => 'delete_mms_products',
        ],
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
        'capability_type'       => 'mms_purchase_order',
        'capabilities' => [
            'edit_post'          => 'edit_mms_purchase_order',
            'read_post'          => 'read_mms_purchase_order',
            'delete_post'        => 'delete_mms_purchase_order',
            'edit_posts'         => 'edit_mms_purchase_orders',
            'edit_others_posts'  => 'edit_others_mms_purchase_orders',
            'publish_posts'      => 'publish_mms_purchase_orders',
            'read_private_posts' => 'read_private_mms_purchase_orders',
            'delete_posts'       => 'delete_mms_purchase_orders',
        ],
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
        'supports'              => array( 'title', 'page-attributes' ),
        'hierarchical'          => true,
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
        'capability_type'       => 'mms_bom',
        'capabilities' => [
            'edit_post'          => 'edit_mms_bom',
            'read_post'          => 'read_mms_bom',
            'delete_post'        => 'delete_mms_bom',
            'edit_posts'         => 'edit_mms_boms',
            'edit_others_posts'  => 'edit_others_mms_boms',
            'publish_posts'      => 'publish_mms_boms',
            'read_private_posts' => 'read_private_mms_boms',
            'delete_posts'       => 'delete_mms_boms',
        ],
        'menu_icon'             => 'dashicons-hammer',
    );
    register_post_type( 'wp_mms_bom', $bom_args );

    // Production Order CPT
    $prod_order_labels = array(
        'name'                  => _x( 'Production Orders', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Production Order', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Production Orders', 'wp-mms' ),
        'name_admin_bar'        => __( 'Production Order', 'wp-mms' ),
        'archives'              => __( 'Production Order Archives', 'wp-mms' ),
        'attributes'            => __( 'Production Order Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent Production Order:', 'wp-mms' ),
        'all_items'             => __( 'All Production Orders', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Production Order', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Production Order', 'wp-mms' ),
        'edit_item'             => __( 'Edit Production Order', 'wp-mms' ),
        'update_item'           => __( 'Update Production Order', 'wp-mms' ),
        'view_item'             => __( 'View Production Order', 'wp-mms' ),
        'view_items'            => __( 'View Production Orders', 'wp-mms' ),
        'search_items'          => __( 'Search Production Order', 'wp-mms' ),
    );
    $prod_order_args = array(
        'label'                 => __( 'Production Order', 'wp-mms' ),
        'description'           => __( 'For managing production work orders', 'wp-mms' ),
        'labels'                => $prod_order_labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false, // Will be added to our custom menu page
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'mms_production_order',
        'capabilities' => [
            'edit_post'          => 'edit_mms_production_order',
            'read_post'          => 'read_mms_production_order',
            'delete_post'        => 'delete_mms_production_order',
            'edit_posts'         => 'edit_mms_production_orders',
            'edit_others_posts'  => 'edit_others_mms_production_orders',
            'publish_posts'      => 'publish_mms_production_orders',
            'read_private_posts' => 'read_private_mms_production_orders',
            'delete_posts'       => 'delete_mms_production_orders',
        ],
        'menu_icon'             => 'dashicons-admin-settings',
    );
    register_post_type( 'wp_mms_production_order', $prod_order_args );

    // Purchase Requisition CPT
    $req_labels = array(
        'name'                  => _x( 'Purchase Requisitions', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Purchase Requisition', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Purchase Requisitions', 'wp-mms' ),
        'name_admin_bar'        => __( 'Purchase Requisition', 'wp-mms' ),
        'archives'              => __( 'Requisition Archives', 'wp-mms' ),
        'attributes'            => __( 'Requisition Attributes', 'wp-mms' ),
        'parent_item_colon'     => __( 'Parent Requisition:', 'wp-mms' ),
        'all_items'             => __( 'All Requisitions', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Requisition', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Requisition', 'wp-mms' ),
        'edit_item'             => __( 'Edit Requisition', 'wp-mms' ),
        'update_item'           => __( 'Update Requisition', 'wp-mms' ),
        'view_item'             => __( 'View Requisition', 'wp-mms' ),
        'view_items'            => __( 'View Requisitions', 'wp-mms' ),
        'search_items'          => __( 'Search Requisition', 'wp-mms' ),
    );
    $req_args = array(
        'label'                 => __( 'Purchase Requisition', 'wp-mms' ),
        'description'           => __( 'For requesting the purchase of materials', 'wp-mms' ),
        'labels'                => $req_labels,
        'supports'              => array( 'title', 'editor', 'author' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false, // Will be added to our custom menu page
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'mms_requisition',
        'capabilities' => [
            'edit_post'          => 'edit_mms_requisition',
            'read_post'          => 'read_mms_requisition',
            'delete_post'        => 'delete_mms_requisition',
            'edit_posts'         => 'edit_mms_requisitions',
            'edit_others_posts'  => 'edit_others_mms_requisitions',
            'publish_posts'      => 'publish_mms_requisitions',
            'read_private_posts' => 'read_private_mms_requisitions',
            'delete_posts'       => 'delete_mms_requisitions',
        ],
        'menu_icon'             => 'dashicons-clipboard',
    );
    register_post_type( 'wp_mms_requisition', $req_args );

    // Lot/Batch CPT
    $lot_labels = array(
        'name'                  => _x( 'Lots / Batches', 'Post Type General Name', 'wp-mms' ),
        'singular_name'         => _x( 'Lot / Batch', 'Post Type Singular Name', 'wp-mms' ),
        'menu_name'             => __( 'Lots / Batches', 'wp-mms' ),
        'name_admin_bar'        => __( 'Lot / Batch', 'wp-mms' ),
        'all_items'             => __( 'All Lots / Batches', 'wp-mms' ),
        'add_new_item'          => __( 'Add New Lot / Batch', 'wp-mms' ),
        'add_new'               => __( 'Add New', 'wp-mms' ),
        'new_item'              => __( 'New Lot / Batch', 'wp-mms' ),
        'edit_item'             => __( 'Edit Lot / Batch', 'wp-mms' ),
        'update_item'           => __( 'Update Lot / Batch', 'wp-mms' ),
        'view_item'             => __( 'View Lot / Batch', 'wp-mms' ),
    );
    $lot_args = array(
        'label'                 => __( 'Lot / Batch', 'wp-mms' ),
        'description'           => __( 'For tracking specific lots or batches of products', 'wp-mms' ),
        'labels'                => $lot_labels,
        'supports'              => array( 'title', 'page-attributes' ),
        'hierarchical'          => true,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'edit.php?post_type=wp_mms_product', // Show under Products menu
        'capability_type'       => 'mms_lot',
        'capabilities' => [
            'edit_post'          => 'edit_mms_lot',
            'read_post'          => 'read_mms_lot',
            'delete_post'        => 'delete_mms_lot',
            'edit_posts'         => 'edit_mms_lots',
            'edit_others_posts'  => 'edit_others_mms_lots',
            'publish_posts'      => 'publish_mms_lots',
            'read_private_posts' => 'read_private_mms_lots',
            'delete_posts'       => 'delete_mms_lots',
        ],
    );
    register_post_type( 'wp_mms_lot', $lot_args );
}
add_action( 'init', 'wp_mms_register_cpts', 0 );