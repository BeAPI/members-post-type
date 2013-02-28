<?php
class MPT_Shortcode_Registration extends MPT_Shortcode {
	
	public static $string_fields = array(
		'user_email',
		'first_name',
		'last_name',
	);
	
	/**
	 * All about the registration shortcode
	 */
	public function __construct() {
		add_shortcode( 'member-registration' , array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect', array( __CLASS__, 'check_changes'), 12 );
	}
	
	public static function shortcode() {
		// Fix notices, string
		$user = new StdClass;
		$user->ID = 0;
		foreach( self::$string_fields as $key ) { 
			if ( isset($_POST['new_user'][$key]) ) {
				if ( is_string($_POST['new_user'][$key]) ) {
					$user->$key = stripslashes($_POST['new_user'][$key]);
				} 
			} else {
				$user->$key = '';
			}
		}

		// Password fields
		$user->password = ( isset($_POST['new_user']['password']) ) ? $_POST['new_user']['password'] : '';
		$user->password_repeat = ( isset($_POST['new_user']['password_repeat']) ) ? $_POST['new_user']['password_repeat'] : '';

		// Require the file tempalte
		ob_start();
		
		parent::load_template( 'member-registration' );
		
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
		
	}

	/**
	 * Check POST data for creation user. Need for set_cookie function.
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function check_changes() {
		global $message, $status, $form_errors;
		
		if ( is_admin() ) {
			return false;
		}
		
		// TODO if ( !is_user_logged_in .... )
		
		if ( isset($_POST['creation_user']) ) {
			check_admin_referer( 'creation-user' );
			
			$message = '';
			
			// Password
			if ( isset($_POST['new_user']['password']) && isset($_POST['new_user']['password_repeat']) && !empty($_POST['new_user']['password']) && !empty($_POST['new_user']['password_repeat']) ) {
				if ( $_POST['new_user']['password'] != $_POST['new_user']['password_repeat'] ) { // password is the same ?
					$status = 'error';
					$message .= __( "The two password you filled doesn't match<br />", 'mpt' );
					$form_errors[] = 'password';
					$form_errors[] = 'password_repeat';
				} elseif( strlen($_POST['new_user']['password']) < 6 ) {
					$status = 'error';
					$message .= __( "You password need to be at least 6 characters long<br />", 'mpt' );
					$form_errors[] = 'password';
				}
			} else {
				$status = 'error';
				$form_errors[] = 'password';
				$message .= __( "You need to fill the two password fields<br />", 'mpt' );
			}
			
			// Email valid ?
			if ( isset($_POST['new_user']['user_email']) && !is_email($_POST['new_user']['user_email']) ) {
				$status = 'error';
				$message .= __( "You need to enter a valid email addresse<br />", 'mpt' );
				$form_errors[] = 'user_email';
			}
			
			//Email exists
			// TODO use mpt_email_exists
			if ( email_exists( $_POST['new_user']['user_email'] ) ){
				$status = 'error';
				$message .= __( "This email address is already taken<br />", 'mpt' );
				$form_errors[] = 'user_email';
			}
			
			// All is fine ? start insertion
			if ( empty( $message ) ) {
				
				// Default user insert args 
				$args = array(
					'role' 				=> 'subscriber',
					'user_registered' 	=> current_time('mysql')
				);
				
				$args['user_pass'] = $_POST['new_user']['password'];
				$args['user_login'] 	= stripslashes( $_POST['new_user']['user_nickname'] );
				$args['user_email']		= sanitize_email( $_POST['new_user']['user_email'] );
				$args['first_name']		= stripslashes( $_POST['new_user']['first_name'] );
				$args['last_name']		= stripslashes( $_POST['new_user']['last_name'] );
				
				// insert member
				var_dump($args);
				die();
				
				// An error ?+
				if ( is_wp_error($user_id) ) {
					wp_die( $user_id->get_error_message() );
				}
				
				$status = "success";
				$message = __( "Your account has beed created. You can now log-in with your access<br />", 'mpt' );
				
				
				add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
				$content = sprintf( __( '<p>Hello, </p>
				<p>Your subscription to the website %1s is completed. </p>
				<p>Your login : <br />%2s</p>
				<p><a href="%3s">Go to my account</a></p>
				', get_bloginfo( 'name' ), $args['user_login'], home_url() ) ); // TODO get the correct page
				
				$mail = wp_mail( $args['user_login'], 'Votre inscription au site ' . get_bloginfo( 'name' ), $content );
				
				wp_redirect( add_query_arg( array( 'confirm-user-registered' => true ), home_url() ) );
				exit;
			}
		}
		
		return true;
	}
	
	/**
	 * Return the value of a field when the form is submitted
	 * 
	 * @param string $field_name the name of the subfield
	 * @param string $parent_field the name of the parent field ( $parent_field['$field_name']) 
	 * 
	 * @return the sanitized content of the field
	 */
	public static function get_field_value( $field_name, $parent_field = 'new_user' ) {
		if ( !isset( $_POST[$parent_field][$field_name] ) )
			return false;
		
		return esc_attr( $_POST[$parent_field][$field_name] );
	}
}