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

    $is_cpt_edit_page = ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type );
    $is_kanban_page = 'mms_production_order_page_wp_mms_kanban_board' === $hook;

    $script_post_types = [ 'wp_mms_purchase_order', 'wp_mms_bom', 'wp_mms_requisition', 'wp_mms_routing', 'wp_mms_production_order' ];

    // Determine if we are on a page that needs our main admin script.
    $load_script = false;
    if ( $is_kanban_page || ($is_cpt_edit_page && in_array($post->post_type, $script_post_types)) ) {
        $load_script = true;
    }

    if ( ! $load_script ) {
        return;
    }

    // Enqueue styles for specific pages
    if ( $is_kanban_page || ($is_cpt_edit_page && 'wp_mms_routing' === $post->post_type) ) {
        wp_enqueue_style(
            'wp-mms-admin-styles',
            WP_MMS_PLUGIN_URL . 'assets/css/admin-styles.css',
            [],
            WP_MMS_VERSION
        );
    }

    // Determine script dependencies
    $deps = ['jquery'];
    if ( ($is_cpt_edit_page && in_array($post->post_type, ['wp_mms_bom', 'wp_mms_routing'])) || $is_kanban_page ) {
        $deps[] = 'jquery-ui-sortable';
    }
    if ($is_kanban_page) {
        $deps[] = 'jquery-ui-droppable';
    }

    // Enqueue the single admin script
    wp_enqueue_script(
        'wp-mms-admin-script',
        WP_MMS_PLUGIN_URL . 'assets/js/admin-scripts.js',
        array_unique($deps),
        WP_MMS_VERSION,
        true
    );

    // Localize data for CPT edit pages
    $localized_data = [];
    if ($is_cpt_edit_page) {
        // Data for BOM editor
        if ( 'wp_mms_bom' === $post->post_type ) {
            $component_products_query = get_posts( [ 'post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
            $components = [];
            $item_type_labels = [ 'raw_material' => __( 'Raw Material', 'wp-mms' ), 'component' => __( 'Component', 'wp-mms' ), 'finished_good' => __( 'Sub-Assembly', 'wp-mms' ) ];
            foreach ($component_products_query as $product) {
                $item_type = get_post_meta( $product->ID, '_wp_mms_item_type', true );
                $components[] = [ 'id' => $product->ID, 'title' => $product->post_title, 'type' => $item_type_labels[$item_type] ?? $item_type ];
            }
            $localized_data['components'] = $components;
        }

        // Data for Requisition editor
        if ( 'wp_mms_requisition' === $post->post_type ) {
            $all_products_query = get_posts( [ 'post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
            $products = [];
            foreach ( $all_products_query as $product ) {
                $products[] = [ 'id' => $product->ID, 'title' => $product->post_title ];
            }
            $localized_data['products'] = $products;
        }

        // Data for Production Order screen
        if ( 'wp_mms_production_order' === $post->post_type ) {
            $localized_data['get_boms_nonce'] = wp_create_nonce( 'get_boms_for_product_nonce' );
            $localized_data['get_routing_nonce'] = wp_create_nonce( 'get_routing_for_product_nonce' );
            $localized_data['selected_bom_id'] = get_post_meta( $post->ID, '_wp_mms_bom_id', true );
            $worker_query = new WP_User_Query( ['role' => 'mms_operator', 'fields' => ['ID', 'display_name']] );
            $localized_data['workers'] = $worker_query->get_results();
            $localized_data['routing_assignments'] = get_post_meta( $post->ID, '_wp_mms_routing_assignments', true ) ?: [];
            $product_id = get_post_meta( $post->ID, '_wp_mms_product_id', true );
            if( $product_id ) {
                $raw_materials = wp_mms_get_exploded_bom_materials( $product_id );
                $scrap_components = [];
                foreach( $raw_materials as $material_id => $qty ) {
                    $scrap_components[] = [ 'id' => $material_id, 'title' => get_the_title($material_id) ];
                }
                $localized_data['scrap_components'] = $scrap_components;
            }
        }
    }
    wp_localize_script( 'wp-mms-admin-script', 'wp_mms_data', $localized_data );

    // Localize data for Kanban page
    if ($is_kanban_page) {
        wp_localize_script( 'wp-mms-admin-script', 'wp_mms_kanban_data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wp_mms_kanban_nonce' ),
        ]);
    }
}
add_action( 'admin_enqueue_scripts', 'wp_mms_admin_enqueue_assets' );