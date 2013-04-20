<?php
class MPT_Security {
	/**
	 * Register hook depending user settings
	 * 
	 * @return boolean
	 */
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
	
	/**
	 * Check password with WP algorithm (auto mode)
	 * 
	 * @param boolean $flag
	 * @param string $password
	 * @param MPT_Member $member_data
	 * @return boolean|WP_Error
	 */
	public static function mpt_set_password_auto_mode( $flag = false, $password = '', $member_data = null ) {
			// Get WP scoring for password
			$scoring = (int) self::check_wp_password_strength( $password, $member_data );
			
			// Compare scoring with minimum required level
			$current_options = get_option( 'mpt-security' );
			if( $scoring >= (int) $current_options['auto-mode-level'] ) {
					return $flag;
			}
			
			return new WP_Error( 'password_auto_mode', __('The password does not meet the criteria required by the security policy.', 'mpt') );
	}
	
	/**
	 * Check password with custom algorithm (custom mode)
	 * 
	 * @param boolean $flag
	 * @param string $password
	 * @param MPT_Member $member_data
	 * @return boolean|WP_Error
	 */
	public static function mpt_set_password_custom_mode( $flag = false, $password = '', $member_data = null ) {
		$current_options = get_option( 'mpt-security' );
		if ( $current_options == false ) {
			return $flag;
		}
		
		$_errors = array();
		if ( isset($current_options['minimum-length']) && strlen( $password ) < (int) $current_options['minimum-length'] ) {
			$_errors[] = new WP_Error( 'minimum-length', __('Password too short.', 'mpt') );
		}
		if ( isset($current_options['user-data-password']) && $current_options['user-data-password'] == 'on' && self::_has_word( $password, array($member_data->get_display_name(), $member_data->last_name, $member_data->first_name, $member_data->email) )  ) {
			$_errors[] = new WP_Error( 'user-data-password', __('Your password must not contain a word or phrase from your personal data.', 'mpt') );
		}
		if ( isset($current_options['uppercase-character']) && $current_options['uppercase-character'] == 'on' && !preg_match( "/[A-Z]/", $password ) ) {
			$_errors[] = new WP_Error( 'uppercase-character', __('Missing uppercase character.', 'mpt') );
		}
		if ( isset($current_options['lowercase-character']) && $current_options['lowercase-character'] == 'on' && !preg_match( "/[a-z]/", $password ) ) {
			$_errors[] = new WP_Error( 'lowercase-character', __('Missing lowercase character.', 'mpt') );
		}
		if ( isset($current_options['number-character']) && $current_options['number-character'] == 'on' && !preg_match( "/[0-9]/", $password ) ) {
			$_errors[] = new WP_Error( 'number-character', __('Missing number character.', 'mpt') );
		}
		if ( isset($current_options['special-character']) && $current_options['special-character'] == 'on' && !preg_match( "/[^a-zA-Z0-9]/", $password ) ) {
			$_errors[] = new WP_Error( 'special-character', __('Missing special character.', 'mpt') );
		}
		if ( isset($current_options['blacklist-keywords']) && !empty($current_options['blacklist-keywords']) && self::_has_word( $password, explode(',', $current_options['blacklist-keywords']) ) ) {
			$_errors[] = new WP_Error( 'blacklist-keywords', __('Your password contains a word or phrase prohibited by administrator.', 'mpt') );
		}
		
		if ( !empty($_errors) ) {
			return $_errors;
		}
		
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
		if ( self::_has_word( $password, array($member_data->get_display_name(), $member_data->last_name, $member_data->first_name, $member_data->email) ) )
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
	
	/**
	 * Allows you to search one or more words contained in a string. 
	 * 
	 * @param string $string_to_check
	 * @param string|array $test_strings
	 * @return boolean
	 */
	private static function _has_word( $string_to_check = '', $test_strings = array() ) {
		if ( empty($string_to_check) || empty($test_strings) ) {
			return false;
		}
		
		// Convert string to array, for next
		if ( !is_array($test_strings) ) {
			$test_strings = (array) $test_strings;
		}
		
		// convert to lower
		$string_to_check = strtolower($string_to_check);
		$test_strings = array_map('strtolower', $test_strings);
		
		// Exact word in array ?
		if (in_array($string_to_check, $test_strings)) {
			return true;
		}
		
		// Partial word ?
		foreach( $test_strings as $test_string ) {
			if (stripos($test_string, $string_to_check) !== false ) {
				return true;
			}
		}
		
		return false;
	}
}