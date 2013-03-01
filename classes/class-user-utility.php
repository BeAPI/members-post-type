<?php
class MPT_User_Utility {
	public function __construct() {}
	
	/**
	 * Authenticate user with remember capability.
	 *
	 * The credentials is an array that has 'user_login', 'user_password', and
	 * 'remember' indices. If the credentials is not given, then the log in form
	 * will be assumed and used if set.
	 *
	 * The various authentication cookies will be set by this function and will be
	 * set for a longer period depending on if the 'remember' credential is set to
	 * true.
	 *
	 * @since 2.5.0
	 *
	 * @param array $credentials Optional. User info in order to sign on.
	 * @param bool $secure_cookie Optional. Whether to use secure cookie.
	 * @return object Either WP_Error on failure, or WP_User on success.
	 */
	function signon( $credentials = '', $secure_cookie = '' ) {
		if ( empty($credentials) ) {
			if ( ! empty($_POST['log']) ) {
				$credentials['user_login'] = $_POST['log'];
			}
			if ( ! empty($_POST['pwd']) ) {
				$credentials['user_password'] = $_POST['pwd'];
			}
			if ( ! empty($_POST['rememberme']) ) {
				$credentials['remember'] = $_POST['rememberme'];
			}
		}
	
		if ( !empty($credentials['remember']) ) {
			$credentials['remember'] = true;
		} else {
			$credentials['remember'] = false;
		}
	
		if ( '' === $secure_cookie ) {
			$secure_cookie = is_ssl();
		}
	
		$secure_cookie = apply_filters('mpt_secure_signon_cookie', $secure_cookie, $credentials);
	
		global $auth_secure_cookie; // XXX ugly hack to pass this to authenticate_cookie
		$auth_secure_cookie = $secure_cookie;
	
		add_filter('mpt_authenticate', array(__CLASS__, 'authenticate_cookie'), 30, 3);
	
		$user = self::authenticate($credentials['user_login'], $credentials['user_password']);
	
		if ( is_wp_error($user) ) {
			if ( $user->get_error_codes() == array('empty_username', 'empty_password') ) {
				$user = new WP_Error('', '');
			}
	
			return $user;
		}
	
		self::set_auth_cookie($user->id, $credentials['remember'], $secure_cookie);
		do_action('mpt_login', $credentials['user_login']);
		return $user;
	}

	/**
	 * Authenticate the user using the WordPress auth cookie.
	 */
	public static function authenticate_cookie($user, $username, $password) {
		if ( is_a($user, 'MPT_User') ) { return $user; }
	
		if ( empty($username) && empty($password) ) {
			$user_id = self::validate_auth_cookie();
			if ( $user_id ) {
				return new MPT_User($user_id);
			}
	
			global $auth_secure_cookie;
	
			if ( $auth_secure_cookie ) {
				$auth_cookie = SECURE_AUTH_COOKIE;
			} else {
				$auth_cookie = AUTH_COOKIE;
			}	
			
			if ( !empty($_COOKIE[$auth_cookie]) ) {
				return new WP_Error('expired_session', __('Please log in again.'));
			}
			
			// If the cookie is not set, be silent.
		}
	
		return $user;
	}

