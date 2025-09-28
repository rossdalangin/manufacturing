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
 */
function wp_mms_render_supplier_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_supplier_meta_box_data', 'wp_mms_supplier_meta_box_nonce' );
    $contact_name = get_post_meta( $post->ID, '_wp_mms_contact_name', true );
    $email = get_post_meta( $post->ID, '_wp_mms_email', true );
    $phone = get_post_meta( $post->ID, '_wp_mms_phone', true );
    $website = get_post_meta( $post->ID, '_wp_mms_website', true );
    $address = get_post_meta( $post->ID, '_wp_mms_address', true );
    $lead_time = get_post_meta( $post->ID, '_wp_mms_lead_time', true );
    $notes = get_post_meta( $post->ID, '_wp_mms_notes', true );
    ?>
    <table class="form-table">
        <tr valign="top"><th scope="row"><label for="wp_mms_contact_name"><?php _e( 'Contact Name', 'wp-mms' ); ?></label></th><td><input type="text" id="wp_mms_contact_name" name="wp_mms_contact_name" value="<?php echo esc_attr( $contact_name ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_email"><?php _e( 'Email Address', 'wp-mms' ); ?></label></th><td><input type="email" id="wp_mms_email" name="wp_mms_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_phone"><?php _e( 'Phone Number', 'wp-mms' ); ?></label></th><td><input type="text" id="wp_mms_phone" name="wp_mms_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_website"><?php _e( 'Website', 'wp-mms' ); ?></label></th><td><input type="url" id="wp_mms_website" name="wp_mms_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_address"><?php _e( 'Address', 'wp-mms' ); ?></label></th><td><textarea id="wp_mms_address" name="wp_mms_address" rows="4" class="large-text"><?php echo esc_textarea( $address ); ?></textarea></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_lead_time"><?php _e( 'Lead Time (Days)', 'wp-mms' ); ?></label></th><td><input type="number" id="wp_mms_lead_time" name="wp_mms_lead_time" value="<?php echo esc_attr( $lead_time ); ?>" class="small-text" min="0" step="1" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_notes"><?php _e( 'Internal Notes', 'wp-mms' ); ?></label></th><td><textarea id="wp_mms_notes" name="wp_mms_notes" rows="6" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea></td></tr>
    </table>
    <?php
}

/**
 * Save the meta box data when the post is saved and log the changes.
 */
