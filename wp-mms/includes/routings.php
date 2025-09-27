<?php
/**
 * Routing specific functions
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * AJAX handler to get the default routing steps for a given product ID.
 */
function wp_mms_get_routing_for_product_ajax_handler() {
    check_ajax_referer( 'get_routing_for_product_nonce', 'nonce' );

    if ( ! isset( $_POST['product_id'] ) || ! current_user_can( 'edit_mms_production_orders' ) ) {
        wp_send_json_error( 'Invalid request.' );
    }

    $product_id = intval( $_POST['product_id'] );
    $routing_id = get_post_meta( $product_id, '_wp_mms_default_routing_id', true );

    if ( empty( $routing_id ) ) {
        wp_send_json_success( ['steps' => []] );
        return;
    }

    $steps = get_post_meta( $routing_id, '_wp_mms_routing_steps', true );

    if ( empty( $steps ) || ! is_array( $steps ) ) {
        wp_send_json_success( ['steps' => []] );
        return;
    }

    // Enhance steps with work center names for display
    foreach ( $steps as $i => $step ) {
        $steps[$i]['work_center_name'] = $step['work_center_id'] ? get_the_title( $step['work_center_id'] ) : __( 'N/A', 'wp-mms' );
    }

    wp_send_json_success( [ 'routing_id' => $routing_id, 'steps' => $steps ] );
}
add_action( 'wp_ajax_get_routing_for_product', 'wp_mms_get_routing_for_product_ajax_handler' );