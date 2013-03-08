<?php
function mpt_get_login_permalink() {
	return MPT_Main::get_action_permalink( 'login' );
}

function mpt_get_logout_permalink() {
	return MPT_Main::get_action_permalink( 'logout' ); 
}

function mpt_get_register_permalink() {
	return MPT_Main::get_action_permalink( 'registration' );
}

function mpt_get_registration_permalink() {
	return MPT_Main::get_action_permalink( 'registration' );
}

function mpt_get_lost_password_permalink() {
	return MPT_Main::get_action_permalink( 'lost-password' );
}

function mpt_get_change_password_permalink() {
	return MPT_Main::get_action_permalink( 'change-password' );
}