function wp_mms_save_supplier_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_supplier_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_supplier_meta_box_nonce'], 'wp_mms_save_supplier_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_supplier', $post_id ) ) return;

    // Log creation of a new supplier
    if ( ! $update ) {
        wp_mms_log_action( 'supplier_created', [
            'object_id'   => $post_id,
            'object_type' => 'Supplier',
            'description' => 'created supplier',
        ]);
        // No need to log individual fields for a new post
    }

    $fields = [
        'wp_mms_contact_name' => [ 'label' => 'Contact Name', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_email'        => [ 'label' => 'Email', 'sanitize' => 'sanitize_email' ],
        'wp_mms_phone'        => [ 'label' => 'Phone', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_website'      => [ 'label' => 'Website', 'sanitize' => 'esc_url_raw' ],
        'wp_mms_address'      => [ 'label' => 'Address', 'sanitize' => 'sanitize_textarea_field' ],
        'wp_mms_lead_time'    => [ 'label' => 'Lead Time', 'sanitize' => 'intval' ],
        'wp_mms_notes'        => [ 'label' => 'Notes', 'sanitize' => 'sanitize_textarea_field' ],
    ];

    foreach ( $fields as $key => $details ) {
        if ( isset( $_POST[ $key ] ) ) {
            $old_value = get_post_meta( $post_id, '_' . $key, true );
            $new_value = call_user_func( $details['sanitize'], $_POST[ $key ] );

            if ( $update && $old_value != $new_value ) {
                wp_mms_log_action( 'supplier_updated', [
                    'object_id'   => $post_id,
                    'object_type' => 'Supplier',
                    'description' => "updated {$details['label']}",
                    'old_value'   => $old_value,
                    'new_value'   => $new_value,
                ]);
            }
            update_post_meta( $post_id, '_' . $key, $new_value );
        }
    }
}
add_action( 'save_post_wp_mms_supplier', 'wp_mms_save_supplier_meta_box_data', 10, 3 );

/**
 * Log the deletion of a supplier.
 */
function wp_mms_log_supplier_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_supplier' ) {
        wp_mms_log_action( 'supplier_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Supplier',
            'description' => 'deleted supplier',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_supplier_deletion' );

/**
 * Add meta boxes for the Product CPT.
 */
function wp_mms_add_product_meta_boxes() {
    add_meta_box( 'wp_mms_product_details', __( 'Product Details', 'wp-mms' ), 'wp_mms_render_product_meta_box', 'wp_mms_product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wp_mms_add_product_meta_boxes' );

/**
 * Render the HTML for the Product meta box.
 */
function wp_mms_render_product_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_product_meta_box_data', 'wp_mms_product_meta_box_nonce' );
    $sku = get_post_meta( $post->ID, '_wp_mms_sku', true );
    $barcode = get_post_meta( $post->ID, '_wp_mms_barcode', true );
    $stock_quantity = get_post_meta( $post->ID, '_wp_mms_stock_quantity', true );
    $reorder_point = get_post_meta( $post->ID, '_wp_mms_reorder_point', true );
    $item_type = get_post_meta( $post->ID, '_wp_mms_item_type', true );
    $location = get_post_meta( $post->ID, '_wp_mms_warehouse_location', true );
    $unit_cost = get_post_meta( $post->ID, '_wp_mms_unit_cost', true );
    $selling_price = get_post_meta( $post->ID, '_wp_mms_selling_price', true );
    ?>
    <table class="form-table">
        <tr valign="top"><th scope="row"><label for="wp_mms_sku"><?php _e( 'SKU', 'wp-mms' ); ?></label></th><td><input type="text" id="wp_mms_sku" name="wp_mms_sku" value="<?php echo esc_attr( $sku ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_barcode"><?php _e( 'Barcode/UPC', 'wp-mms' ); ?></label></th><td><input type="text" id="wp_mms_barcode" name="wp_mms_barcode" value="<?php echo esc_attr( $barcode ); ?>" class="regular-text" /></td></tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_stock_quantity"><?php _e( 'Stock Quantity', 'wp-mms' ); ?></label></th>
            <td>
                <input type="number" id="wp_mms_stock_quantity" name="wp_mms_stock_quantity" value="<?php echo esc_attr( $stock_quantity ); ?>" class="small-text" readonly />
                <p class="description"><?php _e( 'Stock is automatically calculated from inventory lots. To adjust stock, create or edit lots.', 'wp-mms' ); ?></p>
            </td>
        </tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_reorder_point"><?php _e( 'Reorder Point', 'wp-mms' ); ?></label></th><td><input type="number" id="wp_mms_reorder_point" name="wp_mms_reorder_point" value="<?php echo esc_attr( $reorder_point ); ?>" class="small-text" min="0" step="any" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_item_type"><?php _e( 'Item Type', 'wp-mms' ); ?></label></th><td><select id="wp_mms_item_type" name="wp_mms_item_type"><option value="raw_material" <?php selected( $item_type, 'raw_material' ); ?>><?php _e( 'Raw Material', 'wp-mms' ); ?></option><option value="component" <?php selected( $item_type, 'component' ); ?>><?php _e( 'Component', 'wp-mms' ); ?></option><option value="finished_good" <?php selected( $item_type, 'finished_good' ); ?>><?php _e( 'Finished Good', 'wp-mms' ); ?></option></select></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_warehouse_location"><?php _e( 'Warehouse Location', 'wp-mms' ); ?></label></th><td><input type="text" id="wp_mms_warehouse_location" name="wp_mms_warehouse_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_unit_cost"><?php _e( 'Unit Cost', 'wp-mms' ); ?></label></th><td><input type="number" id="wp_mms_unit_cost" name="wp_mms_unit_cost" value="<?php echo esc_attr( $unit_cost ); ?>" class="small-text" min="0" step="0.01" /></td></tr>
        <tr valign="top" class="finished-good-field" style="display: <?php echo $item_type === 'finished_good' ? 'table-row' : 'none'; ?>;">
            <th scope="row"><label for="wp_mms_selling_price"><?php _e( 'Selling Price', 'wp-mms' ); ?></label></th>
            <td><input type="number" id="wp_mms_selling_price" name="wp_mms_selling_price" value="<?php echo esc_attr( $selling_price ); ?>" class="small-text" min="0" step="0.01" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Save the meta box data for the Product CPT and log changes.
 */
function wp_mms_save_product_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_product_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_product_meta_box_nonce'], 'wp_mms_save_product_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_product', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'product_created', [
            'object_id'   => $post_id,
            'object_type' => 'Product',
            'description' => 'created product',
        ]);
    }

    $fields = [
        'wp_mms_sku'                => [ 'label' => 'SKU', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_barcode'            => [ 'label' => 'Barcode/UPC', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_reorder_point'      => [ 'label' => 'Reorder Point', 'sanitize' => 'floatval' ],
        'wp_mms_item_type'          => [ 'label' => 'Item Type', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_warehouse_location' => [ 'label' => 'Warehouse Location', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_unit_cost'          => [ 'label' => 'Unit Cost', 'sanitize' => 'floatval' ],
        'wp_mms_selling_price'      => [ 'label' => 'Selling Price', 'sanitize' => 'floatval' ],
    ];

    foreach ( $fields as $key => $details ) {
        if ( isset( $_POST[ $key ] ) ) {
            $old_value = get_post_meta( $post_id, '_' . $key, true );
            $new_value = call_user_func( $details['sanitize'], $_POST[ $key ] );

            if ( $update && $old_value != $new_value ) {
                wp_mms_log_action( 'product_updated', [
                    'object_id'   => $post_id,
                    'object_type' => 'Product',
                    'description' => "updated {$details['label']}",
                    'old_value'   => $old_value,
                    'new_value'   => $new_value,
                ]);
            }
            update_post_meta( $post_id, '_' . $key, $new_value );
        }
    }
}
add_action( 'save_post_wp_mms_product', 'wp_mms_save_product_meta_box_data', 10, 3 );

/**
 * Log the deletion of a product.
 */
function wp_mms_log_product_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_product' ) {
        wp_mms_log_action( 'product_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Product',
            'description' => 'deleted product',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_product_deletion' );

/**
 * Add meta boxes for the Purchase Order CPT.
 */
function wp_mms_add_purchase_order_meta_boxes() {
    add_meta_box( 'wp_mms_po_details', __( 'Purchase Order Details', 'wp-mms' ), 'wp_mms_render_po_meta_box', 'wp_mms_purchase_order', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wp_mms_add_purchase_order_meta_boxes' );

/**
 * Render the HTML for the Purchase Order meta box.
 */
function wp_mms_render_po_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_po_meta_box_data', 'wp_mms_po_meta_box_nonce' );
    $supplier_id = get_post_meta( $post->ID, '_wp_mms_supplier_id', true );
    $status = get_post_meta( $post->ID, '_wp_mms_status', true );
    $order_date = get_post_meta( $post->ID, '_wp_mms_order_date', true );
    $expected_date = get_post_meta( $post->ID, '_wp_mms_expected_date', true );
    $line_items = get_post_meta( $post->ID, '_wp_mms_line_items', true );
    $suppliers = get_posts( array( 'post_type' => 'wp_mms_supplier', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
    $products = get_posts( array( 'post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
    ?>
    <table class="form-table">
        <tr valign="top"><th scope="row"><label for="wp_mms_supplier_id"><?php _e( 'Supplier', 'wp-mms' ); ?></label></th><td><select id="wp_mms_supplier_id" name="wp_mms_supplier_id" class="widefat"><option value=""><?php _e( 'Select a Supplier', 'wp-mms' ); ?></option><?php foreach ( $suppliers as $supplier ) : ?><option value="<?php echo esc_attr( $supplier->ID ); ?>" <?php selected( $supplier_id, $supplier->ID ); ?>><?php echo esc_html( $supplier->post_title ); ?></option><?php endforeach; ?></select></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_status"><?php _e( 'Order Status', 'wp-mms' ); ?></label></th><td><select id="wp_mms_status" name="wp_mms_status"><option value="pending" <?php selected( $status, 'pending' ); ?>><?php _e( 'Pending', 'wp-mms' ); ?></option><option value="approved" <?php selected( $status, 'approved' ); ?>><?php _e( 'Approved', 'wp-mms' ); ?></option><option value="shipped" <?php selected( $status, 'shipped' ); ?>><?php _e( 'Shipped', 'wp-mms' ); ?></option><option value="received" <?php selected( $status, 'received' ); ?>><?php _e( 'Received', 'wp-mms' ); ?></option><option value="canceled" <?php selected( $status, 'canceled' ); ?>><?php _e( 'Canceled', 'wp-mms' ); ?></option></select></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_order_date"><?php _e( 'Order Date', 'wp-mms' ); ?></label></th><td><input type="date" id="wp_mms_order_date" name="wp_mms_order_date" value="<?php echo esc_attr( $order_date ); ?>" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_expected_date"><?php _e( 'Expected Delivery Date', 'wp-mms' ); ?></label></th><td><input type="date" id="wp_mms_expected_date" name="wp_mms_expected_date" value="<?php echo esc_attr( $expected_date ); ?>" /></td></tr>
    </table><hr><h3><?php _e( 'Products on this Order', 'wp-mms' ); ?></h3>
    <table id="po-line-items" class="wp-list-table widefat fixed striped">
        <thead><tr><th class="manage-column" style="width: 50%;"><?php _e( 'Product', 'wp-mms' ); ?></th><th class="manage-column" style="width: 15%;"><?php _e( 'Quantity', 'wp-mms' ); ?></th><th class="manage-column" style="width: 15%;"><?php _e( 'Unit Price', 'wp-mms' ); ?></th><th class="manage-column" style="width: 20%;"><?php _e( 'Actions', 'wp-mms' ); ?></th></tr></thead>
        <tbody id="line-items-container">
            <?php if ( ! empty( $line_items ) && is_array( $line_items ) ) : foreach ( $line_items as $i => $item ) : ?>
                <tr class="line-item">
                    <td><select name="wp_mms_line_items[<?php echo $i; ?>][product_id]" class="widefat"><option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option><?php foreach ( $products as $product ) : ?><option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option><?php endforeach; ?></select></td>
                    <td><input type="number" name="wp_mms_line_items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="1" step="1" /></td>
                    <td><input type="number" name="wp_mms_line_items[<?php echo $i; ?>][unit_price]" value="<?php echo esc_attr( $item['unit_price'] ); ?>" class="small-text" min="0" step="0.01" /></td>
                    <td><a href="#" class="button remove-line-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <p><a href="#" id="add-line-item" class="button button-primary"><?php _e( 'Add Product', 'wp-mms' ); ?></a></p>
    <script type="text/template" id="line-item-template">
        <tr class="line-item"><td><select name="wp_mms_line_items[{index}][product_id]" class="widefat"><option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option><?php foreach ( $products as $product ) : ?><option value="<?php echo esc_attr( $product->ID ); ?>"><?php echo esc_html( $product->post_title ); ?></option><?php endforeach; ?></select></td><td><input type="number" name="wp_mms_line_items[{index}][quantity]" value="1" class="small-text" min="1" step="1" /></td><td><input type="number" name="wp_mms_line_items[{index}][unit_price]" value="0.00" class="small-text" min="0" step="0.01" /></td><td><a href="#" class="button remove-line-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td></tr>
    </script>
    <?php
}

/**
 * Save the meta box data for the Purchase Order CPT, handle stock updates, and log changes.
 */
function wp_mms_save_po_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_po_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_po_meta_box_nonce'], 'wp_mms_save_po_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_purchase_order', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'po_created', [
            'object_id'   => $post_id,
            'object_type' => 'Purchase Order',
            'description' => 'created purchase order',
        ]);
    }

    $old_line_items = get_post_meta( $post_id, '_wp_mms_line_items', true ) ?: [];

    $fields = [
        'wp_mms_supplier_id'    => [ 'label' => 'Supplier', 'sanitize' => 'intval' ],
        'wp_mms_status'         => [ 'label' => 'Status', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_order_date'     => [ 'label' => 'Order Date', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_expected_date'  => [ 'label' => 'Expected Date', 'sanitize' => 'sanitize_text_field' ],
    ];

    foreach ( $fields as $key => $details ) {
        if ( isset( $_POST[ $key ] ) ) {
            $old_value = get_post_meta( $post_id, '_' . $key, true );
            $new_value = call_user_func( $details['sanitize'], $_POST[ $key ] );
            if ( $update && $old_value != $new_value ) {
                wp_mms_log_action( 'po_updated', [
                    'object_id'   => $post_id, 'object_type' => 'Purchase Order', 'description' => "updated {$details['label']}",
                    'old_value'   => $old_value, 'new_value'   => $new_value,
                ]);
            }
            update_post_meta( $post_id, '_' . $key, $new_value );
        }
    }

    $new_line_items = [];
    if ( isset( $_POST['wp_mms_line_items'] ) && is_array( $_POST['wp_mms_line_items'] ) ) {
        foreach ( $_POST['wp_mms_line_items'] as $item ) {
            if ( empty( $item['product_id'] ) ) continue;
            $new_line_items[] = [ 'product_id' => intval( $item['product_id'] ), 'quantity' => intval( $item['quantity'] ), 'unit_price' => floatval( $item['unit_price'] ) ];
        }
    }

    if ( $update && serialize($old_line_items) !== serialize($new_line_items) ) {
        wp_mms_log_action( 'po_updated', [
            'object_id'   => $post_id, 'object_type' => 'Purchase Order', 'description' => 'updated line items',
            'old_value'   => $old_line_items, 'new_value'   => $new_line_items,
        ]);
    }
    update_post_meta( $post_id, '_wp_mms_line_items', $new_line_items );

    // Grab new status directly from post meta after update for accurate comparison
    $new_status = get_post_meta( $post_id, '_wp_mms_status', true );
    $old_status = $fields['wp_mms_status']['sanitize']( $_POST['wp_mms_status'] ?? '' ) == $new_status ? $new_status : get_post_meta( $post_id, '_wp_mms_status', true ); // A bit redundant, but ensures we have the true old status before this save.

    $affected_products = [];
    foreach($new_line_items as $item) { if($item['product_id']) $affected_products[] = $item['product_id']; }
    foreach($old_line_items as $item) { if($item['product_id']) $affected_products[] = $item['product_id']; }

    $create_lots_for_po = function( $po_id, $line_items ) {
        if( empty($line_items) || !is_array($line_items) ) return;
        $created_lot_ids = [];
        foreach( $line_items as $index => $item ) {
            $new_lot_id = wp_insert_post( [ 'post_title' => 'PO-' . $po_id . '-' . ($index + 1), 'post_status' => 'publish', 'post_type' => 'wp_mms_lot' ], true );
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
                wp_delete_post( $lot_id, true );
            }
        }
        delete_post_meta( $po_id, '_wp_mms_created_lot_ids' );
    };

    if ( $new_status === 'received' && $old_status !== 'received' ) {
        $create_lots_for_po( $post_id, $new_line_items );
        update_post_meta( $post_id, '_wp_mms_date_received', current_time( 'Y-m-d' ) );

        // --- Weighted Average Cost Calculation ---
        $options = get_option( 'wp_mms_settings', ['inventory_valuation_method' => 'manual'] );
        if ( 'weighted_average' === $options['inventory_valuation_method'] ) {
            foreach( $new_line_items as $item ) {
                $product_id = $item['product_id'];
                $new_qty = floatval($item['quantity']);
                $new_cost = floatval($item['unit_price']);

                $old_qty = floatval(get_post_meta( $product_id, '_wp_mms_stock_quantity', true ));
                $old_cost = floatval(get_post_meta( $product_id, '_wp_mms_unit_cost', true ));

                if ( ($old_qty + $new_qty) > 0 ) {
                    $new_weighted_cost = ( ($old_qty * $old_cost) + ($new_qty * $new_cost) ) / ($old_qty + $new_qty);
                    $new_weighted_cost = round($new_weighted_cost, 2); // Round to 2 decimal places

                    update_post_meta( $product_id, '_wp_mms_unit_cost', $new_weighted_cost );

                    wp_mms_log_action( 'product_updated', [
                        'object_id'   => $product_id, 'object_type' => 'Product', 'description' => 'updated Unit Cost (Weighted Avg)',
                        'old_value'   => $old_cost, 'new_value'   => $new_weighted_cost,
                    ]);
                }
            }
        }
        // --- End Weighted Average Cost Calculation ---

    } else if ( $new_status !== 'received' && $old_status === 'received' ) {
        $delete_lots_for_po( $post_id );
        delete_post_meta( $post_id, '_wp_mms_date_received' );
    } else if ( $new_status === 'received' && $old_status === 'received' ) {
        if ( $old_line_items != $new_line_items ) {
            $delete_lots_for_po( $post_id );
            $create_lots_for_po( $post_id, $new_line_items );
        }
    }

    if ( !empty($affected_products) ) {
        foreach( array_unique($affected_products) as $product_id ) {
            if ( $product_id ) {
                wp_mms_update_product_stock_from_lots( $product_id );
            }
        }
    }
}
add_action( 'save_post_wp_mms_purchase_order', 'wp_mms_save_po_meta_box_data', 10, 3 );

/**
 * Log the deletion of a purchase order.
 */
function wp_mms_log_po_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_purchase_order' ) {
        wp_mms_log_action( 'po_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Purchase Order',
            'description' => 'deleted purchase order',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_po_deletion' );

/**
 * Add meta boxes for the BOM CPT.
 */
function wp_mms_add_bom_meta_boxes() {
    add_meta_box( 'wp_mms_bom_details', __( 'Bill of Materials Details', 'wp-mms' ), 'wp_mms_render_bom_meta_box', 'wp_mms_bom', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wp_mms_add_bom_meta_boxes' );

/**
 * Render the HTML for the BOM meta box.
 */
function wp_mms_render_bom_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_bom_meta_box_data', 'wp_mms_bom_meta_box_nonce' );
    $finished_product_id = get_post_meta( $post->ID, '_wp_mms_finished_product_id', true );
    $components = get_post_meta( $post->ID, '_wp_mms_components', true );
    $finished_goods = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_wp_mms_item_type', 'meta_value' => 'finished_good'] );
    $component_products = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'] );
    ?>
    <table class="form-table">
        <tr valign="top"><th scope="row"><label for="wp_mms_finished_product_id"><?php _e( 'Finished Product', 'wp-mms' ); ?></label></th><td><select id="wp_mms_finished_product_id" name="wp_mms_finished_product_id" class="widefat"><option value=""><?php _e( 'Select a Finished Product', 'wp-mms' ); ?></option><?php foreach ( $finished_goods as $product ) : ?><option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $finished_product_id, $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option><?php endforeach; ?></select><p class="description"><?php _e( 'Select the final product that this Bill of Materials is for.', 'wp-mms' ); ?></p></td></tr>
    </table><hr><h3><?php _e( 'Components', 'wp-mms' ); ?></h3>
    <table id="bom-components" class="wp-list-table widefat fixed striped">
        <thead><tr><th class="manage-column column-icon" style="width: 5%;"></th><th class="manage-column" style="width: 65%;"><?php _e( 'Component Product', 'wp-mms' ); ?></th><th class="manage-column" style="width: 15%;"><?php _e( 'Quantity', 'wp-mms' ); ?></th><th class="manage-column" style="width: 15%;"><?php _e( 'Actions', 'wp-mms' ); ?></th></tr></thead>
        <tbody id="components-container">
             <?php if ( ! empty( $components ) && is_array( $components ) ) : foreach ( $components as $i => $item ) : ?>
                <tr class="component-item"><td class="component-handle" style="cursor: move; text-align: center;"><span class="dashicons dashicons-move"></span></td><td><select name="wp_mms_components[<?php echo $i; ?>][product_id]" class="widefat"><option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option><?php $item_type_labels = [ 'raw_material' => __( 'Raw Material', 'wp-mms' ), 'component' => __( 'Component', 'wp-mms' ), 'finished_good' => __( 'Sub-Assembly', 'wp-mms' ) ]; foreach ( $component_products as $product ) : $item_type = get_post_meta( $product->ID, '_wp_mms_item_type', true ); $display_text = $product->post_title . ' (' . ( $item_type_labels[$item_type] ?? $item_type ) . ')'; ?><option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $item['product_id'], $product->ID ); ?>><?php echo esc_html( $display_text ); ?></option><?php endforeach; ?></select><div class="alternatives-container" style="margin-top: 10px;"><?php if ( !empty($item['alternatives']) && is_array($item['alternatives']) ) { foreach( $item['alternatives'] as $alt_i => $alt_id ) { ?><div class="alternative-item" style="margin-top: 5px;"><span class="dashicons dashicons-arrow-right-alt" style="vertical-align: middle;"></span><select name="wp_mms_components[<?php echo $i; ?>][alternatives][<?php echo $alt_i; ?>]" class="widefat" style="width: 80%; display: inline-block;"><option value=""><?php _e( 'Select an Alternative', 'wp-mms' ); ?></option><?php foreach ( $component_products as $product ) : $item_type = get_post_meta( $product->ID, '_wp_mms_item_type', true ); $display_text = $product->post_title . ' (' . ( $item_type_labels[$item_type] ?? $item_type ) . ')'; ?><option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $alt_id, $product->ID ); ?>><?php echo esc_html( $display_text ); ?></option><?php endforeach; ?></select><a href="#" class="button remove-alternative-item" style="vertical-align: middle;">&times;</a></div><?php } } ?></div><a href="#" class="button button-secondary add-alternative-item" style="margin-top: 10px;"><?php _e( 'Add Alternative', 'wp-mms' ); ?></a></td><td><input type="number" name="wp_mms_components[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="0" step="any" /></td><td><a href="#" class="button remove-component-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td></tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <p><a href="#" id="add-component-item" class="button button-primary"><?php _e( 'Add Component', 'wp-mms' ); ?></a></p>
    <script type="text/template" id="component-item-template"><tr class="component-item"><td class="component-handle" style="cursor: move; text-align: center;"><span class="dashicons dashicons-move"></span></td><td><select name="wp_mms_components[{index}][product_id]" class="widefat component-product-select"><option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option></select><div class="alternatives-container" style="margin-top: 10px;"></div><a href="#" class="button button-secondary add-alternative-item" style="margin-top: 10px;"><?php _e( 'Add Alternative', 'wp-mms' ); ?></a></td><td><input type="number" name="wp_mms_components[{index}][quantity]" value="1" class="small-text" min="0" step="any" /></td><td><a href="#" class="button remove-component-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td></tr></script>
    <script type="text/template" id="alternative-item-template"><div class="alternative-item" style="margin-top: 5px;"><span class="dashicons dashicons-arrow-right-alt" style="vertical-align: middle;"></span><select name="wp_mms_components[{component_index}][alternatives][]" class="widefat alternative-product-select" style="width: 80%; display: inline-block;"><option value=""><?php _e( 'Select an Alternative', 'wp-mms' ); ?></option></select><a href="#" class="button remove-alternative-item" style="vertical-align: middle;">&times;</a></div></script>
    <?php
}

/**
 * Save the meta box data for the BOM CPT and log changes.
 */
function wp_mms_save_bom_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_bom_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_bom_meta_box_nonce'], 'wp_mms_save_bom_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_bom', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'bom_created', [
            'object_id'   => $post_id,
            'object_type' => 'BOM',
            'description' => 'created BOM',
        ]);
    }

    $old_product_id = get_post_meta( $post_id, '_wp_mms_finished_product_id', true );
    if ( isset( $_POST['wp_mms_finished_product_id'] ) ) {
        $new_product_id = intval( $_POST['wp_mms_finished_product_id'] );
        if ( $update && $old_product_id != $new_product_id ) {
            wp_mms_log_action( 'bom_updated', [
                'object_id'   => $post_id, 'object_type' => 'BOM', 'description' => 'updated Finished Product',
                'old_value'   => get_the_title($old_product_id), 'new_value' => get_the_title($new_product_id),
            ]);
        }
        update_post_meta( $post_id, '_wp_mms_finished_product_id', $new_product_id );
    }

    $old_components = get_post_meta( $post_id, '_wp_mms_components', true ) ?: [];
    $new_components = [];
    if ( isset( $_POST['wp_mms_components'] ) && is_array( $_POST['wp_mms_components'] ) ) {
        foreach ( $_POST['wp_mms_components'] as $item ) {
            if ( empty( $item['product_id'] ) || !isset( $item['quantity'] ) ) continue;
            $new_item = [
                'product_id' => intval( $item['product_id'] ), 'quantity'   => floatval( $item['quantity'] ), 'alternatives' => [],
            ];
            if ( !empty($item['alternatives']) && is_array($item['alternatives']) ) {
                foreach ( $item['alternatives'] as $alt_id ) {
                    if ( !empty($alt_id) ) {
                        $new_item['alternatives'][] = intval($alt_id);
                    }
                }
            }
            $new_components[] = $new_item;
        }
    }

    if ( $update && serialize($old_components) !== serialize($new_components) ) {
        wp_mms_log_action( 'bom_updated', [
            'object_id'   => $post_id, 'object_type' => 'BOM', 'description' => 'updated components list',
            'old_value'   => $old_components, 'new_value' => $new_components,
        ]);
    }
    update_post_meta( $post_id, '_wp_mms_components', $new_components );
    wp_mms_calculate_bom_cost( $post_id );
}
add_action( 'save_post_wp_mms_bom', 'wp_mms_save_bom_meta_box_data', 10, 3 );

