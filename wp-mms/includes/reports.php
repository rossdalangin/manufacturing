<?php
/**
 * Reports Page
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handle the CSV export request for the inventory report.
 */
function wp_mms_handle_inventory_export() {
    if ( isset( $_GET['page'] ) && 'wp_mms_reports' === $_GET['page'] && isset( $_GET['action'] ) && 'export_inventory_csv' === $_GET['action'] ) {

        if ( ! current_user_can( 'view_mms_reports' ) ) {
            wp_die( __( 'You do not have sufficient permissions to export this data.', 'wp-mms' ) );
        }

        $filename = 'inventory-report-' . date( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );

        fputcsv( $output, [
            __( 'SKU', 'wp-mms' ),
            __( 'Product Name', 'wp-mms' ),
            __( 'Item Type', 'wp-mms' ),
            __( 'Stock Quantity', 'wp-mms' ),
            __( 'Reorder Point', 'wp-mms' ),
            __( 'Unit Cost', 'wp-mms' ),
            __( 'Stock Value', 'wp-mms' ),
        ] );

        $products_query = new WP_Query( ['post_type' => 'wp_mms_product', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'] );

        $item_type_labels = [
            'raw_material' => __( 'Raw Material', 'wp-mms' ),
            'component' => __( 'Component', 'wp-mms' ),
            'finished_good' => __( 'Finished Good', 'wp-mms' ),
        ];

        while ( $products_query->have_posts() ) {
            $products_query->the_post();
            $sku = get_post_meta( get_the_ID(), '_wp_mms_sku', true );
            $item_type = get_post_meta( get_the_ID(), '_wp_mms_item_type', true );
            $stock_quantity = get_post_meta( get_the_ID(), '_wp_mms_stock_quantity', true );
            $reorder_point = get_post_meta( get_the_ID(), '_wp_mms_reorder_point', true );
            $unit_cost = get_post_meta( get_the_ID(), '_wp_mms_unit_cost', true );
            $stock_value = is_numeric($stock_quantity) && is_numeric($unit_cost) ? floatval($stock_quantity) * floatval($unit_cost) : 0;

            fputcsv( $output, [ $sku, get_the_title(), $item_type_labels[$item_type] ?? $item_type, $stock_quantity, $reorder_point, $unit_cost, number_format_i18n($stock_value, 2) ] );
        }
        wp_reset_postdata();

        fclose( $output );
        exit;
    }
}
add_action( 'admin_init', 'wp_mms_handle_inventory_export' );

/**
 * Handle the CSV export request for the supplier performance report.
 */
function wp_mms_handle_supplier_export() {
    if ( isset( $_GET['page'] ) && 'wp_mms_reports' === $_GET['page'] && isset( $_GET['action'] ) && 'export_supplier_csv' === $_GET['action'] ) {

        if ( ! current_user_can( 'view_mms_reports' ) ) {
            wp_die( __( 'You do not have sufficient permissions to export this data.', 'wp-mms' ) );
        }

        $filename = 'supplier-performance-report-' . date( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );

        fputcsv( $output, [
            __( 'Supplier', 'wp-mms' ),
            __( 'Total Completed Orders', 'wp-mms' ),
            __( 'Total Value of Orders', 'wp-mms' ),
            __( 'Avg. Lead Time (Days)', 'wp-mms' ),
            __( 'On-Time Delivery %', 'wp-mms' ),
        ] );

        // --- Refactored Export Logic ---
        $supplier_data = wp_mms_get_supplier_performance_data();

        // 3. Output the processed data to the CSV.
        foreach ($supplier_data as $data) {
            fputcsv( $output, [
                $data['name'],
                $data['total_orders'],
                number_format_i18n($data['total_value'], 2),
                ($data['total_orders'] > 0) ? round($data['total_lead_time_days'] / $data['total_orders'], 1) : 'N/A',
                ($data['total_orders'] > 0) ? round(($data['on_time_orders'] / $data['total_orders']) * 100, 1) . '%' : 'N/A',
            ] );
        }

        fclose( $output );
        exit;
    }
}
add_action( 'admin_init', 'wp_mms_handle_supplier_export' );

/**
 * Get and process supplier performance data efficiently.
 * @return array
 */
function wp_mms_get_supplier_performance_data() {
    // 1. Get all suppliers and store them in an associative array for easy lookup.
    $suppliers = get_posts( ['post_type' => 'wp_mms_supplier', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'] );
    $supplier_data = [];
    foreach ($suppliers as $supplier) {
        $supplier_data[$supplier->ID] = [
            'name' => $supplier->post_title,
            'total_orders' => 0,
            'total_value' => 0,
            'total_lead_time_days' => 0,
            'on_time_orders' => 0,
        ];
    }

    // 2. Get all completed purchase orders in a single query.
    $po_query = new WP_Query([
        'post_type' => 'wp_mms_purchase_order',
        'posts_per_page' => -1,
        'meta_query' => [ ['key' => '_wp_mms_status', 'value' => 'received'] ]
    ]);

    if ( $po_query->have_posts() ) {
        while( $po_query->have_posts() ) {
            $po_query->the_post();
            $supplier_id = get_post_meta( get_the_ID(), '_wp_mms_supplier_id', true );

            if ( !isset($supplier_data[$supplier_id]) ) continue;

            $supplier_data[$supplier_id]['total_orders']++;

            $line_items = get_post_meta( get_the_ID(), '_wp_mms_line_items', true );
            if( !empty($line_items) && is_array($line_items) ) {
                foreach( $line_items as $item ) {
                    $supplier_data[$supplier_id]['total_value'] += floatval($item['quantity']) * floatval($item['unit_price']);
                }
            }

            $order_date = get_post_meta( get_the_ID(), '_wp_mms_order_date', true );
            $received_date = get_post_meta( get_the_ID(), '_wp_mms_date_received', true );
            if( !empty($order_date) && !empty($received_date) ) {
                $supplier_data[$supplier_id]['total_lead_time_days'] += date_diff( date_create($order_date), date_create($received_date) )->days;
            }

            $expected_date = get_post_meta( get_the_ID(), '_wp_mms_expected_date', true );
            if( !empty($received_date) && !empty($expected_date) && (strtotime($received_date) <= strtotime($expected_date)) ) {
                $supplier_data[$supplier_id]['on_time_orders']++;
            }
        }
    }
    wp_reset_postdata();
    return $supplier_data;
}


/**
 * Display the reports page HTML.
 */
function wp_mms_reports_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e( 'Use the reports below to gain insights into your manufacturing operations.', 'wp-mms' ); ?></p>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <div class="hndle">
                                <h2 style="display:inline-block;"><?php _e( 'Inventory Stock Levels', 'wp-mms' ); ?></h2>
                                <a href="<?php echo esc_url( add_query_arg( 'action', 'export_inventory_csv' ) ); ?>" class="button button-primary" style="float:right; margin-top: 6px;"><?php _e( 'Export to CSV', 'wp-mms' ); ?></a>
                            </div>
                            <div class="inside">
                                <?php
                                $products_query = new WP_Query( [ 'post_type' => 'wp_mms_product', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
                                if ( $products_query->have_posts() ) :
                                ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php _e( 'SKU', 'wp-mms' ); ?></th>
                                            <th scope="col"><?php _e( 'Product Name', 'wp-mms' ); ?></th>
                                            <th scope="col"><?php _e( 'Item Type', 'wp-mms' ); ?></th>
                                            <th scope="col"><?php _e( 'Stock Quantity', 'wp-mms' ); ?></th>
                                            <th scope="col"><?php _e( 'Reorder Point', 'wp-mms' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ( $products_query->have_posts() ) : $products_query->the_post(); ?>
                                            <?php
                                            $sku = get_post_meta( get_the_ID(), '_wp_mms_sku', true );
                                            $item_type = get_post_meta( get_the_ID(), '_wp_mms_item_type', true );
                                            $stock_quantity = get_post_meta( get_the_ID(), '_wp_mms_stock_quantity', true );
                                            $reorder_point = get_post_meta( get_the_ID(), '_wp_mms_reorder_point', true );
                                            $item_type_labels = [ 'raw_material' => __( 'Raw Material', 'wp-mms' ), 'component' => __( 'Component', 'wp-mms' ), 'finished_good' => __( 'Finished Good', 'wp-mms' ) ];
                                            $low_stock_style = '';
                                            if ( is_numeric( $stock_quantity ) && is_numeric( $reorder_point ) && floatval( $reorder_point ) > 0 && floatval( $stock_quantity ) <= floatval( $reorder_point ) ) {
                                                $low_stock_style = 'style="background-color: #f8d7da;"';
                                            }
                                            ?>
                                            <tr <?php echo $low_stock_style; ?>>
                                                <td><?php echo esc_html( $sku ); ?></td>
                                                <td><a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>"><?php the_title(); ?></a></td>
                                                <td><?php echo esc_html( $item_type_labels[$item_type] ?? $item_type ); ?></td>
                                                <td><?php echo esc_html( $stock_quantity ); ?></td>
                                                <td><?php echo esc_html( $reorder_point ); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php else : echo '<p>' . __( 'No products found.', 'wp-mms' ) . '</p>'; endif; wp_reset_postdata(); ?>
                            </div>
                        </div>

                        <div class="postbox">
                            <div class="hndle">
                                <h2 style="display:inline-block;"><?php _e( 'Supplier Performance', 'wp-mms' ); ?></h2>
                                <a href="<?php echo esc_url( add_query_arg( 'action', 'export_supplier_csv' ) ); ?>" class="button button-primary" style="float:right; margin-top: 6px;"><?php _e( 'Export to CSV', 'wp-mms' ); ?></a>
                            </div>
                            <div class="inside">
                                <?php
                                $supplier_data = wp_mms_get_supplier_performance_data();
                                if ( !empty($supplier_data) ) :
                                ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php _e( 'Supplier', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Total Completed Orders', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Total Value of Orders', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Avg. Lead Time (Days)', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'On-Time Delivery %', 'wp-mms' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($supplier_data as $supplier_id => $data) :
                                            $avg_lead_time = ($data['total_orders'] > 0) ? round($data['total_lead_time_days'] / $data['total_orders'], 1) : 'N/A';
                                            $on_time_percentage = ($data['total_orders'] > 0) ? round(($data['on_time_orders'] / $data['total_orders']) * 100, 1) . '%' : 'N/A';
                                        ?>
                                        <tr>
                                            <td><a href="<?php echo esc_url( get_edit_post_link( $supplier_id ) ); ?>"><?php echo esc_html($data['name']); ?></a></td>
                                            <td><?php echo esc_html( $data['total_orders'] ); ?></td>
                                            <td><?php echo esc_html( number_format_i18n($data['total_value'], 2) ); ?></td>
                                            <td><?php echo esc_html( $avg_lead_time ); ?></td>
                                            <td><?php echo esc_html( $on_time_percentage ); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else : ?>
                                    <p><?php _e( 'No supplier data to display.', 'wp-mms' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="postbox">
                            <div class="hndle">
                                <h2 style="display:inline-block;"><?php _e( 'Production Scrap Report', 'wp-mms' ); ?></h2>
                            </div>
                            <div class="inside">
                                <?php
                                $scrap_query = new WP_Query([
                                    'post_type' => 'wp_mms_production_order',
                                    'posts_per_page' => -1,
                                    'meta_query' => [
                                        ['key' => '_wp_mms_total_scrap_cost', 'compare' => 'EXISTS'],
                                        ['key' => '_wp_mms_total_scrap_cost', 'value' => 0, 'compare' => '!='],
                                    ]
                                ]);
                                if ( $scrap_query->have_posts() ) :
                                ?>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php _e( 'Production Order', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Product Made', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Quantity Made', 'wp-mms' ); ?></th>
                                            <th><?php _e( 'Total Scrap Cost', 'wp-mms' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ( $scrap_query->have_posts() ) : $scrap_query->the_post();
                                            $product_id = get_post_meta( get_the_ID(), '_wp_mms_product_id', true );
                                            $quantity = get_post_meta( get_the_ID(), '_wp_mms_quantity', true );
                                            $scrap_cost = get_post_meta( get_the_ID(), '_wp_mms_total_scrap_cost', true );
                                        ?>
                                        <tr>
                                            <td><a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>"><?php the_title(); ?></a></td>
                                            <td><?php echo esc_html( get_the_title( $product_id ) ); ?></td>
                                            <td><?php echo esc_html( $quantity ); ?></td>
                                            <td><?php echo esc_html( number_format_i18n( $scrap_cost, 2 ) ); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php else : ?>
                                    <p><?php _e( 'No production orders with scrap have been recorded.', 'wp-mms' ); ?></p>
                                <?php endif; wp_reset_postdata(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
    <?php
}