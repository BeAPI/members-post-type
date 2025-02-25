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
		if( isset($_GET['mpt-action']) && $_GET['mpt-action'] == 'force-change-password' ) {
			parent::set_message( 'force_change_password', __('Your password has expired. You must change before continuing your visit.', 'mpt') );
		}

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
			$nonce = isset($_POST['_mptnonce']) ? $_POST['_mptnonce'] : '';
			if ( !mpt_verify_nonce($nonce, 'mptchangepwd') ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}

			// Check if passwords are the same
			if ( $_POST['mptchangepwd']['new'] != $_POST['mptchangepwd']['new_confirmation'] ) {
				parent::set_message( 'new_confirmation', __( 'The two passwords you entered don\'t match.', 'mpt' ), 'error' );
				return false;
			}

			// Get current member info
			$current_member = MPT_Member_Auth::get_current_member();

			$user_login = ( mpt_is_allowed_email_signon() ) ? $current_member->email : $current_member->username;

			// Re-sign-on, once password reset
			$result = MPT_Member_Auth::authenticate( $user_login, $_POST['mptchangepwd']['old'] );

			// result sign-on are error ?
			if ( is_wp_error($result) ) {
				parent::set_message( 'old_incorrect', __('You old password is incorrect.', 'mpt'), 'error' );
				return false;
			}

			// Set new password
			$result = $current_member->set_password($_POST['mptchangepwd']['new']);
			if ( $result !== true ) {
				if ( is_wp_error($result) ) {
					parent::set_message( $result->get_error_code(), $result->get_error_message(), 'error' );
				} elseif( is_array($result) ) {
					foreach ( $result as $_result ) {
						if ( is_wp_error($_result) ) {
							parent::set_message( $_result->get_error_code(), $_result->get_error_message(), 'error' );
						}
					}
				}

				// Have messages ? If empty, set generic error password
				$messages = parent::get_messages( 'raw' );
				if ( empty($messages) ) {
					parent::set_message( 'change_password_generic_error', __('An error occurred, password has not been changed.', 'mpt'), 'error' );
				}

				return true;
			}

			// Force logout
			MPT_Member_Auth::logout();

			// Re sign-on on real time for not broken member session
			$signon = MPT_Member_Auth::signon( array(
				'm_login' => $user_login,
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
