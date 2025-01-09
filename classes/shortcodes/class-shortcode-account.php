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

		return parent::load_template( 'member-account', $member );
	}
}
