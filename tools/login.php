<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

if ( MPT_User_Utility::is_logged_in() ) {
	$result = MPT_User_Utility::get_current_user();
	var_dump($result);
	
	MPT_User_Utility::logout();
	die('connected, logout');
} else {
	$result = MPT_User_Utility::signon( array('user_login' => 'herewithme', 'user_password' => 'pass') );
	var_dump($result);
	die('not connected, signon');
}
