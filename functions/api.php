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
 * Get current member object
 *
 * @return MPT_Member|false
 * @author Nicolas Juen
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
	if ( empty( $current_member ) ) {
		return false;
	}
	
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
function mpt_get_option_value( $option_name, $field, $failback_default = false ) {
	return MPT_Options::get_option_value( $option_name, $field, $failback_default );
}
/*
 * Check if is a member validation registration
 */
function mpt_registration_with_member_validation(){
	$option = MPT_Options::get_option_value( 'mpt-main', 'subscription-member-validation' );
	if( $option === 1 ){
		return 'off';
	}
	return $option;
}

/**
 * Verify that correct nonce was used with time limit.
 *
 * The user is given an amount of time to use the token, so therefore, since the
 * UID and $action remain the same, the independent variable is the time.
 *
 * @since 0.6.0
 *
 * @param string     $nonce  Nonce that was used in the form to verify
 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
 * @return bool Whether the nonce check passed or failed.
 */
function mpt_verify_nonce( $nonce, $action = -1 ) {
	return MPT_Nonces::verify_nonce( $nonce, $action );
}

/**
 * @param int $action
 *
 * @return string
 * @author Nicolas Juen
 */
 function mpt_create_nonce($action = -1) {
	return MPT_Nonces::create_nonce( $action );
}

/**
 * Retrieve URL with nonce added to URL query.
 *
 * @since 0.6.0
 *
 * @param string     $actionurl URL to add nonce action.
 * @param int|string $action    Optional. Nonce action name. Default -1.
 * @param string     $name      Optional. Nonce name. Default '_mptnonce'.
 * @return string Escaped URL with nonce action added.
 */
function mpt_nonce_url( $actionurl, $action = -1, $name = '_mptnonce' ) {
	return MPT_Nonces::nonce_url( $actionurl, $action, $name );
}

/**
 * Retrieve or display nonce hidden field for forms.
 *
 * The nonce field is used to validate that the contents of the form came from
 * the location on the current site and not somewhere else. The nonce does not
 * offer absolute protection, but should protect against most cases. It is very
 * important to use nonce field in forms.
 *
 * The $action and $name are optional, but if you want to have better security,
 * it is strongly suggested to set those two parameters. It is easier to just
 * call the function without any parameters, because validation of the nonce
 * doesn't require any parameters, but since crackers know what the default is
 * it won't be difficult for them to find a way around your nonce and cause
 * damage.
 *
 * The input name will be whatever $name value you gave. The input value will be
 * the nonce creation value.
 *
 * @since 0.6.0
 *
 * @param int|string $action  Optional. Action name. Default -1.
 * @param string     $name    Optional. Nonce name. Default '_mptnonce'.
 * @param bool       $referrer Optional. Whether to set the referrer field for validation. Default true.
 * @param bool       $echo    Optional. Whether to display or return hidden form field. Default true.
 * @return string Nonce field HTML markup.
 */
function mpt_nonce_field( $action = -1, $name = "_mptnonce", $referrer = true , $echo = true ) {
	return MPT_Nonces::nonce_field( $action, $name, $referrer, $echo );
}

/**
 * Retrieve the current session token from the logged_in cookie.
 *
 * @since 0.6.0
 *
 * @return string Token.
 * @author Nicolas Juen
 */
function mpt_get_session_token() {
	return MPT_Nonces::get_session_token();
}