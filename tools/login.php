<?php
define('WP_DEBUG', true);
require( dirname(__FILE__) . '/../../../../wp-load.php' );

if ( MPT_Member_Auth::is_logged_in() ) {
	$result = MPT_Member_Auth::get_current_member();
	var_dump($result);
	
	MPT_Member_Auth::logout();
	die('connected, logout');
} else {
	$result = MPT_Member_Auth::signon( array('m_login' => 'herewithme', 'm_password' => 'pass') );
	var_dump($result);
	die('not connected, signon');
}
