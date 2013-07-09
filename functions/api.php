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

/**
 * Whether current member has capability or role.
 *
 * @param string $capability Capability or role name.
 * @return bool
 */
function current_member_can( $capability ) {
	$current_member = mpt_get_current_member();
	if ( empty( $current_member ) )
		return false;

	$args = array_slice( func_get_args(), 1 );
	$args = array_merge( array( $capability ), $args );

	return call_user_func_array( array( $current_member, 'has_cap' ), $args );
}

/**
 * Whether a particular member has capability or role.
 *
 * @param int|object $member member ID or object.
 * @param string $capability Capability or role name.
 * @return bool
 */
function member_can( $member, $capability ) {
	if ( ! is_object( $member ) )
		$member = MPT_Member( $member );

	if ( ! $member || ! $member->exists() )
		return false;

	$args = array_slice( func_get_args(), 2 );
	$args = array_merge( array( $capability ), $args );

	return call_user_func_array( array( $member, 'has_cap' ), $args );
}

/**
 * Retrieve role object.
 *
 * @see MPT_Roles::get_role() Uses method to retrieve role object.
 *
 * @param string $role Role name.
 * @return object
 */
function mpt_get_role( $role ) {
	return MPT_Roles::get_role( $role );
}

/**
 * Add role, if it does not exist.
 *
 * @see MPT_Roles::add_role() Uses method to add role.
 *
 * @param string $role Role name.
 * @param string $display_name Display name for role.
 * @param array $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
 * @return null|MPT_Role MPT_Role object if role is added, null if already exists.
 */
function mpt_add_role( $role, $display_name, $capabilities = array() ) {
	return MPT_Roles::add_role( $role, $display_name, $capabilities );
}

/**
 * Remove role, if it exists.
 *
 * @see MPT_Roles::remove_role() Uses method to remove role.
 *
 * @param string $role Role name.
 * @return null
 */
function mpt_remove_role( $role ) {
	return MPT_Roles::remove_role( $role );
}

/**
 * Shortlink for MPT_Options::get_option_value method
 */
function mpt_get_option_value( $option_name, $field, $failback_default ) {
	return MPT_Options::get_option_value( $option_name, $field, $failback_default );
}