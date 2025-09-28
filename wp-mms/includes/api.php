<?php
/**
 * REST API Endpoints
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Check if the user has permission to manage products.
 *
 * @param WP_REST_Request $request The request object.
 * @return bool True if the user has permission, otherwise false.
 */
function wp_mms_manage_products_permissions_check( $request ) {
    return current_user_can( 'publish_mms_products' );
}

/**
 * Create a new product.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function wp_mms_create_product( $request ) {
    $params = $request->get_params();
    $post_args = [
        'post_title'  => $params['name'],
        'post_type'   => 'wp_mms_product',
        'post_status' => 'publish',
    ];
    $post_id = wp_insert_post( $post_args );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'cant_create', $post_id->get_error_message(), [ 'status' => 500 ] );
    }

    // Update meta fields
    $schema = wp_mms_get_product_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    $response = wp_mms_get_product( new WP_REST_Request( 'GET', sprintf( '/wp-mms/v1/products/%d', $post_id ) ) );
    $response->set_status( 201 );
    return $response;
}

/**
 * Update an existing product.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function wp_mms_update_product( $request ) {
    $post_id = $request['id'];
    $params = $request->get_params();

    // Update post title if 'name' is provided
    if ( isset( $params['name'] ) ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $params['name'] ] );
    }

    // Update meta fields
    $schema = wp_mms_get_product_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    return wp_mms_get_product( $request );
}

/**
 * Delete a product.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function wp_mms_delete_product( $request ) {
    $post_id = $request['id'];
    $result = wp_delete_post( $post_id, true ); // true to force delete

    if ( ! $result ) {
        return new WP_Error( 'cant_delete', 'Failed to delete product', [ 'status' => 500 ] );
    }

    return new WP_REST_Response( [ 'deleted' => true, 'previous' => wp_mms_prepare_product_for_api( $result ) ], 200 );
}


/**
 * Register all the custom REST API routes.
 */
function wp_mms_register_api_routes() {
    $namespace = 'wp-mms/v1';

    // Product Routes
    register_rest_route( $namespace, '/products', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_products',
            'permission_callback' => 'wp_mms_get_products_permissions_check',
            'args'                => [],
        ],
    ]);

    register_rest_route( $namespace, '/products/(?P<id>\d+)', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_product',
            'permission_callback' => 'wp_mms_get_products_permissions_check',
            'args'                => [
                'id' => [
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'wp_mms_create_product',
            'permission_callback' => 'wp_mms_manage_products_permissions_check',
            'args'                => 'wp_mms_get_product_schema',
        ],
    ]);

    register_rest_route( $namespace, '/products/(?P<id>\d+)', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_product',
            'permission_callback' => 'wp_mms_get_products_permissions_check',
            'args'                => [
                'id' => [
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'wp_mms_update_product',
            'permission_callback' => 'wp_mms_manage_products_permissions_check',
            'args'                => 'wp_mms_get_product_schema',
        ],
        [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => 'wp_mms_delete_product',
            'permission_callback' => 'wp_mms_manage_products_permissions_check',
            'args'                => [
                'id' => [
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ],
            ],
        ],
    ]);

    // Supplier Routes
    register_rest_route( $namespace, '/suppliers', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_suppliers',
            'permission_callback' => 'wp_mms_get_suppliers_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'wp_mms_create_supplier',
            'permission_callback' => 'wp_mms_manage_suppliers_permissions_check',
            'args'                => 'wp_mms_get_supplier_schema',
        ],
    ]);

    register_rest_route( $namespace, '/suppliers/(?P<id>\d+)', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_supplier',
            'permission_callback' => 'wp_mms_get_suppliers_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'wp_mms_update_supplier',
            'permission_callback' => 'wp_mms_manage_suppliers_permissions_check',
            'args'                => 'wp_mms_get_supplier_schema',
        ],
        [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => 'wp_mms_delete_supplier',
            'permission_callback' => 'wp_mms_manage_suppliers_permissions_check',
        ],
    ]);

    // Purchase Order Routes
    register_rest_route( $namespace, '/purchase-orders', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_purchase_orders',
            'permission_callback' => 'wp_mms_get_purchase_orders_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'wp_mms_create_purchase_order',
            'permission_callback' => 'wp_mms_manage_purchase_orders_permissions_check',
            'args'                => 'wp_mms_get_purchase_order_schema',
        ],
    ]);

    register_rest_route( $namespace, '/purchase-orders/(?P<id>\d+)', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_purchase_order',
            'permission_callback' => 'wp_mms_get_purchase_orders_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'wp_mms_update_purchase_order',
            'permission_callback' => 'wp_mms_manage_purchase_orders_permissions_check',
            'args'                => 'wp_mms_get_purchase_order_schema',
        ],
        [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => 'wp_mms_delete_purchase_order',
            'permission_callback' => 'wp_mms_manage_purchase_orders_permissions_check',
        ],
    ]);

    // Production Order Routes
    register_rest_route( $namespace, '/production-orders', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_production_orders',
            'permission_callback' => 'wp_mms_get_production_orders_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'wp_mms_create_production_order',
            'permission_callback' => 'wp_mms_manage_production_orders_permissions_check',
            'args'                => 'wp_mms_get_production_order_schema',
        ],
    ]);

    register_rest_route( $namespace, '/production-orders/(?P<id>\d+)', [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'wp_mms_get_production_order',
            'permission_callback' => 'wp_mms_get_production_orders_permissions_check',
        ],
        [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'wp_mms_update_production_order',
            'permission_callback' => 'wp_mms_manage_production_orders_permissions_check',
            'args'                => 'wp_mms_get_production_order_schema',
        ],
        [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => 'wp_mms_delete_production_order',
            'permission_callback' => 'wp_mms_manage_production_orders_permissions_check',
        ],
    ]);
}
add_action( 'rest_api_init', 'wp_mms_register_api_routes' );

