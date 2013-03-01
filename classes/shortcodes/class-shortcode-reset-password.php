<?php
class MPT_Shortcode_Reset_Password extends MPT_Shortcode {
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-reset-password' , array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect', array( __CLASS__, 'check_changes'), 12 );
	}
	
	public static function shortcode() {
		// Require the file tempalte
		ob_start();
		
		parent::load_template( 'member-reset-password' );
		
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
		
	}

	/**
	 * Check POST data 
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function check_changes() {
		if ( is_admin() ) {
			return false;
		}
		
		return true;
	}
}