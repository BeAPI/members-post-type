<?php
/**
 * Check if member are logged in
 * 
 * @return boolean False if disconnected, True if connected
 */
function mpt_is_member_logged_in() {
	return MPT_User_Utility::is_logged_in();
}