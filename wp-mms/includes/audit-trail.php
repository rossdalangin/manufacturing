<?php
/**
 * Audit Trail Functions
 *
 * @package WP_MMS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Logs a user action to the audit trail.
 *
 * This is the central function for creating audit log entries. It abstracts
 * the creation of the custom post type entry and stores structured data
 * in post meta fields for easy querying and display.
 *
 * @param string $action      A short, computer-readable key for the action (e.g., 'product_created', 'po_status_changed').
 * @param array  $details     An array of details about the event.
 *   @type int    $object_id   The ID of the post or object that was affected.
 *   @type string $object_type The type of object (e.g., 'Product', 'Purchase Order').
 *   @type mixed  $old_value   The value before the change.
 *   @type mixed  $new_value   The value after the change.
 *   @type string $description A human-readable description of the event.
 */
function wp_mms_log_action( $action, $details = [] ) {
    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );
    $user_display_name = $user ? $user->display_name : __( 'System', 'wp-mms' );

    // Sanitize and set defaults for the details array.
    $details = wp_parse_args( $details, [
        'object_id'   => 0,
        'object_type' => '',
        'old_value'   => '',
        'new_value'   => '',
        'description' => '',
    ] );

    // Create a descriptive title for the log entry.
    $title = sprintf(
        // Translators: 1: User name, 2: Action description, 3: Object type, 4: Object ID
        __( '%1$s %2$s %3$s #%4$s', 'wp-mms' ),
        $user_display_name,
        esc_html( $details['description'] ),
        esc_html( $details['object_type'] ),
        esc_html( $details['object_id'] )
    );

    // Create the post array for the new log entry.
    $log_entry = [
        'post_title'   => $title,
        'post_content' => '', // Content can be used for more detailed, unstructured notes if needed.
        'post_status'  => 'publish',
        'post_author'  => $user_id,
        'post_type'    => 'wp_mms_audit_log',
    ];

    // Insert the post into the database.
    $log_id = wp_insert_post( $log_entry );

    // If the post was created successfully, add the structured data as meta fields.
    if ( $log_id && ! is_wp_error( $log_id ) ) {
        update_post_meta( $log_id, '_wp_mms_action', $action );
        update_post_meta( $log_id, '_wp_mms_object_id', absint( $details['object_id'] ) );
        update_post_meta( $log_id, '_wp_mms_object_type', sanitize_text_field( $details['object_type'] ) );

        // Don't store giant objects, just serialize if it's an array/object.
        $old_val = is_scalar( $details['old_value'] ) ? $details['old_value'] : maybe_serialize( $details['old_value'] );
        $new_val = is_scalar( $details['new_value'] ) ? $details['new_value'] : maybe_serialize( $details['new_value'] );

        update_post_meta( $log_id, '_wp_mms_old_value', $old_val );
        update_post_meta( $log_id, '_wp_mms_new_value', $new_val );
    }
}

// Load the WP_List_Table class if it's not already loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * MMS_Audit_Log_List_Table class.
 *
 * Renders the list table for the audit trail.
 */
