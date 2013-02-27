<?php
class MPT_User {
	public function __construct() {

	}

	/**
	 * Log the current user out.
	 *
	 * @since 2.5.0
	 */
	function logout() {
		wp_clear_auth_cookie();
	}

	/**
	 * Retrieve user info by a given field
	 *
	 *
	 * @param string $field The field to retrieve the user with.  id | slug | email | login
	 * @param int|string $value A value for $field.  A user ID, slug, email address, or login name.
	 * @return bool|object False on failure, User DB row object
	 */
	function get_user_by($field, $value) {
		global $wpdb;

		switch ($field) {
			case 'id':
				return get_userdata($value);
				break;
			case 'slug':
				$user_id = wp_cache_get($value, 'userslugs');
				$field = 'user_nicename';
				break;
			case 'email':
				$user_id = wp_cache_get($value, 'useremail');
				$field = 'user_email';
				break;
			case 'login':
				$value = sanitize_user( $value );
				$user_id = wp_cache_get($value, 'userlogins');
				$field = 'user_login';
				break;
			default:
				return false;
		}

		 if ( false !== $user_id )
			return get_userdata($user_id);

		if ( !$user = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->users WHERE $field = %s", $value) ) )
			return false;

		return $user;
	}

	/**
	 * Notify the blog admin of a user changing password, normally via email.
	 *
	 * @since 2.7
	 *
	 * @param object $user User Object
	 */
	function password_change_notification(&$user) {
		// send a copy of password change notification to the admin
		// but check to see if it's the admin whose password we're changing, and skip this
		if ( $user->user_email != get_option('admin_email') ) {
			$message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), $blogname), $message);
		}
	}

	/**
	 * Notify the blog admin of a new user, normally via email.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id User ID
	 * @param string $plaintext_pass Optional. The user's plaintext password
	 */
	function new_user_notification($user_id, $plaintext_pass = '') {
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

		if ( empty($plaintext_pass) )
			return;

		$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		$message .= wp_login_url() . "\r\n";

		wp_mail($user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);

	}

	/**
	 * Updates the user's password with a new encrypted one.
	 *
	 * For integration with other applications, this function can be overwritten to
	 * instead use the other package password checking algorithm.
	 *
	 * @since 2.5
	 * @uses $wpdb WordPress database object for queries
	 * @uses wp_hash_password() Used to encrypt the user's password before passing to the database
	 *
	 * @param string $password The plaintext new user password
	 * @param int $user_id User ID
	 */
	function set_password( $password, $user_id ) {
		global $wpdb;

		$hash = wp_hash_password($password);
		$wpdb->update($wpdb->users, array('user_pass' => $hash, 'user_activation_key' => ''), array('ID' => $user_id) );

		wp_cache_delete($user_id, 'users');
	}
}