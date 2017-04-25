<?php
class MPT_Shortcode_Login extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-login' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init' , array( __CLASS__, 'init' ), 12 );
	}
	
	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return apply_filters( 'mpt_shortcode_login_member_logged_in', '<!-- Members already logged-in. -->', mpt_get_current_member() );
		}
		
		// Get data from POST, cleanup it
		$member_data = ( !isset($_POST['mptlogin']) ) ? array() : $_POST['mptlogin'];
		
		// Parse vs defaults
		$member_data = wp_parse_args( $member_data, array('username' => '', 'rememberme' => '', 'redirect_to' => '', 'rememberme' => false) );
		
		// If no redirect on POST, try to get it on $_GET
		if ( isset($_GET['redirect_to']) && !empty($_GET['redirect_to']) ) {
			$member_data['redirect_to'] = stripslashes($_GET['redirect_to']);
		}
		
		return parent::load_template( 'member-login', $member_data );
	}

	/**
	 * Test if the members try to login
	 */
	public static function init() {
		if ( isset($_POST['mptlogin']) ) {
			// Cleanup data
			$_POST['mptlogin'] = stripslashes_deep($_POST['mptlogin']);
			
			// Check _NONCE
			$nonce = isset($_POST['_mptnonce']) ? $_POST['_mptnonce'] : '';
			if ( !mpt_verify_nonce($nonce, 'mptlogin') ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}
			
			// Parse vs defaults
			$_POST['mptlogin'] = wp_parse_args( $_POST['mptlogin'], array('username' => '', 'rememberme' => '', 'redirect_to' => '', 'rememberme' => false) );
			
			// Try sign-on
			$signon = MPT_Member_Auth::signon( array(
				'm_login' => $_POST['mptlogin']['username'], 
				'm_password' => $_POST['mptlogin']['password'],
				'remember' => $_POST['mptlogin']['rememberme']
			) );
			
			// result sign-on are error ?
			if ( is_wp_error($signon) ) {
				parent::set_message( $signon->get_error_code(), $signon->get_error_message(), 'error' );
				return false;
			}
			
			// Failback redirect to home...
			$redirect_to = (isset($_POST['mptlogin']['redirect_to']) && !empty($_POST['mptlogin']['redirect_to'])) ? $_POST['mptlogin']['redirect_to'] : home_url('/');
			
			// Need to look at the URL the way it will end up in wp_redirect()
			$redirect_to = wp_sanitize_redirect($redirect_to);
			$redirect_to = wp_validate_redirect($redirect_to, home_url('/'));

			wp_redirect( apply_filters( 'mpt_login_redirect', $redirect_to ) );
			exit();
		}
		
		return false;
	}
}
