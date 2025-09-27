<?php
/**
 * Dashboard Widgets
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the MMS Status dashboard widget.
 */
function wp_mms_register_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wp_mms_status_widget',         // Widget slug.
        __( 'MMS Status', 'wp-mms' ),   // Title.
        'wp_mms_render_status_widget'   // Display function.
    );
}
add_action( 'wp_dashboard_setup', 'wp_mms_register_dashboard_widgets' );

/**
 * Render the content for the MMS Status dashboard widget.
 */
function wp_mms_render_status_widget() {
    // --- Low Stock Alerts ---
    // Query for all products that have a reorder point set.
    $products_with_reorder_point = new WP_Query([
        'post_type' => 'wp_mms_product',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key'     => '_wp_mms_reorder_point',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            ]
        ]
    ]);

    // Loop through the results in PHP to compare the two meta fields.
    $low_stock_items = [];
    if ( $products_with_reorder_point->have_posts() ) {
        while( $products_with_reorder_point->have_posts() ) {
            $products_with_reorder_point->the_post();
            $stock = get_post_meta( get_the_ID(), '_wp_mms_stock_quantity', true );
            $reorder_point = get_post_meta( get_the_ID(), '_wp_mms_reorder_point', true );
            if ( is_numeric($stock) && floatval($stock) <= floatval($reorder_point) ) {
                $low_stock_items[] = get_post();
            }
        }
        wp_reset_postdata();
    }

    echo '<h4>' . __('Low Stock Alerts', 'wp-mms') . '</h4>';
    if ( !empty($low_stock_items) ) {
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        foreach( $low_stock_items as $item ) {
            $stock = get_post_meta( $item->ID, '_wp_mms_stock_quantity', true );
            $reorder_point = get_post_meta( $item->ID, '_wp_mms_reorder_point', true );
            printf(
                '<li><a href="%s">%s</a> - <strong>%s:</strong> %s/%s</li>',
                esc_url( get_edit_post_link($item->ID) ),
                esc_html( $item->post_title ),
                __('Stock', 'wp-mms'),
                esc_html($stock),
                esc_html($reorder_point)
            );
        }
        echo '</ul>';
    } else {
        echo '<p style="color: green;">' . __( 'All product stock levels are OK.', 'wp-mms' ) . '</p>';
    }

    echo '<hr>';

    // --- Overdue Purchase Order Alerts ---
    $today = current_time('Y-m-d');
    $overdue_pos = new WP_Query([
        'post_type' => 'wp_mms_purchase_order',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_wp_mms_status',
                'value' => ['received', 'canceled'],
                'compare' => 'NOT IN'
            ],
            [
                'key' => '_wp_mms_expected_date',
                'value' => $today,
                'compare' => '<',
                'type' => 'DATE'
            ]
        ]
    ]);

    echo '<h4>' . __('Overdue Purchase Orders', 'wp-mms') . '</h4>';
    if ( $overdue_pos->have_posts() ) {
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        while( $overdue_pos->have_posts() ) {
            $overdue_pos->the_post();
            $expected_date = get_post_meta( get_the_ID(), '_wp_mms_expected_date', true );
            printf(
                '<li><a href="%s">%s</a> - <strong>%s:</strong> %s</li>',
                esc_url( get_edit_post_link() ),
                esc_html( get_the_title() ),
                __('Expected', 'wp-mms'),
                esc_html($expected_date)
            );
        }
        echo '</ul>';
    } else {
        echo '<p style="color: green;">' . __( 'No purchase orders are overdue.', 'wp-mms' ) . '</p>';
    }
    wp_reset_postdata();
}