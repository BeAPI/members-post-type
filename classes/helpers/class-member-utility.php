<?php
class MPT_Member_Utility {
	public function __construct() {}

	/**
	 * Is allow to sign-on member with mail
	 */
	public static function is_allowed_email_signon() {
		if ( mpt_get_option_value( 'mpt-main', 'allow-signon-email' ) == 'on' ) {
			return true;
		}

		return false;
	}

	/**
	 * Is unique email constraint on DB
	 */
	public static function is_unique_email() {
		if ( mpt_get_option_value( 'mpt-main', 'unique-email' ) == 'on' ) {
			return true;
		}

		return false;
	}

	/**
	 * A simpler way of inserting an member into the database.
	 *
	 * Creates a new member with just the username, password, and email. For a more
	 * detail creation of a member, use MPT_Member_Auth::insert_member() to specify more infomation.
	 *
	 * @see MPT_Member_Auth::insert_member() More complete way to create a new member
	 *
	 * @param string $username The member's username.
	 * @param string $password The member's password.
	 * @param string $email The member's email
	 * @return int The new member's ID.
	 */
	public static function create_member($username, $password, $email) {
		$username 	= esc_sql( $username );
		$email 		= esc_sql( $email );

		return self::insert_member( array('username' => $username, 'email' => $email, 'password' => $password) );
	}

	/**
	 * Insert an member into the database.
	 *
	 * The $memberdata array can contain the following fields:
	 * 'password' - A string that contains the plain text password for the member.
	 * 'username' - A string that contains the member's username for logging in.
	 * 'email' - A string containing the member's email address.
	 * 'first_name' - The member's first name.
	 * 'last_name' - The member's last name.
	 * 'member_registered' - The date the member registered. Format is 'Y-m-d H:i:s'.
	 * 'role' - A string used to set the member's role
	 *
	 * @param array $memberdata An array of member data.
	 * @return int|WP_Error The newly created member's ID or a WP_Error object if the member could not be created.
	 */
	public static function insert_member($memberdata) {
		if ( mpt_is_unique_email() && mpt_email_exists($memberdata['email']) ) {
			return new WP_Error('existing_member_email', __('This email address is already registered.') );
		}

		if ( !isset($memberdata['member_registered']) || empty($memberdata['member_registered']) ){
			$memberdata['member_registered'] = gmdate('Y-m-d H:i:s');
		}

		if( !isset($memberdata['post_status']) || empty($memberdata['post_status']) ){
			$memberdata['post_status'] = 'publish';
		}

		$member_id = wp_insert_post( array(
			'post_title' => 'tmp',
			'post_type' => MPT_CPT_NAME,
			'post_status' => $memberdata['post_status'],
			'post_date' => $memberdata['member_registered']
		) );

		if ( is_wp_error($member_id) ) {
			return $member_id;
		}

		// Instanciate member for have methods
		$member = new MPT_Member($member_id);
		if( !$member->exists() ) {
			return new WP_Error('member_not_exists', __('The member is invalid.'));
		}

		// Set password
		if ( isset($memberdata['password']) ) {
			$member->set_password( $memberdata['password'] );
		}

		// Set core fields
		foreach ( MPT_Member::$core_fields as $field ) {
			if ( !isset($memberdata[$field]) ) {
				continue;
			}

			$member->set_meta_value( $field, $memberdata[$field] );
		}

		// Set proper post title
		$member->regenerate_post_title( true );

		// Set role
		if ( isset($memberdata['role']) ) {
			$member->set_role($memberdata['role']);
		} else {
			// TODO: Manage default role
			// $member->set_role(get_option('default_role'));
		}

		do_action('mpt_insert_member', $member->id);

		return $member->id;
	}


	/**
	 * @param MPT_Member $member
	 * @param $new_data
	 *
	 * @return bool
	 */
	public static function need_to_update( MPT_Member $member, $new_data ): bool {
		foreach ( MPT_Member::$core_fields as $field ) {
			if ( ! isset( $new_data[ $field ] ) ) {
				continue;
			}

			if ( $new_data[ $field ] === $member->$field ) {
				continue;
			}

			return true;
		}

		return apply_filters( 'mpt_need_to_update_member', false, $member, $new_data );
	}

	public static function update_member( MPT_Member $member, $member_data ) {
		$allowed_field = [
			'last_name',
			'first_name',
			'email',
		];
		foreach ( $allowed_field as $field ) {
			$new_data = $member_data[ $field ] ?? '';
			// Skip if same data
			if ( $member->$field === $new_data ) {
				continue;
			}

			if ( 'email' === $field && ! empty( $new_data ) ) {
				$new_email = sanitize_email( $new_data );
				update_post_meta( $member->id, 'email_change_requested_at', $new_email );
				// Send email to validate new member's email
				$member->validate_new_email( $new_email );
				continue;
			}

			update_post_meta( $member->id, $field, sanitize_text_field( $new_data ) );
		}

		do_action( 'mpt_update_member', $member, $member_data );

		return wp_update_post(
			[
				'ID'         => $member->id,
				'post_title' => sanitize_text_field( $member->get_display_name() ),
			]
		);
	}
}
