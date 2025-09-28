<?php
/**
 * Settings Page
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the settings page and all its fields.
 */
function wp_mms_register_settings() {
    // Register the main setting group
    register_setting(
        'wp_mms_options_group', // Option group
        'wp_mms_settings',      // Option name
        'wp_mms_settings_sanitize' // Sanitize callback
    );

    // Add the main section for Inventory Settings
    add_settings_section(
        'wp_mms_inventory_section', // ID
        __( 'Inventory Settings', 'wp-mms' ), // Title
        'wp_mms_inventory_section_callback', // Callback
        'wp_mms_settings_page' // Page
    );

    // Add Lot Consumption Method field
    add_settings_field(
        'lot_consumption_method', // ID
        __( 'Lot Consumption Method', 'wp-mms' ), // Title
        'wp_mms_lot_consumption_method_callback', // Callback
        'wp_mms_settings_page', // Page
        'wp_mms_inventory_section' // Section
    );

    // Add Inventory Valuation Method field
    add_settings_field(
        'inventory_valuation_method', // ID
        __( 'Inventory Valuation Method', 'wp-mms' ), // Title
        'wp_mms_inventory_valuation_method_callback', // Callback
        'wp_mms_settings_page', // Page
        'wp_mms_inventory_section' // Section
    );
}
add_action( 'admin_init', 'wp_mms_register_settings' );

/**
 * Sanitize the settings input.
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function wp_mms_settings_sanitize( $input ) {
    $new_input = [];
    if ( isset( $input['lot_consumption_method'] ) ) {
        $new_input['lot_consumption_method'] = in_array($input['lot_consumption_method'], ['fifo', 'lifo']) ? $input['lot_consumption_method'] : 'fifo';
    }
    if ( isset( $input['inventory_valuation_method'] ) ) {
        $new_input['inventory_valuation_method'] = in_array($input['inventory_valuation_method'], ['manual', 'weighted_average']) ? $input['inventory_valuation_method'] : 'manual';
    }
    return $new_input;
}

/**
 * Print the Section text
 */
function wp_mms_inventory_section_callback() {
    print __( 'Configure how inventory is consumed and valued.', 'wp-mms' );
}

/**
 * Render the Lot Consumption Method radio buttons.
 */
function wp_mms_lot_consumption_method_callback() {
    $options = get_option( 'wp_mms_settings', ['lot_consumption_method' => 'fifo'] );
    $method = $options['lot_consumption_method'];
    ?>
    <fieldset>
        <label><input type="radio" name="wp_mms_settings[lot_consumption_method]" value="fifo" <?php checked( $method, 'fifo' ); ?> /> <?php _e( 'FIFO (First-In, First-Out)', 'wp-mms' ); ?></label>
        <p class="description"><?php _e( 'The oldest inventory lots are consumed first. This is the most common method.', 'wp-mms' ); ?></p>
        <br>
        <label><input type="radio" name="wp_mms_settings[lot_consumption_method]" value="lifo" <?php checked( $method, 'lifo' ); ?> /> <?php _e( 'LIFO (Last-In, First-Out)', 'wp-mms' ); ?></label>
        <p class="description"><?php _e( 'The newest inventory lots are consumed first.', 'wp-mms' ); ?></p>
    </fieldset>
    <?php
}

/**
 * Render the Inventory Valuation Method radio buttons.
 */
function wp_mms_inventory_valuation_method_callback() {
    $options = get_option( 'wp_mms_settings', ['inventory_valuation_method' => 'manual'] );
    $method = $options['inventory_valuation_method'];
    ?>
    <fieldset>
        <label><input type="radio" name="wp_mms_settings[inventory_valuation_method]" value="manual" <?php checked( $method, 'manual' ); ?> /> <?php _e( 'Manual Costing', 'wp-mms' ); ?></label>
        <p class="description"><?php _e( 'Product unit costs are set manually on the product page. This is the default method.', 'wp-mms' ); ?></p>
        <br>
        <label><input type="radio" name="wp_mms_settings[inventory_valuation_method]" value="weighted_average" <?php checked( $method, 'weighted_average' ); ?> /> <?php _e( 'Weighted Average Cost', 'wp-mms' ); ?></label>
        <p class="description"><?php _e( 'Product unit cost is automatically recalculated as a weighted average each time new stock is received via a Purchase Order.', 'wp-mms' ); ?></p>
    </fieldset>
    <?php
}

/**
 * Render the main settings page HTML wrapper.
 */
function wp_mms_settings_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'wp_mms_options_group' );
            do_settings_sections( 'wp_mms_settings_page' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}