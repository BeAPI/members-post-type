<?php
class MPT_Shortcode_Change_Password extends MPT_Shortcode {
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-change-password' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	public static function shortcode() {
		return parent::load_template( 'member-change-password' );
	}

	/**
	 * Check POST data 
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function init() {
		return true;
	}
}