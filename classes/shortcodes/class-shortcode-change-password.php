<?php
class MPT_Shortcode_Change_Password extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-change-password' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( !mpt_is_member_logged_in() ) {
			return __('You can\'t change your password, if you aren\'t logged-in.', 'mpt');
		}
		
		return parent::load_template( 'member-change-password', array() );
	}

	/**
	 * Check POST data 
	 *
	 * @return boolean
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function init() {
		if ( isset($_POST['mptchangepwd']) ) {
			// Cleanup data
			$_POST['mptchangepwd'] = stripslashes_deep($_POST['mptchangepwd']);
			
			// Check _NONCE
			$nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
			if ( !wp_verify_nonce($nonce, 'mptchangepwd') ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}
			
			// Check if passwords are the same
			if ( $_POST['mptchangepwd']['new'] != $_POST['mptchangepwd']['new_confirmation'] ) {
				parent::set_message( 'new_confirmation', __( 'The two passwords you entered don\'t match.', 'mpt' ), 'error' );
				return false;
			}
			
			// Check password complexity
			if( strlen($_POST['mptchangepwd']['new']) < 6 ) { // TODO: Hooks and function for test password security
				parent::set_message( 'new_security', __('You password need to be at least 6 characters long', 'mpt'), 'error' );
				return false;
			}
			
			// Get current member info
			$current_member = MPT_Member_Auth::get_current_member();
			
			// Re-sign-on, once password reset
			$result = MPT_Member_Auth::authenticate( $current_member->username, $_POST['mptchangepwd']['old'] );
			
			// result sign-on are error ?
			if ( is_wp_error($result) ) {
				parent::set_message( 'old_incorrect', __('You old password is incorrect.', 'mpt'), 'error' );
				return false;
			}
			
			// Set new password
			$current_member->set_password($_POST['mptchangepwd']['new']);
			
			// Force logout
			MPT_Member_Auth::logout();
			
			// Re sign-on on real time for not broken member session
			$signon = MPT_Member_Auth::signon( array(
				'm_login' => $current_member->username, 
				'm_password' => $_POST['mptchangepwd']['new']
			) );
			
			// result sign-on are error ?
			if ( is_wp_error($signon) ) {
				parent::set_message( $signon->get_error_code(), $signon->get_error_message(), 'error' );
				return false;
			}
			
			parent::set_message( 'change_password_success', __('Password updated with success.', 'mpt'), 'updated' );
			return true;
		}
		
		return false;
	}
}