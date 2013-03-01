<?php
class MPT_Shortcode {
	/**
	 * Register X shortcodes : [member-registration], [member-login], [member-forget-password], [member-change-password]
	 */
	public function __construct() {
		self::load_shortcodes();
	}
	
	public static function load_shortcodes() {
		_mpt_load_files(MPT_DIR . '/classes/shortcodes/', array('shortcode-registration', 'shortcode-login', 'shortcode-forgot-password', 'shortcode-reset-password'), 'class-');
		
		new MPT_Shortcode_Registration();
		new MPT_Shortcode_Login();
		new MPT_Shortcode_Forgot_Password();
		new MPT_Shortcode_Reset_Password();
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
		if ( file_exists( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( STYLESHEETPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( file_exists( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' ) ) {
			include( TEMPLATEPATH . '/shortcodes/mpt-' . $template . '.tpl.php' );
		} elseif ( file_exists( MPT_DIR . '/views/client/' . $template . '.tpl.php' ) ) {
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
			foreach( $mpt_messages as $error_code => $message ) {
				$output .= '<div class="' . esc_attr( $message['status'] ) . '">' . stripslashes($message['message']) . '</div>';
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
	public static function set_message( $error_code = '', $message = '', $status = 'error' ) {
		global $mpt_messages;
		
		if ( empty($error_code) || empty($message) ) {
			return false;
		}
		
		if ( !isset($mpt_messages) ) {
			$mpt_messages = array();
		}
		
		$mpt_messages[$error_code] = array('status' => $status, 'message' => $message);
		return true;
	}
}