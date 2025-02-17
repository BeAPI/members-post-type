<?php

class MPT_Shortcode_Change_Profile extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-change-profile', [ __CLASS__, 'shortcode' ] );
		add_action( 'init', [ __CLASS__, 'check_url_email_change' ], 8 );
		add_action( 'init', [ __CLASS__, 'cancel_email_change' ], 9 );
		add_action( 'init', [ __CLASS__, 'init' ], 9 );
		add_action( 'template_redirect', [ __CLASS__, 'template_redirect' ] );
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( ! mpt_is_member_logged_in() ) {
			return __( 'You need to log in to update your profile.', 'mpt' );
		}

		$update_profile = sanitize_text_field( ( $_GET['update-profile'] ?? '' ) ); //phpcs:ignore
		if ( '1' === $update_profile ) {
			parent::set_message( 'change_profile_success', __( 'Profile updated with success.', 'mpt' ), 'updated' );
		} elseif ( '2' === $update_profile ) {
			parent::set_message( 'change_profile_error', __( 'An error occurred during the update. Please try again later or contact the site administrator.', 'mpt' ), 'failed' );
		}

		$member = mpt_get_current_member();

		return parent::load_template( 'member-change-profile', $member );
	}

	/**
	 * Check if member click on validation link, verify key/email on DB
	 *
	 * @return void
	 * @author Egidio CORICA
	 */
	public static function check_url_email_change() {
		$action = $_GET['mpt-action'] ?? ''; //phpcs:ignore

		if ( 'validate-new-email' !== $action ) {
			return;
		}

		$member_id = (int) ( $_GET['id'] ?? 0 ); //phpcs:ignore
		$mpt_nonce = (string) ( $_GET['_mptnonce'] ?? '' ); //phpcs:ignore

		if ( empty( $member_id ) || empty( $mpt_nonce ) || ! mpt_verify_nonce( $mpt_nonce, 'mpt_update_mail' ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		}

		// Try load member with this activation_key
		$member = new MPT_Member();
		$member->fill_by( 'id', $member_id );
		if ( ! $member->exists() || ( $member->exists() && $member->id !== $member_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?', 'mpt' ) );
		}

		$email_waiting = $member->get_email_waiting_for_validation();

		if ( empty( $email_waiting ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		}

		update_post_meta( $member->id, 'email', $email_waiting );
		$member->delete_email_waiting_for_validation();
		self::redirect_clear_url();
	}

	/**
	 * Process cancel email action.
	 *
	 * @return void
	 */
	public static function cancel_email_change() {
		$action = $_GET['mpt-action'] ?? ''; //phpcs:ignore
		if ( 'cancel-change-email' !== $action ) {
			return;
		}

		$mtp_nonce = $_GET['_mptnonce'] ?? ''; //phpcs:ignore
		if ( empty( $mtp_nonce ) || ! mpt_verify_nonce( $mtp_nonce, 'mptnewemail' ) ) {
			parent::set_message( 'check-nonce', 'Security check failed', 'error' );

			return;
		}

		$dismiss = $_GET['dismiss'] ?? ''; // phpcs:ignore
		if ( 'new_email' !== $dismiss ) {
			return;
		}

		// Get current member info
		$current_member = mpt_get_current_member();
		if ( ! ( $current_member instanceof MPT_Member ) || ! $current_member->exists() ) {
			return;
		}

		$current_member->delete_email_waiting_for_validation();
		self::redirect_clear_url();
	}

	/**
	 * Check POST data
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function init() {
		$profile_data = $_POST['mptchangeprofile'] ?? []; //phpcs:ignore

		if ( empty( $profile_data ) ) {
			return;
		}

		$mtp_nonce = $_POST['_mptnonce'] ?? ''; //phpcs:ignore
		if ( empty( $mtp_nonce ) || ! mpt_verify_nonce( $mtp_nonce, 'mptchangeprofile' ) ) {
			parent::set_message( 'check-nonce', 'Security check failed', 'error' );

			return;
		}

		// Get current member info
		$get_current_member = mpt_get_current_member();
		$clean_url          = self::get_clean_url();

		if ( MPT_Member_Utility::need_to_update( $get_current_member, $profile_data ) ) {
			$member_id = MPT_Member_Utility::update_member( $get_current_member, $profile_data );

			// Fail to update user
			if ( is_wp_error( $member_id ) ) {
				wp_safe_redirect( add_query_arg( 'update-profile', 2, $clean_url ) );
				exit();
			}
		}

		$status = [
			'update-profile' => 1,
		];
		do_action( 'mpt_redirect_after_profile_updated', $get_current_member, $status );

		// Force to update value into field
		wp_safe_redirect( add_query_arg( $status, $clean_url ) );
		exit();
	}

	/**
	 * Get clean URL
	 * @return string
	 */
	public static function get_clean_url() {
		return remove_query_arg(
			[
				'mpt-action',
				'_mptnonce',
				'dismiss',
			],
			(string) get_permalink(),
		);
	}

	/**
	 * @return void
	 */
	public static function redirect_clear_url() {
		wp_safe_redirect( add_query_arg( 'update', true, self::get_clean_url() ) );
		exit;
	}

	/**
	 * Redirect anonymous members to the login page.
	 *
	 * @return void
	 */
	public static function template_redirect() {
		if ( MPT_Main::is_action_page( 'change-profile' ) && ! mpt_is_member_logged_in() ) {
			$account_link = MPT_Main::get_action_permalink( 'login' );
			if ( ! empty( $account_link ) ) {
				wp_safe_redirect( $account_link, 302, 'mpt' );
				exit;
			}
		}
	}
}
