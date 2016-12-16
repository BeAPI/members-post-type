<?php class MPT_Member {
	public static $core_fields = array(
		'email',
		'username',
		'first_name',
		'last_name',
		'password',
		'connection_type',
		'social_id'
	);

	// Core public fields
	public $id = 0;
	public $email = null;
	public $username = null;
	public $first_name = null;
	public $last_name = null;
	public $password = null;
	public $connection_type = null;
	public $social_id = null;

	/**
	 * The individual capabilities the member has been given.
	 *
	 * @access public
	 * @var array
	 */
	public $caps = array();

	/**
	 * The roles the member is part of.
	 *
	 * @access public
	 * @var array
	 */
	public $roles = array();

	/**
	 * All capabilities the member has, including individual and role based.
	 *
	 * @access public
	 * @var array
	 */
	public $allcaps = array();

	// Private object
	private $_object = false;

	/**
	 * Constructor
	 *
	 * @param integer $id [description]
	 */
	public function __construct( $id = 0 ) {
		if ( (int) $id > 0 ) {
			$this->fill_by( 'id', $id );
		}

		self::$core_fields = apply_filters( 'mpt_core_fields', self::$core_fields );
	}

	/**
	 * Test if member exist
	 *
	 * @return bool False on failure, True on success
	 */
	public function exists() {
		if ( $this->_object == false || is_wp_error( $this->_object ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve member info by a given field
	 *
	 * @param string $field The field to retrieve the member with.  id | email |
	 * username
	 * @param int|string $value A value for $field.  A member ID, email address, or
	 * username.
	 *
	 * @return bool False on failure, True on success
	 */
	public function fill_by( $field, $value ) {
		switch ( $field ) {
			case 'id' :
				$this->_object = get_post( $value );
				break;
			case 'email' :
			case 'username' :
			case 'activation_key' :
				$id = self::get_id_from_key_value( $field, $value );
				if ( $id == 0 ) {
					return false;
				}
				$this->_object = get_post( $id );
				break;
			default :
				return false;
		}

		if ( ! $this->exists() ) {
			return false;
		}

		// Set ID
		$this->id = $this->_object->ID;

		// Set core fields
		foreach ( self::$core_fields as $key ) {
			$this->$key = get_post_meta( $this->id, $key, true );
		}

		// Set caps
		$this->_init_caps();

		return true;
	}

	/**
	 * Retrieve member info by a given meta key/values
	 *
	 * @param string $field The field (meta_kye) to retrieve the member
	 * @param int|string $value A value for $field.  A meta value
	 *
	 * @return bool False on failure, True on success
	 */
	public function fill_by_meta( $field, $value ) {
		$id = self::get_id_from_key_value( $field, $value );
		if ( $id == 0 ) {
			return false;
		}

		return $this->fill_by( 'id', $id );
	}

	/**
	 * Update post meta value of members
	 *
	 * @param string $key [description]
	 * @param boolean $value [description]
	 */
	public function set_meta_value( $key = '', $value = null ) {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		if ( $key == 'password' ) {// Forbide, use specific method
			return false;
		}

		if ( ! in_array( $key, self::$core_fields ) ) {// Allow only core member fields
			return false;
		}

		// Check if email is unique, when option is enabled, restore old value if already
		// exist.
		if ( $key == 'email' && mpt_is_unique_email() && $this->email != $value && mpt_email_exists( $value ) ) {
			return false;
			// TODO: WP ERROR ?
		}

		return update_post_meta( $this->id, $key, $value );
	}

	/**
	 * Updates the member's password with a new encrypted one.
	 *
	 * For integration with other applications, this function can be overwritten to
	 * instead use the other package password checking algorithm.
	 *
	 * @param string $password The plaintext new member password
	 */
	public function set_password( $new_password = '' ) {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		if ( empty( $new_password ) ) {// Valid password ?
			return false;
		}

		$stop = apply_filters_ref_array( 'mpt_set_password_check', array( false, $new_password, &$this ) );
		if ( $stop !== false ) {
			return new WP_Error( 'no_password_change', __( 'Password change is not allowed for this member' ) );
		} elseif ( is_wp_error( $stop ) ) {
			return $stop;
		}

		$old_hash = $this->password;
		$new_hash = wp_hash_password( $new_password );

		update_post_meta( $this->id, 'password', $new_hash );
		do_action_ref_array( 'mpt_set_password', array( $new_hash, $new_password, $old_hash, &$this ) );

		delete_post_meta( $this->id, 'activation_key' );

		return true;
	}

	/**
	 * Private method for get member id from key/value, work post meta table
	 *
	 * @param  string $key [description]
	 * @param  string $value [description]
	 * @param  array $exclude_ids [description]
	 *
	 * @return integer        [description]
	 */
	public static function get_id_from_key_value( $key = '', $value = '', $exclude_ids = array() ) {
		global $wpdb;

		// Convert to array if necessary
		if ( ! is_array( $exclude_ids ) ) {
			$exclude_ids = (array) $exclude_ids;
		}

		// Cleanup array
		$exclude_ids = array_map( 'intval', $exclude_ids );

		// Prepare query
		$query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $key, $value );

		// Add SQL exclusion if IDs is filled
		if ( ! empty( $exclude_ids ) ) {
			$query .= " AND post_id NOT IN ('" . implode( "', '", $exclude_ids ) . "')";
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Notify the blog admin of a member changing password, normally via email.
	 *
	 * @param object $member Member Object
	 */
	public function password_change_notification() {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		$stop = apply_filters_ref_array( 'mpt_password_change_notification', array( false, &$this ) );
		if ( $stop === true ) {
			return $stop;
		}

		$recipients = explode( ',', mpt_get_option_value( 'mpt-emails', 'lost_password_admin_mail' ) );
		$recipients = array_map( 'trim', $recipients );
		foreach ( $recipients as $mail ) {
			// send a copy of password change notification to the admin
			// but check to see if it's the admin whose password we're changing, and skip this
			if ( $this->email != $mail ) {
				// The blogname option is escaped with esc_html on the way into the database in sanitize_option
				// we want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

				$subject = mpt_get_option_value( 'mpt-emails', 'lost_password_admin_subject', true );
				$content = mpt_get_option_value( 'mpt-emails', 'lost_password_admin_content', true );

				//Empty subject ? Empty content ? go out.
				if ( empty( $subject ) && empty( $content ) ) {
					return false;
				}

				// Replace with good values
				$subject = str_replace( '%%blog_name%%', $blogname, $subject );
				$content = str_replace( '%%user_name%%', $this->get_user_name(), $content );

				wp_mail( stripslashes( $mail ), $subject, $content );

				return true;
			}
		}

		return false;
	}

	/**
	 * Notify the blog admin of a new member, normally via email.
	 *
	 * @param string $plaintext_pass Optional. The member's plaintext password
	 */
	public function register_notification( $plaintext_pass = '' ) {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		$stop = apply_filters_ref_array( 'mpt_register_notification', array( false, &$this, $plaintext_pass ) );
		if ( $stop === true ) {
			return false;
		}

		$display_name = stripslashes( $this->get_display_name() );
		$email        = stripslashes( $this->email );
		$username     = stripslashes( $this->get_user_name() );

		// The blogname option is escaped with esc_html on the way into the database in
		// sanitize_option
		// we want to reverse this for the plain text arena of emails.

		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Get all options for admin notification email.
		$message    = mpt_get_option_value( 'mpt-emails', 'registration_member_admin_content', true );
		$subject    = mpt_get_option_value( 'mpt-emails', 'registration_member_admin_subject', true );
		$recipients = explode( ',', mpt_get_option_value( 'mpt-emails', 'registration_member_admin_mail' ) );
		$recipients = array_map( 'trim', $recipients );

		//No message ? No object ? No recipient ? Go OUT !!!
		if ( empty( $message ) && empty( $subject ) ) {
			return false;
		}

		$subject = str_replace( '%%blog_name%%', $blogname, $subject );
		$message = str_replace( '%%blog_name%%', $blogname, $message );
		$message = str_replace( '%%display_name%%', $display_name, $message );
		$message = str_replace( '%%user_name%%', $username, $message );
		$message = str_replace( '%%user_email%%', $email, $message );
		$message = str_replace( '%%first_name%%', ( ! empty( $this->first_name ) ) ? stripslashes( $this->first_name ) : "", $message );
		$message = str_replace( '%%last_name%%', ( ! empty( $this->last_name ) ) ? stripslashes( $this->last_name ) : "", $message );

		foreach ( $recipients as $mail ) {
			// Send mail to admin
			@wp_mail( stripslashes( $mail ), $subject, $message );
		}

		if ( empty( $plaintext_pass ) ) {
			return false;
		}

		$message = mpt_get_option_value( 'mpt-emails', 'register_member_content', true );
		$subject = mpt_get_option_value( 'mpt-emails', 'register_member_subject', true );

		if ( empty( $message ) && empty( $subject ) ) {
			return false;
		}

		$subject = str_replace( '%%blog_name%%', $blogname, $subject );
		$message = str_replace( '%%blog_name%%', $blogname, $message );
		$message = str_replace( '%%display_name%%', $display_name, $message );
		$message = str_replace( '%%user_name%%', $username, $message );
		$message = str_replace( '%%user_email%%', $email, $message );
		$message = str_replace( '%%user_password%%', $plaintext_pass, $message );
		$message = str_replace( '%%site_url%%', network_site_url(), $message );
		$message = str_replace( '%%login_url%%', mpt_get_login_permalink(), $message );
		$message = str_replace( '%%first_name%%', ( ! empty( $this->first_name ) ) ? stripslashes( $this->first_name ) : "", $message );
		$message = str_replace( '%%last_name%%', ( ! empty( $this->last_name ) ) ? stripslashes( $this->last_name ) : "", $message );

		// Allow plugins hooks
		$subject = apply_filters( 'mpt_register_notification_subject', $subject, $this );
		$message = apply_filters( 'mpt_register_notification_message', $message, $plaintext_pass, $this );

		return wp_mail( $email, $subject, $message );
	}

	/**
	 * Send notification with confirmation link to member
	 *
	 * @access public
	 *
	 * @return mixed Value.
	 */
	public function register_validation_notification( $key ) {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		$stop = apply_filters_ref_array( 'mpt_register_validation_notification', array( false, &$this, $key ) );
		if ( $stop === true ) {
			return false;
		}
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$username     = stripslashes( $this->get_user_name() );
		$display_name = stripslashes( $this->get_display_name() );
		$email        = stripslashes( $this->email );

		// Get all options for admin notification email.
		$message = mpt_get_option_value( 'mpt-emails', 'register_member_validation_content', true );
		$subject = mpt_get_option_value( 'mpt-emails', 'register_member_validation_subject', true );

		if ( empty( $message ) && empty( $subject ) ) {
			return false;
		}

		// Build message text
		$subject = str_replace( '%%blog_name%%', $blogname, $subject );
		$message = str_replace( '%%blog_name%%', $blogname, $message );
		$message = str_replace( '%%display_name%%', $display_name, $message );
		$message = str_replace( '%%user_name%%', $username, $message );
		$message = str_replace( '%%site_url%%', network_site_url(), $message );
		$message = str_replace( '%%confirm_register_link%%', '' . add_query_arg( array(
				'mpt-action' => 'validation-member-registration',
				'ID'         => $this->id,
				'key'        => $key
			), mpt_get_registration_permalink() ) . '', $message );
		$message = str_replace( '%%first_name%%', ( ! empty( $this->first_name ) ) ? stripslashes( $this->first_name ) : "", $message );
		$message = str_replace( '%%last_name%%', ( ! empty( $this->last_name ) ) ? stripslashes( $this->last_name ) : "", $message );

		// Allow plugins hooks
		$subject = apply_filters( 'mpt_register_validation_notification_subject', $subject, $this );
		$message = apply_filters( 'mpt_register_validation_notification_message', $message, $key, $this );

		return wp_mail( $email, $subject, $message );
	}

	/**
	 * Get better display name, first name, last name, username, email or id...
	 */
	public function get_display_name() {
		if ( ! $this->exists() ) {// Valid instance member ?
			return '';
		}

		// Build post title
		if ( ! empty( $this->last_name ) || ! empty( $this->last_name ) ) {
			$separator    = ( ! empty( $this->last_name ) && ! empty( $this->last_name ) ) ? ' ' : '';
			$display_name = $this->last_name . $separator . $this->first_name;
		} elseif ( ! empty( $this->username ) ) {
			$display_name = $this->username;
		} elseif ( ! empty( $this->email ) ) {
			$display_name = $this->email;
		} else {
			$display_name = $this->id;
		}

		return apply_filters( 'mpt_get_display_name', $display_name, $this );
	}

	/**
	 * Get "username" depending on if is email login activated or not
	 *
	 * @since 1.0.2
	 *
	 * @author Maxime CULEA
	 *
	 * @return string, user's email or user_name
	 */
	public function get_user_name() {
		return mpt_is_allowed_email_signon() ? $this->email : $this->username;
	}

	/**
	 * Build a proper post title, using filled values when it disponible
	 */
	public function regenerate_post_title( $force_refresh = false ) {
		global $wpdb;

		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		// Refresh values from DB ?
		if ( $force_refresh == true ) {
			$this->fill_by( 'id', $this->id );
		}

		// Get display name
		$display_name = $this->get_display_name();

		// Allow plugin change display name
		$display_name = apply_filters( 'mpt_regenerate_post_title', $display_name, $this );

		// update DB
		$wpdb->update( $wpdb->posts, array(
			'post_title' => $display_name,
			'post_name'  => wp_unique_post_slug( sanitize_title( $display_name ), $this->id, $this->_object->post_status, MPT_CPT_NAME, $this->_object->post_parent )
		), array( 'ID' => $this->id ) );

		// Refresh cache
		clean_post_cache( $this->id );

		return true;
	}

	/**
	 * Send member email with reset password link
	 *
	 * @access public
	 *
	 * @return mixed Value.
	 */
	public function reset_password_link() {
		do_action( 'mpt_retrieve_password', $this->id );

		$allow = apply_filters( 'mpt_allow_password_reset', true, $this->id );
		if ( $allow == false ) {
			return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this member' ) );
		} elseif ( is_wp_error( $allow ) ) {
			return $allow;
		}

		// Buid new member activation key
		$key = get_post_meta( $this->id, 'activation_key', true );
		if ( empty( $key ) ) {
			// Generate something random for a key...
			$key = wp_generate_password( 20, false );

			// Allow events
			do_action( 'mpt_retrieve_password_key', $this->id, $key );

			// Now insert the new key into the db
			update_post_meta( $this->id, 'activation_key', $key );
		}

		$stop = apply_filters_ref_array( 'mpt_reset_password_notification', array( false, &$this, $key ) );
		if ( $stop === true ) {
			return false;
		}

		// Get all options for admin notification email.
		$message = mpt_get_option_value( 'mpt-emails', 'lost_password_member_content', true );
		$subject = mpt_get_option_value( 'mpt-emails', 'lost_password_member_subject', true );

		//No message ? No object ? Go OUT !!!
		if ( empty( $message ) && empty( $subject ) ) {
			return false;
		}

		// Build title
		$subject = str_replace( '%%blog_name%%', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $subject );

		// Build message text
		$message = str_replace( '%%site_url%%', network_site_url(), $message );
		$message = str_replace( '%%user_name%%', $this->get_user_name(), $message );
		$message = str_replace( '%%reset_pwd_link%%', add_query_arg( array(
			'mpt-action' => 'lost-password',
			'key'        => $key,
			'id'         => $this->id
		), mpt_get_lost_password_permalink() ), $message );

		// Allow plugins hooks
		$subject = apply_filters( 'mpt_retrieve_password_title', $subject );
		$message = apply_filters( 'mpt_retrieve_password_message', $message, $key );

		if ( $message && ! wp_mail( $this->email, $subject, $message ) ) {
			wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );
		}

		return true;
	}

	/**
	 * Set up capability object properties.
	 *
	 * @access private
	 *
	 */
	private function _init_caps() {
		// Caps is always an array
		$this->caps = array();

		// Get current role of member
		$terms = get_the_terms( $this->id, MPT_TAXO_NAME );
		if ( $terms != false && is_array( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$this->caps[ $term->slug ] = 1;
			}
		}

		$this->get_role_caps();
	}

	/**
	 * Retrieve all of the role capabilities and merge with individual capabilities.
	 *
	 * All of the capabilities of the roles the member belongs to are merged with
	 * the members individual roles. This also means that the member can be denied
	 * specific roles that their role might have, but the specific member isn't
	 * granted permission to.
	 *
	 * @access public
	 */
	public function get_role_caps() {
		// Filter out caps that are not role names and assign to $this->roles
		if ( is_array( $this->caps ) ) {
			$this->roles = array_filter( array_keys( $this->caps ), array( 'MPT_Roles', 'is_role' ) );
		}

		// Build $allcaps from role caps, overlay member's $caps
		$this->allcaps = array();
		foreach ( (array) $this->roles as $role ) {
			$the_role      = MPT_Roles::get_role( $role );
			$this->allcaps = array_merge( (array) $this->allcaps, (array) $the_role->capabilities );
		}
		$this->allcaps = array_merge( (array) $this->allcaps, (array) $this->caps );
	}

	/**
	 * Add role to member.
	 *
	 * Updates the member's meta data option with capabilities and roles.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public function add_role( $role ) {
		$this->caps[ $role ] = true;

		$this->_refresh_term_associations();
		$this->get_role_caps();
	}

	/**
	 * Remove role from member.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public function remove_role( $role ) {
		if ( ! in_array( $role, $this->roles ) ) {
			return false;
		}

		unset( $this->caps[ $role ] );

		$this->_refresh_term_associations();
		$this->get_role_caps();

		return true;
	}

	/**
	 * Set the role of the member.
	 *
	 * This will remove the previous roles of the member and assign the member the
	 * new one. You can set the role to an empty string and it will remove all
	 * of the roles from the member.
	 *
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public function set_role( $role ) {
		if ( 1 == count( $this->roles ) && $role == current( $this->roles ) ) {
			return false;
		}

		foreach ( (array) $this->roles as $oldrole ) {
			unset( $this->caps[ $oldrole ] );
		}

		if ( ! empty( $role ) ) {
			$this->caps[ $role ] = true;
			$this->roles         = array( $role => true );
		} else {
			$this->roles = false;
		}

		$this->_refresh_term_associations();
		$this->get_role_caps();

		do_action( 'set_member_role', $this->id, $role );

		return true;
	}

	/**
	 * Whether member has capability or role name.
	 *
	 * This is useful for looking up whether the member has a specific role
	 * assigned to the member. The second optional parameter can also be used to
	 * check for capabilities against a specific object, such as a post or member.
	 *
	 * @access public
	 *
	 * @param string|int $cap Capability or role name to search.
	 *
	 * @return bool True, if member has capability; false, if member does not have
	 * capability.
	 */
	function has_cap( $cap ) {
		$args = array_slice( func_get_args(), 1 );
		$args = array_merge( array( $cap, $this->id ), $args );
		$caps = call_user_func_array( 'map_meta_cap', $args );

		// Must have ALL requested caps
		$capabilities          = apply_filters( 'member_has_cap', $this->allcaps, $caps, $args );
		$capabilities['exist'] = true;
		// Everyone is allowed to exist
		foreach ( (array) $caps as $cap ) {
			if ( empty( $capabilities[ $cap ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * _refresh_term_associations
	 *
	 * @access private
	 *
	 * @return mixed Value.
	 */
	private function _refresh_term_associations() {
		// Loop on role, find term data, set relation
		$relation_ids = array();
		foreach ( $this->caps as $_role => $_value ) {
			$term = get_term_by( 'slug', $_role, MPT_TAXO_NAME );
			if ( $term != false ) {
				$relation_ids[] = (int) $term->term_id;
			}
		}

		// Set relation
		wp_set_object_terms( $this->id, $relation_ids, MPT_TAXO_NAME, false );
	}

	public function is_pending_member() {
		if ( ! $this->exists() ) {// Valid instance member ?
			return false;
		}

		if ( $this->_object->post_status === 'pending' ) {
			return true;
		}

		return false;
	}

}
