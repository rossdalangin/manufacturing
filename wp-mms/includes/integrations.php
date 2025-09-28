<?php
/**
 * Integration Framework
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the settings and fields for the Integrations page.
 */
function wp_mms_register_integration_settings() {
    // Register the main setting group for e-commerce integration
    register_setting(
        'wp_mms_integrations_group',      // Option group
        'wp_mms_ecommerce_settings',      // Option name
        'wp_mms_ecommerce_settings_sanitize' // Sanitize callback
    );

    // Add the section for E-commerce Settings
    add_settings_section(
        'wp_mms_ecommerce_section',          // ID
        __( 'Generic E-commerce Integration', 'wp-mms' ), // Title
        'wp_mms_ecommerce_section_callback', // Callback
        'wp_mms_integrations_page'           // Page
    );

    // API Endpoint URL
    add_settings_field(
        'ecommerce_api_endpoint',
        __( 'API Endpoint URL', 'wp-mms' ),
        'wp_mms_ecommerce_api_endpoint_callback',
        'wp_mms_integrations_page',
        'wp_mms_ecommerce_section'
    );

    // API Key
    add_settings_field(
        'ecommerce_api_key',
        __( 'API Key', 'wp-mms' ),
        'wp_mms_ecommerce_api_key_callback',
        'wp_mms_integrations_page',
        'wp_mms_ecommerce_section'
    );

    // Enable Automatic Syncing
    add_settings_field(
        'ecommerce_enable_auto_sync',
        __( 'Automatic Syncing', 'wp-mms' ),
        'wp_mms_ecommerce_enable_auto_sync_callback',
        'wp_mms_integrations_page',
        'wp_mms_ecommerce_section'
    );
}
add_action( 'admin_init', 'wp_mms_register_integration_settings' );

/**
 * Sanitize the e-commerce settings input.
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function wp_mms_ecommerce_settings_sanitize( $input ) {
    $new_input = [];
    if ( isset( $input['api_endpoint'] ) ) {
        $new_input['api_endpoint'] = esc_url_raw( $input['api_endpoint'] );
    }
    if ( isset( $input['api_key'] ) ) {
        $new_input['api_key'] = sanitize_text_field( $input['api_key'] );
    }
    $new_input['enable_auto_sync'] = isset( $input['enable_auto_sync'] ) ? 1 : 0;
    return $new_input;
}

/**
 * Render the API Endpoint URL field.
 */
function wp_mms_ecommerce_api_endpoint_callback() {
    $options = get_option( 'wp_mms_ecommerce_settings' );
    $endpoint = isset( $options['api_endpoint'] ) ? $options['api_endpoint'] : '';
    printf(
        '<input type="url" id="ecommerce_api_endpoint" name="wp_mms_ecommerce_settings[api_endpoint]" value="%s" class="regular-text" />',
        esc_attr( $endpoint )
    );
}

/**
 * Render the API Key field.
 */
function wp_mms_ecommerce_api_key_callback() {
    $options = get_option( 'wp_mms_ecommerce_settings' );
    $api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
    printf(
        '<input type="password" id="ecommerce_api_key" name="wp_mms_ecommerce_settings[api_key]" value="%s" class="regular-text" />',
        esc_attr( $api_key )
    );
}

/**
 * Render the Enable Automatic Syncing checkbox.
 */
function wp_mms_ecommerce_enable_auto_sync_callback() {
    $options = get_option( 'wp_mms_ecommerce_settings' );
    $checked = isset( $options['enable_auto_sync'] ) && $options['enable_auto_sync'] ? 'checked="checked"' : '';
    echo '<label><input type="checkbox" id="ecommerce_enable_auto_sync" name="wp_mms_ecommerce_settings[enable_auto_sync]" value="1" ' . $checked . ' /> ';
    _e( 'Enable automatic syncing when a product is updated', 'wp-mms' );
    echo '</label>';
}

/**
 * Handle the manual product sync request.
 */
function wp_mms_handle_manual_sync() {
    check_admin_referer( 'wp_mms_manual_sync_nonce' );

    if ( ! current_user_can( 'manage_mms_options' ) ) {
        wp_die( 'Permission denied.' );
    }

    $products_query = new WP_Query([
        'post_type'      => 'wp_mms_product',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'   => '_wp_mms_item_type',
                'value' => 'finished_good',
            ]
        ],
    ]);

    $synced_count = 0;
    $error_count = 0;

    if ( $products_query->have_posts() ) {
        while ( $products_query->have_posts() ) {
            $products_query->the_post();
            $result = wp_mms_sync_product_to_ecommerce( get_the_ID() );
            if ( $result ) {
                $synced_count++;
            } else {
                $error_count++;
            }
        }
    }
    wp_reset_postdata();

    // Redirect back with a notice
    $redirect_url = add_query_arg(
        [
            'page' => 'wp_mms_integrations',
            'synced' => $synced_count,
            'errors' => $error_count
        ],
        admin_url( 'admin.php' )
    );
    wp_redirect( $redirect_url );
    exit;
}
add_action( 'admin_post_wp_mms_manual_sync_products', 'wp_mms_handle_manual_sync' );

