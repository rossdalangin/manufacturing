<?php
/**
 * Meta Box Setup
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add meta boxes for the Supplier CPT.
 */
function wp_mms_add_supplier_meta_boxes() {
    add_meta_box(
        'wp_mms_supplier_details',
        __( 'Supplier Details', 'wp-mms' ),
        'wp_mms_render_supplier_meta_box',
        'wp_mms_supplier',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_supplier_meta_boxes' );

/**
 * Render the HTML for the Supplier meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_supplier_meta_box( $post ) {
    // Add a nonce field for security
    wp_nonce_field( 'wp_mms_save_supplier_meta_box_data', 'wp_mms_supplier_meta_box_nonce' );

    // Get existing values
    $contact_name = get_post_meta( $post->ID, '_wp_mms_contact_name', true );
    $email = get_post_meta( $post->ID, '_wp_mms_email', true );
    $phone = get_post_meta( $post->ID, '_wp_mms_phone', true );
    $website = get_post_meta( $post->ID, '_wp_mms_website', true );
    $address = get_post_meta( $post->ID, '_wp_mms_address', true );
    $lead_time = get_post_meta( $post->ID, '_wp_mms_lead_time', true );
    $notes = get_post_meta( $post->ID, '_wp_mms_notes', true );

    // Render the fields
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_contact_name"><?php _e( 'Contact Name', 'wp-mms' ); ?></label></th>
            <td><input type="text" id="wp_mms_contact_name" name="wp_mms_contact_name" value="<?php echo esc_attr( $contact_name ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_email"><?php _e( 'Email Address', 'wp-mms' ); ?></label></th>
            <td><input type="email" id="wp_mms_email" name="wp_mms_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_phone"><?php _e( 'Phone Number', 'wp-mms' ); ?></label></th>
            <td><input type="text" id="wp_mms_phone" name="wp_mms_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_website"><?php _e( 'Website', 'wp-mms' ); ?></label></th>
            <td><input type="url" id="wp_mms_website" name="wp_mms_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_address"><?php _e( 'Address', 'wp-mms' ); ?></label></th>
            <td><textarea id="wp_mms_address" name="wp_mms_address" rows="4" class="large-text"><?php echo esc_textarea( $address ); ?></textarea></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_lead_time"><?php _e( 'Lead Time (Days)', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_lead_time" name="wp_mms_lead_time" value="<?php echo esc_attr( $lead_time ); ?>" class="small-text" min="0" step="1" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_notes"><?php _e( 'Internal Notes', 'wp-mms' ); ?></label></th>
            <td><textarea id="wp_mms_notes" name="wp_mms_notes" rows="6" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea></td>
        </tr>
    </table>
    <?php
}

/**
 * Save the meta box data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_supplier_meta_box_data( $post_id ) {
    // Check if our nonce is set.
    if ( ! isset( $_POST['wp_mms_supplier_meta_box_nonce'] ) ) {
        return;
    }
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['wp_mms_supplier_meta_box_nonce'], 'wp_mms_save_supplier_meta_box_data' ) ) {
        return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'wp_mms_supplier' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Sanitize user input and update the meta fields.
    $fields = [
        'wp_mms_contact_name' => 'sanitize_text_field',
        'wp_mms_email'        => 'sanitize_email',
        'wp_mms_phone'        => 'sanitize_text_field',
        'wp_mms_website'      => 'esc_url_raw',
        'wp_mms_address'      => 'sanitize_textarea_field',
        'wp_mms_lead_time'    => 'intval',
        'wp_mms_notes'        => 'sanitize_textarea_field',
    ];

    foreach ( $fields as $key => $sanitize_callback ) {
        if ( isset( $_POST[ $key ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $key ] );
            update_post_meta( $post_id, '_' . $key, $value );
        }
    }
}
add_action( 'save_post', 'wp_mms_save_supplier_meta_box_data' );

/**
 * Add meta boxes for the Product CPT.
 */
function wp_mms_add_product_meta_boxes() {
    add_meta_box(
        'wp_mms_product_details',
        __( 'Product Details', 'wp-mms' ),
        'wp_mms_render_product_meta_box',
        'wp_mms_product',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_product_meta_boxes' );

/**
 * Render the HTML for the Product meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_product_meta_box( $post ) {
    // Add a nonce field for security
    wp_nonce_field( 'wp_mms_save_product_meta_box_data', 'wp_mms_product_meta_box_nonce' );

    // Get existing values
    $sku = get_post_meta( $post->ID, '_wp_mms_sku', true );
    $stock_quantity = get_post_meta( $post->ID, '_wp_mms_stock_quantity', true );
    $reorder_point = get_post_meta( $post->ID, '_wp_mms_reorder_point', true );
    $item_type = get_post_meta( $post->ID, '_wp_mms_item_type', true );
    $location = get_post_meta( $post->ID, '_wp_mms_warehouse_location', true );
    $unit_cost = get_post_meta( $post->ID, '_wp_mms_unit_cost', true );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_sku"><?php _e( 'SKU', 'wp-mms' ); ?></label></th>
            <td><input type="text" id="wp_mms_sku" name="wp_mms_sku" value="<?php echo esc_attr( $sku ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_stock_quantity"><?php _e( 'Stock Quantity', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_stock_quantity" name="wp_mms_stock_quantity" value="<?php echo esc_attr( $stock_quantity ); ?>" class="small-text" min="0" step="any" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_reorder_point"><?php _e( 'Reorder Point', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_reorder_point" name="wp_mms_reorder_point" value="<?php echo esc_attr( $reorder_point ); ?>" class="small-text" min="0" step="any" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_item_type"><?php _e( 'Item Type', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_item_type" name="wp_mms_item_type">
                    <option value="raw_material" <?php selected( $item_type, 'raw_material' ); ?>><?php _e( 'Raw Material', 'wp-mms' ); ?></option>
                    <option value="component" <?php selected( $item_type, 'component' ); ?>><?php _e( 'Component', 'wp-mms' ); ?></option>
                    <option value="finished_good" <?php selected( $item_type, 'finished_good' ); ?>><?php _e( 'Finished Good', 'wp-mms' ); ?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_warehouse_location"><?php _e( 'Warehouse Location', 'wp-mms' ); ?></label></th>
            <td><input type="text" id="wp_mms_warehouse_location" name="wp_mms_warehouse_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_unit_cost"><?php _e( 'Unit Cost', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_unit_cost" name="wp_mms_unit_cost" value="<?php echo esc_attr( $unit_cost ); ?>" class="small-text" min="0" step="0.01" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Save the meta box data for the Product CPT.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_product_meta_box_data( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['wp_mms_product_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_product_meta_box_nonce'], 'wp_mms_save_product_meta_box_data' ) ) {
        return;
    }
    // Check for autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check user permissions
    if ( isset( $_POST['post_type'] ) && 'wp_mms_product' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Sanitize and save the data
    $fields = [
        'wp_mms_sku' => 'sanitize_text_field',
        'wp_mms_stock_quantity' => 'floatval',
        'wp_mms_reorder_point' => 'floatval',
        'wp_mms_item_type' => 'sanitize_text_field',
        'wp_mms_warehouse_location' => 'sanitize_text_field',
        'wp_mms_unit_cost' => 'floatval',
    ];

    foreach ( $fields as $key => $sanitize_callback ) {
        if ( isset( $_POST[ $key ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $key ] );
            update_post_meta( $post_id, '_' . $key, $value );
        }
    }
}
add_action( 'save_post', 'wp_mms_save_product_meta_box_data' );

/**
 * Add meta boxes for the Purchase Order CPT.
 */
function wp_mms_add_purchase_order_meta_boxes() {
    add_meta_box(
        'wp_mms_po_details',
        __( 'Purchase Order Details', 'wp-mms' ),
        'wp_mms_render_po_meta_box',
        'wp_mms_purchase_order',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_purchase_order_meta_boxes' );

/**
 * Render the HTML for the Purchase Order meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_po_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_po_meta_box_data', 'wp_mms_po_meta_box_nonce' );

    // Get existing values
    $supplier_id = get_post_meta( $post->ID, '_wp_mms_supplier_id', true );
    $status = get_post_meta( $post->ID, '_wp_mms_status', true );
    $order_date = get_post_meta( $post->ID, '_wp_mms_order_date', true );
    $expected_date = get_post_meta( $post->ID, '_wp_mms_expected_date', true );
    $line_items = get_post_meta( $post->ID, '_wp_mms_line_items', true );

    // Get all suppliers and products
    $suppliers = get_posts( array( 'post_type' => 'wp_mms_supplier', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
    $products = get_posts( array( 'post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_supplier_id"><?php _e( 'Supplier', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_supplier_id" name="wp_mms_supplier_id" class="widefat">
                    <option value=""><?php _e( 'Select a Supplier', 'wp-mms' ); ?></option>
                    <?php foreach ( $suppliers as $supplier ) : ?>
                        <option value="<?php echo esc_attr( $supplier->ID ); ?>" <?php selected( $supplier_id, $supplier->ID ); ?>><?php echo esc_html( $supplier->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_status"><?php _e( 'Order Status', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_status" name="wp_mms_status">
                    <option value="pending" <?php selected( $status, 'pending' ); ?>><?php _e( 'Pending', 'wp-mms' ); ?></option>
                    <option value="approved" <?php selected( $status, 'approved' ); ?>><?php _e( 'Approved', 'wp-mms' ); ?></option>
                    <option value="shipped" <?php selected( $status, 'shipped' ); ?>><?php _e( 'Shipped', 'wp-mms' ); ?></option>
                    <option value="received" <?php selected( $status, 'received' ); ?>><?php _e( 'Received', 'wp-mms' ); ?></option>
                    <option value="canceled" <?php selected( $status, 'canceled' ); ?>><?php _e( 'Canceled', 'wp-mms' ); ?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_order_date"><?php _e( 'Order Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="wp_mms_order_date" name="wp_mms_order_date" value="<?php echo esc_attr( $order_date ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_expected_date"><?php _e( 'Expected Delivery Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="wp_mms_expected_date" name="wp_mms_expected_date" value="<?php echo esc_attr( $expected_date ); ?>" /></td>
        </tr>
    </table>
    <hr>
    <h3><?php _e( 'Products on this Order', 'wp-mms' ); ?></h3>
    <table id="po-line-items" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="manage-column" style="width: 50%;"><?php _e( 'Product', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Quantity', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Unit Price', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 20%;"><?php _e( 'Actions', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody id="line-items-container">
            <?php
            if ( ! empty( $line_items ) && is_array( $line_items ) ) {
                foreach ( $line_items as $i => $item ) {
                    ?>
                    <tr class="line-item">
                        <td>
                            <select name="wp_mms_line_items[<?php echo $i; ?>][product_id]" class="widefat">
                                <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                                <?php foreach ( $products as $product ) : ?>
                                    <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="wp_mms_line_items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="1" step="1" /></td>
                        <td><input type="number" name="wp_mms_line_items[<?php echo $i; ?>][unit_price]" value="<?php echo esc_attr( $item['unit_price'] ); ?>" class="small-text" min="0" step="0.01" /></td>
                        <td><a href="#" class="button remove-line-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
    <p>
        <a href="#" id="add-line-item" class="button button-primary"><?php _e( 'Add Product', 'wp-mms' ); ?></a>
    </p>
    <script type="text/template" id="line-item-template">
        <tr class="line-item">
            <td>
                <select name="wp_mms_line_items[{index}][product_id]" class="widefat">
                    <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                    <?php foreach ( $products as $product ) : ?>
                        <option value="<?php echo esc_attr( $product->ID ); ?>"><?php echo esc_html( $product->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" name="wp_mms_line_items[{index}][quantity]" value="1" class="small-text" min="1" step="1" /></td>
            <td><input type="number" name="wp_mms_line_items[{index}][unit_price]" value="0.00" class="small-text" min="0" step="0.01" /></td>
            <td><a href="#" class="button remove-line-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
        </tr>
    </script>
    <?php
}


/**
 * Save the meta box data for the Purchase Order CPT and handle stock updates.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_po_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wp_mms_po_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_po_meta_box_nonce'], 'wp_mms_save_po_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'wp_mms_purchase_order' ) {
        return;
    }

    // Get old data BEFORE any changes are made
    $old_status = get_post_meta( $post_id, '_wp_mms_status', true );
    $old_line_items = get_post_meta( $post_id, '_wp_mms_line_items', true ) ?: [];

    // Get new status from submission
    $new_status = isset( $_POST['wp_mms_status'] ) ? sanitize_text_field( $_POST['wp_mms_status'] ) : '';

    // Save main fields
    $fields = [
        'wp_mms_supplier_id' => 'intval',
        'wp_mms_status' => 'sanitize_text_field',
        'wp_mms_order_date' => 'sanitize_text_field',
        'wp_mms_expected_date' => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize_callback ) {
        if ( isset( $_POST[ $key ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $key ] );
            update_post_meta( $post_id, '_' . $key, $value );
        }
    }

    // Sanitize and save line items
    $new_line_items = [];
    if ( isset( $_POST['wp_mms_line_items'] ) && is_array( $_POST['wp_mms_line_items'] ) ) {
        foreach ( $_POST['wp_mms_line_items'] as $item ) {
            if ( empty( $item['product_id'] ) ) {
                continue;
            }
            $new_line_items[] = [
                'product_id' => intval( $item['product_id'] ),
                'quantity'   => intval( $item['quantity'] ),
                'unit_price' => floatval( $item['unit_price'] ),
            ];
        }
    }
    update_post_meta( $post_id, '_wp_mms_line_items', $new_line_items );

    // --- Refactored Stock Adjustment Logic ---

    // Helper function to adjust stock for a set of items
    $adjust_stock = function( $items, $operation ) {
        if ( empty( $items ) || !is_array($items) ) return;
        foreach ( $items as $item ) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            if ( empty( $product_id ) || !isset($item['quantity']) ) continue;

            $current_stock = get_post_meta( $product_id, '_wp_mms_stock_quantity', true );
            $new_stock = ( $operation === 'add' )
                ? floatval( $current_stock ) + floatval( $quantity )
                : floatval( $current_stock ) - floatval( $quantity );
            update_post_meta( $product_id, '_wp_mms_stock_quantity', $new_stock );
        }
    };

    // Case 1: Status changed TO received
    if ( $new_status === 'received' && $old_status !== 'received' ) {
        $adjust_stock( $new_line_items, 'add' );
    }
    // Case 2: Status changed FROM received
    else if ( $new_status !== 'received' && $old_status === 'received' ) {
        $adjust_stock( $old_line_items, 'subtract' );
    }
    // Case 3: Status REMAINS received, check if line items changed
    else if ( $new_status === 'received' && $old_status === 'received' ) {
        // Only adjust stock if the line items have actually changed.
        if ( $old_line_items != $new_line_items ) {
            // Revert old stock counts and apply new ones
            $adjust_stock( $old_line_items, 'subtract' );
            $adjust_stock( $new_line_items, 'add' );
        }
    }
    // Case 4 (implied): Status was not and is not 'received'. Do nothing.
}
add_action( 'save_post', 'wp_mms_save_po_meta_box_data' );

/**
 * Add meta boxes for the BOM CPT.
 */
function wp_mms_add_bom_meta_boxes() {
    add_meta_box(
        'wp_mms_bom_details',
        __( 'Bill of Materials Details', 'wp-mms' ),
        'wp_mms_render_bom_meta_box',
        'wp_mms_bom',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_bom_meta_boxes' );

/**
 * Render the HTML for the BOM meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_bom_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_bom_meta_box_data', 'wp_mms_bom_meta_box_nonce' );

    // Get existing values
    $finished_product_id = get_post_meta( $post->ID, '_wp_mms_finished_product_id', true );
    $components = get_post_meta( $post->ID, '_wp_mms_components', true );

    // Get finished goods (products) and components (raw materials/sub-assemblies)
    $finished_goods = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'meta_key' => '_wp_mms_item_type', 'meta_value' => 'finished_good'] );
    $component_products = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'meta_query' => [
        'relation' => 'OR',
        ['meta_key' => '_wp_mms_item_type', 'meta_value' => 'raw_material'],
        ['meta_key' => '_wp_mms_item_type', 'meta_value' => 'component']
    ]] );

    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_finished_product_id"><?php _e( 'Finished Product', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_finished_product_id" name="wp_mms_finished_product_id" class="widefat">
                    <option value=""><?php _e( 'Select a Finished Product', 'wp-mms' ); ?></option>
                    <?php foreach ( $finished_goods as $product ) : ?>
                        <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $finished_product_id, $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e( 'Select the final product that this Bill of Materials is for.', 'wp-mms' ); ?></p>
            </td>
        </tr>
    </table>
    <hr>
    <h3><?php _e( 'Components', 'wp-mms' ); ?></h3>
    <table id="bom-components" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="manage-column" style="width: 70%;"><?php _e( 'Component Product', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Quantity', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Actions', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody id="components-container">
             <?php
            if ( ! empty( $components ) && is_array( $components ) ) {
                foreach ( $components as $i => $item ) {
                    ?>
                    <tr class="component-item">
                        <td>
                            <select name="wp_mms_components[<?php echo $i; ?>][product_id]" class="widefat">
                                <option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option>
                                <?php foreach ( $component_products as $product ) : ?>
                                    <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="wp_mms_components[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="0" step="any" /></td>
                        <td><a href="#" class="button remove-component-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
     <p>
        <a href="#" id="add-component-item" class="button button-primary"><?php _e( 'Add Component', 'wp-mms' ); ?></a>
    </p>
    <script type="text/template" id="component-item-template">
        <tr class="component-item">
            <td>
                <select name="wp_mms_components[{index}][product_id]" class="widefat component-product-select">
                     <option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option>
                </select>
            </td>
            <td><input type="number" name="wp_mms_components[{index}][quantity]" value="1" class="small-text" min="0" step="any" /></td>
            <td><a href="#" class="button remove-component-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
        </tr>
    </script>
    <?php
}

/**
 * Save the meta box data for the BOM CPT.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_bom_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wp_mms_bom_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_bom_meta_box_nonce'], 'wp_mms_save_bom_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'wp_mms_bom' ) {
        return;
    }

    // Save finished product link
    if ( isset( $_POST['wp_mms_finished_product_id'] ) ) {
        update_post_meta( $post_id, '_wp_mms_finished_product_id', intval( $_POST['wp_mms_finished_product_id'] ) );
    }

    // Save components
    $new_components = [];
    if ( isset( $_POST['wp_mms_components'] ) && is_array( $_POST['wp_mms_components'] ) ) {
        foreach ( $_POST['wp_mms_components'] as $item ) {
            if ( empty( $item['product_id'] ) || !isset( $item['quantity'] ) ) {
                continue;
            }
            $new_components[] = [
                'product_id' => intval( $item['product_id'] ),
                'quantity'   => floatval( $item['quantity'] ),
            ];
        }
    }
    update_post_meta( $post_id, '_wp_mms_components', $new_components );

    // --- Auto Cost Roll-up ---
    wp_mms_calculate_bom_cost( $post_id );
}
add_action( 'save_post', 'wp_mms_save_bom_meta_box_data' );


/**
 * Calculate the total cost of a BOM and save it to the finished product.
 *
 * @param int $bom_post_id The ID of the BOM post.
 */
function wp_mms_calculate_bom_cost( $bom_post_id ) {
    $finished_product_id = get_post_meta( $bom_post_id, '_wp_mms_finished_product_id', true );
    if ( empty( $finished_product_id ) ) {
        return;
    }

    $components = get_post_meta( $bom_post_id, '_wp_mms_components', true );
    $total_cost = 0;

    if ( ! empty( $components ) && is_array( $components ) ) {
        foreach ( $components as $item ) {
            $component_id = $item['product_id'];
            $quantity = floatval( $item['quantity'] );
            $component_cost = floatval( get_post_meta( $component_id, '_wp_mms_unit_cost', true ) );
            $total_cost += ( $component_cost * $quantity );
        }
    }

    // Save the calculated cost on the finished product's post meta
    update_post_meta( $finished_product_id, '_wp_mms_bom_cost', $total_cost );
}

/**
 * Add a read-only meta box to Finished Goods to show the calculated BOM cost.
 */
function wp_mms_add_product_cost_meta_box() {
    global $post;
    if ( isset($post->ID) && get_post_meta( $post->ID, '_wp_mms_item_type', true ) === 'finished_good' ) {
        add_meta_box(
            'wp_mms_bom_cost_display',
            __( 'Calculated BOM Cost', 'wp-mms' ),
            'wp_mms_render_product_cost_meta_box',
            'wp_mms_product',
            'side',
            'low'
        );
    }
}
add_action( 'add_meta_boxes_wp_mms_product', 'wp_mms_add_product_cost_meta_box' );

/**
 * Render the HTML for the product cost display meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_product_cost_meta_box( $post ) {
    $bom_cost = get_post_meta( $post->ID, '_wp_mms_bom_cost', true );
    $cost_display = is_numeric( $bom_cost ) ? number_format_i18n( $bom_cost, 2 ) : 'N/A';
    echo '<strong>' . esc_html( $cost_display ) . '</strong>';
    echo '<p class="description">' . __( 'This cost is automatically calculated from the product\'s Bill of Materials.', 'wp-mms' ) . '</p>';
}