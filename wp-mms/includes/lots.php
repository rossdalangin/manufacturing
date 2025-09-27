<?php
/**
 * Lot / Batch Tracking Functions
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Updates a product's total stock quantity by summing up all its lots.
 *
 * @param int $product_id The ID of the product to update.
 */
function wp_mms_update_product_stock_from_lots( $product_id ) {
    if ( empty( $product_id ) ) {
        return;
    }

    $lot_query = new WP_Query([
        'post_type' => 'wp_mms_lot',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_wp_mms_product_id',
                'value' => $product_id,
                'compare' => '=',
            ],
        ],
    ]);

    $total_stock = 0;
    if ( $lot_query->have_posts() ) {
        while ( $lot_query->have_posts() ) {
            $lot_query->the_post();
            $total_stock += floatval( get_post_meta( get_the_ID(), '_wp_mms_quantity', true ) );
        }
    }
    wp_reset_postdata();

    // Update the master stock quantity on the product itself.
    update_post_meta( $product_id, '_wp_mms_stock_quantity', $total_stock );
}

/**
 * Trigger stock update when a lot is saved.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_trigger_stock_update_on_save( $post_id ) {
    if ( get_post_type( $post_id ) !== 'wp_mms_lot' ) {
        return;
    }
    // Also handles the case where the product associated with a lot is changed.
    $product_id = get_post_meta( $post_id, '_wp_mms_product_id', true );
    wp_mms_update_product_stock_from_lots( $product_id );

    // If the product was changed, we also need to update the old product's stock.
    $old_product_id = get_post_meta( $post_id, '_wp_mms_product_id', true, true ); // Get previous value
    if ( $old_product_id && $old_product_id !== $product_id ) {
        wp_mms_update_product_stock_from_lots( $old_product_id );
    }
}
add_action( 'save_post_wp_mms_lot', 'wp_mms_trigger_stock_update_on_save' );

/**
 * Trigger stock update when a lot is deleted.
 *
 * @param int $post_id The ID of the post being deleted.
 */
function wp_mms_trigger_stock_update_on_delete( $post_id ) {
    if ( get_post_type( $post_id ) !== 'wp_mms_lot' ) {
        return;
    }
    $product_id = get_post_meta( $post_id, '_wp_mms_product_id', true );
    wp_mms_update_product_stock_from_lots( $product_id );
}
add_action( 'after_delete_post', 'wp_mms_trigger_stock_update_on_delete' );