/**
 * Display notices after manual sync.
 */
function wp_mms_sync_admin_notices() {
    if ( isset( $_GET['page'] ) && 'wp_mms_integrations' === $_GET['page'] ) {
        if ( isset( $_GET['synced'] ) ) {
            $synced_count = intval( $_GET['synced'] );
            $error_count = isset( $_GET['errors'] ) ? intval( $_GET['errors'] ) : 0;
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( 'Manual sync complete. Successfully synced %d products. Failed to sync %d products.', 'wp-mms' ), $synced_count, $error_count ) . '</p></div>';
        }
    }
}
add_action( 'admin_notices', 'wp_mms_sync_admin_notices' );


/**
 * Handle automatic product syncing on save.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wp_mms_handle_automatic_sync( $post_id ) {
    // Check if this is an autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check if the post type is correct.
    if ( 'wp_mms_product' !== get_post_type( $post_id ) ) {
        return;
    }
    // Check user permissions.
    if ( ! current_user_can( 'edit_mms_product', $post_id ) ) {
        return;
    }

    $options = get_option( 'wp_mms_ecommerce_settings', [] );
    if ( ! empty( $options['enable_auto_sync'] ) ) {
        wp_mms_sync_product_to_ecommerce( $post_id );
    }
}
add_action( 'save_post_wp_mms_product', 'wp_mms_handle_automatic_sync' );


/**
 * Sends a single product's data to the configured e-commerce endpoint.
 *
 * @param int $product_id The ID of the product to sync.
 * @return bool True on success, false on failure.
 */
function wp_mms_sync_product_to_ecommerce( $product_id ) {
    $options = get_option( 'wp_mms_ecommerce_settings' );
    $endpoint_url = isset( $options['api_endpoint'] ) ? $options['api_endpoint'] : '';
    $api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';

    if ( empty( $endpoint_url ) ) {
        return false;
    }

    $product = get_post( $product_id );
    if ( ! $product || 'wp_mms_product' !== $product->post_type ) {
        return false;
    }

    // Prepare the data payload
    $data = [
        'id'             => $product->ID,
        'name'           => $product->post_title,
        'sku'            => get_post_meta( $product->ID, '_wp_mms_sku', true ),
        'price'          => (float) get_post_meta( $product->ID, '_wp_mms_selling_price', true ),
        'stock_quantity' => (float) get_post_meta( $product->ID, '_wp_mms_stock_quantity', true ),
    ];

    $response = wp_remote_post( $endpoint_url, [
        'method'    => 'POST',
        'headers'   => [
            'Content-Type'  => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body'      => wp_json_encode( $data ),
        'timeout'   => 15,
    ]);

    if ( is_wp_error( $response ) ) {
        // Optionally log the error: error_log( 'E-commerce Sync Error: ' . $response->get_error_message() );
        return false;
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    return ( $response_code >= 200 && $response_code < 300 );
}

/**
 * Print the Section text for the e-commerce section.
 */
function wp_mms_ecommerce_section_callback() {
    print __( 'Configure the connection to a generic e-commerce platform. The system will send product data (name, SKU, price, stock) to the specified endpoint.', 'wp-mms' );
}

/**
 * Render the main Integrations page HTML wrapper.
 */
function wp_mms_integrations_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e( 'Configure connections to external services like e-commerce platforms and accounting software.', 'wp-mms' ); ?></p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'wp_mms_integrations_group' );
            do_settings_sections( 'wp_mms_integrations_page' );
            submit_button( 'Save Settings' );
            ?>
        </form>

        <hr>
        <h2><?php _e( 'Manual Sync', 'wp-mms' ); ?></h2>
        <p><?php _e( 'Manually push all "Finished Good" products to the configured e-commerce endpoint.', 'wp-mms' ); ?></p>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <input type="hidden" name="action" value="wp_mms_manual_sync_products">
            <?php wp_nonce_field( 'wp_mms_manual_sync_nonce' ); ?>
            <?php submit_button( 'Sync All Products Now' ); ?>
        </form>
    </div>
    <?php
}