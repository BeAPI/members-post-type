<?php
class MPT_Admin_Content_Permissions {
    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		$current_options = MPT_Options::get_option( 'mpt-main' );
		if ( $current_options == false ) {
			return false;
		}
		
		if ( isset($current_options['features']) && isset($current_options['features']['content-permissions']) ) {
			// Register metabox
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 10, 1 );
			
			/* Saves the content permissions metabox data to a custom field. */
			add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
			add_action( 'add_attachment', array( __CLASS__, 'save_meta' ), 10, 2 );
			add_action( 'edit_attachment', array( __CLASS__, 'save_meta' ), 10, 2 );
		}
	}
	
    /**
     * add_meta_boxes
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function add_meta_boxes( $post_type = '' ) {
		// Remove this box for MPT post type
		if ( $post_type == MPT_CPT_NAME ) {
			return false;
		}

		if ( apply_filters( 'mpt_disabled_content_permissions_meta_boxes', false, $post_type ) ) {
			return false;
		}
		
		// TODO : Add this caps
		/* Only add the meta box if the current user has the 'mpt_restrict_content' capability. */
		//if ( current_user_can( 'mpt_restrict_content' ) ) {
		
		add_meta_box( MPT_CPT_NAME.'-content-permissions', __('Content permissions (for MPT)', 'mpt'), array( __CLASS__, 'metabox' ), $post_type, 'normal', 'high' );
		return true;
	}

	/**
	 * Adds the content permissions meta box to the 'add_meta_boxes' hook.
	 * 
	 * @param WP_Post $post
	 */
	public static function metabox( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-content-permissions' );

		// Get values from DB
		$current_roles = get_post_meta( $post->ID, '_mpt_role', true );
		$current_message = get_post_meta( $post->ID, '_mpt_access_error', true );
		
		// Get MPT roles availables
		$mpt_roles = MPT_Roles::get_roles();
		
		// Show error messages
		settings_errors( MPT_CPT_NAME.'-content-permissions' );

		// Call Template
		include( MPT_DIR . 'views/admin/metabox-content-permissions.php');
	}
	
	/**
	 * Saves the content permissions metabox data to a custom field.
	 * 
	 * @param int $post_id
	 * @param WP_Post|mixed $post
	 * @return boolean
	 */
	public static function save_meta( $post_id, $post = '' ) {
		/* Fix for attachment save issue in WordPress 3.5. @link http://core.trac.wordpress.org/ticket/21963 */
		if ( !is_object( $post ) ) {
			$post = get_post();
		}
	
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
		if ( defined('DOING_AJAX') && DOING_AJAX ) return false;
		if ( defined('DOING_CRON') && DOING_CRON ) return false;

		if ( !isset( $_POST[MPT_CPT_NAME.'-content-permissions'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-content-permissions'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}
		
		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return false;
		}
		
		/* Don't save if the post is only a revision. */
		if ( 'revision' == $post->post_type ) {
			return false;
		}

		// Save checked role
		if ( isset($_POST['mpt_role']) ) {
			$_POST['mpt_role'] = array_map('intval', $_POST['mpt_role']);
			
			update_post_meta( $post_id, '_mpt_role', $_POST['mpt_role'] );
		} else {
			delete_post_meta( $post_id, '_mpt_role' );
		}
		
		// Save error message
		if ( isset($_POST['mpt_access_error']) && !empty($_POST['mpt_access_error']) ) {
			update_post_meta( $post_id, '_mpt_access_error', esc_html($_POST['mpt_access_error']) );
		} else {
			delete_post_meta( $post_id, '_mpt_access_error' );
		}
		
		return true;
	}
}
