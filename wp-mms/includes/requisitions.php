<?php
/**
 * Requisition specific functions
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handle the conversion of an approved requisition to a Purchase Order.
 */
function wp_mms_handle_requisition_conversion() {
    if ( ! isset( $_GET['action'] ) || 'convert_to_po' !== $_GET['action'] ) {
        return;
    }

    if ( ! isset( $_GET['requisition_id'] ) ) {
        wp_die( __( 'Invalid requisition ID.', 'wp-mms' ) );
    }

    $requisition_id = intval( $_GET['requisition_id'] );

    // Verify nonce
    check_admin_referer( 'convert_req_to_po_' . $requisition_id );

    // Check user permissions
    if ( ! current_user_can( 'publish_mms_purchase_orders' ) ) {
        wp_die( __( 'You do not have permission to create Purchase Orders.', 'wp-mms' ) );
    }

    // Get requisition data
    $requisition = get_post( $requisition_id );
    if ( !$requisition || 'wp_mms_requisition' !== $requisition->post_type ) {
        wp_die( __( 'Invalid requisition.', 'wp-mms' ) );
    }

    $req_status = get_post_meta( $requisition_id, '_wp_mms_status', true );
    if ( 'approved' !== $req_status ) {
        wp_die( __( 'This requisition has not been approved.', 'wp-mms' ) );
    }

    $requested_items = get_post_meta( $requisition_id, '_wp_mms_requested_items', true );
    $desired_date = get_post_meta( $requisition_id, '_wp_mms_desired_date', true );

    // Create new Purchase Order
    $po_args = [
        'post_title'   => 'PO from Req: ' . $requisition->post_title,
        'post_content' => 'This Purchase Order was generated from Requisition #' . $requisition_id . '.',
        'post_status'  => 'draft',
        'post_type'    => 'wp_mms_purchase_order',
    ];
    $new_po_id = wp_insert_post( $po_args );

    if ( is_wp_error( $new_po_id ) ) {
        wp_die( __( 'Failed to create Purchase Order.', 'wp-mms' ) );
    }

    // Copy line items from requisition to PO
    $po_line_items = [];
    if ( !empty($requested_items) && is_array($requested_items) ) {
        foreach ($requested_items as $item) {
            $unit_cost = get_post_meta( $item['product_id'], '_wp_mms_unit_cost', true );
            $po_line_items[] = [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $unit_cost ?: '0.00',
            ];
        }
    }
    update_post_meta( $new_po_id, '_wp_mms_line_items', $po_line_items );
    update_post_meta( $new_po_id, '_wp_mms_expected_date', $desired_date );
    update_post_meta( $new_po_id, '_wp_mms_status', 'pending' );

    // Update the requisition status to 'completed'
    update_post_meta( $requisition_id, '_wp_mms_status', 'completed' );
    update_post_meta( $requisition_id, '_wp_mms_converted_po_id', $new_po_id );

    // Log the conversion action
    wp_mms_log_action( 'requisition_converted', [
        'object_id'   => $requisition_id,
        'object_type' => 'Requisition',
        'description' => 'converted to Purchase Order',
        'new_value'   => $new_po_id, // Store the new PO ID for reference
    ]);

    // Redirect to the new PO edit screen
    wp_redirect( admin_url( 'post.php?post=' . $new_po_id . '&action=edit' ) );
    exit;
}
add_action( 'admin_init', 'wp_mms_handle_requisition_conversion' );