class MMS_Audit_Log_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Audit Log', 'wp-mms' ),
            'plural'   => __( 'Audit Logs', 'wp-mms' ),
            'ajax'     => false,
        ] );
    }

    /**
     * Get the list of columns.
     *
     * @return array
     */
    public function get_columns() {
        return [
            'date'    => __( 'Date', 'wp-mms' ),
            'user'    => __( 'User', 'wp-mms' ),
            'action'  => __( 'Action', 'wp-mms' ),
            'object'  => __( 'Object', 'wp-mms' ),
            'changes' => __( 'Changes', 'wp-mms' ),
        ];
    }

    /**
     * Get the list of sortable columns.
     *
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'date' => [ 'date', true ], // True for default sort descending.
        ];
    }

    /**
     * Prepare the items for the table.
     */
    public function prepare_items() {
        $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

        $per_page     = $this->get_items_per_page( 'audit_logs_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = self::get_log_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );

        $this->items = self::get_logs( $per_page, $current_page );
    }

    /**
     * Get the total number of log items.
     *
     * @return int
     */
    public static function get_log_count() {
        $counts = wp_count_posts( 'wp_mms_audit_log' );
        return $counts->publish;
    }

    /**
     * Get the log items from the database.
     *
     * @param int $per_page
     * @param int $page_number
     * @return array
     */
    public static function get_logs( $per_page = 20, $page_number = 1 ) {
        $args = [
            'post_type'      => 'wp_mms_audit_log',
            'posts_per_page' => $per_page,
            'paged'          => $page_number,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
        $query = new WP_Query( $args );
        return $query->posts;
    }

    /**
     * Render the default column.
     *
     * @param object $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        return get_post_meta( $item->ID, '_wp_mms_' . $column_name, true );
    }

    /**
     * Render the date column.
     *
     * @param object $item
     * @return string
     */
    public function column_date( $item ) {
        return get_the_date( 'Y/m/d H:i:s', $item );
    }

    /**
     * Render the user column.
     *
     * @param object $item
     * @return string
     */
    public function column_user( $item ) {
        $author_id = $item->post_author;
        $user = get_userdata( $author_id );
        return $user ? $user->display_name : __( 'Unknown', 'wp-mms' );
    }

    /**
     * Render the action column.
     *
     * @param object $item
     * @return string
     */
    public function column_action( $item ) {
        return esc_html( $item->post_title );
    }

    /**
     * Render the object column.
     *
     * @param object $item
     * @return string
     */
    public function column_object( $item ) {
        $object_id = get_post_meta( $item->ID, '_wp_mms_object_id', true );
        $object_type = get_post_meta( $item->ID, '_wp_mms_object_type', true );
        $link = get_edit_post_link( $object_id );
        if ($link) {
            return sprintf('<a href="%s">%s #%d</a>', esc_url($link), esc_html($object_type), esc_html($object_id));
        }
        return sprintf('%s #%d', esc_html($object_type), esc_html($object_id));
    }

    /**
     * Render the changes column.
     *
     * @param object $item
     * @return string
     */
    public function column_changes( $item ) {
        $old_value = get_post_meta( $item->ID, '_wp_mms_old_value', true );
        $new_value = get_post_meta( $item->ID, '_wp_mms_new_value', true );

        if ( empty($old_value) && empty($new_value) ) {
            return '&mdash;';
        }

        $old_value = maybe_unserialize($old_value);
        $new_value = maybe_unserialize($new_value);

        if ( is_array($old_value) || is_array($new_value) ) {
            return '<pre>' . esc_html( wp_json_encode( [ 'from' => $old_value, 'to' => $new_value ], JSON_PRETTY_PRINT ) ) . '</pre>';
        }

        return sprintf(
            '<strong>%s:</strong> %s<br><strong>%s:</strong> %s',
            __( 'From', 'wp-mms' ),
            esc_html( $old_value ),
            __( 'To', 'wp-mms' ),
            esc_html( $new_value )
        );
    }
}

/**
 * Logs a user's successful login.
 *
 * @param string  $user_login The user's login name.
 * @param WP_User $user       The WP_User object.
 */
function wp_mms_log_user_login( $user_login, $user ) {
    wp_mms_log_action( 'user_login', [
        'object_id'   => $user->ID,
        'object_type' => 'User',
        'description' => 'logged in',
    ]);
}
add_action( 'wp_login', 'wp_mms_log_user_login', 10, 2 );

/**
 * Logs a user's logout.
 */
function wp_mms_log_user_logout() {
    $user = wp_get_current_user();
    if ( $user && $user->ID > 0 ) {
        wp_mms_log_action( 'user_logout', [
            'object_id'   => $user->ID,
            'object_type' => 'User',
            'description' => 'logged out',
        ]);
    }
}
add_action( 'wp_logout', 'wp_mms_log_user_logout' );

/**
 * Logs a failed login attempt.
 *
 * @param string $username The username attempted.
 */
function wp_mms_log_failed_login( $username ) {
    wp_mms_log_action( 'user_login_failed', [
        'object_type' => 'System',
        'description' => 'Failed login attempt',
        'new_value'   => $username,
    ]);
}
add_action( 'wp_login_failed', 'wp_mms_log_failed_login' );

/**
 * Logs changes to a user's profile.
 *
 * @param int     $user_id       The ID of the user being updated.
 * @param WP_User $old_user_data The old user data.
 */
function wp_mms_log_profile_update( $user_id, $old_user_data ) {
    wp_mms_log_action( 'user_profile_updated', [
        'object_id'   => $user_id,
        'object_type' => 'User Profile',
        'description' => 'updated profile',
    ]);
}
add_action( 'profile_update', 'wp_mms_log_profile_update', 10, 2 );

/**
 * Logs changes to a user's role.
 *
 * @param int    $user_id The user ID.
 * @param string $role    The new role.
 * @param array  $old_roles An array of the user's old roles.
 */
function wp_mms_log_user_role_change( $user_id, $role, $old_roles ) {
    $old_role = ! empty( $old_roles ) ? implode( ', ', $old_roles ) : 'none';
    wp_mms_log_action( 'user_role_changed', [
        'object_id'   => $user_id,
        'object_type' => 'User Role',
        'description' => 'changed user role',
        'old_value'   => $old_role,
        'new_value'   => $role,
    ]);
}
add_action( 'set_user_role', 'wp_mms_log_user_role_change', 10, 3 );