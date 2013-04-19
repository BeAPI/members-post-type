<?php
class MPT_Member {
	public static $core_fields = array('email', 'username', 'first_name', 'last_name', 'password');

	// Core public fields
	public $id 			= 0;
	public $email 		= null;
	public $username 	= null;
	public $first_name 	= null;
	public $last_name 	= null;
	public $password 	= null;

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
	}
	
	/**
	 * Test if member exist
	 * 
	 * @return bool False on failure, True on success
	 */
	public function exists() {
		if ( $this->_object == false || is_wp_error($this->_object) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Retrieve member info by a given field
	 *
	 * @param string $field The field to retrieve the member with.  id | email | username
	 * @param int|string $value A value for $field.  A member ID, email address, or username.
	 * @return bool False on failure, True on success
	 */
	public function fill_by($field, $value) {
		switch ($field) {
			case 'id':
				$this->_object = get_post($value);
				break;
			case 'email':
			case 'username':
			case 'activation_key':
				$id = self::get_id_from_key_value( $field, $value );
				if ( $id == 0 ) {
					return false;
				}
				$this->_object = get_post($id);
				break;
			default:
				return false;
		}
		
		
		if ( !$this->exists() ) {
			return false;
		}

		// Set ID
		$this->id = $this->_object->ID;
		
		// Set core fields
		foreach( self::$core_fields as $key ) {
			$this->$key = get_post_meta( $this->id, $key, true );
		}

		// Set caps
		$this->_init_caps();

		return true;
	}

	/**
	 * Update post meta value of members
	 * 
	 * @param string $key   [description]
	 * @param boolean $value [description]
	 */
	public function set_meta_value( $key = '', $value = null ) {
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}

		if ( $key == 'password' ) { // Forbide, use specific method
			return false;
		}

		if( !in_array($key, self::$core_fields) ) { // Allow only core member fields
			return false;
		}

		// Check if email is unique, when option is enabled, restore old value if already exist.
		if ( $key == 'email' && mpt_is_unique_email() && $this->email != $value && mpt_email_exists($value) ) {
			return false; // TODO: WP ERROR ?
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
	public function set_password( $password = '' ) {
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}

		if ( empty($password) ) { // Valid password ?
			return false;
		}
		
		$stop = apply_filters_ref_array('mpt_set_password', array(false, $password, &$this) );
		if ( $stop !== false ) {
			return $stop;
		}

		$hash = wp_hash_password($password);

		update_post_meta( $this->id, 'password', $hash );
		delete_post_meta( $this->id, 'activation_key' );

		return true;
	}

	/**
	 * Private method for get member id from key/value, work post meta table
	 * 
	 * @param  string $key   [description]
	 * @param  string $value [description]
	 * @param  array $exclude_ids [description]
	 * @return integer        [description]
	 */
	public static function get_id_from_key_value( $key = '', $value = '', $exclude_ids = array() ) {
		global $wpdb;
		
		// Convert to array if necessary
		if ( !is_array($exclude_ids) ) {
			$exclude_ids = (array) $exclude_ids;
		}
		
		// Cleanup array
		$exclude_ids = array_map('intval', $exclude_ids );
		
		// Prepare query
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $key, $value);
		
		// Add SQL exclusion if IDs is filled
		if ( !empty($exclude_ids) ) {
			$query .= " AND post_id NOT IN ('" . implode( "', '", $term_ids ) . "')";
		}
		
		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Notify the blog admin of a member changing password, normally via email.
	 *
	 * @param object $member Member Object
	 */
	public function password_change_notification() {
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}
		
		$stop = apply_filters_ref_array('mpt_password_change_notification', array(false, &$this) );
		if ( $stop === true ) {
			return $stop;
		}
		
		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( $this->email != get_option('admin_email') ) {
			$message = sprintf(__('Password Lost and Changed for member: %s'), $this->username) . "\r\n";
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), $blogname), $message);
			return true;
		}
		
		return false;
	}

	/**
	 * Notify the blog admin of a new member, normally via email.
	 *
	 * @param string $plaintext_pass Optional. The member's plaintext password
	 */
	public function register_notification($plaintext_pass = '') {
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}
		
		$stop = apply_filters_ref_array('mpt_register_notification', array(false, &$this, $plaintext_pass) );
		if ( $stop === true ) {
			return false;
		}
		
		$username = stripslashes($this->username);
		$email = stripslashes($this->email);

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New member registration on your site %s:', 'mpt'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s', 'mpt'), $username) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s', 'mpt'), $email) . "\r\n";

		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New Member Registration', 'mpt'), $blogname), $message);

		if ( empty($plaintext_pass) ) {
			return false;
		}
		
		$message  = sprintf(__('Username: %s', 'mpt'), $username) . "\r\n";
		$message .= sprintf(__('Password: %s', 'mpt'), $plaintext_pass) . "\r\n";
		$message .= mpt_get_login_permalink() . "\r\n";
		
		return wp_mail($email, sprintf(__('[%s] Your username and password', 'mpt'), $blogname), $message);
	}
	
	/**
	 * Get better display name, first name, last name, username, email or id...
	 */
	function get_display_name() {
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}
		
		// Build post title
		if ( !empty($this->last_name) || !empty($this->last_name) ) {
			$separator = ( !empty($this->last_name) && !empty($this->last_name) ) ? ' ' : '';
			$display_name = $this->last_name . $separator . $this->first_name;
		} elseif( !empty($this->username) ) {
			$display_name = $this->username;
		} elseif( !empty($this->email) ) {
			$display_name = $this->email;
		} else {
			$display_name = $this->id;
		}
		
		return $display_name;
	}
	
	/**
	 * Build a proper post title, using filled values when it disponible
	 */
	public function regenerate_post_title( $force_refresh = false ) {
		global $wpdb;
		
		if ( !$this->exists() ) { // Valid instance member ?
			return false;
		}
		
		// Refresh values from DB ?
		if ( $force_refresh == true ) {
			$this->fill_by('id', $this->id);
		}
		
		// update DB
		$wpdb->update( $wpdb->posts, array('post_title' => $this->get_display_name()), array('ID' => $this->id) );
		
		// Refresh cache
		clean_post_cache($this->id);
		
		return true;
	}
	
	public function reset_password_link() {
		do_action('mpt_retrieve_password', $this->id);
		
		$allow = apply_filters('mpt_allow_password_reset', true, $this->id);
		if ( $allow == false ) {
			return new WP_Error('no_password_reset', __('Password reset is not allowed for this member'));
		} elseif ( is_wp_error($allow) ) {
			return $allow;
		}
		
		// Buid new member activation key
		$key = get_post_meta( $this->id, 'activation_key', true );
		if ( empty($key) ) {
			// Generate something random for a key...
			$key = wp_generate_password(20, false);
			
			// Allow events
			do_action('mpt_retrieve_password_key', $this->id, $key);
			
			// Now insert the new key into the db
			update_post_meta( $this->id, 'activation_key', $key );
		}
		
		$stop = apply_filters_ref_array('mpt_reset_password_notification', array(false, &$this, $key) );
		if ( $stop === true ) {
			return false;
		}
		
		// Build message text
		$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		$message .= network_site_url() . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $this->get_display_name()) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . add_query_arg( array('mpt-action' => 'lost-password', 'key' => $key, 'id' => $this->id), mpt_get_lost_password_permalink()) . ">\r\n";
		
		// Build title
		$title = sprintf( __('[%s] Password Reset'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) );
		
		// Allow plugins hooks
		$title = apply_filters('mpt_retrieve_password_title', $title);
		$message = apply_filters('mpt_retrieve_password_message', $message, $key);
		
		if ( $message && !wp_mail($this->email, $title, $message) )
			wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );
		
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
		if ( $terms != false && is_array($terms) && !is_wp_error($terms) ) {
			foreach( $terms as $term ) {
				$this->caps[$term->slug] = 1;
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
	 * @uses $mpt_roles
	 * @access public
	 */
	public function get_role_caps() {
		global $mpt_roles;

		if ( ! isset( $mpt_roles ) )
			$mpt_roles = new MPT_Roles();

		// Filter out caps that are not role names and assign to $this->roles
		if ( is_array( $this->caps ) )
			$this->roles = array_filter( array_keys( $this->caps ), array( $mpt_roles, 'is_role' ) );
		
		// Build $allcaps from role caps, overlay member's $caps
		$this->allcaps = array();
		foreach ( (array) $this->roles as $role ) {
			$the_role = $mpt_roles->get_role( $role );
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
		$this->caps[$role] = true;

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
		if ( !in_array($role, $this->roles) )
			return false;

		unset( $this->caps[$role] );

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
		if ( 1 == count( $this->roles ) && $role == current( $this->roles ) )
			return false;

		foreach ( (array) $this->roles as $oldrole )
			unset( $this->caps[$oldrole] );

		if ( !empty( $role ) ) {
			$this->caps[$role] = true;
			$this->roles = array( $role => true );
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
	 * @return bool True, if member has capability; false, if member does not have capability.
	 */
	function has_cap( $cap ) {
		$args = array_slice( func_get_args(), 1 );
		$args = array_merge( array( $cap, $this->id ), $args );
		$caps = call_user_func_array( 'map_meta_cap', $args ); // TODO, keep it ?

		// Must have ALL requested caps
		$capabilities = apply_filters( 'member_has_cap', $this->allcaps, $caps, $args );
		$capabilities['exist'] = true; // Everyone is allowed to exist
		foreach ( (array) $caps as $cap ) {
			if ( empty( $capabilities[ $cap ] ) )
				return false;
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
		foreach( $this->caps as $_role => $_value ) {
			$term = get_term_by( 'slug', $_role, MPT_TAXO_NAME );
			if ( $term != false ) {
				$relation_ids[] = (int) $term->term_id;
			}
		}

		// Set relation
		wp_set_object_terms( $this->id, $relation_ids, MPT_TAXO_NAME, false );
	}
}