	/**
	 * Checks a user's login information and logs them in if it checks out.
	 *
	 * @since 2.5.0
	 *
	 * @param string $username User's username
	 * @param string $password User's password
	 * @return WP_Error|MPT_User MPT_User object if login successful, otherwise WP_Error object.
	 */
	public static function authenticate($username, $password) {
		$username = sanitize_user($username);
		$password = trim($password);
	
		add_filter('mpt_authenticate', array(__CLASS__, 'authenticate_username_password'), 20, 3);
		$user = apply_filters('mpt_authenticate', null, $username, $password);
	
		if ( $user == null ) {
			// TODO what should the error message be? (Or would these even happen?)
			// Only needed if all authentication handlers fail to return anything.
			$user = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));
		}
	
		$ignore_codes = array('empty_username', 'empty_password');
	
		if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes) ) {
			do_action('mpt_login_failed', $username);
		}
	
		return $user;
	}
	
	/**
	 * Authenticate the user using the username and password.
	 */
	function authenticate_username_password($user, $username, $password) {
		if ( is_a($user, 'MPT_User') ) {
			return $user;
		}
	
		if ( empty($username) || empty($password) ) {
			$error = new WP_Error();
	
			if ( empty($username) ) {
				$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));
			}
			if ( empty($password) ) {
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
			}
			
			return $error;
		}
		
		$userdata = new MPT_User();
		$userdata->fill_by( 'username', $username );
	
		if ( !$userdata->exists() ) {
			return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), site_url('wp-login.php?action=lostpassword', 'login')));
		}
		
		$userdata = apply_filters('mpt_authenticate_user', $userdata, $password);
		if ( is_wp_error($userdata) ) {
			return $userdata;
		}
	
		if ( !wp_check_password($password, $userdata->password, $userdata->id) ) {
			return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
			$username, site_url( 'wp-login.php?action=lostpassword', 'login' ) ) );
		}
	
		return $userdata;
	}

	/**
	 * Checks if the current visitor is a logged in user.
	 *
	 * @return bool True if user is logged in, false if not logged in.
	 */
	public static function is_logged_in() {
		$user = self::get_current_user();
		if ( $user->id == 0 ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Retrieve the current user object.
	 *
	 * @return MPT_User Current user MPT_User object
	 */
	public static function get_current_user() {
		global $current_mpt_user;
		self::get_currentuserinfo();
		
		return $current_mpt_user;
	}
	
	/**
	 * Changes the current user by ID or name.
	 *
	 * Set $id to null and specify a name if you do not know a user's ID.
	 *
	 * Some WordPress functionality is based on the current user and not based on
	 * the signed in user. Therefore, it opens the ability to edit and perform
	 * actions on users who aren't signed in.
	 *
	 * @global object $current_mpt_user The current user object which holds the user data.
	 * @uses do_action() Calls 'set_current_user' hook after setting the current user.
	 *
	 * @param int $id User ID
	 * @param string $name User's username
	 * @return MPT_User Current user User object
	 */
	public static function set_current_user($id, $name = '') {
		global $current_mpt_user;
	
		if ( isset($current_mpt_user) && ($id == $current_mpt_user->id) ) {
			return $current_mpt_user;
		}
	
		if ( !empty($name) ) {
			$current_mpt_user = new MPT_User();
			$current_mpt_user = fill('username', $name);
		} else {
			$current_mpt_user = new MPT_User($id);
		}
		
		do_action('set_mpt_current_user');
	
		return $current_mpt_user;
	}
		
	/**
	 * Populate global variables with information about the currently logged in user.
	 *
	 * Will set the current user, if the current user is not set. The current user
	 * will be set to the logged in person. If no user is logged in, then it will
	 * set the current user to 0, which is invalid and won't have any permissions.
	 *
	 * @uses $current_mpt_user Checks if the current user is set
	 * @uses wp_validate_auth_cookie() Retrieves current logged in user.
	 *
	 * @return bool|null False on XMLRPC Request and invalid auth cookie. Null when current user set
	 */
	public static function get_currentuserinfo() {
		global $current_mpt_user;
		
		if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST )
			return false;
	
		if ( ! empty($current_mpt_user) )
			return false;
	
		if ( ! $user = self::validate_auth_cookie() ) {
			if ( empty($_COOKIE[LOGGED_IN_COOKIE]) || !$user = self::validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in') ) {
				self::set_current_user(0);
				return false;
			}
		}
	
		self::set_current_user($user);
		return true;
	}
	
	/**
	 * Log the current user out.
	 *
	 */
	public static function logout() {
		self::clear_auth_cookie();
	}

	/**
	 * Validates authentication cookie.
	 *
	 * The checks include making sure that the authentication cookie is set and
	 * pulling in the contents (if $cookie is not used).
	 *
	 * Makes sure the cookie is not expired. Verifies the hash in cookie is what is
	 * should be and compares the two.
	 *
	 * @param string $cookie Optional. If used, will validate contents instead of cookie's
	 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
	 * @return bool|int False if invalid cookie, User ID if valid.
	 */
	public static function validate_auth_cookie($cookie = '', $scheme = '') {
		if ( ! $cookie_elements = self::parse_auth_cookie($cookie, $scheme) ) {
			do_action('mpt_auth_cookie_malformed', $cookie, $scheme);
			return false;
		}

		extract($cookie_elements, EXTR_OVERWRITE);

		$expired = $expiration;

		// Allow a grace period for POST and AJAX requests
		if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] )
			$expired += 3600;

		// Quick check to see if an honest cookie has expired
		if ( $expired < time() ) {
			do_action('mpt_auth_cookie_expired', $cookie_elements);
			return false;
		}
		
		$userdata = new MPT_User();
		$userdata->fill_by( 'username', $username );
		if ( ! $userdata->exists() ) {
			do_action('mpt_auth_cookie_bad_username', $cookie_elements);
			return false;
		}

		$pass_frag = substr($userdata->password, 8, 4);
		
		$key = wp_hash($username . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', $username . '|' . $expiration, $key);

		if ( $hmac != $hash ) {
			do_action('mpt_auth_cookie_bad_hash', $cookie_elements);
			return false;
		}

		if ( $expiration < time() ) // AJAX/POST grace period set above
			$GLOBALS['login_grace_period'] = 1;

		do_action('mpt_auth_cookie_valid', $cookie_elements, $user);

		return $user->id;
	}

	/**
	 * Parse a cookie into its components
	 *
	 * @since 2.7
	 *
	 * @param string $cookie
	 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
	 * @return array Authentication cookie components
	 */
	public static function parse_auth_cookie($cookie = '', $scheme = '') {
		if ( empty($cookie) ) {
			switch ($scheme){
				case 'auth':
					$cookie_name = AUTH_COOKIE;
					break;
				case 'secure_auth':
					$cookie_name = SECURE_AUTH_COOKIE;
					break;
				case "logged_in":
					$cookie_name = LOGGED_IN_COOKIE;
					break;
				default:
					if ( is_ssl() ) {
						$cookie_name = SECURE_AUTH_COOKIE;
						$scheme = 'secure_auth';
					} else {
						$cookie_name = AUTH_COOKIE;
						$scheme = 'auth';
					}
			}

			if ( empty($_COOKIE[$cookie_name]) )
				return false;
			$cookie = $_COOKIE[$cookie_name];
		}

		$cookie_elements = explode('|', $cookie);
		if ( count($cookie_elements) != 3 )
			return false;

		list($username, $expiration, $hmac) = $cookie_elements;

		return compact('username', 'expiration', 'hmac', 'scheme');
	}

	/**
	 * Sets the authentication cookies based User ID.
	 *
	 * The $remember parameter increases the time that the cookie will be kept. The
	 * default the cookie is kept without remembering is two days. When $remember is
	 * set, the cookies will be kept for 14 days or two weeks.
	 *
	 * @param int $user_id User ID
	 * @param bool $remember Whether to remember the user
	 */
	public static function set_auth_cookie($user_id, $remember = false, $secure = '') {
		if ( $remember ) {
			$expiration = $expire = time() + apply_filters('mpt_auth_cookie_expiration', 1209600, $user_id, $remember);
		} else {
			$expiration = time() + apply_filters('mpt_auth_cookie_expiration', 172800, $user_id, $remember);
			$expire = 0;
		}

		if ( '' === $secure )
			$secure = is_ssl();

		$secure = apply_filters('mpt_secure_auth_cookie', $secure, $user_id);
		$secure_logged_in_cookie = apply_filters('mpt_secure_logged_in_cookie', false, $user_id, $secure);

		if ( $secure ) {
			$auth_cookie_name = SECURE_AUTH_COOKIE;
			$scheme = 'secure_auth';
		} else {
			$auth_cookie_name = AUTH_COOKIE;
			$scheme = 'auth';
		}

		$auth_cookie = self::generate_auth_cookie($user_id, $expiration, $scheme);
		$logged_in_cookie = self::generate_auth_cookie($user_id, $expiration, 'logged_in');

		do_action('mpt_set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme);
		do_action('mpt_set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in');
		
		setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
		setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
		setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
		}
	}
	
	/**
	 * Generate authentication cookie contents.
	 *
	 * @uses apply_filters() Calls 'auth_cookie' hook on $cookie contents, User ID
	 *		and expiration of cookie.
	 *
	 * @param int $user_id User ID
	 * @param int $expiration Cookie expiration in seconds
	 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
	 * @return string Authentication cookie contents
	 */
	public static function generate_auth_cookie($user_id, $expiration, $scheme = 'auth') {
		$user = new MPT_User($user_id);

		$pass_frag = substr($user->password, 8, 4);

		$key = wp_hash($user->username . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', $user->username . '|' . $expiration, $key);

		$cookie = $user->username . '|' . $expiration . '|' . $hash;

		return apply_filters('mpt_auth_cookie', $cookie, $user_id, $expiration, $scheme);
	}

	/**
	 * Removes all of the cookies associated with authentication.
	 *
	 */
	public static function clear_auth_cookie() {
		do_action('mpt_clear_auth_cookie');

		setcookie(AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(SECURE_AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
		setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
	}
}