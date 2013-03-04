<?php
class MPT_Main {
	public function __construct( ) {
		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'mpt_login', array( __CLASS__, 'mpt_login' ), 10, 2 );
	}

	/**
	 * Manage logout
	 */
	public static function init( ) {
		if ( isset( $_GET['mpt-action'] ) && $_GET['mpt-action'] == 'logout' ) {
			if ( MPT_User_Utility::is_logged_in( ) ) {
				MPT_User_Utility::logout( );
				wp_redirect( home_url( '/#logout-success' ) );
				exit( );
			} else {
				wp_redirect( home_url( '/#logout-error' ) );
				exit( );
			}
		}
	}

	/**
	 * Manage login counter, last connection
	 */
	public static function mpt_login( $user_name = '', $user_id = 0 ) {
		// Increment counter
		$counter = (int) get_post_meta( $user_id, '_counter_sign_on', true );
		$counter++;
		update_post_meta( $user_id, '_counter_sign_on', $counter );

		// Update latest date connection
		update_post_meta( $user_id, '_last_sign_on_date', current_time( 'mysql' ) );
	}

}
