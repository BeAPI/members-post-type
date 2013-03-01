<?php
class MPT_Admin_Main {
	
	public function __construct( ) {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ));
	}

	public static function admin_enqueue_scripts( $hook ) {
		global $post;
		
		if ( 
			in_array( $hook, array( 'edit.php', 'post-new.php' ) ) && isset( $_GET['post_type'] ) && $_GET['post_type'] == MPT_CPT_NAME ||
			in_array( $hook, array( 'post.php' ) ) && isset($post) && $post->post_type == MPT_CPT_NAME
			) {
			wp_enqueue_style( MPT_CPT_NAME . '-admin', MPT_URL . '/assets/css/admin.css', array( ), MPT_VERSION, 'all' );
		}
	}
	
	public static function admin_menu() {
		add_options_page( __('Member post type Options', 'mpt'), __( 'Member post type', 'mpt' ), 'manage_options', 'member-post-type', array( __CLASS__, 'load_option_page' ) );
		register_setting( 'member-post-type' , "member-post-type", array( __CLASS__, 'sanitize' ) );
		add_settings_section( 'mpt-pages-sc' , __('Pages', 'mpt'), array( __CLASS__, 'pages_settings_text' ), 'member-post-type');
		add_settings_field( 'mpt-pages-sf' , __('Pages', 'mpt'), array( __CLASS__, 'pages_dropdown' ), 'member-post-type');
	}
	
	
	/**
	 * Load the option page template
	 * 
	 * @author Benjamin Niess
	 */
	public static function load_option_page() {
		if ( !file_exists( MPT_DIR . '/views/admin/options-page.php' ) ) {
			return false;
		}
		
		require( MPT_DIR . '/views/admin/options-page.php' );
		exit;
	}
	
	function sanitize( $fields ) {
		var_dump($fields);
		die();
		return $fields;
	}
	
	public static function pages_dropdown() {
		$pages = array( 
			'register_page' => __( 'Registration page', 'mpt' ),
			'login_page' => __( 'Login page', 'mpt' ),
			'forgot_pwd_page' => __( 'Forgot password page', 'mpt' ),
			'reset_pwd_page' => __( 'Reset password page', 'mpt' ),
		 );
		$options = maybe_unserialize( get_option( MPT_OPTION ) );
		$selected_pages = ( isset( $options['selected_pages'] ) && is_array( $options['selected_pages'] ) ) ? $options['selected_pages'] : array(); 
		
		foreach ( $pages as $page_slug => $page_name ) {
			echo '<h3>' . $page_name . '</h3>';
			wp_dropdown_pages( array( 'name' => MPT_OPTION.'[selected_pages][' . $page_slug . ']', 'selected' => ( isset( $selected_pages[$page_slug] ) ) ? esc_attr( $selected_pages[$page_slug] ) : 0 ) );
			
		}
	}
	
	public static function pages_settings_text() {
		_e( 'Please select the WordPress pages for each dropdown list', "mpt" );
	}
}
