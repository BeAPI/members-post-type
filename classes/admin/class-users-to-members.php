<?php
class MPT_Admin_Users_To_Members {
	
	function __construct() {
		add_filter( 'restrict_manage_users', array( __CLASS__, 'restrict_manage_users' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}
	
	/**
	 * Add button in user manage page
	 * @author Lucie Gomes
	 */
	public static function restrict_manage_users() {
		//add button 
		echo '<button type="submit" class="button" name="mpt-action-bulk-user" value="true">Convert to MPT</button>';
		wp_nonce_field( 'convert_to_MPT', 'convert_to_MPT' );
	}
	
	/**
	 * check authorisation befor insert users in members 
	 * @author Lucie Gomes
	 */
	public static function admin_init() {
		//user can modify users ?
		if ( !current_user_can( 'edit_users' ) ){
			return false;
		}
		
		// convert to MPT button push ?
		if( !isset( $_GET['mpt-action-bulk-user'] ) || $_GET['mpt-action-bulk-user'] != "true" ){
			return false;
		}
		
		//nonce ok ?
		if ( !isset( $_GET['convert_to_MPT'] ) || !wp_verify_nonce( $_GET['convert_to_MPT'], 'convert_to_MPT' ) ) {
			wp_redirect( 'users.php' );
			exit();
		}
		
		//users array ok ?
		if( !isset( $_GET['users'] ) || !is_array( $_GET['users'] ) ){
			wp_redirect( 'users.php' );
			exit();
		}
		
		$users = array_map( 'intval' , $_GET['users'] );
		
		//insert user in member
		foreach ( $users as $user ) {
			self::insert_member( $user );
		}
			
		wp_redirect( 'edit.php?post_type=member' );
		exit();
	}

	/**
	 * insert users in members
	 * $user int
	 * @author Lucie Gomes
	 */
	public static function insert_member( $user ){
		$user_data = new WP_User( $user );
		
		if( empty( $user_data ) || is_wp_error( $user_data ) ){
			return false;
		}
		
		//user information 
		$data_for_member = array(
							'username' => $user_data->user_login,
							'first_name' => $user_data->first_name,
							'last_name' => $user_data->last_name,
							'email' => $user_data->user_email,
							'member_registered' => $user_data->user_registered,
							'password' => $user_data->user_pass,
						);
		//add user to member post type
		$member_id = MPT_Member_Utility::insert_member( $data_for_member );
		if( is_wp_error( $member_id ) || ( int ) $member_id <= 0 ){
			return false;
		}
		
		//update member's password
		update_post_meta( $member_id, 'password', $user_data->user_pass );
		
		//delete user
		wp_delete_user( $user );
	}
}