<?php

/**
 * 
 */
class MPT_Users_To_Members {
	
	function __construct() {
		
		add_filter( 'restrict_manage_users', array( __CLASS__, 'restrict_manage_users' ) );
		add_action( 'admin_init', array( __CLASS__, 'restrict_insert_member' ) );
	}
	
	public static function restrict_manage_users() {
		//add button 
		echo '<button type="submit" class="button" name="mpt-action-bulk-user" value="true">Convert to MPT</button>';
		
	}
	
	public static function restrict_insert_member() {
		
		if ( !current_user_can( 'administrator' ) ){
			return false;
		}
		
		if( !isset( $_GET['mpt-action-bulk-user'] ) || $_GET['mpt-action-bulk-user'] != "true" ){
			return false;
		}
		
		if( !isset( $_GET['users'] ) || !is_array( $_GET['users'] ) ){
			return false;
		}
		
		foreach ($_GET['users'] as $user) {
			self::insert_member( $user );
		}
	}

	public static function insert_member( $user ){
		$user_data = get_userdata( $user );
		
		if( !isset($user_data) || !is_object( $user_data ) ){
			return false;
		}
		
		$fullname = $user_data->display_name;
		$fullname = explode( ' ', $fullname );
		
		//user information 
		$data_for_member = array(
							'username' => $user_data->user_login,
							'first_name' => $fullname[0],
							'last_name' => $fullname[1],
							'email' => $user_data->user_email,
							'member_registered' => $user_data->user_registered,
							'password' => $user_data->user_pass,
						);
		//add user to member post type
		$member_id = MPT_Member_Utility::insert_member( $data_for_member );
		if( is_wp_error( $member_id ) || (int) $member_id <= 0 ){
			return false;
		}
		
		update_post_meta( $member_id, 'password', $user_data->user_pass );
		
		//delete user
		wp_delete_user( $user );
	}
	
	
}