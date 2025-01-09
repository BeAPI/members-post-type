<?php
class MPT_Shortcode {
	/**
     * Register 5 shortcodes : [member-registration], [member-login], [member-lost-password], [member-change-password], [member-change-profile],[member-account],
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		_mpt_load_files( 'classes/shortcodes/', array('shortcode-registration', 'shortcode-login', 'shortcode-lost-password', 'shortcode-change-password', 'shortcode-change-profile', 'shortcode-account'), 'class-');
		
		new MPT_Shortcode_Registration();
		new MPT_Shortcode_Login();
		new MPT_Shortcode_Lost_Password();
		new MPT_Shortcode_Change_Password();
		new MPT_Shortcode_Change_Profile();
		new MPT_Shortcode_Account();
	}
	
	/**
	 * Load a shortcode template from the theme or directly from the plugin
	 * 
	 * @param string $template the template name without extension and path
	 * @param array $member_data variable to passe to template for display _POST values
	 * @return string $default_path the plugin path
	 * 
	 * @author Benjamin Niess
	 */
	public static function load_template( $template = '', $member_data = array(), $default_path = '' ) {
		if ( empty( $template ) ) {
			return false;
		}
		
		if( empty( $default_path ) ){
			$default_path = MPT_DIR;
		}

		$member_data = apply_filters( 'mpt_shortcode_data', $member_data, $template );
		
		ob_start();
		if ( is_file( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( is_file( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( is_file( $default_path . 'views/client/' . $template . '.tpl.php' ) ) {
			include( $default_path . 'views/client/' . $template . '.tpl.php' );
		} else {
			ob_end_clean();
			return false;
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Get message success/error global messages
	 * 
	 * @param format the return format. 'display' for having the div container, 'raw' for having an array
	 * @author Benjamin Niess
	 */
	public static function get_messages( $format = 'display' ) {
		global $mpt_messages;
		
		if ( !isset($mpt_messages) ) {
			$mpt_messages = array();
		}

		$mpt_messages = apply_filters( 'mpt_get_messages', $mpt_messages, $format );
		
		if ( $format == 'display' && !empty($mpt_messages) ) {
			$output = '';
			foreach( $mpt_messages as $field => $message ) {
				$output .= '<div class="field-target-' . esc_attr($field) . ' ' . esc_attr($message['status']) . '">' . stripslashes($message['message']) . '</div>';
			}
			return $output;
		} else {
			return ( $format == 'display' ) && empty( $mpt_messages ) ? '' : $mpt_messages;
		}
	}
	
	/**
	 * Set message success/error global messages
	 * 
	 * @param string $field the key of the message in the global messages
	 * @param string $message the message
	 * @param string $status the message status
	 * @author Benjamin Niess
	 */
	public static function set_message( $field = '', $message = '', $status = 'error' ) {
		global $mpt_messages;
		
		if ( empty($field) || empty($message) ) {
			return false;
		}
		
		if ( !isset($mpt_messages) ) {
			$mpt_messages = array();
		}
		
		$mpt_messages[$field] = apply_filters( 'mpt_set_message', array('status' => $status, 'message' => $message), $field );
		return true;
	}
	
	/**
	 * Remove a succes/error message from global messages
	 * 
	 * @params string $field the field of the message to be removed
	 */
	public static function remove_message( $field ) {
		global $mpt_messages;
		
		if ( !isset($mpt_messages) ) {
			return false;
		}
		
		if ( !isset($mpt_messages[$field]) ) {
			return false;
		}
		
		unset( $mpt_messages[$field] );
		
		return true;
	}
}