/**
 * Get the schema for a production order.
 * @return array
 */
function wp_mms_get_production_order_schema() {
    return [
        'name'           => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        'product_id'     => [ 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'intval' ],
        'bom_id'         => [ 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'intval' ],
        'quantity'       => [ 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'intval' ],
        'status'         => [ 'type' => 'string', 'enum' => ['pending', 'in_progress', 'completed', 'canceled'] ],
        'start_date'     => [ 'type' => 'string', 'format' => 'date', 'sanitize_callback' => 'sanitize_text_field' ],
        'end_date'       => [ 'type' => 'string', 'format' => 'date', 'sanitize_callback' => 'sanitize_text_field' ],
    ];
}

/**
 * Check permissions for getting production orders.
 * @return bool
 */
function wp_mms_get_production_orders_permissions_check() {
    return current_user_can( 'edit_mms_production_orders' );
}

/**
 * Check permissions for managing production orders.
 * @return bool
 */
function wp_mms_manage_production_orders_permissions_check() {
    return current_user_can( 'publish_mms_production_orders' );
}

/**
 * Get a list of production orders.
 * @return WP_REST_Response
 */
function wp_mms_get_production_orders() {
    $posts = get_posts( [ 'post_type' => 'wp_mms_production_order', 'posts_per_page' => -1 ] );
    $data = array_map( 'wp_mms_prepare_production_order_for_api', $posts );
    return new WP_REST_Response( $data, 200 );
}

/**
 * Get a single production order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_get_production_order( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) || 'wp_mms_production_order' !== $post->post_type ) {
        return new WP_Error( 'not_found', 'Production Order not found', [ 'status' => 404 ] );
    }
    return new WP_REST_Response( wp_mms_prepare_production_order_for_api( $post ), 200 );
}

/**
 * Create a new production order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_create_production_order( $request ) {
    $params = $request->get_params();
    $post_id = wp_insert_post( [
        'post_title' => $params['name'],
        'post_type'  => 'wp_mms_production_order',
        'post_status'=> 'publish',
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'cant_create', $post_id->get_error_message(), [ 'status' => 500 ] );
    }

    $schema = wp_mms_get_production_order_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    $response = wp_mms_get_production_order( new WP_REST_Request( 'GET', sprintf( '/wp-mms/v1/production-orders/%d', $post_id ) ) );
    $response->set_status( 201 );
    return $response;
}

/**
 * Update an existing production order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_update_production_order( $request ) {
    $post_id = $request['id'];
    $params = $request->get_params();

    if ( isset( $params['name'] ) ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $params['name'] ] );
    }

    $schema = wp_mms_get_production_order_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }
    // Manually trigger the save_post hook to ensure inventory logic runs.
    wp_update_post( [ 'ID' => $post_id ] );

    return wp_mms_get_production_order( $request );
}

/**
 * Delete a production order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_delete_production_order( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) ) {
        return new WP_Error( 'not_found', 'Production Order not found', [ 'status' => 404 ] );
    }

    $result = wp_delete_post( $request['id'], true );
    if ( ! $result ) {
        return new WP_Error( 'cant_delete', 'Failed to delete production order', [ 'status' => 500 ] );
    }

    return new WP_REST_Response( [ 'deleted' => true, 'previous' => wp_mms_prepare_production_order_for_api( $post ) ], 200 );
}

/**
 * Prepare production order data for API response.
 * @param WP_Post $post
 * @return array
 */
