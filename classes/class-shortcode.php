<?php
class MPT_Shortcode {
	/**
     * Register 5 shortcodes : [member-registration], [member-login], [member-lost-password], [member-change-password]
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		_mpt_load_files(MPT_DIR . '/classes/shortcodes/', array('shortcode-registration', 'shortcode-login', 'shortcode-lost-password', 'shortcode-change-password'), 'class-');
		
		new MPT_Shortcode_Registration();
		new MPT_Shortcode_Login();
		new MPT_Shortcode_Lost_Password();
		new MPT_Shortcode_Change_Password();
	} 
	
	/**
	 * Load a shortcode template from the theme or directly from the plugin
	 * 
	 * @param string $template the template name without extension and path
	 * @param array $user_data variable to passe to template for display _POST values
	 * @return string the file content | bool false
	 * 
	 * @author Benjamin Niess
	 */
	public static function load_template( $template = '', $user_data = array() ) {
		if ( empty( $template ) ) {
			return false;
		}
		
		ob_start();
		if ( is_file( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( is_file( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( is_file( MPT_DIR . '/views/client/' . $template . '.tpl.php' ) ) {
			include( MPT_DIR . '/views/client/' . $template . '.tpl.php' );
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
		
		if ( $format == 'display' ) {
			$output = '';
			foreach( $mpt_messages as $field => $message ) {
				$output .= '<div class="field-target-' . esc_attr($field) . ' ' . esc_attr($message['status']) . '">' . stripslashes($message['message']) . '</div>';
			}
			return $output;
		} else {
			return $mpt_messages;
		}
	}
	
	/**
	 * Set message success/error global messages
	 * 
	 * @param format the return format. 'display' for having the div container, 'raw' for having an array
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
		
		$mpt_messages[$field] = array('status' => $status, 'message' => $message);
		return true;
	}
}