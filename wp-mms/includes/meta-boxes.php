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
        if ( ! current_user_can( 'edit_mms_supplier', $post_id ) ) {
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
        if ( ! current_user_can( 'edit_mms_product', $post_id ) ) {
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
    if ( ! current_user_can( 'edit_mms_purchase_order', $post_id ) ) {
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

    // --- Lot-Based Stock Adjustment Logic ---

    $create_lots_for_po = function( $po_id, $line_items ) {
        if( empty($line_items) || !is_array($line_items) ) return;

        $created_lot_ids = [];
        foreach( $line_items as $index => $item ) {
            $lot_number = 'PO-' . $po_id . '-' . ($index + 1);
            $lot_args = [
                'post_title'   => $lot_number,
                'post_status'  => 'publish',
                'post_type'    => 'wp_mms_lot',
            ];
            $new_lot_id = wp_insert_post( $lot_args, true );
            if ( !is_wp_error($new_lot_id) ) {
                update_post_meta( $new_lot_id, '_wp_mms_product_id', $item['product_id'] );
                update_post_meta( $new_lot_id, '_wp_mms_quantity', $item['quantity'] );
                $created_lot_ids[] = $new_lot_id;
            }
        }
        update_post_meta( $po_id, '_wp_mms_created_lot_ids', $created_lot_ids );
    };

    $delete_lots_for_po = function( $po_id ) {
        $lot_ids = get_post_meta( $po_id, '_wp_mms_created_lot_ids', true );
        if ( !empty($lot_ids) && is_array($lot_ids) ) {
            foreach( $lot_ids as $lot_id ) {
                wp_delete_post( $lot_id, true ); // true to force delete
            }
        }
        delete_post_meta( $po_id, '_wp_mms_created_lot_ids' );
    };

    // Case 1: Status changed TO received
    if ( $new_status === 'received' && $old_status !== 'received' ) {
        $create_lots_for_po( $post_id, $new_line_items );
        update_post_meta( $post_id, '_wp_mms_date_received', current_time( 'Y-m-d' ) );
    }
    // Case 2: Status changed FROM received
    else if ( $new_status !== 'received' && $old_status === 'received' ) {
        $delete_lots_for_po( $post_id );
        delete_post_meta( $post_id, '_wp_mms_date_received' );
    }
    // Case 3: Status REMAINS received, but line items might have changed
    else if ( $new_status === 'received' && $old_status === 'received' ) {
        if ( $old_line_items != $new_line_items ) {
            $delete_lots_for_po( $post_id );
            $create_lots_for_po( $post_id, $new_line_items );
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

    // Get finished goods (products) and all possible components
    $finished_goods = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_wp_mms_item_type', 'meta_value' => 'finished_good'] );
    $component_products = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'] );

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
                <th class="manage-column column-icon" style="width: 5%;"></th>
                <th class="manage-column" style="width: 65%;"><?php _e( 'Component Product', 'wp-mms' ); ?></th>
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
                        <td class="component-handle" style="cursor: move; text-align: center;"><span class="dashicons dashicons-move"></span></td>
                        <td>
                            <select name="wp_mms_components[<?php echo $i; ?>][product_id]" class="widefat">
                                <option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option>
                                <?php
                                $item_type_labels = [
                                    'raw_material' => __( 'Raw Material', 'wp-mms' ),
                                    'component' => __( 'Component', 'wp-mms' ),
                                    'finished_good' => __( 'Sub-Assembly', 'wp-mms' ),
                                ];
                                foreach ( $component_products as $product ) :
                                    $item_type = get_post_meta( $product->ID, '_wp_mms_item_type', true );
                                    $display_text = $product->post_title . ' (' . ( $item_type_labels[$item_type] ?? $item_type ) . ')';
                                ?>
                                    <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $display_text ); ?></option>
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
            <td class="component-handle" style="cursor: move; text-align: center;"><span class="dashicons dashicons-move"></span></td>
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
    if ( ! current_user_can( 'edit_mms_bom', $post_id ) ) {
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
 * Add meta boxes for the Production Order CPT.
 */
function wp_mms_add_production_order_meta_boxes() {
    add_meta_box(
        'wp_mms_production_order_details',
        __( 'Production Order Details', 'wp-mms' ),
        'wp_mms_render_production_order_meta_box',
        'wp_mms_production_order',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_production_order_meta_boxes' );
add_action( 'add_meta_boxes_wp_mms_production_order', 'wp_mms_add_consumed_lots_meta_box' );

/**
 * Add a meta box to show the consumed lots for a completed production order.
 */
function wp_mms_add_consumed_lots_meta_box( $post ) {
    if ( get_post_meta( $post->ID, '_wp_mms_status', true ) === 'completed' ) {
        add_meta_box(
            'wp_mms_consumed_lots',
            __( 'Consumption Record', 'wp-mms' ),
            'wp_mms_render_consumed_lots_meta_box',
            'wp_mms_production_order',
            'normal',
            'low'
        );
    }
}

/**
 * Render the HTML for the consumed lots meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_consumed_lots_meta_box( $post ) {
    $consumed_lots = get_post_meta( $post->ID, '_wp_mms_consumed_lots', true );

    if ( empty( $consumed_lots ) || !is_array($consumed_lots) ) {
        echo '<p>' . __( 'No consumption record found.', 'wp-mms' ) . '</p>';
        return;
    }
    ?>
    <p class="description"><?php _e( 'This table shows the specific component lots that were consumed to fulfill this production order.', 'wp-mms' ); ?></p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Component Lot / Batch', 'wp-mms' ); ?></th>
                <th><?php _e( 'Quantity Consumed', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $consumed_lots as $lot_id => $data ) : ?>
                <tr>
                    <td><?php echo esc_html( $data['title'] ); ?></td>
                    <td><?php echo esc_html( $data['qty'] ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}


/**
 * Render the HTML for the Production Order meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_production_order_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_production_order_meta_box_data', 'wp_mms_production_order_meta_box_nonce' );

    // Get existing values
    $product_id = get_post_meta( $post->ID, '_wp_mms_product_id', true );
    $quantity = get_post_meta( $post->ID, '_wp_mms_quantity', true );
    $status = get_post_meta( $post->ID, '_wp_mms_status', true );
    $start_date = get_post_meta( $post->ID, '_wp_mms_start_date', true );
    $end_date = get_post_meta( $post->ID, '_wp_mms_end_date', true );

    // Get finished goods
    $finished_goods = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_wp_mms_item_type', 'meta_value' => 'finished_good'] );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_product_id"><?php _e( 'Product to Produce', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_product_id" name="wp_mms_product_id" class="widefat">
                    <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                    <?php foreach ( $finished_goods as $product ) : ?>
                        <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product_id, $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_quantity"><?php _e( 'Quantity to Produce', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_quantity" name="wp_mms_quantity" value="<?php echo esc_attr( $quantity ); ?>" class="small-text" min="1" step="1" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_status"><?php _e( 'Order Status', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_status" name="wp_mms_status">
                    <option value="pending" <?php selected( $status, 'pending' ); ?>><?php _e( 'Pending', 'wp-mms' ); ?></option>
                    <option value="in_progress" <?php selected( $status, 'in_progress' ); ?>><?php _e( 'In Progress', 'wp-mms' ); ?></option>
                    <option value="completed" <?php selected( $status, 'completed' ); ?>><?php _e( 'Completed', 'wp-mms' ); ?></option>
                    <option value="canceled" <?php selected( $status, 'canceled' ); ?>><?php _e( 'Canceled', 'wp-mms' ); ?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_start_date"><?php _e( 'Start Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="wp_mms_start_date" name="wp_mms_start_date" value="<?php echo esc_attr( $start_date ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_end_date"><?php _e( 'Expected Completion Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="wp_mms_end_date" name="wp_mms_end_date" value="<?php echo esc_attr( $end_date ); ?>" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Recursively explodes a BOM to determine the total raw materials required.
 *
 * @param int   $product_id The ID of the product to explode.
 * @param float $qty_needed The quantity of this product needed.
 * @param array &$raw_materials Reference to an array to store the final list of raw materials.
 * @param array &$visited_boms  To prevent infinite loops.
 */
function wp_mms_get_exploded_bom_recursive( $product_id, $qty_needed, &$raw_materials, &$visited_boms ) {
    if ( in_array( $product_id, $visited_boms ) ) return;
    $visited_boms[] = $product_id;

    $bom_query = new WP_Query(['post_type' => 'wp_mms_bom', 'posts_per_page' => 1, 'meta_key' => '_wp_mms_finished_product_id', 'meta_value' => $product_id, 'fields' => 'ids']);
    if ( !$bom_query->have_posts() ) return;

    $bom_id = $bom_query->posts[0];
    $components = get_post_meta( $bom_id, '_wp_mms_components', true );
    if ( empty( $components ) || !is_array( $components ) ) return;

    foreach ( $components as $item ) {
        $component_id = $item['product_id'];
        if ( empty( $component_id ) ) continue;

        $component_qty_per_parent = floatval( $item['quantity'] );
        $total_component_qty = $component_qty_per_parent * $qty_needed;
        $component_item_type = get_post_meta( $component_id, '_wp_mms_item_type', true );

        if ( 'finished_good' === $component_item_type ) {
            wp_mms_get_exploded_bom_recursive( $component_id, $total_component_qty, $raw_materials, $visited_boms );
        } else {
            if ( ! isset( $raw_materials[ $component_id ] ) ) {
                $raw_materials[ $component_id ] = 0;
            }
            $raw_materials[ $component_id ] += $total_component_qty;
        }
    }
}

/**
 * Save the meta box data for the Production Order CPT and handle inventory adjustments.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_production_order_meta_box_data( $post_id ) {
     if ( ! isset( $_POST['wp_mms_production_order_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_production_order_meta_box_nonce'], 'wp_mms_save_production_order_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_mms_production_order', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'wp_mms_production_order' ) {
        return;
    }

    // Get old data before updating
    $old_status = get_post_meta( $post_id, '_wp_mms_status', true );
    $old_details = [
        'product_id' => get_post_meta( $post_id, '_wp_mms_product_id', true ),
        'quantity'   => get_post_meta( $post_id, '_wp_mms_quantity', true ),
        'consumed_lots' => get_post_meta( $post_id, '_wp_mms_consumed_lots', true ),
        'output_lot_id' => get_post_meta( $post_id, '_wp_mms_output_lot_id', true ),
    ];

    // Sanitize and save new data
    $new_status = isset( $_POST['wp_mms_status'] ) ? sanitize_text_field( $_POST['wp_mms_status'] ) : '';
    $new_details = [
        'product_id' => isset( $_POST['wp_mms_product_id'] ) ? intval( $_POST['wp_mms_product_id'] ) : 0,
        'quantity'   => isset( $_POST['wp_mms_quantity'] ) ? intval( $_POST['wp_mms_quantity'] ) : 0,
    ];

    $fields = [
        'wp_mms_product_id'   => 'intval',
        'wp_mms_quantity'     => 'intval',
        'wp_mms_status'       => 'sanitize_text_field',
        'wp_mms_start_date'   => 'sanitize_text_field',
        'wp_mms_end_date'     => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize_callback ) {
        if ( isset( $_POST[ $key ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $key ] );
            update_post_meta( $post_id, '_' . $key, $value );
        }
    }

    // --- FIFO Lot-Based Inventory Adjustment Logic ---
    $adjust_inventory_for_production = function( $prod_order_id, $details, $direction ) {
        $product_id = $details['product_id'];
        $quantity_produced = $details['quantity'];
        $affected_products = []; // Track products whose stock needs recalculating.

        if ( empty( $product_id ) || empty( $quantity_produced ) ) {
            return;
        }

        if ( 'complete' === $direction ) {
            $raw_materials_needed = [];
            $visited_boms = [];
            wp_mms_get_exploded_bom_recursive( $product_id, $quantity_produced, $raw_materials_needed, $visited_boms );

            $consumed_lots_record = [];
            foreach ( $raw_materials_needed as $material_id => $qty_to_consume ) {
                $affected_products[] = $material_id;
                $lots_query = new WP_Query([
                    'post_type' => 'wp_mms_lot', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'ASC',
                    'meta_query' => [ ['key' => '_wp_mms_product_id', 'value' => $material_id] ]
                ]);

                if ( $lots_query->have_posts() ) {
                    while ( $lots_query->have_posts() && $qty_to_consume > 0 ) {
                        $lots_query->the_post();
                        $lot_id = get_the_ID();
                        $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );
                        $qty_taken = min( $lot_qty, $qty_to_consume );

                        // Enhance the consumption record for robust restoration
                        $consumed_lots_record[$lot_id] = [
                            'qty' => $qty_taken,
                            'product_id' => $material_id,
                            'title' => get_the_title()
                        ];

                        $new_lot_qty = $lot_qty - $qty_taken;
                        $qty_to_consume -= $qty_taken;

                        if ( $new_lot_qty <= 0 ) {
                            wp_delete_post( $lot_id, true );
                        } else {
                            update_post_meta( $lot_id, '_wp_mms_quantity', $new_lot_qty );
                        }
                    }
                }
                wp_reset_postdata();
            }
            update_post_meta( $prod_order_id, '_wp_mms_consumed_lots', $consumed_lots_record );

            // Create a new lot for the finished good.
            $output_lot_id = wp_insert_post([
                'post_title' => 'PROD-' . $prod_order_id,
                'post_type' => 'wp_mms_lot', 'post_status' => 'publish',
            ]);
            if ( !is_wp_error($output_lot_id) ) {
                update_post_meta( $output_lot_id, '_wp_mms_product_id', $product_id );
                update_post_meta( $output_lot_id, '_wp_mms_quantity', $quantity_produced );
                update_post_meta( $prod_order_id, '_wp_mms_output_lot_id', $output_lot_id );
            }

        } elseif ( 'revert' === $direction ) {
            // Restore consumed lots.
            $consumed_lots = $details['consumed_lots'];
            if ( !empty($consumed_lots) && is_array($consumed_lots) ) {
                foreach ( $consumed_lots as $lot_id => $data ) {
                    $affected_products[] = $data['product_id'];
                    $existing_lot = get_post($lot_id);
                    if ($existing_lot) { // If lot still exists, just add quantity back
                        $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );
                        update_post_meta( $lot_id, '_wp_mms_quantity', $lot_qty + $data['qty'] );
                    } else { // If lot was deleted, re-create it
                        $recreated_lot_id = wp_insert_post([
                            'post_title' => $data['title'], 'post_type' => 'wp_mms_lot', 'post_status' => 'publish',
                        ]);
                        if (!is_wp_error($recreated_lot_id)) {
                             update_post_meta( $recreated_lot_id, '_wp_mms_product_id', $data['product_id'] );
                             update_post_meta( $recreated_lot_id, '_wp_mms_quantity', $data['qty'] );
                        }
                    }
                }
            }
            delete_post_meta( $prod_order_id, '_wp_mms_consumed_lots' );

            // Delete the output lot for the finished good.
            $output_lot_id = $details['output_lot_id'];
            if ( !empty($output_lot_id) ) {
                wp_delete_post( $output_lot_id, true );
            }
            delete_post_meta( $prod_order_id, '_wp_mms_output_lot_id' );
        }

        // Explicitly update stock for all affected products
        $affected_products[] = $product_id;
        foreach ( array_unique($affected_products) as $p_id ) {
            wp_mms_update_product_stock_from_lots( $p_id );
        }
    };

    // Case 1: Status changed TO completed
    if ( $new_status === 'completed' && $old_status !== 'completed' ) {
        $adjust_inventory_for_production( $post_id, $new_details, 'complete' );
    }
    // Case 2: Status changed FROM completed
    else if ( $new_status !== 'completed' && $old_status === 'completed' ) {
        $adjust_inventory_for_production( $post_id, $old_details, 'revert' );
    }
    // Case 3: Status REMAINS completed, but details might have changed
    else if ( $new_status === 'completed' && $old_status === 'completed' ) {
        if ( $old_details['product_id'] != $new_details['product_id'] || $old_details['quantity'] != $new_details['quantity'] ) {
            $adjust_inventory_for_production( $post_id, $old_details, 'revert' );
            $adjust_inventory_for_production( $post_id, $new_details, 'complete' );
        }
    }
}
add_action( 'save_post', 'wp_mms_save_production_order_meta_box_data' );


/**
 * Recursively calculate the total cost of a BOM and save it to the finished product.
 *
 * @param int   $bom_post_id  The ID of the BOM post to calculate.
 * @param array $visited_boms An array of BOM IDs already visited in this recursion, to prevent infinite loops.
 * @return float The calculated cost.
 */
function wp_mms_calculate_bom_cost( $bom_post_id, $visited_boms = [] ) {
    // Protection against infinite loops
    if ( in_array( $bom_post_id, $visited_boms ) ) {
        return 0; // Circular dependency detected, return 0 cost.
    }
    $visited_boms[] = $bom_post_id;

    $finished_product_id = get_post_meta( $bom_post_id, '_wp_mms_finished_product_id', true );
    $components = get_post_meta( $bom_post_id, '_wp_mms_components', true );
    $total_cost = 0;

    if ( ! empty( $components ) && is_array( $components ) ) {
        foreach ( $components as $item ) {
            $component_id = $item['product_id'];
            $quantity = floatval( $item['quantity'] );
            $component_cost = 0;

            if ( empty( $component_id ) ) {
                continue;
            }

            $component_item_type = get_post_meta( $component_id, '_wp_mms_item_type', true );

            if ( 'finished_good' === $component_item_type ) {
                // It's a sub-assembly. Find its BOM and recursively calculate its cost.
                $sub_bom_query = new WP_Query([
                    'post_type' => 'wp_mms_bom',
                    'posts_per_page' => 1,
                    'meta_key' => '_wp_mms_finished_product_id',
                    'meta_value' => $component_id,
                    'fields' => 'ids',
                ]);

                if ( ! empty( $sub_bom_query->posts ) ) {
                    $sub_bom_id = $sub_bom_query->posts[0];
                    $component_cost = wp_mms_calculate_bom_cost( $sub_bom_id, $visited_boms );
                }
            } else {
                // It's a raw material or simple component. Use its direct unit cost.
                $component_cost = floatval( get_post_meta( $component_id, '_wp_mms_unit_cost', true ) );
            }

            $total_cost += ( $component_cost * $quantity );
        }
    }

    // Save the calculated cost on the finished product's post meta.
    if ( ! empty( $finished_product_id ) ) {
        update_post_meta( $finished_product_id, '_wp_mms_bom_cost', $total_cost );
    }

    // Return the calculated cost for use in parent recursive calls.
    return $total_cost;
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
add_action( 'add_meta_boxes_wp_mms_product', 'wp_mms_add_exploded_bom_meta_box' );
add_action( 'add_meta_boxes_wp_mms_product', 'wp_mms_add_product_lots_meta_box' );

/**
 * Add a meta box to show all lots for a product.
 */
function wp_mms_add_product_lots_meta_box( $post ) {
    add_meta_box(
        'wp_mms_product_lots',
        __( 'Inventory Lots / Batches', 'wp-mms' ),
        'wp_mms_render_product_lots_meta_box',
        'wp_mms_product',
        'normal',
        'low'
    );
}

/**
 * Render the HTML for the product lots meta box.
 */
function wp_mms_render_product_lots_meta_box( $post ) {
    $lot_query = new WP_Query([
        'post_type' => 'wp_mms_lot',
        'posts_per_page' => -1,
        'meta_key' => '_wp_mms_product_id',
        'meta_value' => $post->ID,
    ]);

    if ( !$lot_query->have_posts() ) {
        echo '<p>' . __( 'No lots found for this product.', 'wp-mms' ) . '</p>';
        return;
    }
    ?>
    <p class="description"><?php _e( 'This table shows the individual lots that make up the total stock quantity for this product.', 'wp-mms' ); ?></p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Lot / Batch Number', 'wp-mms' ); ?></th>
                <th><?php _e( 'Quantity', 'wp-mms' ); ?></th>
                <th><?php _e( 'Expiry Date', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php while ( $lot_query->have_posts() ) : $lot_query->the_post(); ?>
                <tr>
                    <td><a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>"><?php the_title(); ?></a></td>
                    <td><?php echo esc_html( get_post_meta( get_the_ID(), '_wp_mms_quantity', true ) ); ?></td>
                    <td><?php echo esc_html( get_post_meta( get_the_ID(), '_wp_mms_expiry_date', true ) ?: 'N/A' ); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    wp_reset_postdata();
}


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

/**
 * Add a meta box to show the fully exploded BOM for a finished good.
 */
function wp_mms_add_exploded_bom_meta_box() {
    global $post;
    if ( isset($post->ID) && get_post_meta( $post->ID, '_wp_mms_item_type', true ) === 'finished_good' ) {
        add_meta_box(
            'wp_mms_exploded_bom',
            __( 'Exploded Bill of Materials (Raw Materials)', 'wp-mms' ),
            'wp_mms_render_exploded_bom_meta_box',
            'wp_mms_product',
            'normal',
            'low'
        );
    }
}

/**
 * Render the HTML for the exploded BOM meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_exploded_bom_meta_box( $post ) {
    $raw_materials = wp_mms_get_exploded_bom_materials( $post->ID );

    if ( empty( $raw_materials ) ) {
        echo '<p>' . __( 'No Bill of Materials found or no raw materials required.', 'wp-mms' ) . '</p>';
        return;
    }
    ?>
    <p class="description"><?php _e( 'This table shows the total quantity of each raw material required to produce one unit of this finished good.', 'wp-mms' ); ?></p>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Raw Material', 'wp-mms' ); ?></th>
                <th><?php _e( 'Total Quantity Required', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $raw_materials as $material_id => $quantity ) : ?>
                <tr>
                    <td><?php echo esc_html( get_the_title( $material_id ) ); ?></td>
                    <td><?php echo esc_html( $quantity ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

/**
 * Wrapper function to initiate the BOM explosion.
 *
 * @param int $product_id The ID of the finished good.
 * @return array A flat list of raw materials [id => total_quantity].
 */
function wp_mms_get_exploded_bom_materials( $product_id ) {
    $raw_materials = [];
    $visited_boms = [];
    wp_mms_get_exploded_bom_recursive( $product_id, 1, $raw_materials, $visited_boms );
    return $raw_materials;
}

/**
 * Add meta boxes for the Lot CPT.
 */
function wp_mms_add_lot_meta_boxes() {
    add_meta_box(
        'wp_mms_lot_details',
        __( 'Lot / Batch Details', 'wp-mms' ),
        'wp_mms_render_lot_meta_box',
        'wp_mms_lot',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_lot_meta_boxes' );

/**
 * Render the HTML for the Lot meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_lot_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_lot_meta_box_data', 'wp_mms_lot_meta_box_nonce' );

    $product_id = get_post_meta( $post->ID, '_wp_mms_product_id', true );
    $quantity = get_post_meta( $post->ID, '_wp_mms_quantity', true );
    $expiry_date = get_post_meta( $post->ID, '_wp_mms_expiry_date', true );

    $products = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'] );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="_wp_mms_product_id"><?php _e( 'Product', 'wp-mms' ); ?></label></th>
            <td>
                <select id="_wp_mms_product_id" name="_wp_mms_product_id" class="widefat">
                    <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                    <?php foreach ( $products as $product ) : ?>
                        <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product_id, $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="_wp_mms_quantity"><?php _e( 'Quantity in this Lot', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="_wp_mms_quantity" name="_wp_mms_quantity" value="<?php echo esc_attr( $quantity ); ?>" class="small-text" min="0" step="any" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="_wp_mms_expiry_date"><?php _e( 'Expiry Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="_wp_mms_expiry_date" name="_wp_mms_expiry_date" value="<?php echo esc_attr( $expiry_date ); ?>" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Save the meta box data for the Lot CPT.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_lot_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wp_mms_lot_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_lot_meta_box_nonce'], 'wp_mms_save_lot_meta_box_data' ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'wp_mms_lot' ) {
        return;
    }
    if ( ! current_user_can( 'edit_mms_lot', $post_id ) ) {
        return;
    }

    $fields = [
        '_wp_mms_product_id' => 'intval',
        '_wp_mms_quantity' => 'floatval',
        '_wp_mms_expiry_date' => 'sanitize_text_field',
    ];

    foreach ( $fields as $key => $sanitize_callback ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, call_user_func( $sanitize_callback, $_POST[ $key ] ) );
        }
    }
}
add_action( 'save_post', 'wp_mms_save_lot_meta_box_data' );

/**
 * Add meta boxes for the Requisition CPT.
 */
function wp_mms_add_requisition_meta_boxes() {
    add_meta_box(
        'wp_mms_requisition_details',
        __( 'Requisition Details', 'wp-mms' ),
        'wp_mms_render_requisition_meta_box',
        'wp_mms_requisition',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_requisition_meta_boxes' );
add_action( 'add_meta_boxes_wp_mms_requisition', 'wp_mms_add_requisition_actions_meta_box' );


/**
 * Add an 'Actions' meta box for requisitions.
 */
function wp_mms_add_requisition_actions_meta_box( $post ) {
    if ( get_post_meta( $post->ID, '_wp_mms_status', true ) === 'approved' ) {
        add_meta_box(
            'wp_mms_requisition_actions',
            __( 'Actions', 'wp-mms' ),
            'wp_mms_render_requisition_actions_meta_box',
            'wp_mms_requisition',
            'side',
            'high'
        );
    }
}

/**
 * Render the HTML for the Requisition Actions meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_requisition_actions_meta_box( $post ) {
    $convert_url = wp_nonce_url(
        admin_url( 'admin.php?page=wp_mms_reports&action=convert_to_po&requisition_id=' . $post->ID ),
        'convert_req_to_po_' . $post->ID
    );
    ?>
    <p>
        <a href="<?php echo esc_url( $convert_url ); ?>" class="button button-primary button-large">
            <?php _e( 'Create Purchase Order', 'wp-mms' ); ?>
        </a>
    </p>
    <p class="description"><?php _e( 'This will create a new draft Purchase Order from this requisition and mark this requisition as completed.', 'wp-mms' ); ?></p>
    <?php
}


/**
 * Render the HTML for the Requisition meta box.
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_requisition_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_requisition_meta_box_data', 'wp_mms_requisition_meta_box_nonce' );

    // Get existing values
    $status = get_post_meta( $post->ID, '_wp_mms_status', true ) ?: 'pending';
    $desired_date = get_post_meta( $post->ID, '_wp_mms_desired_date', true );
    $requested_items = get_post_meta( $post->ID, '_wp_mms_requested_items', true );
    $products = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'] );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="wp_mms_status"><?php _e( 'Status', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_status" name="wp_mms_status" <?php disabled( !current_user_can('publish_mms_requisitions') ); ?>>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>><?php _e( 'Pending', 'wp-mms' ); ?></option>
                    <option value="approved" <?php selected( $status, 'approved' ); ?>><?php _e( 'Approved', 'wp-mms' ); ?></option>
                    <option value="rejected" <?php selected( $status, 'rejected' ); ?>><?php _e( 'Rejected', 'wp-mms' ); ?></option>
                    <option value="completed" <?php selected( $status, 'completed' ); ?>><?php _e( 'Completed', 'wp-mms' ); ?></option>
                </select>
                <?php if ( !current_user_can('publish_mms_requisitions') ) : ?>
                <p class="description"><?php _e('Only a manager can change the status.', 'wp-mms'); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_desired_date"><?php _e( 'Desired Delivery Date', 'wp-mms' ); ?></label></th>
            <td><input type="date" id="wp_mms_desired_date" name="wp_mms_desired_date" value="<?php echo esc_attr( $desired_date ); ?>" /></td>
        </tr>
    </table>
    <hr>
    <h3><?php _e( 'Requested Items', 'wp-mms' ); ?></h3>
    <table id="requisition-items" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="manage-column" style="width: 70%;"><?php _e( 'Product', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Quantity', 'wp-mms' ); ?></th>
                <th class="manage-column" style="width: 15%;"><?php _e( 'Actions', 'wp-mms' ); ?></th>
            </tr>
        </thead>
        <tbody id="requisition-items-container">
            <?php
            if ( ! empty( $requested_items ) && is_array( $requested_items ) ) {
                foreach ( $requested_items as $i => $item ) {
                    ?>
                    <tr class="requisition-item">
                        <td>
                            <select name="wp_mms_requested_items[<?php echo $i; ?>][product_id]" class="widefat">
                                <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                                <?php foreach ( $products as $product ) : ?>
                                    <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="wp_mms_requested_items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="1" step="1" /></td>
                        <td><a href="#" class="button remove-requisition-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
    <p>
        <a href="#" id="add-requisition-item" class="button button-primary"><?php _e( 'Add Item', 'wp-mms' ); ?></a>
    </p>
    <script type="text/template" id="requisition-item-template">
        <tr class="requisition-item">
            <td>
                <select name="wp_mms_requested_items[{index}][product_id]" class="widefat requisition-product-select">
                     <option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option>
                </select>
            </td>
            <td><input type="number" name="wp_mms_requested_items[{index}][quantity]" value="1" class="small-text" min="1" step="1" /></td>
            <td><a href="#" class="button remove-requisition-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
        </tr>
    </script>
    <?php
}

/**
 * Save the meta box data for the Requisition CPT.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_requisition_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wp_mms_requisition_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_requisition_meta_box_nonce'], 'wp_mms_save_requisition_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_mms_requisition', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'wp_mms_requisition' ) {
        return;
    }

    // Only allow users with publish caps to change the status
    if ( isset( $_POST['wp_mms_status'] ) && current_user_can('publish_mms_requisitions') ) {
        update_post_meta( $post_id, '_wp_mms_status', sanitize_text_field( $_POST['wp_mms_status'] ) );
    }

    if( isset( $_POST['wp_mms_desired_date'] ) ) {
        update_post_meta( $post_id, '_wp_mms_desired_date', sanitize_text_field( $_POST['wp_mms_desired_date'] ) );
    }

    // Save requested items
    $new_items = [];
    if ( isset( $_POST['wp_mms_requested_items'] ) && is_array( $_POST['wp_mms_requested_items'] ) ) {
        foreach ( $_POST['wp_mms_requested_items'] as $item ) {
            if ( empty( $item['product_id'] ) || !isset( $item['quantity'] ) ) {
                continue;
            }
            $new_items[] = [
                'product_id' => intval( $item['product_id'] ),
                'quantity'   => intval( $item['quantity'] ),
            ];
        }
    }
    update_post_meta( $post_id, '_wp_mms_requested_items', $new_items );
}
add_action( 'save_post', 'wp_mms_save_requisition_meta_box_data' );