function wp_mms_prepare_production_order_for_api( $post ) {
    return [
        'id'            => $post->ID,
        'name'          => $post->post_title,
        'product_id'    => (int) get_post_meta( $post->ID, '_wp_mms_product_id', true ),
        'bom_id'        => (int) get_post_meta( $post->ID, '_wp_mms_bom_id', true ),
        'quantity'      => (int) get_post_meta( $post->ID, '_wp_mms_quantity', true ),
        'status'        => get_post_meta( $post->ID, '_wp_mms_status', true ),
        'start_date'    => get_post_meta( $post->ID, '_wp_mms_start_date', true ),
        'end_date'      => get_post_meta( $post->ID, '_wp_mms_end_date', true ),
        'planned_duration' => (float) get_post_meta( $post->ID, '_wp_mms_planned_duration_hours', true ),
        'actual_duration'  => (float) get_post_meta( $post->ID, '_wp_mms_actual_duration_hours', true ),
    ];
}

/**
 * Get the schema for a purchase order.
 * @return array
 */
function wp_mms_get_purchase_order_schema() {
    return [
        'name'           => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        'supplier_id'    => [ 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'intval' ],
        'status'         => [ 'type' => 'string', 'enum' => ['pending', 'approved', 'shipped', 'received', 'canceled'] ],
        'order_date'     => [ 'type' => 'string', 'format' => 'date', 'sanitize_callback' => 'sanitize_text_field' ],
        'expected_date'  => [ 'type' => 'string', 'format' => 'date', 'sanitize_callback' => 'sanitize_text_field' ],
        'line_items'     => [
            'type'  => 'array',
            'items' => [
                'type'       => 'object',
                'properties' => [
                    'product_id' => [ 'type' => 'integer', 'required' => true ],
                    'quantity'   => [ 'type' => 'number', 'required' => true ],
                    'unit_price' => [ 'type' => 'number', 'required' => true ],
                ],
            ],
        ],
    ];
}

/**
 * Check permissions for getting purchase orders.
 * @return bool
 */
function wp_mms_get_purchase_orders_permissions_check() {
    return current_user_can( 'edit_mms_purchase_orders' );
}

/**
 * Check permissions for managing purchase orders.
 * @return bool
 */
function wp_mms_manage_purchase_orders_permissions_check() {
    return current_user_can( 'publish_mms_purchase_orders' );
}

/**
 * Get a list of purchase orders.
 * @return WP_REST_Response
 */
function wp_mms_get_purchase_orders() {
    $posts = get_posts( [ 'post_type' => 'wp_mms_purchase_order', 'posts_per_page' => -1 ] );
    $data = array_map( 'wp_mms_prepare_purchase_order_for_api', $posts );
    return new WP_REST_Response( $data, 200 );
}

