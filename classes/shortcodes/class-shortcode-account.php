<?php

class MPT_Shortcode_Account extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-account', array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect' , array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( ! mpt_is_member_logged_in() ) {
			return apply_filters( 'mpt_shortcode_account_member_not_logged_in', '<!-- Members not logged-in. -->' );
		}

		$member  = mpt_get_current_member();
		$message = apply_filters( 'mpt_last_login_activity_message', __( 'If you do not recognize the last activity listed above, please change your password immediately.', 'mpt' ), $member );

		return parent::load_template( 'member-account', [ 'member' => $member, 'message' => $message, 'last_activity_data' => self::prepare_data_last_activity( $member ) ] );
	}

	/**
	 * Prepare last activity data.
	 *
	 * @param MPT_Member $member
	 *
	 * @return array
	 */
	public static function prepare_data_last_activity( $member ) {
		if ( ! mpt_get_option_value( 'mpt-security', 'user-activity' ) ) {
			return [];
		}

		$data_last_activity = $member->get_last_login_activity();

		if ( empty( $data_last_activity ) ) {
			return [];
		}

		$prepare_data = [];
		foreach ( $data_last_activity as $key => $data ) {
			$value = $data['value'];

			if ( empty( $value ) ) {
				continue;
			}

			$label          = $data['label'] ?? '';
			$prepare_data[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return $prepare_data;
	}

	/**
	 * Redirect anonymous members to the login page.
	 *
	 * @return void
	 */
	public static function template_redirect() {
		if ( MPT_Main::is_action_page( 'account' ) && ! mpt_is_member_logged_in() ) {
			$login_link = MPT_Main::get_action_permalink( 'login' );
			if ( ! empty( $login_link ) ) {
				wp_safe_redirect( $login_link, 302, 'mpt' );
				exit;
			}
		}
	}
}
