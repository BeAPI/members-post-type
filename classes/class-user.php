<?php
class MPT_User {
	public $core_fields = array('email', 'username', 'first_name', 'last_name', 'password');

	// Core public fields
	public $id 			= 0;
	public $email 		= null;
	public $username 	= null;
	public $first_name 	= null;
	public $last_name 	= null;
	public $password 	= null;

	// Private object
	private $_object 	= false;

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
	 * Retrieve user info by a given field
	 *
	 * @param string $field The field to retrieve the user with.  id | email | username
	 * @param int|string $value A value for $field.  A user ID, email address, or username.
	 * @return bool False on failure, True on success
	 */
	public function fill_by($field, $value) {
		switch ($field) {
			case 'id':
				$this->_object = get_post($value);
				break;
			case 'email':
			case 'username':
				$id = $this->_get_id_from_key_value( $field, $value );
				if ( $id == 0 ) {
					return false;
				}

				$this->_object = get_post($id);
				break;
			default:
				return false;
		}

		if ( $this->_object == false || is_wp_error($this->_object) ) {
			return false;
		}

		// Set ID
		$this->id = $this->_object->ID;

		// Set core fields
		foreach( self::$core_fields as $key ) {
			$this->$key = get_post_meta( $this->id, $key, true );
		} 

		return true;
	}

	/**
	 * Update post meta value of members
	 * 
	 * @param string $key   [description]
	 * @param boolean $value [description]
	 */
	public function set_meta_value( $key = '', $value = null ) {
		if ( $this->id == 0 ) { // Valid instance user ?
			return false;
		}

		if ( $key == 'password' ) { // Forbide, use specific method
			return false;
		}

		if( !in_array($key, self::$core_fields) ) { // Allow only core user fields
			return false;
		}

		return update_post_meta( $this->id, $key, $value );
	}

	/**
	 * Updates the user's password with a new encrypted one.
	 *
	 * For integration with other applications, this function can be overwritten to
	 * instead use the other package password checking algorithm.
	 *
	 * @param string $password The plaintext new user password
	 */
	public function set_password( $password = '' ) {
		if ( $this->id == 0 ) { // Valid instance user ?
			return false;
		}

		if ( empty($password) ) { // Valid password ?
			return false;
		}

		$hash = wp_hash_password($password);

		update_post_meta( $this->id, 'password', $password );
		delete_post_meta( $this->id, 'user_activation_key' );

		return true;
	}

	/**
	 * Private method for get member id from key/value, work post meta table
	 * 
	 * @param  string $key   [description]
	 * @param  string $value [description]
	 * @return integer        [description]
	 */
	private function _get_id_from_key_value( $key = '', $value = '' ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $key, $value) );
	}

	/**
	 * Notify the blog admin of a user changing password, normally via email.
	 * TODO: add hooks
	 *
	 * @param object $user User Object
	 */
	public function password_change_notification() {
		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( $this->email != get_option('admin_email') ) {
			$message = sprintf(__('Password Lost and Changed for user: %s'), $this->username) . "\r\n";
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), $blogname), $message);
		}
	}

	/**
	 * Notify the blog admin of a new user, normally via email.
	 * TODO: Add hooks
	 *
	 * @param string $plaintext_pass Optional. The user's plaintext password
	 */
	public function new_user_notification($plaintext_pass = '') {
		$username = stripslashes($this->username);
		$email = stripslashes($this->email);

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $username) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $email) . "\r\n";

		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

		if ( empty($plaintext_pass) ) {
			return false;
		}

		$message  = sprintf(__('Username: %s'), $username) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		$message .= wp_login_url() . "\r\n"; // TODO use custom function

		return wp_mail($email, sprintf(__('[%s] Your username and password'), $blogname), $message);
	}
}