/**
 * Get a single purchase order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_get_purchase_order( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) || 'wp_mms_purchase_order' !== $post->post_type ) {
        return new WP_Error( 'not_found', 'Purchase Order not found', [ 'status' => 404 ] );
    }
    return new WP_REST_Response( wp_mms_prepare_purchase_order_for_api( $post ), 200 );
}

/**
 * Create a new purchase order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_create_purchase_order( $request ) {
    $params = $request->get_params();
    $post_id = wp_insert_post( [
        'post_title' => $params['name'],
        'post_type'  => 'wp_mms_purchase_order',
        'post_status'=> 'publish',
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'cant_create', $post_id->get_error_message(), [ 'status' => 500 ] );
    }

    $schema = wp_mms_get_purchase_order_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    $response = wp_mms_get_purchase_order( new WP_REST_Request( 'GET', sprintf( '/wp-mms/v1/purchase-orders/%d', $post_id ) ) );
    $response->set_status( 201 );
    return $response;
}

/**
 * Update an existing purchase order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_update_purchase_order( $request ) {
    $post_id = $request['id'];
    $params = $request->get_params();

    if ( isset( $params['name'] ) ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $params['name'] ] );
    }

    $schema = wp_mms_get_purchase_order_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            // This will trigger the save_post hook which handles stock adjustments.
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }
    // Manually trigger the save_post hook to ensure inventory logic runs.
    wp_update_post( [ 'ID' => $post_id ] );

    return wp_mms_get_purchase_order( $request );
}

/**
 * Delete a purchase order.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_delete_purchase_order( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) ) {
        return new WP_Error( 'not_found', 'Purchase Order not found', [ 'status' => 404 ] );
    }

    $result = wp_delete_post( $request['id'], true );
    if ( ! $result ) {
        return new WP_Error( 'cant_delete', 'Failed to delete purchase order', [ 'status' => 500 ] );
    }

    return new WP_REST_Response( [ 'deleted' => true, 'previous' => wp_mms_prepare_purchase_order_for_api( $post ) ], 200 );
}

/**
 * Prepare purchase order data for API response.
 * @param WP_Post $post
 * @return array
 */
function wp_mms_prepare_purchase_order_for_api( $post ) {
    return [
        'id'            => $post->ID,
        'name'          => $post->post_title,
        'supplier_id'   => (int) get_post_meta( $post->ID, '_wp_mms_supplier_id', true ),
        'status'        => get_post_meta( $post->ID, '_wp_mms_status', true ),
        'order_date'    => get_post_meta( $post->ID, '_wp_mms_order_date', true ),
        'expected_date' => get_post_meta( $post->ID, '_wp_mms_expected_date', true ),
        'date_received' => get_post_meta( $post->ID, '_wp_mms_date_received', true ),
        'line_items'    => get_post_meta( $post->ID, '_wp_mms_line_items', true ) ?: [],
    ];
}

/**
 * Get the schema for a supplier.
 * @return array
 */
function wp_mms_get_supplier_schema() {
    return [
        'name'         => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        'contact_name' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        'email'        => [ 'type' => 'string', 'format' => 'email', 'sanitize_callback' => 'sanitize_email' ],
        'phone'        => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        'address'      => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ],
        'website'      => [ 'type' => 'string', 'format' => 'uri', 'sanitize_callback' => 'esc_url_raw' ],
        'lead_time'    => [ 'type' => 'integer', 'sanitize_callback' => 'intval' ],
    ];
}

/**
 * Check permissions for getting suppliers.
 * @return bool
 */
function wp_mms_get_suppliers_permissions_check() {
    return current_user_can( 'edit_mms_suppliers' );
}

/**
 * Check permissions for managing suppliers.
 * @return bool
 */
function wp_mms_manage_suppliers_permissions_check() {
    return current_user_can( 'publish_mms_suppliers' );
}

/**
 * Get a list of suppliers.
 * @return WP_REST_Response
 */
function wp_mms_get_suppliers() {
    $posts = get_posts( [ 'post_type' => 'wp_mms_supplier', 'posts_per_page' => -1 ] );
    $data = array_map( 'wp_mms_prepare_supplier_for_api', $posts );
    return new WP_REST_Response( $data, 200 );
}

/**
 * Get a single supplier.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_get_supplier( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) || 'wp_mms_supplier' !== $post->post_type ) {
        return new WP_Error( 'not_found', 'Supplier not found', [ 'status' => 404 ] );
    }
    return new WP_REST_Response( wp_mms_prepare_supplier_for_api( $post ), 200 );
}

/**
 * Create a new supplier.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_create_supplier( $request ) {
    $params = $request->get_params();
    $post_id = wp_insert_post( [
        'post_title' => $params['name'],
        'post_type'  => 'wp_mms_supplier',
        'post_status'=> 'publish',
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'cant_create', $post_id->get_error_message(), [ 'status' => 500 ] );
    }

    $schema = wp_mms_get_supplier_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    $response = wp_mms_get_supplier( new WP_REST_Request( 'GET', sprintf( '/wp-mms/v1/suppliers/%d', $post_id ) ) );
    $response->set_status( 201 );
    return $response;
}

/**
 * Update an existing supplier.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_update_supplier( $request ) {
    $post_id = $request['id'];
    $params = $request->get_params();

    if ( isset( $params['name'] ) ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $params['name'] ] );
    }

    $schema = wp_mms_get_supplier_schema();
    foreach ( $schema as $key => $args ) {
        if ( isset( $params[ $key ] ) ) {
            update_post_meta( $post_id, '_wp_mms_' . $key, $params[ $key ] );
        }
    }

    return wp_mms_get_supplier( $request );
}

/**
 * Delete a supplier.
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function wp_mms_delete_supplier( $request ) {
    $post = get_post( $request['id'] );
    if ( empty( $post ) ) {
        return new WP_Error( 'not_found', 'Supplier not found', [ 'status' => 404 ] );
    }

    $result = wp_delete_post( $request['id'], true );
    if ( ! $result ) {
        return new WP_Error( 'cant_delete', 'Failed to delete supplier', [ 'status' => 500 ] );
    }

    return new WP_REST_Response( [ 'deleted' => true, 'previous' => wp_mms_prepare_supplier_for_api( $post ) ], 200 );
}

/**
 * Prepare supplier data for API response.
 * @param WP_Post $post
 * @return array
 */
