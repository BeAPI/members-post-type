<?php
class MPT_Admin_Post_Type {
	public function __construct() {
		add_action( 'admin_head', array(__CLASS__, 'admin_head') );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_post' ) );
	}

	public static function admin_head() {
		echo '<style type="text/css" media="screen">';
			echo '#menu-posts-<?php echo MPT_CPT_NAME; ?> .wp-menu-image {background: transparent url(<?php echo MPT_URL;?>/assets/images/toilet.png) no-repeat 6px -17px !important;}';
			echo '#menu-posts-<?php echo MPT_CPT_NAME; ?>:hover .wp-menu-image, #menu-posts-<?php echo MPT_CPT_NAME; ?>.wp-has-current-submenu .wp-menu-image {background-position:6px 7px!important;}';
			echo '.icon32-posts-<?php echo MPT_CPT_NAME; ?> {background: transparent url(<?php echo MPT_URL;?>/assets/images/toilet.png) no-repeat 6px 12px !important;}';
		echo '</style>';
	}

	public static function add_meta_boxes( ) {
		add_meta_box( MPT_CPT_NAME.'-main-data', __('Main data', 'mpt') , array( __CLASS__, 'metabox' ), MPT_CPT_NAME, 'advanced', 'high' );
	}

	public static function metabox( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-main-data' );
	}

	public static function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		
		if ( !isset( $_POST[MPT_CPT_NAME.'-main-data'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-main-data'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}

		if ( !current_user_can( 'edit_post', $post_id ) ) { // TODO : Use custom capabilities
			return false;
		}

		// Sanitize user inputs
		//$mydata = sanitize_text_field( $_POST['myplugin_new_field'] );
		//$mydata = sanitize_text_field( $_POST['myplugin_new_field'] );
		//$mydata = sanitize_text_field( $_POST['myplugin_new_field'] );

		return true;
	}
}