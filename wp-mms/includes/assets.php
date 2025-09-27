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

    $allowed_post_types = [ 'wp_mms_purchase_order', 'wp_mms_bom' ];
    if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type ) && in_array( $post->post_type, $allowed_post_types ) ) {

        $deps = ['jquery'];
        if ( 'wp_mms_bom' === $post->post_type ) {
            $deps[] = 'jquery-ui-sortable';
        }

        wp_enqueue_script(
            'wp-mms-admin-script',
            WP_MMS_PLUGIN_URL . 'assets/js/admin-scripts.js',
            $deps,
            WP_MMS_VERSION,
            true
        );

        // Prepare data for the script
        $localized_data = [];
        if ( 'wp_mms_bom' === $post->post_type ) {
            $component_products_query = get_posts( [
                'post_type' => 'wp_mms_product',
                'numberposts' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => [
                    'relation' => 'OR',
                    ['meta_key' => '_wp_mms_item_type', 'meta_value' => 'raw_material'],
                    ['meta_key' => '_wp_mms_item_type', 'meta_value' => 'component']
                ]
            ] );

            $components = [];
            foreach ($component_products_query as $product) {
                $components[] = [
                    'id' => $product->ID,
                    'title' => $product->post_title
                ];
            }
            $localized_data['components'] = $components;
        }

        // Pass the data to the script
        wp_localize_script( 'wp-mms-admin-script', 'wp_mms_data', $localized_data );
    }
}
add_action( 'admin_enqueue_scripts', 'wp_mms_admin_enqueue_assets' );