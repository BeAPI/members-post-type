<?php
class MPT_Security {
	public function __construct() {
		$current_options = get_option( 'mpt-security' );
		if ( $current_options == false ) {
				return false;
		}
		
		if ( $current_options['mode'] == 'auto' ) {
				add_filter('mpt_set_password', array(__CLASS__, 'mpt_set_password' . '_auto_mode'), 10, 3 );
		} elseif( $current_options['mode'] == 'custom' ) {
				add_filter('mpt_set_password', array(__CLASS__, 'mpt_set_password' . '_custom_mode'), 10, 3 );
		}
	}
	
	public static function mpt_set_password_auto_mode( $flag = false, $password = '', $member_data = null ) {
			// Get WP scoring for password
			$scoring = (int) self::check_wp_password_strength( $password, $member_data );
			
			// Compare scoring with minimum required level
			$current_options = get_option( 'mpt-security' );
			if( $scoring >= (int) $current_options['auto-mode-level'] ) {
					return $flag;
			}
			
			return new WP_Error( 'password_auto_mode', __('This password is not secure enough.', 'mpt') );
	}
	
	public static function mpt_set_password_custom_mode( $flag = false, $password = '', $member_data = null ) {
			return $flag;
	}
	
	/**
	 * Check for password strength - based on JS function in WP core: /wp-admin/js/password-strength-meter.js
	 *
	 * @param $password string The password
	 * @param $member_data string The memnber's data
	 * 
	 * @return integer 1 = very weak; 2 = weak; 3 = medium; 4 = strong
	 */
	public static function check_wp_password_strength( $password, $member_data ) {
		$h = 1; $e = 2; $b = 3; $a = 4; $d = 0; $g = null; $c = null;
		if ( strlen( $password ) < 4 )
			return $h;
		if ( strtolower( $password ) == strtolower( $member_data->get_display_name() ) )
			return $h;
		if ( preg_match( "/[0-9]/", $password ) )
			$d += 10;
		if ( preg_match( "/[a-z]/", $password ) )
			$d += 26;
		if ( preg_match( "/[A-Z]/", $password ) )
			$d += 26;
		if ( preg_match( "/[^a-zA-Z0-9]/", $password ) )
			$d += 31;
		$g = log( pow( $d, strlen( $password ) ) );
		$c = $g / log( 2 );
		if ( $c < 40 )
			return $e;
		if ( $c < 56 )
			return $b;
		
		return $a;
	}
}