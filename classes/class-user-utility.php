<?php
class MPT_User_Utility {
	public function __construct() {}

	/**
	 * Checks if the current visitor is a logged in user.
	 *
	 * @return bool True if user is logged in, false if not logged in.
	 */
	public static function is_logged_in() {
		$user = wp_get_current_user();

		if ( $user->id == 0 )
			return false;

		return true;
	}

	/**
	 * Log the current user out.
	 *
	 */
	public static function logout() {
		wp_clear_auth_cookie();
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
		if ( ! $cookie_elements = wp_parse_auth_cookie($cookie, $scheme) ) {
			do_action('auth_cookie_malformed', $cookie, $scheme);
			return false;
		}

		extract($cookie_elements, EXTR_OVERWRITE);

		$expired = $expiration;

		// Allow a grace period for POST and AJAX requests
		if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] )
			$expired += 3600;

		// Quick check to see if an honest cookie has expired
		if ( $expired < time() ) {
			do_action('auth_cookie_expired', $cookie_elements);
			return false;
		}

		$user = get_userdatabylogin($username);
		if ( ! $user ) {
			do_action('auth_cookie_bad_username', $cookie_elements);
			return false;
		}

		$pass_frag = substr($user->user_pass, 8, 4);

		$key = wp_hash($username . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', $username . '|' . $expiration, $key);

		if ( $hmac != $hash ) {
			do_action('auth_cookie_bad_hash', $cookie_elements);
			return false;
		}

		if ( $expiration < time() ) // AJAX/POST grace period set above
			$GLOBALS['login_grace_period'] = 1;

		do_action('auth_cookie_valid', $cookie_elements, $user);

		return $user->ID;
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
		$user = get_userdata($user_id);

		$pass_frag = substr($user->user_pass, 8, 4);

		$key = wp_hash($user->user_login . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', $user->user_login . '|' . $expiration, $key);

		$cookie = $user->user_login . '|' . $expiration . '|' . $hash;

		return apply_filters('auth_cookie', $cookie, $user_id, $expiration, $scheme);
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
			$expiration = $expire = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, $remember);
		} else {
			$expiration = time() + apply_filters('auth_cookie_expiration', 172800, $user_id, $remember);
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

		$auth_cookie = wp_generate_auth_cookie($user_id, $expiration, $scheme);
		$logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

		do_action('mpt_set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme);
		do_action('mpt_set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in');

		// Set httponly if the php version is >= 5.2.0
		if ( version_compare(phpversion(), '5.2.0', 'ge') ) {
			setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
			setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
			setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
			if ( COOKIEPATH != SITECOOKIEPATH )
				setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
		} else {
			$cookie_domain = COOKIE_DOMAIN;
			if ( !empty($cookie_domain) )
				$cookie_domain .= '; HttpOnly';
			setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, $cookie_domain, $secure);
			setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, $cookie_domain, $secure);
			setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, $cookie_domain, $secure_logged_in_cookie);
			if ( COOKIEPATH != SITECOOKIEPATH )
				setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, $cookie_domain, $secure_logged_in_cookie);
		}
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