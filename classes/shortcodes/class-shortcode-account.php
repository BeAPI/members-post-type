<?php

class MPT_Shortcode_Account extends MPT_Shortcode {
	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-account', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Skip render shortcode in the bo
		if ( is_admin() || ! empty( $_GET['_locale'] ) ) {
			return '';
		}

		// Member logged-in ?
		if ( ! mpt_is_member_logged_in() ) {
			$location = wp_validate_redirect( mpt_get_login_permalink() );

			if ( empty( $location ) ) {
				$location = home_url();
			}

			wp_safe_redirect( $location, 302, 'mpt' );
			exit;
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

			$label = $data['label'] ?? '';
			if ( 'date_time' === $key ) {
				try {
					$date_time = new \DateTime( $value );
					$value     = $date_time->format( 'j F Y - H:i' );
				} catch ( Exception $e ) {
					continue;
				}
			}
			$prepare_data[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return $prepare_data;
	}
}
