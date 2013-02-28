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
	 * @return string the file content | bool false
	 * 
	 * @author Benjamin Niess
	 */
	public static function load_template( $template = '' ) {
		if ( empty( $template ) ) {
			return false;
		}
		
		if ( file_exists( TEMPLATEPATH . '/mpt/' . $template . '.tpl.php' ) ) {
			require( TEMPLATEPATH . '/mpt/' . $template . '.tpl.php' );
		} elseif ( file_exists( MPT_DIR . '/views/client/' . $template . '.tpl.php' ) ) {
			require( MPT_DIR . '/views/client/' . $template . '.tpl.php' );
		} else {
			return false;
		}
	}
	
	/**
	 * Display the global message and status values
	 * 
	 * @param format the return format. 'display' for having the div container, 'raw' for having an array
	 * 
	 * @author Benjamin Niess
	 */
	public static function get_message( $format = 'display' ) {
		global $message, $status;
		
		if ( $format == 'display' ) {
			echo '<div class="' . esc_attr( $status ) . '">' . stripslashes( $message ) . '</div>';
		} else {
			return array( 'message' => $message, 'status' => $status );
		}
	}

}