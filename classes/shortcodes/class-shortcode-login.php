<?php
class MPT_Shortcode_Login extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-login' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init' , array( __CLASS__, 'init' ) );
	}
	
	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// User logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return '<!-- Members already logged-in. -->';
		}
		
		// Get data from POST, cleanup it
		$user_data = ( !isset($_POST['mptlogin']) ) ? array() : $_POST['mptlogin'];
		
		// Parse vs defaults
		$user_data = wp_parse_args( $user_data, array('username' => '', 'rememberme' => '') );
		
		return parent::load_template( 'member-login', $user_data );
	}

	/**
	 * Test if the users try to login
	 */
	public static function init() {
		if ( isset($_POST['mptlogin']) ) {
			// Cleanup data
			$_POST['mptlogin'] = stripslashes_deep($_POST['mptlogin']);
			
			// Check _NONCE
			$nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
			if ( !wp_verify_nonce($nonce, 'mptlogin') ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}
			
			// Try sign-on
			$signon = MPT_User_Auth::signon( array(
				'user_login' => $_POST['mptlogin']['username'], 
				'user_password' => $_POST['mptlogin']['password']
			) );
			
			// result sign-on are error ?
			if ( is_wp_error($signon) ) {
				parent::set_message( $signon->get_error_code(), $signon->get_error_message(), 'error' );
				return false;
			}
			
			// Failback redirect to home...
			$redirect_to = isset($_POST['mptlogin']['redirect_to']) ? $_POST['mptlogin']['redirect_to'] : home_url('/');
			
			wp_safe_redirect( $redirect_to );
			exit();
		}
		
		return false;
	}
}