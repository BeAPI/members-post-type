<?php
class MPT_Shortcode_Registration extends MPT_Shortcode {
	public static $form_fields = array(
		'username' => '',
		'first_name' => '',
		'last_name'  => '',
		'email'  => '',
		'password'  => '',
		'password_repeat'  => ''
	);
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-registration' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	public static function shortcode() {
		// User logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return '<!-- Members already logged-in. -->';
		}
		
		// Get data from POST, cleanup it
		$user_data = ( !isset($_POST['mptregistration']) ) ? array() : $_POST['mptregistration'];
		
		// Parse vs defaults
		$user_data = wp_parse_args( $user_data, self::$form_fields );
		
		return parent::load_template( 'member-registration', $user_data );
	}

	/**
	 * Check POST data for creation user. Need for set_cookie function.
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function init() {
		if ( isset($_POST['mptregistration']) ) {
			// Cleanup data
			$mptr = $_POST['mptregistration'] = stripslashes_deep($_POST['mptregistration']);
			
			// Parse vs default
			$mptr = wp_parse_args( $mptr, self::$form_fields );
			
			// Check _NONCE
			$nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
			if ( !wp_verify_nonce($nonce, 'mptregistration') ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}
			
			// Check password, confirmation, complexity
			if ( !empty($mptr['password']) && !empty($mptr['password_repeat']) ) {
				if ( $mptr['password'] != $mptr['password_repeat'] ) { // password is the same ?
					parent::set_message( 'password_repeat', __('The two password you filled doesn\'t match', 'mpt'), 'error' );
				} elseif( strlen($mptr['password']) < 6 ) {
					parent::set_message( 'password', __('You password need to be at least 6 characters long', 'mpt'), 'error' );
				}
			} else {
				parent::set_message( 'password', __('You need to fill the two password fields', 'mpt'), 'error' );
			}
			
			// Email valid ?
			if ( !is_email($mptr['email']) ) {
				parent::set_message( 'email', __('You need to enter a valid email address', 'mpt'), 'error' );
			}
			
			// Email exists
			if ( mpt_email_exists($mptr['email']) ) {
				parent::set_message( 'email', __('This email address is already taken', 'mpt'), 'error' );
			}
			
			// Have messages ?
			$messages = parent::get_messages( 'raw' );
			
			// All is fine ? start insertion
			if ( empty($messages) ) {
				
				// Default user insert args 
				$args = array();
				$args['password'] 	= $mptr['password'];
				$args['username'] 	= sanitize_text_field($mptr['username']);
				$args['email'] 		= sanitize_email( $mptr['email'] );
				$args['first_name'] = sanitize_text_field($mptr['first_name']);
				$args['last_name'] 	= sanitize_text_field($mptr['last_name']);
				
				// insert member
				$user_id = MPT_User_Utility::insert_user( $args );
				
				// An wp error ?
				if ( is_wp_error($user_id) ) {
					parent::set_message( $user_id->get_error_code(), $user_id->get_error_message(), 'error' );
					return false;
				}

				// Send user notification
				$user = new MPT_User($user_id);
				$user->new_user_notification( $args['password'] );

				// Flush POST
				unset($_POST['mptregistration']);
				
				// Set success message
				parent::set_message( 'mptregistration', __( 'Your account has beed created. You can now log-in with your access', 'mpt' ), 'success' );
			}
		}
		
		return true;
	}
}