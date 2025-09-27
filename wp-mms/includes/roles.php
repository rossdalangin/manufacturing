<?php
/**
 * User Roles and Capabilities
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Gets a list of base capabilities for a CPT.
 * @param string $slug The CPT slug (singular).
 * @return array
 */
function wp_mms_get_cpt_caps( $slug ) {
    return [
        "edit_{$slug}", "read_{$slug}", "delete_{$slug}",
        "edit_{$slug}s", "edit_others_{$slug}s", "publish_{$slug}s", "read_private_{$slug}s",
        "delete_{$slug}s", "delete_private_{$slug}s", "delete_published_{$slug}s", "delete_others_{$slug}s", "edit_private_{$slug}s", "edit_published_{$slug}s",
    ];
}

/**
 * Gets a list of read-only capabilities for a CPT.
 * @param string $slug The CPT slug (singular).
 * @return array
 */
function wp_mms_get_cpt_read_caps( $slug ) {
    return [ "read_{$slug}", "read_private_{$slug}s", "edit_{$slug}s" ]; // 'edit_{slug}s' is needed to view the admin list table.
}

/**
 * Get all custom capabilities for the plugin.
 * @return array
 */
function wp_mms_get_all_capabilities() {
    $caps = [];
    $cpts = ['mms_supplier', 'mms_product', 'mms_purchase_order', 'mms_bom', 'mms_production_order', 'mms_requisition'];
    foreach ( $cpts as $cpt ) {
        $caps = array_merge( $caps, wp_mms_get_cpt_caps( $cpt ) );
    }
    $caps[] = 'view_mms_reports';
    $caps[] = 'manage_mms_settings';
    return array_unique( $caps );
}

/**
 * Converts a simple array of caps into an associative array for role creation.
 * @param array $caps_array A simple array of capability strings.
 * @return array
 */
function wp_mms_format_caps_for_role( $caps_array ) {
    return array_fill_keys( $caps_array, true );
}

/**
 * Add custom roles and capabilities on plugin activation.
 */
function wp_mms_add_roles_and_caps() {
    // Grant all MMS caps to Administrator
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        foreach ( wp_mms_get_all_capabilities() as $cap ) {
            $admin_role->add_cap( $cap );
        }
    }

    // Add MMS Manager Role
    add_role( 'mms_manager', __( 'MMS Manager', 'wp-mms' ), wp_mms_format_caps_for_role( wp_mms_get_all_capabilities() ) );

    // Add Purchasing Officer Role
    $purchasing_caps = array_merge(
        wp_mms_get_cpt_caps( 'mms_supplier' ),
        wp_mms_get_cpt_caps( 'mms_purchase_order' ),
        wp_mms_get_cpt_caps( 'mms_requisition' ),
        wp_mms_get_cpt_read_caps( 'mms_product' ),
        ['view_mms_reports', 'read']
    );
    add_role( 'purchasing_officer', __( 'Purchasing Officer', 'wp-mms' ), wp_mms_format_caps_for_role( $purchasing_caps ) );

    // Add Inventory Controller Role
    $inventory_caps = array_merge(
        wp_mms_get_cpt_caps( 'mms_product' ),
        wp_mms_get_cpt_read_caps( 'mms_purchase_order' ),
        wp_mms_get_cpt_caps( 'mms_requisition' ),
        ['view_mms_reports', 'read']
    );
    add_role( 'inventory_controller', __( 'Inventory Controller', 'wp-mms' ), wp_mms_format_caps_for_role( $inventory_caps ) );

    // Add Production Planner Role
    $planner_caps = array_merge(
        wp_mms_get_cpt_caps( 'mms_production_order' ),
        wp_mms_get_cpt_read_caps( 'mms_bom' ),
        wp_mms_get_cpt_read_caps( 'mms_product' ),
        wp_mms_get_cpt_caps( 'mms_requisition' ),
        ['view_mms_reports', 'read']
    );
    add_role( 'production_planner', __( 'Production Planner', 'wp-mms' ), wp_mms_format_caps_for_role( $planner_caps ) );

    // Add BOM Engineer Role
    $bom_caps = array_merge(
        wp_mms_get_cpt_caps( 'mms_bom' ),
        wp_mms_get_cpt_caps( 'mms_product' ),
        wp_mms_get_cpt_caps( 'mms_requisition' ),
        ['read']
    );
    add_role( 'bom_engineer', __( 'BOM Engineer', 'wp-mms' ), wp_mms_format_caps_for_role( $bom_caps ) );

    // Add Operator Role
    $operator_caps = array_merge(
        wp_mms_get_cpt_read_caps( 'mms_production_order' ),
        wp_mms_get_cpt_caps( 'mms_requisition' ),
        ['read']
    );
    add_role( 'mms_operator', __( 'MMS Operator', 'wp-mms' ), wp_mms_format_caps_for_role( $operator_caps ) );

    // Add Viewer Role
    add_role( 'mms_viewer', __( 'MMS Viewer', 'wp-mms' ), ['read' => true, 'view_mms_reports' => true] );
}

/**
 * Remove custom roles and capabilities on plugin deactivation.
 */
function wp_mms_remove_roles() {
    $roles_to_remove = ['mms_manager', 'purchasing_officer', 'inventory_controller', 'production_planner', 'bom_engineer', 'mms_operator', 'mms_viewer'];
    foreach ( $roles_to_remove as $role ) {
        remove_role( $role );
    }

    // Remove all caps from Administrator
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        foreach ( wp_mms_get_all_capabilities() as $cap ) {
            $admin_role->remove_cap( $cap );
        }
    }
}