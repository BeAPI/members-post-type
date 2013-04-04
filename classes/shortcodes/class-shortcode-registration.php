<?php
class MPT_Shortcode_Registration extends MPT_Shortcode {
	public static $form_fields = array(
		'username' => '',
		'first_name' => '',
		'last_name' => '',
		'email' => '',
		'password' => '',
		'password_repeat' => ''
	);
	
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-registration' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return '<!-- Members already logged-in. -->';
		}
		
		// Get data from POST, cleanup it
		$member_data = ( !isset($_POST['mptregistration']) ) ? array() : $_POST['mptregistration'];
		
		// Parse vs defaults
		$member_data = wp_parse_args( $member_data, self::$form_fields );
		
		return parent::load_template( 'member-registration', $member_data );
	}

	/**
	 * Check POST data for creation member. Need for set_cookie function.
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
				} elseif( strlen($mptr['password']) < 6 ) { // TODO: Hooks and function for test password security
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
			if ( mpt_is_unique_email() && mpt_email_exists($mptr['email']) ) {
				parent::set_message( 'email', __('This email address is already taken', 'mpt'), 'error' );
			}
			
			do_action( 'mpt_check_user_subscription' );
			
			// Have messages ?
			$messages = parent::get_messages( 'raw' );
			
			// All is fine ? start insertion
			if ( empty($messages) ) {
				
				// Default member insert args 
				$args = array();
				$args['password'] 	= $mptr['password'];
				$args['username'] 	= sanitize_text_field($mptr['username']);
				$args['email'] 		= sanitize_email( $mptr['email'] );
				$args['first_name'] = sanitize_text_field($mptr['first_name']);
				$args['last_name'] 	= sanitize_text_field($mptr['last_name']);
				
				// insert member
				$member_id = MPT_Member_Utility::insert_member( $args );
				
				// An wp error ?
				if ( is_wp_error($member_id) ) {
					parent::set_message( $member_id->get_error_code(), $member_id->get_error_message(), 'error' );
					return false;
				}

				// Send member notification
				$member = new MPT_Member($member_id);
				$member->register_notification( $args['password'] );

				// Flush POST
				unset($_POST['mptregistration']);
				
				// Set success message
				parent::set_message( 'mptregistration', __( 'Your account has beed created. You can now log-in with your access', 'mpt' ), 'success' );
			}
		}
		
		return true;
	}
}
