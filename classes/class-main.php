<?php
class MPT_Main {
	public function __construct() {
		add_action('init', array(__CLASS__, 'init') );
	}

	public static function init() {
		if ( isset($_GET['mpt-action']) && $_GET['mpt-action'] == 'logout' ) {
			if ( MPT_User_Utility::is_logged_in() ) {
				MPT_User_Utility::logout();
				wp_redirect(home_url('/#logout-success') );
				exit();
			} else {
				wp_redirect(home_url('/#logout-error') );
				exit();
			}
		}
	}
}