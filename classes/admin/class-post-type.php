<?php
class MPT_Admin_Post_Type {
	public function __construct() {
		add_action( 'admin_init', array(__CLASS__, 'admin_init') );
		add_action( 'admin_head', array(__CLASS__, 'admin_head') );

		// Metabox member
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_post' ) );
		
		// Register settings tabs
		new MPT_Admin_Settings_Main();
		new MPT_Admin_Settings_Pages();
		new MPT_Admin_Settings_Security();
	}
	
	public static function admin_init() {
		if ( isset($_GET['mpt-message']) && $_GET['mpt-message'] == '1' ) {
			add_settings_error( MPT_CPT_NAME.'-password', MPT_CPT_NAME.'-password', __('Password and confirmation must be the same.', 'mpt'), 'error' );
		}
	}

	public static function admin_head() {
		echo '<style type="text/css" media="screen">';
			echo '#menu-posts-'.MPT_CPT_NAME.' .wp-menu-image {background: transparent url('.MPT_URL.'/assets/images/toilet.png) no-repeat 6px -17px !important;}';
			echo '#menu-posts-'.MPT_CPT_NAME.':hover .wp-menu-image, #menu-posts-'.MPT_CPT_NAME.'.wp-has-current-submenu .wp-menu-image {background-position:6px 7px!important;}';
			echo '.icon32-posts-'.MPT_CPT_NAME.' {background: transparent url('.MPT_URL.'/assets/images/toilet.png) no-repeat 6px 12px !important;}';
		echo '</style>';
	}

	public static function add_meta_boxes( ) {
		add_meta_box( MPT_CPT_NAME.'-main', __('Main information', 'mpt'), array( __CLASS__, 'metabox_main' ), MPT_CPT_NAME, 'normal', 'high' );
		add_meta_box( MPT_CPT_NAME.'-password', __('Change password', 'mpt'), array( __CLASS__, 'metabox_password' ), MPT_CPT_NAME, 'normal', 'high' );
	}

	public static function metabox_main( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-main' );

		// Get values from DB
		$member = array();
		foreach ( MPT_User::$core_fields as $field ) {
			$member[$field] = get_post_meta($post->ID, $field, true);
		}

		// Call Template
		include( MPT_DIR . '/views/admin/metabox-main.php');
	}

	public static function metabox_password( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-password' );
		
		// Show error messages
		settings_errors( MPT_CPT_NAME.'-password' );
		
		// Call Template
		include( MPT_DIR . '/views/admin/metabox-password.php');
	}

	public static function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( !current_user_can( 'edit_post', $post_id ) ) { // TODO : Use custom capabilities
			return false;
		}

		self::save_metabox_main( $post_id );
		self::save_metabox_password( $post_id );
	}

	public static function save_metabox_main( $post_id ) {
		if ( !isset( $_POST[MPT_CPT_NAME.'-main'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-main'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}

		// Instanciate user
		$user = new MPT_User( $post_id );

		// Sanitize user inputs
		foreach ( MPT_User::$core_fields as $field ) {
			if ( !isset($_POST['member'][$field]) ) {
				continue;
			}

			if ( $field == 'email' ) {
				$value = sanitize_email( $_POST['member'][$field] );
			} else {
				$value = sanitize_text_field( $_POST['member'][$field] );
			}
			
			$user->set_meta_value( $field, $value );
		}
		
		// Set proper post_title for WP
		$user->regenerate_post_title();

		return true;
	}

	public static function save_metabox_password( $post_id ) {
		if ( !isset( $_POST[MPT_CPT_NAME.'-password'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-password'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}
		
		$pmp = $_POST['memberpwd'];
		if ( empty($pmp['password']) && empty($pmp['confirm_password']) ) {
			return false;
		}
		
		if ( empty($pmp['password']) || empty($pmp['confirm_password']) || $pmp['password'] != $pmp['confirm_password'] ) {
			// Add param on URL redirect
			add_filter('redirect_post_location', array(__CLASS__, 'redirect_post_location'), 10, 2 );
			return false;
		}

		// Instanciate user
		$user = new MPT_User( $post_id );
		$user->set_password( $_POST['memberpwd']['password'] );
		
		return true;
	}
	
	public static function redirect_post_location( $location, $post_id ) {
		return add_query_arg( 'mpt-message', 1, $location );
	}
}