function wp_mms_prepare_supplier_for_api( $post ) {
    return [
        'id'           => $post->ID,
        'name'         => $post->post_title,
        'contact_name' => get_post_meta( $post->ID, '_wp_mms_contact_name', true ),
        'email'        => get_post_meta( $post->ID, '_wp_mms_email', true ),
        'phone'        => get_post_meta( $post->ID, '_wp_mms_phone', true ),
        'address'      => get_post_meta( $post->ID, '_wp_mms_address', true ),
        'website'      => get_post_meta( $post->ID, '_wp_mms_website', true ),
        'lead_time'    => (int) get_post_meta( $post->ID, '_wp_mms_lead_time', true ),
    ];
}

/**
 * Get the schema for a product, used for argument validation.
 *
 * @return array The schema array.
 */
function wp_mms_get_product_schema() {
    return [
        'name'          => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
        'sku'           => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        'barcode'       => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        'item_type'     => [ 'type' => 'string', 'enum' => ['raw_material', 'component', 'finished_good'] ],
        'reorder_point' => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
        'unit_cost'     => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
        'selling_price' => [ 'type' => 'number', 'sanitize_callback' => 'floatval' ],
    ];
}

/**
 * Check if the user has permission to get products.
 *
 * @param WP_REST_Request $request The request object.
 * @return bool True if the user has permission, otherwise false.
 */
function wp_mms_get_products_permissions_check( $request ) {
    return current_user_can( 'edit_mms_products' );
}

/**
 * Get a list of products.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function wp_mms_get_products( $request ) {
    $args = [
        'post_type'      => 'wp_mms_product',
        'posts_per_page' => -1,
    ];
    $posts = get_posts( $args );

    $data = [];
    foreach ( $posts as $post ) {
        $data[] = wp_mms_prepare_product_for_api( $post );
    }

    return new WP_REST_Response( $data, 200 );
}

/**
 * Get a single product.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function wp_mms_get_product( $request ) {
    $post = get_post( $request['id'] );

    if ( empty( $post ) || 'wp_mms_product' !== $post->post_type ) {
        return new WP_Error( 'not_found', 'Product not found', [ 'status' => 404 ] );
    }

    $data = wp_mms_prepare_product_for_api( $post );
    return new WP_REST_Response( $data, 200 );
}

/**
 * Prepare a product post object for the API response.
 *
 * @param WP_Post $post The post object.
 * @return array The formatted product data.
 */
function wp_mms_prepare_product_for_api( $post ) {
    $product_data = [
        'id'            => $post->ID,
        'name'          => $post->post_title,
        'sku'           => get_post_meta( $post->ID, '_wp_mms_sku', true ),
        'barcode'       => get_post_meta( $post->ID, '_wp_mms_barcode', true ),
        'stock_quantity'=> (float) get_post_meta( $post->ID, '_wp_mms_stock_quantity', true ),
        'reorder_point' => (float) get_post_meta( $post->ID, '_wp_mms_reorder_point', true ),
        'item_type'     => get_post_meta( $post->ID, '_wp_mms_item_type', true ),
        'unit_cost'     => (float) get_post_meta( $post->ID, '_wp_mms_unit_cost', true ),
        'selling_price' => (float) get_post_meta( $post->ID, '_wp_mms_selling_price', true ),
        'last_modified' => $post->post_modified_gmt,
    ];
    return $product_data;
}