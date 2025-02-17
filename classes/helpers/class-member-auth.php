<?php
class MPT_Member_Auth {
    /**
     * Do nothing
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {}
	
	/**
	 * Authenticate member with remember capability.
	 *
	 * The credentials is an array that has 'm_login', 'm_password', and
	 * 'remember' indices. If the credentials is not given, then the log in form
	 * will be assumed and used if set.
	 *
	 * The various authentication cookies will be set by this function and will be
	 * set for a longer period depending on if the 'remember' credential is set to
	 * true.
	 *
	 * @param array $credentials Optional. Member info in order to sign on.
	 * @param bool $secure_cookie Optional. Whether to use secure cookie.
	 * @return object Either WP_Error on failure, or MPT_Member on success.
	 */
	public static function signon( $credentials = '', $secure_cookie = '' ) {
		if ( empty($credentials) ) {
			if ( ! empty($_POST['log']) ) {
				$credentials['m_login'] = $_POST['log'];
			}
			if ( ! empty($_POST['pwd']) ) {
				$credentials['m_password'] = $_POST['pwd'];
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
	
		$member = self::authenticate($credentials['m_login'], $credentials['m_password']);
		if ( is_wp_error($member) ) {
			if ( $member->get_error_codes() == array('empty_username', 'empty_email', 'empty_password') ) {
				$member = new WP_Error('', '');
			}
	
			return $member;
		}
	
		self::set_auth_cookie($member->id, $credentials['remember'], $secure_cookie);
		do_action('mpt_login', $credentials['m_login'], $member->id);
		return $member;
	}

	/**
	 * Authenticate the member using the WordPress auth cookie.
	 */
	public static function authenticate_cookie($member, $username, $password) {
		if ( is_a($member, 'MPT_Member') ) { return $member; }
	
		if ( empty($username) && empty($password) ) {
			$member_id = self::validate_auth_cookie();
			if ( $member_id ) {
				return new MPT_Member($member_id);
			}
	
			global $auth_secure_cookie;
	
			if ( $auth_secure_cookie ) {
				$auth_cookie = MPT_SECURE_AUTH_COOKIE;
			} else {
				$auth_cookie = MPT_AUTH_COOKIE;
			}	
			
			if ( !empty($_COOKIE[$auth_cookie]) ) {
				return new WP_Error('expired_session', __('Please log in again.', 'mpt'));
			}
			
			// If the cookie is not set, be silent.
		}
	
		return $member;
	}

	/**
	 * Checks a member's login information and logs them in if it checks out.
	 *
	 * @param string $username member's username
	 * @param string $password member's password
	 * @return WP_Error|MPT_Member MPT_Member object if login successful, otherwise WP_Error object.
	 */
	public static function authenticate($username, $password) {
		$username = sanitize_user($username);
		$password = trim($password);
	
		if ( mpt_is_allowed_email_signon() ) {
			add_filter('mpt_authenticate', array(__CLASS__, 'authenticate_email_password'), 20, 3);
		} else {
			add_filter('mpt_authenticate', array(__CLASS__, 'authenticate_username_password'), 20, 3);
		}
		
		$member = apply_filters('mpt_authenticate', null, $username, $password);
		if ( $member == null ) {
			// Only needed if all authentication handlers fail to return anything
			$member = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.', 'mpt'));
		}
	
		$ignore_codes = array('empty_username', 'empty_password');
	
		if (is_wp_error($member) && !in_array($member->get_error_code(), $ignore_codes) ) {
			do_action('mpt_login_failed', $username);
		}
	
		return $member;
	}
	
	/**
	 * Authenticate the member using the email and password.
	 */
	public static function authenticate_email_password($member, $email, $password) {
		if ( is_a($member, 'MPT_Member') ) {
			return $member;
		}
	
		if ( empty($email) || empty($password) ) {
			$error = new WP_Error();
	
			if ( empty($email) ) {
				$error->add('empty_email', __('<strong>ERROR</strong>: The email field is empty.', 'mpt'));
			}
			if ( empty($password) ) {
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.', 'mpt'));
			}
			
			return $error;
		}
		
		$member = new MPT_Member();
		$member->fill_by( 'email', $email );
		
		if ( !$member->exists() ) {
			return new WP_Error('invalid_email', sprintf(__('<strong>ERROR</strong>: Invalid email. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'mpt'), mpt_get_lost_password_permalink() ) );
		}
		
		$member = apply_filters('mpt_authenticate_member', $member, $password);
		if ( is_wp_error($member) ) {
			return $member;
		}
	
		if ( !wp_check_password($password, $member->password, false) ) {
			return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the email <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?', 'mpt' ),
			$email, mpt_get_lost_password_permalink() ) );
		}
	
		return $member;
	}
	
	/**
	 * Authenticate the member using the username and password.
	 */
	public static function authenticate_username_password($member, $username, $password) {
		if ( is_a($member, 'MPT_Member') ) {
			return $member;
		}
	
		if ( empty($username) || empty($password) ) {
			$error = new WP_Error();
	
			if ( empty($username) ) {
				$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.', 'mpt'));
			}
			if ( empty($password) ) {
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.', 'mpt'));
			}
			
			return $error;
		}
		
		$member = new MPT_Member();
		$member->fill_by( 'username', $username );
		
		if ( !$member->exists() ) {
			return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'mpt'), mpt_get_lost_password_permalink() ) );
		}
		
		$member = apply_filters('mpt_authenticate_member', $member, $password);
		if ( is_wp_error($member) ) {
			return $member;
		}
	
		if ( !wp_check_password($password, $member->password, false) ) {
			return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?', 'mpt' ),
			$username, mpt_get_lost_password_permalink() ) );
		}
	
		return $member;
	}

	/**
	 * Checks if the current visitor is a logged in member.
	 *
	 * @return bool True if member is logged in, false if not logged in.
	 */
	public static function is_logged_in() {
		$member = self::get_current_member();
		if ( empty( $member ) || $member->id == 0 ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Retrieve the current member object.
	 *
	 * @return MPT_Member Current member MPT_Member object
	 */
	public static function get_current_member() {
		global $current_mpt_member;
		self::get_current_member_info();
		
		return $current_mpt_member;
	}
	
	/**
	 * Changes the current member by ID or name.
	 *
	 * Set $id to null and specify a name if you do not know a member's ID.
	 *
	 * Some WordPress functionality is based on the current member and not based on
	 * the signed in member. Therefore, it opens the ability to edit and perform
	 * actions on members who aren't signed in.
	 *
	 * @global object $current_mpt_member The current member object which holds the member data.
	 * @uses do_action() Calls 'set_current_member' hook after setting the current member.
	 *
	 * @param int $id Member ID
	 * @param string $name Member's username
	 * @return MPT_Member Current member Member object
	 */
	public static function set_current_member($id, $name = '') {
		global $current_mpt_member;
	
		if ( isset($current_mpt_member) && ($id == $current_mpt_member->id) ) {
			return $current_mpt_member;
		}
	
		if ( !empty($name) ) {
			$current_mpt_member = new MPT_Member();
			$current_mpt_member = $current_mpt_member->fill_by('username', $name);
		} else {
			$current_mpt_member = new MPT_Member($id);
		}
		
		do_action('set_mpt_current_member');
	
		return $current_mpt_member;
	}
		
	/**
	 * Populate global variables with information about the currently logged in member.
	 *
	 * Will set the current member, if the current member is not set. The current member
	 * will be set to the logged in person. If no member is logged in, then it will
	 * set the current member to 0, which is invalid and won't have any permissions.
	 *
	 * @uses $current_mpt_member Checks if the current member is set
	 * @uses SELF::validate_auth_cookie() Retrieves current logged in member.
	 *
	 * @return bool|null False on invalid auth cookie. Null when current member set
	 */
	public static function get_current_member_info() {
		global $current_mpt_member;

		if ( ! empty($current_mpt_member) )
			return false;
	
		if ( ! $member = self::validate_auth_cookie() ) {
			if ( empty($_COOKIE[MPT_LOGGED_IN_COOKIE]) || !$member = self::validate_auth_cookie($_COOKIE[MPT_LOGGED_IN_COOKIE], 'logged_in') ) {
				self::set_current_member(0);
				return false;
			}
		}
	
		self::set_current_member($member);
		return true;
	}
	
	/**
	 * Log the current member out.
	 *
	 */
	public static function logout() {
		self::set_last_login_activity();
		self::clear_auth_cookie();
	}

	/**
	 * Set last login activity before user logout
	 * @return void
	 */
	private static function set_last_login_activity(){
		global $current_mpt_member;

		if ( empty( $current_mpt_member ) ) {
			return;
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

		$last_login_activity_data = [
			'date_time'    => [
				'label' => __( 'Date & Hour', 'mpt' ),
				'value' => wp_date( 'j F Y - H:i' ),
			],
			'user_os'      => [
				'label' => __( 'Operating System', 'mpt' ),
				'value' => self::detect_system_version( $user_agent ),
			],
			'user_browser' => [
				'label' => __( 'Browser', 'mpt' ),
				'value' => self::detect_browser_version( $user_agent )
			],
			'user_ip'      => [
				'label' => __( 'IP address', 'mpt' ),
				'value' => $_SERVER['REMOTE_ADDR'] ?? '',
			],
		];

		update_post_meta( $current_mpt_member->id, MPT_LAST_LOGIN_ACTIVITY, $last_login_activity_data );
	}


	/**
	 * Detects the operating system and its version from the User-Agent string.
	 *
	 * @param string $ua The user-agent string
	 *
	 * @return string Operating system name and version
	 */
	private static function detect_system_version( $ua ) {
		$ua = trim( $ua );

		if ( empty( $ua ) ) {
			return 'Not specified';
		}

		// System detection patterns
		$systems = array( 'Android', 'Linux', 'Windows', 'iPhone', 'iPad', 'Macintosh', 'OpenBSD', 'Unix' );

		$system = '';
		$system_version = '';
		foreach ( $systems as $system_id ) {
			if ( strpos( $ua, $system_id ) !== false ) {
				$system = $system_id;
				// Special handling for Android to get version
				if ( $system == 'Android' && preg_match( '/Android\s+([0-9\.]+)/', $ua, $matches ) ) {
					$system_version = $matches[1];
				}
				// Special handling for MacOS to get version
				elseif ( $system == 'Macintosh' && preg_match( '/Mac OS X\s([0-9_\.]+)/', $ua, $matches ) ) {
					$system_version = str_replace('_', '.', $matches[1]);
				}
				// Windows version
				elseif ( $system == 'Windows' && preg_match( '/Windows NT\s([0-9\.]+)/', $ua, $matches ) ) {
					$system_version = $matches[1];
				}
				break;
			}
		}

		// Default unknown system version handling
		if ( !$system_version ) {
			$system_version = 'Unknown';
		}

		return $system . ' ' . $system_version;
	}

	/**
	 * Detects the browser and its version from the User-Agent string.
	 *
	 * @param string $ua The user-agent string
	 *
	 * @return string Browser name and version
	 */
	private static function detect_browser_version( $ua ) {
		$ua = trim( $ua );

		if ( empty( $ua ) ) {
			return 'Not specified';
		}

		// Browser detection patterns and their corresponding names
		$browsers = [
			'Firefox/'   => 'Firefox',
			'OPR/'       => 'Opera',
			'Opera/'     => 'Opera',
			'YaBrowser/' => 'Yandex Browser',
			'Trident/'   => 'Internet Explorer',
			'IE/'        => 'Internet Explorer',
			'Edge/'      => 'Microsoft Edge',
			'Edg/'       => 'Microsoft Edge',
			'Chrome/'    => 'Chrome',
			'Safari/'    => 'Safari',
			'Lynx/'      => 'Lynx',
		];

		$browser = '';
		$browser_version = '';
		foreach ( $browsers as $browser_id => $browser_name ) {
			if ( strpos( $ua, $browser_id ) !== false ) {
				$browser = $browser_name;
				if ( preg_match( '/' . preg_quote( $browser_id, '/' ) . '([0-9\.\_\-]+)/', $ua, $matches ) ) {
					$browser_version = $matches[1];
				}
				break;
			}
		}

		if ( !$browser_version ) {
			$browser_version = 'Unknown';
		}

		return $browser . ' ' . $browser_version;
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
	 * @return bool|int False if invalid cookie, Member ID if valid.
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
		
		$member = new MPT_Member();
		$member->fill_by( 'id', $member_id );
		if ( ! $member->exists() ) {
			do_action('mpt_auth_cookie_bad_username', $cookie_elements);
			return false;
		}

		$pass_frag = substr($member->password, 8, 4);
		
		$key = wp_hash('member-'.$member->id . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', 'member-'.$member->id . '|' . $expiration, $key);

		if ( $hmac != $hash ) {
			do_action('mpt_auth_cookie_bad_hash', $cookie_elements);
			return false;
		}

		if ( $expiration < time() ) // AJAX/POST grace period set above
			$GLOBALS['login_grace_period'] = 1;

		do_action('mpt_auth_cookie_valid', $cookie_elements, $member);

		return $member->id;
	}

	/**
	 * Parse a cookie into its components
	 *
	 * @param string $cookie
	 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
	 * @return array Authentication cookie components
	 */
	public static function parse_auth_cookie($cookie = '', $scheme = '') {
		if ( empty($cookie) ) {
			switch ($scheme){
				case 'auth':
					$cookie_name = MPT_AUTH_COOKIE;
					break;
				case 'secure_auth':
					$cookie_name = MPT_SECURE_AUTH_COOKIE;
					break;
				case "logged_in":
					$cookie_name = MPT_LOGGED_IN_COOKIE;
					break;
				default:
					if ( is_ssl() ) {
						$cookie_name = MPT_SECURE_AUTH_COOKIE;
						$scheme = 'secure_auth';
					} else {
						$cookie_name = MPT_AUTH_COOKIE;
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
		
		list($member_id, $expiration, $hmac) = $cookie_elements;
		
		// Extract ID from text
		$member_id = str_replace('member-', '', $member_id);

		return compact('member_id', 'expiration', 'hmac', 'scheme');
	}

	/**
	 * Sets the authentication cookies based Member ID.
	 *
	 * The $remember parameter increases the time that the cookie will be kept. The
	 * default the cookie is kept without remembering is two days. When $remember is
	 * set, the cookies will be kept for 14 days or two weeks.
	 *
	 * @param int $member_id Member ID
	 * @param bool $remember Whether to remember the member
	 */
	public static function set_auth_cookie($member_id, $remember = false, $secure = '') {
		if ( $remember ) {
			$expiration = $expire = time() + apply_filters('mpt_auth_cookie_expiration', 1209600, $member_id, $remember);
		} else {
			$expiration = time() + apply_filters('mpt_auth_cookie_expiration', 172800, $member_id, $remember);
			$expire = 0;
		}

		if ( '' === $secure )
			$secure = is_ssl();

		$secure = apply_filters('mpt_secure_auth_cookie', $secure, $member_id);
		$secure_logged_in_cookie = apply_filters('mpt_secure_logged_in_cookie', false, $member_id, $secure);

		if ( $secure ) {
			$auth_cookie_name = MPT_SECURE_AUTH_COOKIE;
			$scheme = 'secure_auth';
		} else {
			$auth_cookie_name = MPT_AUTH_COOKIE;
			$scheme = 'auth';
		}

		$auth_cookie = self::generate_auth_cookie($member_id, $expiration, $scheme);
		$logged_in_cookie = self::generate_auth_cookie($member_id, $expiration, 'logged_in');

		do_action('mpt_set_auth_cookie', $auth_cookie, $expire, $expiration, $member_id, $scheme);
		do_action('mpt_set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $member_id, 'logged_in');
		
		setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
		setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
		setcookie(MPT_LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie(MPT_LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
		}
	}
	
	/**
	 * Generate authentication cookie contents.
	 *
	 * @uses apply_filters() Calls 'auth_cookie' hook on $cookie contents, Member ID
	 *		and expiration of cookie.
	 *
	 * @param int $member_id Member ID
	 * @param int $expiration Cookie expiration in seconds
	 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
	 * @return string Authentication cookie contents
	 */
	public static function generate_auth_cookie($member_id, $expiration, $scheme = 'auth') {
		$member = new MPT_Member($member_id);
		if ( !$member->exists() ) {
			return false;
		}
		
		$pass_frag = substr($member->password, 8, 4);

		$key = wp_hash('member-'.$member->id . $pass_frag . '|' . $expiration, $scheme);
		$hash = hash_hmac('md5', 'member-'.$member->id . '|' . $expiration, $key);

		$cookie = 'member-'.$member->id . '|' . $expiration . '|' . $hash;

		return apply_filters('mpt_auth_cookie', $cookie, $member_id, $expiration, $scheme);
	}

	/**
	 * Removes all of the cookies associated with authentication.
	 *
	 */
	public static function clear_auth_cookie() {
		do_action('mpt_clear_auth_cookie');

		setcookie(MPT_AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(MPT_AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
		
		setcookie(MPT_SECURE_AUTH_COOKIE, ' ', time() - 31536000, ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
		setcookie(MPT_SECURE_AUTH_COOKIE, ' ', time() - 31536000, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN);
		
		setcookie(MPT_LOGGED_IN_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
		setcookie(MPT_LOGGED_IN_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
	}
}
