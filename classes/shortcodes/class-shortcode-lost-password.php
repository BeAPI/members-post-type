<?php
class MPT_Shortcode_Lost_Password extends MPT_Shortcode {
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-lost-password', array(__CLASS__, 'shortcode') );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	public static function shortcode() {
		if ( isset( $_GET['action'] ) && $_GET['action'] =='lost-password' ) {
			return parent::load_template( 'member-lost-password-step-2' );
		} else {
			// Get user_login
			$user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
			
			// Default message
			parent::set_message( 'info', __('Please enter your username or email address. You will receive a link to create a new password via email.'), 'notice' );
			
			return parent::load_template( 'member-lost-password-step-1' );
		}
	}
	
	public static function init() {
		self::check_step_1();
		self::check_regeneration_link();
		self::check_step_2();
	}
	
	
	/**
	 * lostpassword form action
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	public static function redirect_default_password() {
		
		$password_url = get_permalink( ); // TODO
		if ( isset($_POST['redirect_to']) ) {
			$password_url = add_query_arg( array('redirect_to' => $_POST['redirect_to']), $password_url );
		}

		wp_redirect($password_url);
		exit();
	}
	
	/**
	 * Redirect with key and login form action
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function redirect_default_password_check_key() {
		
		$password_url = get_permalink(  ); // TODO
		
		// If there is the key, add it
		if ( isset($_GET['key']) ) {
			$password_url = add_query_arg( array('key' => $_GET['key'] ), $password_url );
		}
		
		// If there is the login, add it
		if ( isset($_GET['login']) ) {
			$password_url = add_query_arg( array('login' => urlencode( $_GET['login'] ) ), $password_url );
		}
		
		// Redirect to right page
		wp_redirect( $password_url );
		exit();
	}
	
	/**
	 * Check POST data for email
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	public static function check_step_1() {
		// Check if the user is reseting
		if ( !isset( $_POST['forgot_password'] ) || (int) $_POST['forgot_password'] != 1 ) {
			return false;
		}
		
		// Check if the email field is filled
		if ( !isset( $_POST['mpt_user_email'] ) || empty( $_POST['mpt_user_email'] ) || !is_email( $_POST['mpt_user_email'] ) ) {
			parent::set_message( 'email_invalid', __( "You need to enter a valid email address", 'mpt' ), 'error' );
		}
		
		// che if login given
		$errors = self::retrieve_password();
		
		// Check if errors
		if( is_wp_error( $errors ) ) {
			parent::set_message( $errors->get_error_code(), $errors->get_error_message(), 'error' );
			return false;
		}
		
		// Display the message
		parent::set_message( 'check_step_1', __( "You are going to receive an email with a reset link.", 'mpt' ), 'success' );
	}
	
	/**
	 * Check if the user clicked on the regenerate link
	 * 
	 * @author Benjamin Niess
	 */
	public static function check_regeneration_link() {
		
		if ( !isset( $_GET['action'] ) || $_GET['action'] != 'regenerate_password' ) {
			return false;
		} 
		
		if ( !isset( $_GET['login'] ) || !isset( $_GET['key'] ) || empty( $_GET['login'] ) || empty( $_GET['key'] ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		} 
		
		$user = self::check_password_reset_key($_GET['key'], $_GET['login']);
		if( is_wp_error( $user ) ) {
			wp_die( $user->get_error_message() );
		}
	}
	
	public static function check_step_2() {
		if( isset($_POST['mpt_user_password']) && isset( $_POST['mpt_user_password_confirm'] ) ){
			
			// Check if pass 1 and pass 2 are the same
			if( $_POST['mpt_user_password'] != $_POST['mpt_user_password_confirm'] ) {
				$message = __( "The two passwords you entered don't match", 'mpt' );
				$status = 'error';
				return false;
				
			} elseif( isset( $_POST['mpt_user_password'] ) && !empty( $_POST['mpt_user_password_confirm'] ) ) {
				// Check if the key and login are right and get the user
				$user = self::check_password_reset_key( $_GET['key'], $_GET['login'] );
				
				// reset the user password
				self::reset_password( $user, $_POST['mpt_user_password'] );
				
				// Add the message
				$message = __( "Your password has been changed.", 'mpt' );
				$status = 'success';
			}
		}
	}
}