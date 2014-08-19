<?php
class MPT_Admin_Welcome_Message{
	
	function __construct(){
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 10, 1 );
		add_action( 'mpt_send_mail_to_member' , array( __CLASS__, 'mpt_send_mail_to_member' ), 10, 1 );
	}
	
	/**
     * add_meta_boxes
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function add_meta_boxes() {

		$page = get_current_screen();

		if ( $page->action != 'add' ){
			return false;
		}
		if ( $_GET['post_type'] != MPT_CPT_NAME ){
			return false;
		}
		add_meta_box( MPT_CPT_NAME.'-welcome-message', __( 'Welcome Message (for MPT)', 'mpt' ), array( __CLASS__, 'metabox' ), MPT_CPT_NAME, 'normal', 'high' );
		return true;
	}
	
	/**
	 * Adds the content welcome message meta box to the 'add_meta_boxes' hook.
	 * 
	 * @param WP_Post $post
	 */
	public static function metabox( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-welcome-message' );
		
		// Call Template
		include( MPT_DIR . 'views/admin/metabox-welcome-message.php' );
	}
	
	public static function mpt_send_mail_to_member( $postid ) {
		
		if( empty( $_REQUEST['welcome-message'] ) || $_REQUEST['welcome-message'] != 'yes' ){
			return false;
		}

		if ( ( empty($_REQUEST['memberpwd']['password'] ) || empty($_REQUEST['memberpwd']['confirm_password'] ) || $_REQUEST['memberpwd']['password'] != $_REQUEST['memberpwd']['confirm_password'] ) && empty( $_REQUEST['memberpwd']['password-generate'] ) ) {
			return false;
		}
		if( !empty( $_REQUEST['memberpwd']['password'] ) ){
			$password = $_REQUEST['memberpwd']['password'];
		}elseif( !empty( $_REQUEST['memberpwd']['password-generate'] ) ){
			$password = $_REQUEST['memberpwd']['password-generate'];
		}
		
		
		$member = new MPT_Member( $postid );
		
		$member->register_notification( $password );
	}
}
