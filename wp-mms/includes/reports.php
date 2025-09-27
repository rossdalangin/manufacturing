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

        // Check for user capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to export this data.', 'wp-mms' ) );
        }

        $filename = 'inventory-report-' . date( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );

        // Add header row
        fputcsv( $output, [
            __( 'SKU', 'wp-mms' ),
            __( 'Product Name', 'wp-mms' ),
            __( 'Item Type', 'wp-mms' ),
            __( 'Stock Quantity', 'wp-mms' ),
            __( 'Reorder Point', 'wp-mms' ),
            __( 'Unit Cost', 'wp-mms' ),
            __( 'Stock Value', 'wp-mms' ),
        ] );

        // Get product data
        $products_query = new WP_Query( [
            'post_type' => 'wp_mms_product',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ] );

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

            fputcsv( $output, [
                $sku,
                get_the_title(),
                $item_type_labels[$item_type] ?? $item_type,
                $stock_quantity,
                $reorder_point,
                $unit_cost,
                number_format_i18n($stock_value, 2),
            ] );
        }
        wp_reset_postdata();

        fclose( $output );
        exit;
    }
}
add_action( 'admin_init', 'wp_mms_handle_inventory_export' );


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
                                $products_query = new WP_Query( [
                                    'post_type' => 'wp_mms_product',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC',
                                ] );

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
                                            $item_type_labels = [
                                                'raw_material' => __( 'Raw Material', 'wp-mms' ),
                                                'component' => __( 'Component', 'wp-mms' ),
                                                'finished_good' => __( 'Finished Good', 'wp-mms' ),
                                            ];
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
                                <?php
                                else :
                                    echo '<p>' . __( 'No products found.', 'wp-mms' ) . '</p>';
                                endif;
                                wp_reset_postdata();
                                ?>
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