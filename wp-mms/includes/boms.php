<?php
/**
 * BOM specific functions
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handle the cloning of a BOM to create a new version.
 */
function wp_mms_clone_bom() {
    if ( ! isset( $_GET['action'] ) || 'clone_mms_bom' !== $_GET['action'] ) {
        return;
    }

    $original_post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

    check_admin_referer( 'clone_mms_bom_' . $original_post_id );

    if ( ! current_user_can( 'edit_mms_boms', $original_post_id ) ) {
        wp_die( __( 'You do not have permission to clone this item.', 'wp-mms' ) );
    }

    $source_post = get_post( $original_post_id );
    if ( ! $source_post ) {
        wp_die( __( 'Item to clone not found.', 'wp-mms' ) );
    }

    // If the selected post is a root (no parent) and has children, we should clone the LATEST child instead.
    // This maintains a linear version history.
    $children = get_children([
        'post_parent' => $original_post_id,
        'post_type'   => 'wp_mms_bom',
        'numberposts' => 1,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ]);

    if ( 0 === $source_post->post_parent && ! empty( $children ) ) {
        // It's a root with versions. Get the latest version to clone from.
        $latest_child_post = array_shift( $children );
        $source_post = get_post( $latest_child_post->ID );
    }

    // New post data
    $new_post_args = [
        'post_title'   => $source_post->post_title . ' - ' . __( 'Copy', 'wp-mms' ),
        'post_content' => $source_post->post_content,
        'post_status'  => 'draft',
        'post_type'    => $source_post->post_type,
        'post_parent'  => $source_post->ID,
    ];

    $new_post_id = wp_insert_post( $new_post_args );

    if ( is_wp_error( $new_post_id ) ) {
        wp_die( __( 'Failed to clone item.', 'wp-mms' ) );
    }

    // Copy meta data from the source post
    $meta_keys = ['_wp_mms_finished_product_id', '_wp_mms_components'];
    foreach ( $meta_keys as $meta_key ) {
        $meta_value = get_post_meta( $source_post->ID, $meta_key, true );
        if ( $meta_value ) {
            update_post_meta( $new_post_id, $meta_key, $meta_value );
        }
    }

    // Log the action
    wp_mms_log_action( 'bom_version_created', [
        'object_id'   => $new_post_id,
        'object_type' => 'BOM Version',
        'description' => 'created new version from',
        'old_value'   => $source_post->ID, // Store the original BOM ID for reference
    ]);

    // Redirect to the new draft for editing
    wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
    exit;
}
add_action( 'admin_post_clone_mms_bom', 'wp_mms_clone_bom' );

/**
 * AJAX handler to get all BOM versions for a given product ID.
 */
function wp_mms_get_boms_for_product_ajax_handler() {
    check_ajax_referer( 'get_boms_for_product_nonce', 'nonce' );

    if ( ! isset( $_POST['product_id'] ) ) {
        wp_send_json_error( 'No product ID specified.' );
    }

    $product_id = intval( $_POST['product_id'] );

    $bom_query = new WP_Query([
        'post_type' => 'wp_mms_bom',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_wp_mms_finished_product_id',
                'value' => $product_id,
                'compare' => '=',
            ]
        ],
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    $boms = [];
    if ( $bom_query->have_posts() ) {
        while( $bom_query->have_posts() ) {
            $bom_query->the_post();
            $boms[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
            ];
        }
    }
    wp_reset_postdata();

    wp_send_json_success( $boms );
}
add_action( 'wp_ajax_get_boms_for_product', 'wp_mms_get_boms_for_product_ajax_handler' );