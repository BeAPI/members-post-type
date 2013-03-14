<?php
class MPT_User_Utility {
	public function __construct() {}
	
	/**
	 * Allow signon user with mail
	 */
	public static function is_signon_email() {
		$main_settings = (array) get_option( 'mpt-main' );
		if ( isset($main_settings['allow-signon-email']) && (int) $main_settings['allow-signon-email'] == 1 ) {
			return true;
		}
		
		return false;
	}

	/**
	 * A simpler way of inserting an user into the database.
	 *
	 * Creates a new user with just the username, password, and email. For a more
	 * detail creation of a user, use MPT_User_Auth::insert_user() to specify more infomation.
	 *
	 * @see MPT_User_Auth::insert_user() More complete way to create a new user
	 *
	 * @param string $username The user's username.
	 * @param string $password The user's password.
	 * @param string $email The user's email
	 * @return int The new user's ID.
	 */
	public static function create_user($username, $password, $email) {
		$username 	= esc_sql( $username );
		$email 		= esc_sql( $email );

		return self::insert_user( array('username' => $username, 'email' => $email, 'password' => $password) );
	}

	/**
	 * Insert an user into the database.
	 *
	 * The $userdata array can contain the following fields:
	 * 'password' - A string that contains the plain text password for the user.
	 * 'username' - A string that contains the user's username for logging in.
	 * 'email' - A string containing the user's email address.
	 * 'first_name' - The user's first name.
	 * 'last_name' - The user's last name.
	 * 'user_registered' - The date the user registered. Format is 'Y-m-d H:i:s'.
	 * 'role' - A string used to set the user's role
	 *
	 * @param array $userdata An array of user data.
	 * @return int|WP_Error The newly created user's ID or a WP_Error object if the user could not be created.
	 */
	public static function insert_user($userdata) {
		global $wpdb;
		
		if ( mpt_email_exists($userdata['email']) ) {
			return new WP_Error('existing_user_email', __('This email address is already registered.') );
		}
		
		if ( !isset($userdata['user_registered']) || empty($userdata['user_registered']) )
			$userdata['user_registered'] = gmdate('Y-m-d H:i:s');

		$user_id = wp_insert_post( array(
			'post_title' => 'tmp',
			'post_type' => MPT_CPT_NAME,
			'post_status' => 'publish',
			'post_date' => $userdata['user_registered']
		) );

		if ( is_wp_error($user_id) ) {
			return $user_id;
		}
		
		// Instanciate user for have methods
		$user = new MPT_User($user_id);
		if( !$user->exists() ) {
			return new WP_Error('user_not_exists', __('The user is invalid.'));
		}

		// Set password
		if ( isset($userdata['password']) ) {
			$user->set_password( $userdata['password'] );
		}

		// Set core fields
		foreach ( $user::$core_fields as $field ) {
			if ( !isset($userdata[$field]) ) {
				continue;
			}
			
			$user->set_meta_value( $field, $userdata[$field] );
		}
		
		// Set proper post title
		$user->regenerate_post_title( true );
		
		// Set role
		if ( isset($userdata['role']) ) {
			$user->set_role($userdata['role']);
		} else {
			// TODO: Manage default role
			// $user->set_role(get_option('default_role'));
		}
		
		do_action('mpt_insert_user', $user->id);
		
		return $user->id;
	}
}