/**
 * Log the deletion of a BOM.
 */
function wp_mms_log_bom_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_bom' ) {
        wp_mms_log_action( 'bom_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'BOM',
            'description' => 'deleted BOM',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_bom_deletion' );

/**
 * Add meta boxes for the Production Order CPT.
 */
function wp_mms_add_production_order_meta_boxes() {
    add_meta_box( 'wp_mms_production_order_details', __( 'Production Order Details', 'wp-mms' ), 'wp_mms_render_production_order_meta_box', 'wp_mms_production_order', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wp_mms_add_production_order_meta_boxes' );
add_action( 'add_meta_boxes_wp_mms_production_order', 'wp_mms_add_consumed_lots_meta_box' );
add_action( 'add_meta_boxes_wp_mms_production_order', 'wp_mms_add_scrap_record_meta_box' );
add_action( 'add_meta_boxes_wp_mms_bom', 'wp_mms_add_bom_versioning_meta_box' );

/**
 * Add a meta box for BOM versioning.
 */
function wp_mms_add_bom_versioning_meta_box( $post ) {
    add_meta_box( 'wp_mms_bom_versioning', __( 'Versioning', 'wp-mms' ), 'wp_mms_render_bom_versioning_meta_box', 'wp_mms_bom', 'side', 'core' );
}

/**
 * Render the HTML for the BOM Versioning meta box.
 */
function wp_mms_render_bom_versioning_meta_box( $post ) {
    $clone_url = wp_nonce_url( admin_url( 'admin-post.php?action=clone_mms_bom&post=' . $post->ID ), 'clone_mms_bom_' . $post->ID );
    $parent_id = $post->post_parent;
    $children = get_children( ['post_parent' => $post->ID, 'post_type' => 'wp_mms_bom'] );
    echo '<p><a href="' . esc_url($clone_url) . '" class="button button-secondary">' . __('Create New Version', 'wp-mms') . '</a></p>';
    echo '<p class="description">' . __('This will create a new, editable version of this BOM.', 'wp-mms') . '</p>';
    if ( $parent_id || !empty($children) ) {
        echo '<hr><strong>' . __('Other Versions:', 'wp-mms') . '</strong>';
        echo '<ul>';
        if ($parent_id) printf('<li><a href="%s">%s</a> (%s)</li>', get_edit_post_link($parent_id), get_the_title($parent_id), __('Parent', 'wp-mms'));
        foreach( $children as $child ) printf('<li><a href="%s">%s</a> (%s)</li>', get_edit_post_link($child->ID), get_the_title($child->ID), __('Child', 'wp-mms'));
        echo '</ul>';
    }
}

/**
 * Add a meta box for recording scrap.
 */
function wp_mms_add_scrap_record_meta_box( $post ) {
    if ( get_post_meta( $post->ID, '_wp_mms_status', true ) === 'completed' ) {
        add_meta_box( 'wp_mms_scrap_record', __( 'Record Scrap / Wastage', 'wp-mms' ), 'wp_mms_render_scrap_record_meta_box', 'wp_mms_production_order', 'normal', 'low' );
    }
}

/**
 * Render the HTML for the scrap record meta box.
 */
function wp_mms_render_scrap_record_meta_box( $post ) {
    $scrapped_items = get_post_meta( $post->ID, '_wp_mms_scrapped_items', true );
    $product_id = get_post_meta( $post->ID, '_wp_mms_product_id', true );
    $raw_materials = wp_mms_get_exploded_bom_materials( $product_id );
    ?>
    <p class="description"><?php _e( 'Record any component materials that were scrapped during this production run. This will deduct the items from inventory.', 'wp-mms' ); ?></p>
    <table id="scrap-items" class="wp-list-table widefat fixed striped">
        <thead><tr><th style="width: 70%;"><?php _e( 'Component', 'wp-mms' ); ?></th><th style="width: 15%;"><?php _e( 'Quantity Scrapped', 'wp-mms' ); ?></th><th style="width: 15%;"><?php _e( 'Actions', 'wp-mms' ); ?></th></tr></thead>
        <tbody id="scrap-items-container">
            <?php if ( ! empty( $scrapped_items ) && is_array( $scrapped_items ) ) : foreach ( $scrapped_items as $i => $item ) : ?>
                <tr class="scrap-item"><td><select name="_wp_mms_scrapped_items[<?php echo $i; ?>][product_id]" class="widefat"><option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option><?php foreach ( $raw_materials as $material_id => $qty ) : ?><option value="<?php echo esc_attr( $material_id ); ?>" <?php selected( $item['product_id'], $material_id ); ?>><?php echo esc_html( get_the_title( $material_id ) ); ?></option><?php endforeach; ?></select></td><td><input type="number" name="_wp_mms_scrapped_items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" min="0" step="any" /></td><td><a href="#" class="button remove-scrap-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td></tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <p><a href="#" id="add-scrap-item" class="button button-primary"><?php _e( 'Add Scrap Item', 'wp-mms' ); ?></a></p>
    <script type="text/template" id="scrap-item-template"><tr class="scrap-item"><td><select name="_wp_mms_scrapped_items[{index}][product_id]" class="widefat scrap-product-select"><option value=""><?php _e( 'Select a Component', 'wp-mms' ); ?></option></select></td><td><input type="number" name="_wp_mms_scrapped_items[{index}][quantity]" value="1" class="small-text" min="0" step="any" /></td><td><a href="#" class="button remove-scrap-item"><?php _e( 'Remove', 'wp-mms' ); ?></a></td></tr></script>
    <?php
}

/**
 * Add a meta box to show the consumed lots for a completed production order.
 */
function wp_mms_add_consumed_lots_meta_box( $post ) {
    if ( get_post_meta( $post->ID, '_wp_mms_status', true ) === 'completed' ) {
        add_meta_box( 'wp_mms_consumed_lots', __( 'Consumption Record', 'wp-mms' ), 'wp_mms_render_consumed_lots_meta_box', 'wp_mms_production_order', 'normal', 'low' );
    }
}

/**
 * Render the HTML for the consumed lots meta box.
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
        <thead><tr><th><?php _e( 'Component Lot / Batch', 'wp-mms' ); ?></th><th><?php _e( 'Quantity Consumed', 'wp-mms' ); ?></th></tr></thead>
        <tbody><?php foreach ( $consumed_lots as $lot_id => $data ) : ?><tr><td><?php echo esc_html( $data['title'] ); ?></td><td><?php echo esc_html( $data['qty'] ); ?></td></tr><?php endforeach; ?></tbody>
    </table>
    <?php
}

/**
 * Render the HTML for the Production Order meta box.
 */
function wp_mms_render_production_order_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_production_order_meta_box_data', 'wp_mms_production_order_meta_box_nonce' );
    $product_id = get_post_meta( $post->ID, '_wp_mms_product_id', true );
    $bom_id = get_post_meta( $post->ID, '_wp_mms_bom_id', true );
    $quantity = get_post_meta( $post->ID, '_wp_mms_quantity', true );
    $status = get_post_meta( $post->ID, '_wp_mms_status', true );
    $start_date = get_post_meta( $post->ID, '_wp_mms_start_date', true );
    $end_date = get_post_meta( $post->ID, '_wp_mms_end_date', true );
    $finished_goods = get_posts( ['post_type' => 'wp_mms_product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'meta_key' => '_wp_mms_item_type', 'meta_value' => 'finished_good'] );
    ?>
    <table class="form-table">
        <tr valign="top"><th scope="row"><label for="wp_mms_product_id"><?php _e( 'Product to Produce', 'wp-mms' ); ?></label></th><td><select id="wp_mms_product_id" name="wp_mms_product_id" class="widefat"><option value=""><?php _e( 'Select a Product', 'wp-mms' ); ?></option><?php foreach ( $finished_goods as $product ) : ?><option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product_id, $product->ID ); ?>><?php echo esc_html( $product->post_title ); ?></option><?php endforeach; ?></select></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_bom_id"><?php _e( 'Bill of Materials Version', 'wp-mms' ); ?></label></th><td><select id="wp_mms_bom_id" name="wp_mms_bom_id" class="widefat"><option value=""><?php _e( 'Select a product first...', 'wp-mms' ); ?></option></select><p class="description"><?php _e('Select a product above to load its available BOM versions.', 'wp-mms'); ?></p></td></tr>
        <?php
        $routing_id = get_post_meta( $post->ID, '_wp_mms_routing_id', true );
        $routings = get_posts( ['post_type' => 'wp_mms_routing', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'] );
        ?>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_routing_id"><?php _e( 'Production Routing', 'wp-mms' ); ?></label></th>
            <td>
                <select id="wp_mms_routing_id" name="wp_mms_routing_id" class="widefat">
                    <option value=""><?php _e( 'Select a Routing Template', 'wp-mms' ); ?></option>
                    <?php foreach ( $routings as $routing ) : ?>
                        <option value="<?php echo esc_attr( $routing->ID ); ?>" <?php selected( $routing_id, $routing->ID ); ?>><?php echo esc_html( $routing->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Select the sequence of steps required to produce this item. This will be saved as a snapshot on this order.', 'wp-mms'); ?></p>
            </td>
        </tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_quantity"><?php _e( 'Quantity to Produce', 'wp-mms' ); ?></label></th><td><input type="number" id="wp_mms_quantity" name="wp_mms_quantity" value="<?php echo esc_attr( $quantity ); ?>" class="small-text" min="1" step="1" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_status"><?php _e( 'Order Status', 'wp-mms' ); ?></label></th><td><select id="wp_mms_status" name="wp_mms_status"><option value="pending" <?php selected( $status, 'pending' ); ?>><?php _e( 'Pending', 'wp-mms' ); ?></option><option value="in_progress" <?php selected( $status, 'in_progress' ); ?>><?php _e( 'In Progress', 'wp-mms' ); ?></option><option value="completed" <?php selected( $status, 'completed' ); ?>><?php _e( 'Completed', 'wp-mms' ); ?></option><option value="canceled" <?php selected( $status, 'canceled' ); ?>><?php _e( 'Canceled', 'wp-mms' ); ?></option></select></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_start_date"><?php _e( 'Start Date', 'wp-mms' ); ?></label></th><td><input type="date" id="wp_mms_start_date" name="wp_mms_start_date" value="<?php echo esc_attr( $start_date ); ?>" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_end_date"><?php _e( 'Expected Completion Date', 'wp-mms' ); ?></label></th><td><input type="date" id="wp_mms_end_date" name="wp_mms_end_date" value="<?php echo esc_attr( $end_date ); ?>" /></td></tr>
        <tr valign="top"><th scope="row"><label for="wp_mms_planned_duration_hours"><?php _e( 'Planned Duration (Hours)', 'wp-mms' ); ?></label></th><td><input type="number" id="wp_mms_planned_duration_hours" name="wp_mms_planned_duration_hours" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wp_mms_planned_duration_hours', true ) ); ?>" class="small-text" min="0" step="0.1" /></td></tr>
        <tr valign="top">
            <th scope="row"><label for="wp_mms_actual_duration_hours"><?php _e( 'Actual Duration (Hours)', 'wp-mms' ); ?></label></th>
            <td>
                <input type="text" id="wp_mms_actual_duration_hours" name="wp_mms_actual_duration_hours" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wp_mms_actual_duration_hours', true ) ); ?>" class="small-text" readonly />
                <?php if ( 'in_progress' === $status ) : ?>
                <button class="button button-secondary" id="record-actual-duration"><?php _e( 'Record Actual Duration', 'wp-mms' ); ?></button>
                <p class="description"><?php _e( 'Calculates time since status was set to "In Progress".', 'wp-mms' ); ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Helper function to consume lots for a list of possible products (primary + alternatives),
 * correctly handling partial stock and respecting the chosen consumption method (FIFO/LIFO).
 *
 * @param array $products_to_try      An ordered array of product IDs to consume from (primary first, then alternatives).
 * @param float $qty_needed           The total quantity of the component that needs to be consumed.
 * @param array $consumed_lots_record A referenced array to store the record of which lots were consumed.
 * @param array $affected_products    A referenced array to store the IDs of all products whose stock was changed.
 */
function wp_mms_consume_component_lots( $products_to_try, $qty_needed, &$consumed_lots_record, &$affected_products ) {
    $remaining_qty_to_consume = $qty_needed;

    // Get the chosen consumption method from settings.
    $options = get_option( 'wp_mms_settings', ['lot_consumption_method' => 'fifo'] );
    $consumption_method = $options['lot_consumption_method'];
    $order = ( 'lifo' === $consumption_method ) ? 'DESC' : 'ASC';

    // Iterate through the products in the specified order (primary, then alternatives).
    foreach ( $products_to_try as $product_id ) {
        // If we've already fulfilled the required quantity, we can stop.
        if ( $remaining_qty_to_consume <= 0 ) {
            break;
        }

        $affected_products[] = $product_id;

        // Get all lots for the current product, ordered by the chosen method.
        $lots_query = new WP_Query([
            'post_type'      => 'wp_mms_lot',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => $order,
            'meta_query'     => [
                ['key' => '_wp_mms_product_id', 'value' => $product_id]
            ]
        ]);

        if ( ! $lots_query->have_posts() ) {
            continue; // No lots for this product, so try the next alternative.
        }

        // Go through the lots and consume the needed quantity.
        while ( $lots_query->have_posts() ) {
            $lots_query->the_post();
            $lot_id = get_the_ID();
            $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );

            // Determine how much to take from this specific lot.
            $qty_to_take_from_this_lot = min( $lot_qty, $remaining_qty_to_consume );

            if ( $qty_to_take_from_this_lot > 0 ) {
                // Record exactly what was consumed.
                $consumed_lots_record[ $lot_id ] = [
                    'qty'        => $qty_to_take_from_this_lot,
                    'product_id' => $product_id,
                    'title'      => get_the_title()
                ];

                // Deduct the quantity from the lot.
                $new_lot_qty = $lot_qty - $qty_to_take_from_this_lot;
                if ( $new_lot_qty <= 0 ) {
                    // Delete the lot if it's empty.
                    wp_delete_post( $lot_id, true );
                } else {
                    update_post_meta( $lot_id, '_wp_mms_quantity', $new_lot_qty );
                }

                // Decrement the total remaining quantity we still need to find.
                $remaining_qty_to_consume -= $qty_to_take_from_this_lot;
            }

            // If we've fulfilled the requirement, we can stop processing lots for this product.
            if ( $remaining_qty_to_consume <= 0 ) {
                break;
            }
        }
        wp_reset_postdata();
    }
}

/**
 * Recursively consumes a BOM, respecting alternatives.
 */
function wp_mms_recursively_consume_bom( $bom_id, $quantity_produced, &$consumed_lots_record, &$affected_products, &$visited_boms ) {
    if ( in_array( $bom_id, $visited_boms ) ) return;
    $visited_boms[] = $bom_id;
    $components = get_post_meta( $bom_id, '_wp_mms_components', true );
    if ( empty( $components ) || !is_array( $components ) ) return;
    foreach ( $components as $item ) {
        $component_id = $item['product_id'];
        if ( empty( $component_id ) ) continue;
        $total_component_qty_needed = floatval($item['quantity']) * $quantity_produced;
        $component_item_type = get_post_meta( $component_id, '_wp_mms_item_type', true );
        if ( 'finished_good' === $component_item_type ) {
            $sub_bom_query = new WP_Query(['post_type' => 'wp_mms_bom', 'posts_per_page' => 1, 'meta_key' => '_wp_mms_finished_product_id', 'meta_value' => $component_id, 'fields' => 'ids']);
            if ($sub_bom_query->have_posts()) {
                wp_mms_recursively_consume_bom( $sub_bom_query->posts[0], $total_component_qty_needed, $consumed_lots_record, $affected_products, $visited_boms );
            }
        } else {
            $consumption_list = [$component_id];
            if (!empty($item['alternatives']) && is_array($item['alternatives'])) {
                $consumption_list = array_merge($consumption_list, $item['alternatives']);
            }
            wp_mms_consume_component_lots($consumption_list, $total_component_qty_needed, $consumed_lots_record, $affected_products );
        }
    }
}

/**
 * Save the meta box data for the Production Order CPT, handle inventory adjustments, and log changes.
 */
function wp_mms_save_production_order_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_production_order_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_production_order_meta_box_nonce'], 'wp_mms_save_production_order_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_production_order', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'prod_order_created', [
            'object_id'   => $post_id, 'object_type' => 'Production Order', 'description' => 'created production order',
        ]);
    }

    $old_details = [
        'product_id' => get_post_meta( $post_id, '_wp_mms_product_id', true ), 'quantity' => get_post_meta( $post_id, '_wp_mms_quantity', true ),
        'bom_id' => get_post_meta( $post_id, '_wp_mms_bom_id', true ), 'consumed_lots' => get_post_meta( $post_id, '_wp_mms_consumed_lots', true ),
        'output_lot_id' => get_post_meta( $post_id, '_wp_mms_output_lot_id', true ),
    ];

    $fields = [
        'wp_mms_product_id'             => [ 'label' => 'Product', 'sanitize' => 'intval', 'is_title' => true ],
        'wp_mms_bom_id'                 => [ 'label' => 'BOM', 'sanitize' => 'intval', 'is_title' => true ],
        'wp_mms_routing_id'             => [ 'label' => 'Routing', 'sanitize' => 'intval', 'is_title' => true ],
        'wp_mms_quantity'               => [ 'label' => 'Quantity', 'sanitize' => 'intval' ],
        'wp_mms_status'                 => [ 'label' => 'Status', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_start_date'             => [ 'label' => 'Start Date', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_end_date'               => [ 'label' => 'End Date', 'sanitize' => 'sanitize_text_field' ],
        'wp_mms_planned_duration_hours' => [ 'label' => 'Planned Duration', 'sanitize' => 'floatval' ],
    ];

    $new_details = [];
    foreach ( $fields as $key => $details ) {
        if ( isset( $_POST[ $key ] ) ) {
            $old_value = get_post_meta( $post_id, '_' . $key, true );
            $new_value = call_user_func( $details['sanitize'], $_POST[ $key ] );
            $new_details[ str_replace('wp_mms_', '', $key) ] = $new_value;

            if ( $update && $old_value != $new_value ) {
                $old_display = !empty($details['is_title']) ? get_the_title($old_value) : $old_value;
                $new_display = !empty($details['is_title']) ? get_the_title($new_value) : $new_value;
                wp_mms_log_action( 'prod_order_updated', [
                    'object_id'   => $post_id, 'object_type' => 'Production Order', 'description' => "updated {$details['label']}",
                    'old_value'   => $old_display, 'new_value'   => $new_display,
                ]);
            }
            update_post_meta( $post_id, '_' . $key, $new_value );
        }
    }

    $old_status = $old_details['status'] ?? get_post_meta( $post_id, '_wp_mms_status', true );
    $new_status = $new_details['status'];

    // If the routing has changed, copy the steps from the template to this order as a snapshot.
    $old_routing_id = $old_details['routing_id'] ?? get_post_meta( $post_id, '_wp_mms_routing_id', true );
    $new_routing_id = $new_details['routing_id'] ?? 0;

    if ( $new_routing_id && $new_routing_id != $old_routing_id ) {
        $routing_steps = get_post_meta( $new_routing_id, '_wp_mms_routing_steps', true );
        update_post_meta( $post_id, '_wp_mms_production_steps', $routing_steps ?: [] );
    }

    // Record the start time when status changes to 'in_progress'
    if ( $new_status === 'in_progress' && $old_status !== 'in_progress' ) {
        update_post_meta( $post_id, '_wp_mms_actual_start_time', current_time( 'mysql' ) );
    }

    $adjust_inventory_for_production = function( $prod_order_id, $details, $direction ) {
        $product_id = $details['product_id'];
        $quantity_produced = $details['quantity'];
        $bom_id = $details['bom_id'];
        $affected_products = [];
        if ( empty( $product_id ) || empty( $quantity_produced ) || empty($bom_id) ) return;

        if ( 'complete' === $direction ) {
            $consumed_lots_record = [];
            $visited_boms = [];
            wp_mms_recursively_consume_bom( $bom_id, $quantity_produced, $consumed_lots_record, $affected_products, $visited_boms );
            update_post_meta( $prod_order_id, '_wp_mms_consumed_lots', $consumed_lots_record );
            $output_lot_id = wp_insert_post(['post_title' => 'PROD-' . $prod_order_id, 'post_type' => 'wp_mms_lot', 'post_status' => 'publish']);
            if ( !is_wp_error($output_lot_id) ) {
                update_post_meta( $output_lot_id, '_wp_mms_product_id', $product_id );
                update_post_meta( $output_lot_id, '_wp_mms_quantity', $quantity_produced );
                update_post_meta( $prod_order_id, '_wp_mms_output_lot_id', $output_lot_id );
            }
        } elseif ( 'revert' === $direction ) {
            $consumed_lots = $details['consumed_lots'];
            if ( !empty($consumed_lots) && is_array($consumed_lots) ) {
                foreach ( $consumed_lots as $lot_id => $data ) {
                    $affected_products[] = $data['product_id'];
                    $existing_lot = get_post($lot_id);
                    if ($existing_lot) {
                        $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );
                        update_post_meta( $lot_id, '_wp_mms_quantity', $lot_qty + $data['qty'] );
                    } else {
                        $recreated_lot_id = wp_insert_post(['post_title' => $data['title'], 'post_type' => 'wp_mms_lot', 'post_status' => 'publish']);
                        if (!is_wp_error($recreated_lot_id)) {
                             update_post_meta( $recreated_lot_id, '_wp_mms_product_id', $data['product_id'] );
                             update_post_meta( $recreated_lot_id, '_wp_mms_quantity', $data['qty'] );
                        }
                    }
                }
            }
            delete_post_meta( $prod_order_id, '_wp_mms_consumed_lots' );
            $output_lot_id = $details['output_lot_id'];
            if ( !empty($output_lot_id) ) {
                wp_delete_post( $output_lot_id, true );
            }
            delete_post_meta( $prod_order_id, '_wp_mms_output_lot_id' );
        }
        $affected_products[] = $product_id;
        foreach ( array_unique($affected_products) as $p_id ) {
            wp_mms_update_product_stock_from_lots( $p_id );
        }
    };

    if ( $new_status === 'completed' && $old_status !== 'completed' ) {
        $adjust_inventory_for_production( $post_id, $new_details, 'complete' );
    } else if ( $new_status !== 'completed' && $old_status === 'completed' ) {
        $adjust_inventory_for_production( $post_id, $old_details, 'revert' );
    } else if ( $new_status === 'completed' && $old_status === 'completed' ) {
        if ( $old_details['product_id'] != $new_details['product_id'] || $old_details['quantity'] != $new_details['quantity'] || $old_details['bom_id'] != $new_details['bom_id'] ) {
            $adjust_inventory_for_production( $post_id, $old_details, 'revert' );
            $adjust_inventory_for_production( $post_id, $new_details, 'complete' );
        }
    }

    // --- Scrap Recording and Adjustment Logic ---
    $old_scrapped_items = get_post_meta( $post_id, '_wp_mms_scrapped_items', true ) ?: [];
    $new_scrapped_items = [];
    if ( isset( $_POST['_wp_mms_scrapped_items'] ) && is_array( $_POST['_wp_mms_scrapped_items'] ) ) {
        foreach ( $_POST['_wp_mms_scrapped_items'] as $item ) {
            if ( empty( $item['product_id'] ) || !isset( $item['quantity'] ) || floatval($item['quantity']) <= 0 ) continue;
            $new_scrapped_items[] = [ 'product_id' => intval( $item['product_id'] ), 'quantity'   => floatval( $item['quantity'] ) ];
        }
    }
    if ( $old_scrapped_items != $new_scrapped_items ) {
        wp_mms_log_action( 'prod_order_updated', [
            'object_id'   => $post_id, 'object_type' => 'Production Order', 'description' => 'updated scrap records',
            'old_value'   => $old_scrapped_items, 'new_value'   => $new_scrapped_items,
        ]);

        $affected_scrap_products = [];
        $old_consumed_scrap = get_post_meta( $post_id, '_wp_mms_consumed_scrap_lots', true );
        if ( !empty($old_consumed_scrap) && is_array($old_consumed_scrap) ) {
            foreach ( $old_consumed_scrap as $lot_id => $data ) {
                $affected_scrap_products[] = $data['product_id'];
                $existing_lot = get_post($lot_id);
                if ($existing_lot) {
                    $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );
                    update_post_meta( $lot_id, '_wp_mms_quantity', $lot_qty + $data['qty'] );
                } else {
                    $recreated_lot_id = wp_insert_post([ 'post_title' => $data['title'], 'post_type' => 'wp_mms_lot', 'post_status' => 'publish' ]);
                    if (!is_wp_error($recreated_lot_id)) {
                         update_post_meta( $recreated_lot_id, '_wp_mms_product_id', $data['product_id'] );
                         update_post_meta( $recreated_lot_id, '_wp_mms_quantity', $data['qty'] );
                    }
                }
            }
        }
        $new_consumed_scrap = [];
        $total_scrap_cost = 0;
        if ( !empty($new_scrapped_items) ) {
            foreach ( $new_scrapped_items as $scrap_item ) {
                $material_id = $scrap_item['product_id'];
                $qty_to_consume = $scrap_item['quantity'];
                $affected_scrap_products[] = $material_id;
                $unit_cost = floatval(get_post_meta( $material_id, '_wp_mms_unit_cost', true ));
                $total_scrap_cost += ($qty_to_consume * $unit_cost);
                $lots_query = new WP_Query([ 'post_type' => 'wp_mms_lot', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'ASC', 'meta_query' => [ ['key' => '_wp_mms_product_id', 'value' => $material_id] ] ]);
                if ( $lots_query->have_posts() ) {
                    while ( $lots_query->have_posts() && $qty_to_consume > 0 ) {
                        $lots_query->the_post();
                        $lot_id = get_the_ID();
                        $lot_qty = floatval( get_post_meta( $lot_id, '_wp_mms_quantity', true ) );
                        $qty_taken = min( $lot_qty, $qty_to_consume );
                        $new_consumed_scrap[$lot_id] = [ 'qty' => $qty_taken, 'product_id' => $material_id, 'title' => get_the_title() ];
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
        }
        update_post_meta( $post_id, '_wp_mms_scrapped_items', $new_scrapped_items );
        update_post_meta( $post_id, '_wp_mms_consumed_scrap_lots', $new_consumed_scrap );
        update_post_meta( $post_id, '_wp_mms_total_scrap_cost', $total_scrap_cost );
        foreach ( array_unique($affected_scrap_products) as $p_id ) {
            wp_mms_update_product_stock_from_lots( $p_id );
        }
    }
}
add_action( 'save_post_wp_mms_production_order', 'wp_mms_save_production_order_meta_box_data', 10, 3 );

/**
 * Log the deletion of a production order.
 */
function wp_mms_log_prod_order_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_production_order' ) {
        wp_mms_log_action( 'prod_order_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Production Order',
            'description' => 'deleted production order',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_prod_order_deletion' );

/**
 * AJAX handler to calculate and record the actual duration of a production order.
 */
function wp_mms_record_actual_duration_ajax_handler() {
    // Security checks
    check_ajax_referer( 'record_actual_duration_nonce', 'nonce' );
    if ( ! isset( $_POST['order_id'] ) || ! current_user_can( 'edit_mms_production_order', intval( $_POST['order_id'] ) ) ) {
        wp_send_json_error( 'Invalid request.' );
    }

    $order_id = intval( $_POST['order_id'] );
    $start_time_str = get_post_meta( $order_id, '_wp_mms_actual_start_time', true );

    if ( empty( $start_time_str ) ) {
        wp_send_json_error( 'Actual start time has not been recorded.' );
    }

    $start_time = strtotime( $start_time_str );
    $current_time = current_time( 'timestamp' );

    // Calculate duration in hours
    $duration_hours = ( $current_time - $start_time ) / 3600;
    $duration_formatted = number_format( $duration_hours, 2 );

    // Save the calculated duration
    $old_duration = get_post_meta( $order_id, '_wp_mms_actual_duration_hours', true );
    update_post_meta( $order_id, '_wp_mms_actual_duration_hours', $duration_formatted );

    // Log the action
    wp_mms_log_action( 'prod_order_updated', [
        'object_id'   => $order_id,
        'object_type' => 'Production Order',
        'description' => 'recorded Actual Duration',
        'old_value'   => $old_duration,
        'new_value'   => $duration_formatted,
    ]);

    wp_send_json_success( [ 'duration' => $duration_formatted ] );
}
add_action( 'wp_ajax_record_actual_duration', 'wp_mms_record_actual_duration_ajax_handler' );


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
 * Save the meta box data for the Lot CPT and log changes.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_lot_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_lot_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_lot_meta_box_nonce'], 'wp_mms_save_lot_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_lot', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'lot_created', [
            'object_id'   => $post_id, 'object_type' => 'Lot/Batch', 'description' => 'created lot/batch',
        ]);
    }

    $old_product_id = get_post_meta( $post_id, '_wp_mms_product_id', true );

    $fields = [
        '_wp_mms_product_id'  => [ 'label' => 'Product', 'sanitize' => 'intval', 'is_title' => true ],
        '_wp_mms_quantity'    => [ 'label' => 'Quantity', 'sanitize' => 'floatval' ],
        '_wp_mms_expiry_date' => [ 'label' => 'Expiry Date', 'sanitize' => 'sanitize_text_field' ],
    ];

    $new_product_id = $old_product_id;
    foreach ( $fields as $key => $details ) {
        if ( isset( $_POST[ $key ] ) ) {
            $old_value = get_post_meta( $post_id, $key, true );
            $new_value = call_user_func( $details['sanitize'], $_POST[ $key ] );

            if ( $update && $old_value != $new_value ) {
                $old_display = !empty($details['is_title']) ? get_the_title($old_value) : $old_value;
                $new_display = !empty($details['is_title']) ? get_the_title($new_value) : $new_value;
                wp_mms_log_action( 'lot_updated', [
                    'object_id'   => $post_id, 'object_type' => 'Lot/Batch', 'description' => "updated {$details['label']}",
                    'old_value'   => $old_display, 'new_value'   => $new_display,
                ]);
            }

            update_post_meta( $post_id, $key, $new_value );
            if ( $key === '_wp_mms_product_id' ) {
                $new_product_id = $new_value;
            }
        }
    }

    // After saving meta, update the stock for the affected products.
    if ( $old_product_id && $old_product_id != $new_product_id ) {
        wp_mms_update_product_stock_from_lots( $old_product_id );
    }
    if ( $new_product_id ) {
        wp_mms_update_product_stock_from_lots( $new_product_id );
    }
}
add_action( 'save_post_wp_mms_lot', 'wp_mms_save_lot_meta_box_data', 10, 3 );

/**
 * Log the deletion of a lot.
 */
function wp_mms_log_lot_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_lot' ) {
        wp_mms_log_action( 'lot_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Lot/Batch',
            'description' => 'deleted lot/batch',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_lot_deletion' );

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
 * Save the meta box data for the Requisition CPT and log changes.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_save_requisition_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_requisition_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_requisition_meta_box_nonce'], 'wp_mms_save_requisition_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_mms_requisition', $post_id ) ) return;

    if ( ! $update ) {
        wp_mms_log_action( 'requisition_created', [
            'object_id'   => $post_id, 'object_type' => 'Requisition', 'description' => 'created requisition',
        ]);
    }

    // Only allow users with publish caps to change the status
    if ( isset( $_POST['wp_mms_status'] ) && current_user_can('publish_mms_requisitions') ) {
        $old_status = get_post_meta( $post_id, '_wp_mms_status', true );
        $new_status = sanitize_text_field( $_POST['wp_mms_status'] );
        if ( $update && $old_status != $new_status ) {
            wp_mms_log_action( 'requisition_updated', [
                'object_id'   => $post_id, 'object_type' => 'Requisition', 'description' => 'updated Status',
                'old_value'   => $old_status, 'new_value'   => $new_status,
            ]);
        }
        update_post_meta( $post_id, '_wp_mms_status', $new_status );
    }

    if( isset( $_POST['wp_mms_desired_date'] ) ) {
        $old_date = get_post_meta( $post_id, '_wp_mms_desired_date', true );
        $new_date = sanitize_text_field( $_POST['wp_mms_desired_date'] );
        if ( $update && $old_date != $new_date ) {
            wp_mms_log_action( 'requisition_updated', [
                'object_id'   => $post_id, 'object_type' => 'Requisition', 'description' => 'updated Desired Date',
                'old_value'   => $old_date, 'new_value'   => $new_date,
            ]);
        }
        update_post_meta( $post_id, '_wp_mms_desired_date', $new_date );
    }

    $old_items = get_post_meta( $post_id, '_wp_mms_requested_items', true ) ?: [];
    $new_items = [];
    if ( isset( $_POST['wp_mms_requested_items'] ) && is_array( $_POST['wp_mms_requested_items'] ) ) {
        foreach ( $_POST['wp_mms_requested_items'] as $item ) {
            if ( empty( $item['product_id'] ) || !isset( $item['quantity'] ) ) continue;
            $new_items[] = [ 'product_id' => intval( $item['product_id'] ), 'quantity' => intval( $item['quantity'] ) ];
        }
    }

    if ( $update && serialize($old_items) !== serialize($new_items) ) {
        wp_mms_log_action( 'requisition_updated', [
            'object_id'   => $post_id, 'object_type' => 'Requisition', 'description' => 'updated requested items',
            'old_value'   => $old_items, 'new_value'   => $new_items,
        ]);
    }
    update_post_meta( $post_id, '_wp_mms_requested_items', $new_items );
}
add_action( 'save_post_wp_mms_requisition', 'wp_mms_save_requisition_meta_box_data', 10, 3 );

/**
 * Log the deletion of a requisition.
 */
function wp_mms_log_requisition_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_requisition' ) {
        wp_mms_log_action( 'requisition_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Requisition',
            'description' => 'deleted requisition',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_requisition_deletion' );

/**
 * Save the meta box data for the Routing CPT.
 *
 * @param int     $post_id The ID of the post being saved.
 * @param WP_Post $post    The post object.
 * @param bool    $update  Whether this is an existing post being updated or not.
 */
function wp_mms_save_routing_meta_box_data( $post_id, $post, $update ) {
    if ( ! isset( $_POST['wp_mms_routing_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wp_mms_routing_meta_box_nonce'], 'wp_mms_save_routing_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_mms_routing', $post_id ) ) {
        return;
    }

    $old_steps = get_post_meta( $post_id, '_wp_mms_routing_steps', true ) ?: [];
    $new_steps = [];

    if ( isset( $_POST['_wp_mms_routing_steps'] ) && is_array( $_POST['_wp_mms_routing_steps'] ) ) {
        foreach ( $_POST['_wp_mms_routing_steps'] as $step ) {
            $new_step = [];
            $new_step['description'] = isset( $step['description'] ) ? sanitize_text_field( $step['description'] ) : '';
            $new_step['setup_time'] = isset( $step['setup_time'] ) ? floatval( $step['setup_time'] ) : 0;
            $new_step['run_time'] = isset( $step['run_time'] ) ? floatval( $step['run_time'] ) : 0;
            $new_steps[] = $new_step;
        }
    }

    if ( serialize( $old_steps ) !== serialize( $new_steps ) ) {
        update_post_meta( $post_id, '_wp_mms_routing_steps', $new_steps );

        if ( $update ) {
             wp_mms_log_action( 'routing_updated', [
                'object_id'   => $post_id,
                'object_type' => 'Routing',
                'description' => 'updated routing steps',
                'old_value'   => $old_steps,
                'new_value'   => $new_steps,
            ]);
        }
    }

    if ( ! $update ) {
        wp_mms_log_action( 'routing_created', [
            'object_id'   => $post_id,
            'object_type' => 'Routing',
            'description' => 'created routing',
        ]);
    }
}
add_action( 'save_post_wp_mms_routing', 'wp_mms_save_routing_meta_box_data', 10, 3 );

/**
 * Log the deletion of a routing.
 */
function wp_mms_log_routing_deletion( $post_id ) {
    $post = get_post( $post_id );
    if ( $post->post_type === 'wp_mms_routing' ) {
        wp_mms_log_action( 'routing_deleted', [
            'object_id'   => $post_id,
            'object_type' => 'Routing',
            'description' => 'deleted routing',
            'old_value'   => $post->post_title,
        ]);
    }
}
add_action( 'before_delete_post', 'wp_mms_log_routing_deletion' );

/**
 * Add meta boxes for the Routing CPT.
 */
function wp_mms_add_routing_meta_boxes() {
    add_meta_box(
        'wp_mms_routing_steps',
        __( 'Routing Steps', 'wp-mms' ),
        'wp_mms_render_routing_steps_meta_box',
        'wp_mms_routing',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wp_mms_add_routing_meta_boxes' );

/**
 * Render the HTML for the Routing Steps meta box (the designer).
 *
 * @param WP_Post $post The post object.
 */
function wp_mms_render_routing_steps_meta_box( $post ) {
    wp_nonce_field( 'wp_mms_save_routing_meta_box_data', 'wp_mms_routing_meta_box_nonce' );
    $steps = get_post_meta( $post->ID, '_wp_mms_routing_steps', true );
    ?>
    <div id="routing-steps-container">
        <div id="routing-steps-list">
            <?php
            if ( ! empty( $steps ) && is_array( $steps ) ) :
                foreach ( $steps as $i => $step ) : ?>
                    <div class="routing-step">
                        <span class="step-handle dashicons dashicons-move"></span>
                        <div class="step-fields">
                            <label><?php _e( 'Step Description', 'wp-mms' ); ?></label>
                            <input type="text" name="_wp_mms_routing_steps[<?php echo esc_attr( $i ); ?>][description]" value="<?php echo esc_attr( $step['description'] ); ?>" class="large-text">

                            <label><?php _e( 'Setup Time (Hours)', 'wp-mms' ); ?></label>
                            <input type="number" name="_wp_mms_routing_steps[<?php echo esc_attr( $i ); ?>][setup_time]" value="<?php echo esc_attr( $step['setup_time'] ); ?>" class="small-text" step="0.1" min="0">

                            <label><?php _e( 'Run Time per Unit (Hours)', 'wp-mms' ); ?></label>
                            <input type="number" name="_wp_mms_routing_steps[<?php echo esc_attr( $i ); ?>][run_time]" value="<?php echo esc_attr( $step['run_time'] ); ?>" class="small-text" step="0.01" min="0">
                        </div>
                        <button type="button" class="button remove-step-button">Remove Step</button>
                    </div>
                <?php endforeach;
            endif;
            ?>
        </div>
        <button type="button" id="add-routing-step" class="button button-primary"><?php _e( 'Add Step', 'wp-mms' ); ?></button>
    </div>

    <script type="text/template" id="routing-step-template">
        <div class="routing-step">
            <span class="step-handle dashicons dashicons-move"></span>
            <div class="step-fields">
                <label><?php _e( 'Step Description', 'wp-mms' ); ?></label>
                <input type="text" name="_wp_mms_routing_steps[{index}][description]" value="" class="large-text">

                <label><?php _e( 'Setup Time (Hours)', 'wp-mms' ); ?></label>
                <input type="number" name="_wp_mms_routing_steps[{index}][setup_time]" value="0" class="small-text" step="0.1" min="0">

                <label><?php _e( 'Run Time per Unit (Hours)', 'wp-mms' ); ?></label>
                <input type="number" name="_wp_mms_routing_steps[{index}][run_time]" value="0" class="small-text" step="0.01" min="0">
            </div>
            <button type="button" class="button remove-step-button">Remove Step</button>
        </div>
    </script>
    <?php
}