<?php
/**
 * Return login page link
 * 
 * @return string
 */
function mpt_get_login_permalink() {
	return MPT_Main::get_action_permalink( 'login' );
}

/**
 * Return logout page link
 * 
 * @return string
 */
function mpt_get_logout_permalink() {
	return MPT_Main::get_action_permalink( 'logout' ); 
}

/**
 * Return register page link
 * 
 * @return string
 */
function mpt_get_register_permalink() {
	return MPT_Main::get_action_permalink( 'registration' );
}

/**
 * Return register page link (clone as mpt_get_register_permalink)
 * 
 * @return string
 */
function mpt_get_registration_permalink() {
	return MPT_Main::get_action_permalink( 'registration' );
}

/**
 * Return lost password page link
 * 
 * @return string
 */
function mpt_get_lost_password_permalink() {
	return MPT_Main::get_action_permalink( 'lost-password' );
}

/**
 * Return change password page link
 * 
 * @return string
 */
function mpt_get_change_password_permalink() {
	return MPT_Main::get_action_permalink( 'change-password' );
}