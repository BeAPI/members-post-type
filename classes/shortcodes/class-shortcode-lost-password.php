<?php
class MPT_Shortcode_Lost_Password extends MPT_Shortcode {

	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-lost-password', array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init' ), 12 );
		add_action( 'template_redirect' , array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return '<!-- Members logged-in, impossible to reset password. -->';
		}

		if ( isset( $_GET['mpt-action'] ) && $_GET['mpt-action'] == 'lost-password' ) {
			return parent::load_template( 'member-lost-password-step-2' );
		} else {
			// Default message
			if ( !isset( $_POST ) ) {
				parent::set_message( 'info', __( 'Please enter your username or email address. You will receive a link to create a new password via email.' ), 'notice' );
			}

			if ( isset( $_GET['update'] ) && $_GET['update'] === '1' ) {
				parent::set_message( 'step_1_sucess', __( "You are going to receive an email with a reset link.", 'mpt' ), 'success' );
			}

			return parent::load_template( 'member-lost-password-step-1' );
		}
	}

	public static function init() {
		// Ask link reset
		self::check_step_1();

		// Check link reset and form new password
		self::check_step_2_url();
		self::check_step_2_form();
	}

	/**
	 * Check POST data for email
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	public static function check_step_1() {
		if ( isset( $_POST['mptlostpwd_s1'] ) ) {
			// Cleanup data
			$_POST['mptlostpwd_s1'] = stripslashes_deep( $_POST['mptlostpwd_s1'] );

			// Check _NONCE
			$nonce = isset( $_POST['_mptnonce'] ) ? $_POST['_mptnonce'] : '';
			if ( !mpt_verify_nonce( $nonce, 'mptlostpwd_s1' ) ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}

			// Empty values ?
			if ( empty( $_POST['mptlostpwd_s1']['username'] ) ) {
				parent::set_message( 'check_step_1', __( 'Invalid username or e-mail.', 'mpt' ), 'error' );
				return false;
			}

			// Try find member
			$member = new MPT_Member( );

			// Test if @
			if ( strpos( $_POST['mptlostpwd_s1']['username'], '@' ) !== false ) {
				$member->fill_by( 'email', $_POST['mptlostpwd_s1']['username'] );
			} else {
				$member->fill_by( 'username', $_POST['mptlostpwd_s1']['username'] );
			}

			// No response for email and username, go out
			if ( !$member->exists() ) {
				parent::set_message( 'step_1_error', __( 'No member with this value.', 'mpt' ), 'error' );
				return false;
			}

			if( $member->is_pending_member() ){
				parent::set_message( 'step_1_pending_member', __( 'You have not verified your account. You can not renew your password.', 'mpt' ), 'error' );
				return false;
			}

			// Send reset link
			$result = $member->reset_password_link();
			if ( is_wp_error( $result ) ) {
				parent::set_message( $result->get_error_code(), $result->get_error_message(), 'error' );
				return false;
			}

			wp_safe_redirect( self::redirect_clear_url() );
			exit;
		}

		return false;
	}

	/**
	 * @return void
	 */
	public static function redirect_clear_url() {
		wp_safe_redirect( add_query_arg( 'update', true, self::get_clean_url() ) );
		exit;
	}

	/**
	 * Get clean URL
	 * @return string
	 */
	public static function get_clean_url() {
		return remove_query_arg(
			[
				'update',
				'mpt-action',
				'_mptnonce',
				'dismiss',
			],
			(string) get_permalink(),
		);
	}


	/**
	 * Check if member click on reset link, verify key/id on DB
	 *
	 * @author Benjamin Niess
	 */
	public static function check_step_2_url() {
		if ( !isset( $_GET['mpt-action'] ) || $_GET['mpt-action'] != 'lost-password' ) {
			return false;
		}

		if ( !isset( $_GET['id'] ) || !isset( $_GET['key'] ) || empty( $_GET['id'] ) || empty( $_GET['key'] ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		}

		// Format key
		$_GET['key'] = preg_replace( '/[^a-z0-9]/i', '', $_GET['key'] );

		// Try load member with this activation_key
		$member = new MPT_Member( );
		$member->fill_by( 'activation_key', $_GET['key'] );
		if ( !$member->exists() || ($member->exists() && $member->id != $_GET['id']) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		}

		return true;
	}

	/**
	 * Check form new password
	 */
	public static function check_step_2_form() {
		if ( isset( $_POST['mptlostpwd_s2'] ) ) {
			// Check _NONCE
			$nonce = isset( $_POST['_mptnonce'] ) ? $_POST['_mptnonce'] : '';
			if ( !mpt_verify_nonce( $nonce, 'mptlostpwd_s2' ) ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}

			// Check if passwords are the same
			if ( $_POST['mptlostpwd_s2']['password'] != $_POST['mptlostpwd_s2']['password_confirmation'] ) {
				parent::set_message( 'check_step_2', __( 'The two passwords you entered don\'t match.', 'mpt' ), 'error' );
				return false;
			}

			// Try load member with this activation_key
			$member = new MPT_Member( );
			$member->fill_by( 'activation_key', $_GET['key'] );
			if ( !$member->exists() || ($member->exists() && $member->id != $_GET['id']) ) {
				wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
			}

			// Set new password
			$result = $member->set_password( $_POST['mptlostpwd_s2']['password'] );
			if ( $result !== true ) {
				if ( is_wp_error( $result ) ) {
					parent::set_message( $result->get_error_code(), $result->get_error_message(), 'error' );
				} elseif ( is_array( $result ) ) {
					foreach ( $result as $_result ) {
						if ( is_wp_error( $_result ) ) {
							parent::set_message( $_result->get_error_code(), $_result->get_error_message(), 'error' );
						}
					}
				}

				// Have messages ? If empty, set generic error password
				$messages = parent::get_messages( 'raw' );
				if ( empty( $messages ) ) {
					parent::set_message( 'lost_password_generic_error', __( 'An error occurred, password has not been changed.', 'mpt' ), 'error' );
				}

				return true;
			}

			// Try to get the login page, otherwise get home link
			$location = wp_validate_redirect( mpt_get_login_permalink(), home_url( '/' ) );

			// Redirect
			wp_redirect( $location );
			exit();
		}

		return false;
	}

	/**
	 * Redirect logged in members to the account page.
	 *
	 * @return void
	 */
	public static function template_redirect() {
		if ( MPT_Main::is_action_page( 'lost-password' ) && mpt_is_member_logged_in() ) {
			$account_link = MPT_Main::get_action_permalink( 'account' );
			if ( ! empty( $account_link ) ) {
				wp_safe_redirect( $account_link, 302, 'mpt' );
				exit;
			}
		}
	}
}
