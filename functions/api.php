<?php
/**
 * Check if member are logged in
 * 
 * @return boolean False if disconnected, True if connected
 */
function mpt_is_member_logged_in() {
	return MPT_User_Auth::is_logged_in();
}

/**
 * Test email exists on DB
 * 
 * @param  string $email  [description]
 * @return boolean        [description]
 */
function mpt_email_exists( $email ) {
	$test_user = new MPT_User();
	$test_user->fill_by('email', $email);
	return $test_user->exists();
}
