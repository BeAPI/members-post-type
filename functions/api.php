<?php
/**
 * Check if member are logged in
 * 
 * @return boolean False if disconnected, True if connected
 */
function mpt_is_member_logged_in() {
	return MPT_Member_Auth::is_logged_in();
}

/**
 * Get curent member object
 * 
 * @return boolean False if disconnected, MPT_Member object if connected
 */
function mpt_get_current_member() {
	return MPT_Member_Auth::get_current_member();
}

/**
 * Test email exists on DB
 * 
 * @param  string $email  [description]
 * @return boolean        [description]
 */
function mpt_email_exists( $email ) {
	$test = new MPT_Member();
	$test->fill_by('email', $email);
	return $test->exists();
}

/**
 * Shortlink for MPT_Main::get_action_permalink method
 */
function mpt_get_action_permalink( $action = '' ) {
	return MPT_Main::get_action_permalink( $action );
}

/**
 * Shortlink for MPT_Member_Utility::is_allowed_email_signon method
 */
function mpt_is_allowed_email_signon() {
	return MPT_Member_Utility::is_allowed_email_signon();
}

/**
 * Shortlink for MPT_Member_Utility::is_unique_email method
 */
function mpt_is_unique_email() {
	return MPT_Member_Utility::is_unique_email();
}