<?php
/**
 * Kanban Board Page
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Display the Kanban board page HTML.
 */
function wp_mms_update_order_status_ajax_handler() {
    // Check for nonce security
    $nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
    if ( ! wp_verify_nonce( $nonce, 'wp_mms_kanban_nonce' ) ) {
        wp_send_json_error( 'Nonce verification failed!' );
    }

    // Check user permissions
    if ( ! current_user_can( 'edit_mms_production_orders' ) ) {
        wp_send_json_error( 'You do not have permission to perform this action.' );
    }

    $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
    $new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';

    if ( $order_id > 0 && ! empty( $new_status ) ) {
        // This will trigger the 'save_post' hook we already built,
        // which contains all the inventory adjustment logic.
        update_post_meta( $order_id, '_wp_mms_status', $new_status );
        wp_send_json_success( 'Order status updated.' );
    } else {
        wp_send_json_error( 'Invalid data provided.' );
    }
}
add_action( 'wp_ajax_wp_mms_update_order_status', 'wp_mms_update_order_status_ajax_handler' );


/**
 * Display the Kanban board page HTML.
 */
function wp_mms_kanban_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e( 'Drag and drop production orders to update their status.', 'wp-mms' ); ?></p>

        <div class="wp-mms-kanban-board" id="mms-kanban-board">
            <?php
            // First, fetch and organize all production orders
            $production_orders_query = new WP_Query([
                'post_type' => 'wp_mms_production_order',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);

            $orders_by_status = [
                'pending'     => [],
                'in_progress' => [],
                'completed'   => [],
            ];

            if ( $production_orders_query->have_posts() ) {
                while ( $production_orders_query->have_posts() ) {
                    $production_orders_query->the_post();
                    $status = get_post_meta( get_the_ID(), '_wp_mms_status', true );
                    if ( ! empty( $status ) && array_key_exists( $status, $orders_by_status ) ) {
                        $orders_by_status[$status][] = get_post();
                    }
                }
                wp_reset_postdata();
            }

            $statuses = [
                'pending'     => __( 'Pending', 'wp-mms' ),
                'in_progress' => __( 'In Progress', 'wp-mms' ),
                'completed'   => __( 'Completed', 'wp-mms' ),
            ];

            foreach ( $statuses as $status_key => $status_label ) :
            ?>
            <div class="kanban-column" id="kanban-column-<?php echo esc_attr( $status_key ); ?>">
                <h3 class="kanban-column-title"><?php echo esc_html( $status_label ); ?></h3>
                <div class="kanban-column-body" data-status="<?php echo esc_attr( $status_key ); ?>">
                    <?php
                    if ( ! empty( $orders_by_status[$status_key] ) ) {
                        foreach ( $orders_by_status[$status_key] as $order_post ) {
                            $product_id = get_post_meta( $order_post->ID, '_wp_mms_product_id', true );
                            $quantity = get_post_meta( $order_post->ID, '_wp_mms_quantity', true );
                            $product_title = $product_id ? get_the_title( $product_id ) : __( 'N/A', 'wp-mms' );
                            ?>
                            <div class="kanban-card" data-order-id="<?php echo esc_attr( $order_post->ID ); ?>">
                                <h4><a href="<?php echo get_edit_post_link($order_post->ID); ?>"><?php echo esc_html( $order_post->post_title ); ?></a></h4>
                                <p>
                                    <strong><?php _e( 'Product:', 'wp-mms' ); ?></strong> <?php echo esc_html( $product_title ); ?><br>
                                    <strong><?php _e( 'Quantity:', 'wp-mms' ); ?></strong> <?php echo esc_html( $quantity ); ?>
                                </p>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}