<?php
class MPT_Security {
	/**
	 * Register hook depending user settings
	 * 
	 * @return boolean
	 */
	public function __construct() {
		$current_options = MPT_Options::get_option( 'mpt-security' );
		if ( $current_options == false ) {
			return false;
		}
		
		// Password policy
		if ( isset($current_options['aging']) && (int) $current_options['aging'] > 0 ) {
			add_action('template_redirect', array(__CLASS__, 'template_redirect'.'_aging'), 9 );
			add_action('mpt_set_password', array(__CLASS__, 'mpt_set_password' . '_aging'), 9, 4 );
		}
		if ( isset($current_options['history']) && (int) $current_options['history'] > 0 ) {
			add_filter('mpt_set_password_check', array(__CLASS__, 'mpt_set_password_check' . '_history'), 10, 3 );
			add_action('mpt_set_password', array(__CLASS__, 'mpt_set_password' . '_history'), 10, 4 );
		}
		
		// Password strengh
		if ( isset($current_options['mode']) && $current_options['mode'] == 'auto' ) {
			add_filter('mpt_set_password_check', array(__CLASS__, 'mpt_set_password_check' . '_auto_mode'), 10, 3 );
		} elseif( isset($current_options['mode']) && $current_options['mode'] == 'custom' ) {
			add_filter('mpt_set_password_check', array(__CLASS__, 'mpt_set_password_check' . '_custom_mode'), 10, 3 );
		}
	}
	
	public static function template_redirect_aging() {
		if ( !mpt_is_member_logged_in() ) {
			return false;
		}
		
		$member_data = mpt_get_current_member();
		$last_updated_date = get_post_meta( $member_data->id, '_password_last_updated_date', true );
		if ( $last_updated_date == false ) {
			return false;
		}
		
		// Calcul date of expiration
		$expiration_date = $last_updated_date + ((int) mpt_get_option_value( 'mpt-security', 'aging' ) * DAY_IN_SECONDS);
		
		// Expiration date < to current date, force permanent redirect to change password form
		if ( $expiration_date < time()  ) {
			if( !isset($_GET['mpt-action']) || $_GET['mpt-action'] != 'force-change-password' ) {
				wp_redirect( add_query_arg( 'mpt-action', 'force-change-password', mpt_get_change_password_permalink()) );
				exit();
			}
		} elseif( isset($_GET['mpt-action']) && $_GET['mpt-action'] == 'force-change-password' ) {
			wp_redirect( remove_query_arg('mpt-action') );
			exit();
		}
		
		return true;
	}
	
	public static function mpt_set_password_aging( $new_hash = '', $new_password = '', $old_hash = '', $member_data = null ) {
		update_post_meta( $member_data->id, '_password_last_updated_date', time() );
	}
	
	public static function mpt_set_password_history( $new_hash = '', $new_password = '', $old_hash = '', $member_data = null ) {
		// Get current password history
		$passwords_history = get_post_meta( $member_data->id, '_passwords_history', true );
		if ( $passwords_history == false ) {
			$passwords_history = array();
		}
		
		// If the size of the history has already been reached, the oldest word password is removed.
		if ( count($passwords_history) >= (int) mpt_get_option_value( 'mpt-security', 'history' ) ) {
			array_shift($passwords_history);
		}
		
		// Add current password on array
		array_push($passwords_history, $old_hash);
		
		// Save meta
		update_post_meta( $member_data->id, '_passwords_history', $passwords_history );

		return $flag;
	}
	
	public static function mpt_set_password_check_history( $flag = false, $password = '', $member_data = null ) {
		// Get current password history
		$passwords_history = get_post_meta( $member_data->id, '_passwords_history', true );
		if ( $passwords_history == false ) {
			$passwords_history = array();
		}
		
		if ( empty($passwords_history) ) {
			return $flag;
		}
		
		foreach( $passwords_history as $password_hash ) {
			if ( wp_check_password($password, $password_hash, false) ) {
				return new WP_Error( 'password_auto_mode', __('You can not use this password because of the defined security policy and the fact that you\'ve used in the past.', 'mpt') ); 
			}
		}
		
		return $flag;
	}
	
	/**
	 * Check password with WP algorithm (auto mode)
	 * 
	 * @param boolean $flag
	 * @param string $password
	 * @param MPT_Member $member_data
	 * @return boolean|WP_Error
	 */
	public static function mpt_set_password_check_auto_mode( $flag = false, $password = '', $member_data = null ) {
			// Get WP scoring for password
			$scoring = (int) self::check_wp_password_strength( $password, $member_data );
			
			// Compare scoring with minimum required level
			if( $scoring >= (int) mpt_get_option_value( 'mpt-security', 'auto-mode-level' ) ) {
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
	public static function mpt_set_password_check_custom_mode( $flag = false, $password = '', $member_data = null ) {
		$current_options = MPT_Options::get_option( 'mpt-security' );
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
