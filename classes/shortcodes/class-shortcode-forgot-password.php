<?php
class MPT_Shortcode_Forgot_Password extends MPT_Shortcode {
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-forgot-password' , array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init'), 12 );
	}
	
	public static function init() {
		self::check_step_1();
		self::check_regeneration_link();
		self::check_step_2();
	}
	
	public static function shortcode() {
		global $message, $status;
		
		// Require the file template
		ob_start();
		
		// If we are on regenerating page
		if( isset( $_GET['action'] ) && $_GET['action'] =='regenerate_password' ) {
			
			// Get the template
			parent::load_template( 'member-forgot-password-step-2' );
		}else {
			// Get user_login
			$user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
			
			parent::load_template( 'member-forgot-password' );
		}
		
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
		
	}

	
	/**
	 * lostpassword form action
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	public static function redirect_default_password() {
		
		$password_url = get_permalink( ); // TODO
		if ( isset($_POST['redirect_to']) ) {
			$password_url = add_query_arg( array('redirect_to' => $_POST['redirect_to']), $password_url );
		}

		wp_redirect($password_url);
		exit();
	}
	
	/**
	 * Redirect with key and login form action
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function redirect_default_password_check_key() {
		
		$password_url = get_permalink(  ); // TODO
		
		// If there is the key, add it
		if ( isset($_GET['key']) ) {
			$password_url = add_query_arg( array('key' => $_GET['key'] ), $password_url );
		}
		
		// If there is the login, add it
		if ( isset($_GET['login']) ) {
			$password_url = add_query_arg( array('login' => urlencode( $_GET['login'] ) ), $password_url );
		}
		
		// Redirect to right page
		wp_redirect( $password_url );
		exit();
	}
	
	/**
	 * Check POST data for email
	 *
	 * @return void
	 * @author Benjamin Niess
	 */
	public static function check_step_1() {
		global $message, $status;
		
		// Check if the user is reseting
		if ( !isset( $_POST['forgot_password'] ) || (int) $_POST['forgot_password'] != 1 ) {
			return false;
		}
		
		// Check if the email field is filled
		if ( !isset( $_POST['mpt_user_email'] ) || empty( $_POST['mpt_user_email'] ) || !is_email( $_POST['mpt_user_email'] ) ) {
			$message = __( "You need to enter a valid email address", 'mpt' );
			$status = 'error';
		}
		
		// che if login given
		$errors = self::retrieve_password();
		// Check if errors
		if( is_wp_error( $errors ) ) {
			$message = $errors->get_error_message();
			$status = 'error';
			return false;
		}
		
		// Display the message
		$message = __( "You are going to receive an email with a reset link.", 'mpt' );
		$status = 'success'; 
		
	}
	
	/**
	 * Check if the user clicked on the regenerate link
	 * 
	 * @author Benjamin Niess
	 */
	public static function check_regeneration_link() {
		
		if ( !isset( $_GET['action'] ) || $_GET['action'] != 'regenerate_password' ) {
			return false;
		} 
		
		if ( !isset( $_GET['login'] ) || !isset( $_GET['key'] ) || empty( $_GET['login'] ) || empty( $_GET['key'] ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		} 
		
		$user = self::check_password_reset_key($_GET['key'], $_GET['login']);
		if( is_wp_error( $user ) ) {
			wp_die( $user->get_error_message() );
		}
	}
	
	public static function check_step_2() {
		if( isset($_POST['mpt_user_password']) && isset( $_POST['mpt_user_password_confirm'] ) ){
			
			// Check if pass 1 and pass 2 are the same
			if( $_POST['mpt_user_password'] != $_POST['mpt_user_password_confirm'] ) {
				$message = __( "The two passwords you entered don't match", 'mpt' );
				$status = 'error';
				return false;
				
			} elseif( isset( $_POST['mpt_user_password'] ) && !empty( $_POST['mpt_user_password_confirm'] ) ) {
				// Check if the key and login are right and get the user
				$user = self::check_password_reset_key( $_GET['key'], $_GET['login'] );
				
				// reset the user password
				self::reset_password( $user, $_POST['mpt_user_password'] );
				
				// Add the message
				$message = __( "Your password has been changed.", 'mpt' );
				$status = 'success';
			}
		}
	}

	public static function retrieve_password() {
		global $wpdb, $current_site;
	
		$errors = new WP_Error();
		$user = new MPT_User();
		
		if ( empty( $_POST['user_login'] ) || !is_email( $_POST['user_login'] ) ) {
			$errors->add('empty_username', __( 'Please enter a valid email address', 'mpt' ) );
		} else {
			$user->fill_by( 'email' , $_POST['user_login'] );
			if ( !$user->exists()) {
				$errors->add('invalid_email', __( "This email addresse doesn't exists on the site", 'mpt' ));
			}
		}
	
		if ( $errors->get_error_code() )
			return $errors;
	
		$key = $activation_key = get_post_meta( $user->id, 'user_activation_key', true );
		if ( empty($key) ) {
			// Generate something random for a key...
			$key = wp_generate_password(20, false);
			do_action('mpt_retrieve_password_key', $user->username, $key);
			
			// Now insert the new md5 key into post meta
			update_post_meta( $user->id, 'user_activation_key', $key );
		}
		
		$headers = array("From: " . get_bloginfo( 'name' ) . "<" . get_bloginfo( 'admin_email' ) . ">","Content-Type: text/html; charset=utf-8");
		$h = implode("\r\n", $headers ) . "\r\n";
		
		$message = __('Someone requested that the password be reset for the following account:', 'mpt') . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s', 'mpt'), $user->email) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'mpt') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:', 'mpt') . "\r\n\r\n";
		$step_2_url = add_query_arg( array( 'action' => 'regenerate_password', 'key' => $key, 'login' => rawurlencode($user->email) ), home_url() );
		$message .= "<a href=\"" . $step_2_url. "\">" . $step_2_url . "</a>";
	
		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
		$title = sprintf( __('[%s] Password Reset'), $blogname );
	
		$title = apply_filters('mpt_retrieve_password_title', __( "Reset your password", 'mpt' ));
		$message = apply_filters('mpy_retrieve_password_message', $message, $key);
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		if ( $message && !wp_mail($user->email, $title, $message, $headers ) )
			wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );
	
		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login
	 *
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 *
	 * @return object|WP_Error
	 */
	public static function check_password_reset_key($key, $login) {
		global $wpdb;
	
		$key = preg_replace('/[^a-z0-9]/i', '', $key);
	
		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error('invalid_key', __('Invalid key'));
	
		if ( empty($login) || !is_string($login) )
			return new WP_Error('invalid_key', __('Invalid key'));
	
		$activation_key = "ok"; // TODO
		if ( empty( $activation_key ) )
			return new WP_Error('invalid_key', __('Invalid key'));
	
		return $activation_key;
	}
	
	/**
	 * Handles resetting the user's password.
	 *
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 */
	public static function reset_password($user, $new_pass) {
		do_action('password_reset', $user, $new_pass);
	
		wp_set_password($new_pass, $user->ID);
	
		wp_password_change_notification($user);
	}
}