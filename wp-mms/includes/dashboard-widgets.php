<?php
/**
 * Dashboard Widgets
 *
 * This file handles the registration of all custom dashboard widgets for the MMS plugin.
 * The "customizable dashboard" feature is achieved by leveraging the native WordPress
 * dashboard functionality. Users can drag, drop, and toggle the visibility of these
 * widgets via the "Screen Options" tab on their dashboard.
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
    // Main Status Widget
    wp_add_dashboard_widget(
        'wp_mms_status_widget',
        __( 'MMS Status', 'wp-mms' ),
        'wp_mms_render_status_widget'
    );

    // Profitability Widget
    wp_add_dashboard_widget(
        'wp_mms_profitability_widget',
        __( 'Profitability Snapshot', 'wp-mms' ),
        'wp_mms_render_profitability_widget'
    );

    // Efficiency Widget
    wp_add_dashboard_widget(
        'wp_mms_efficiency_widget',
        __( 'Production Efficiency (Last 30 Days)', 'wp-mms' ),
        'wp_mms_render_efficiency_widget'
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

/**
 * Render the content for the Profitability Snapshot widget.
 */
function wp_mms_render_profitability_widget() {
    $products_query = new WP_Query([
        'post_type' => 'wp_mms_product', 'posts_per_page' => -1, 'meta_query' => [['key' => '_wp_mms_item_type', 'value' => 'finished_good']]
    ]);

    $products = [];
    if ( $products_query->have_posts() ) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            $selling_price = (float) get_post_meta(get_the_ID(), '_wp_mms_selling_price', true);
            $bom_cost = (float) get_post_meta(get_the_ID(), '_wp_mms_bom_cost', true);
            if ($selling_price > 0) {
                $products[] = [
                    'name' => get_the_title(),
                    'profit' => $selling_price - $bom_cost,
                ];
            }
        }
    }
    wp_reset_postdata();

    if (empty($products)) {
        echo '<p>' . __('No finished goods with pricing information found.', 'wp-mms') . '</p>';
        return;
    }

    // Sort by profit
    usort($products, function($a, $b) {
        return $b['profit'] <=> $a['profit'];
    });

    echo '<h4>' . __('Top 5 Most Profitable', 'wp-mms') . '</h4>';
    echo '<ul>';
    foreach (array_slice($products, 0, 5) as $p) {
        printf('<li>%s: <strong>%s</strong></li>', esc_html($p['name']), esc_html(number_format_i18n($p['profit'], 2)));
    }
    echo '</ul><hr>';

    echo '<h4>' . __('Top 5 Least Profitable', 'wp-mms') . '</h4>';
    echo '<ul>';
    foreach (array_slice(array_reverse($products), 0, 5) as $p) {
         printf('<li>%s: <strong style="color:red;">%s</strong></li>', esc_html($p['name']), esc_html(number_format_i18n($p['profit'], 2)));
    }
    echo '</ul>';
}

/**
 * Render the content for the Production Efficiency widget.
 */
function wp_mms_render_efficiency_widget() {
    $orders_query = new WP_Query([
        'post_type' => 'wp_mms_production_order',
        'posts_per_page' => -1,
        'date_query' => [['after' => '30 days ago', 'inclusive' => true]],
        'meta_query' => [['key' => '_wp_mms_status', 'value' => 'completed']]
    ]);

    $total_planned = 0;
    $total_actual = 0;
    $order_count = 0;

    if ($orders_query->have_posts()) {
        while ($orders_query->have_posts()) {
            $orders_query->the_post();
            $planned_hours = (float) get_post_meta(get_the_ID(), '_wp_mms_planned_duration_hours', true);
            $actual_hours = (float) get_post_meta(get_the_ID(), '_wp_mms_actual_duration_hours', true);

            if ($planned_hours > 0 || $actual_hours > 0) {
                $order_count++;
                $total_planned += $planned_hours;
                $total_actual += $actual_hours;
            }
        }
    }
    wp_reset_postdata();

    if ($order_count > 0) {
        $avg_variance = ($total_actual - $total_planned) / $order_count;
        printf('<p>Average Variance: <strong style="font-size: 1.2em; color: %s;">%s hours</strong></p>', ($avg_variance > 0 ? 'red' : 'green'), esc_html(number_format($avg_variance, 2)));
        echo '<p class="description">' . __('Based on', 'wp-mms') . ' ' . $order_count . ' ' . __('completed orders.', 'wp-mms') . '</p>';
    } else {
        echo '<p>' . __('No completed orders with duration data in the last 30 days.', 'wp-mms') . '</p>';
    }
}