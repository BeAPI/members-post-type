<?php
class MPT_Shortcode_Login extends MPT_Shortcode {
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-login' , array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect', array( __CLASS__, 'check_changes'), 12 );
		add_action( 'init' , array( __CLASS__, 'init' ) );
	}
	
	public static function init() {
		self::check_log();
	}
	
	public static function shortcode() {
		// Require the file tempalte
		ob_start();
		
		parent::load_template( 'member-login' );
		
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
		
	}
	
	/**
	 * Test if the users try to login
	 */
	public static function check_log() {
		//$signon = MPT_User_Utility::signon();
		//var_dump($signon);
	}


	/**
	 * Check POST data for creation user. Need for set_cookie function.
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function check_changes() {
		global $message, $status, $form_errors;
		
		if ( is_admin() ) {
			return false;
		}
		
		return true;
	}
}