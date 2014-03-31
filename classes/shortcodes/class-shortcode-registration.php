<?php
class MPT_Shortcode_Registration extends MPT_Shortcode {

	public static $form_fields = array(
		'username' => '',
		'first_name' => '',
		'last_name' => '',
		'email' => '',
		'password' => '',
		'password_repeat' => ''
	);

	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-registration', array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'init' ), 12 );
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Member logged-in ?
		if ( mpt_is_member_logged_in() ) {
			return '<!-- Members already logged-in. -->';
		}
		
		if ( isset( $_GET['mpt-action'] ) && $_GET['mpt-action'] == 'validation-member-registration' ) {
			// Get data from POST, cleanup it
			$member_data = (!isset( $_POST['mptregistration_s2'] ) ) ? array() : $_POST['mptregistration_s2'];

			// Parse vs defaults
			$member_data = wp_parse_args( $member_data, self::$form_fields );
			
			return parent::load_template( 'member-registration-step-2', $member_data );
		} else {
			// Get data from POST, cleanup it
			$member_data = (!isset( $_POST['mptregistration'] ) ) ? array() : $_POST['mptregistration'];

			// Parse vs defaults
			$member_data = wp_parse_args( $member_data, self::$form_fields );

			return parent::load_template( 'member-registration', $member_data );
		}
	}
	
	public static function init() {
		// Ask link reset
		self::check_step_1();

		// Check link reset and form new password
		self::check_step_2_url();
		self::check_step_2_form();
	}
	/**
	 * Check POST data for creation member. Need for set_cookie function.
	 *
	 * @return void
	 * @author Benjamin Niess
	 * @access public
	 */
	public static function check_step_1(){
		if ( isset( $_POST['mptregistration'] ) ) {
			// Cleanup data
			$mptr = $_POST['mptregistration'] = stripslashes_deep( $_POST['mptregistration'] );

			// Parse vs default
			$mptr = wp_parse_args( $mptr, self::$form_fields );

			// Check _NONCE
			$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
			if ( !wp_verify_nonce( $nonce, 'mptregistration' ) ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
				return false;
			}
			
			// Add filter for other plugins to stop process
			$stop = apply_filters( 'mpt_shortcode_check_registration_start', false, $mptr );
			if ( $stop === true ) {
				return false;
			}
			
			// Email valid ?
			if ( !is_email( $mptr['email'] ) ) {
				parent::set_message( 'email_invalid', __( 'You need to enter a valid email address', 'mpt' ), 'error' );
				return false;
			}

			// Email exists
			if ( mpt_is_unique_email() && mpt_email_exists( $mptr['email'] ) ) {
				parent::set_message( 'email_exists', __( 'This email address is already taken', 'mpt' ), 'error' );
				return false;
			}
			
			// Fix username when exists
			if ( mpt_is_allowed_email_signon() && empty($mptr['username']) ) {
				$mptr['username'] = $mptr['email'];
			}
			
			// Admin must validate member ?
			$admin_validation = mpt_registration_with_member_validation();
			if ( $admin_validation === 'on' ) {
				// Generate something random for a validation reset key.
				$key = wp_generate_password( 20, false );

				// Default member insert args 
				$args = array();
				$args['password'] = wp_generate_password( 12 );
				$args['email'] = sanitize_email( $mptr['email'] );
				$args['post_status'] = 'pending';

				// insert member
				$member_id = MPT_Member_Utility::insert_member( $args );

				// An wp error ?
				if ( is_wp_error( $member_id ) ) {
					parent::set_message( $member_id->get_error_code(), $member_id->get_error_message(), 'error' );
					return false;
				}

				// Add post meta key
				add_post_meta( $member_id, 'mpt_validation_registration_key', $key, true );
				
				// Add filter for other plugins to stop process
				$stop = apply_filters( 'mpt_admin_validation_registration', false, $member_id, $mptr );
				if ( $stop === true ) {
					return false;
				}
				
				// Send reset link
				$member = new MPT_Member( $member_id );
				$result = $member->register_validation_notification( $key);
				if ( is_wp_error( $result ) ) {
					parent::set_message( $result->get_error_code(), $result->get_error_message(), 'error' );
					return false;
				}
				
				// Flush POST
				unset( $_POST['mptregistration'] );

				// Set success message
				parent::set_message( 'mptregistration', __( 'Your account has been created. You just received an email to confirm your registration', 'mpt' ), 'success' );
				return true;
			}
			
			// Check password, confirmation, complexity
			if ( !empty( $mptr['password'] ) && !empty( $mptr['password_repeat'] ) ) {
				if ( $mptr['password'] != $mptr['password_repeat'] ) { // password is the same ?
					parent::set_message( 'password_repeat', __( 'The two password you filled doesn\'t match', 'mpt' ), 'error' );
				} elseif ( strlen( $mptr['password'] ) < 6 ) { // TODO: Hooks and function for test password security
					parent::set_message( 'password_security', __( 'You password need to be at least 6 characters long', 'mpt' ), 'error' );
				}
			} else {
				parent::set_message( 'password', __( 'You need to fill the two password fields', 'mpt' ), 'error' );
			}
			
			do_action( 'mpt_shortcode_check_registration', $mptr );

			// Have messages ?
			$messages = parent::get_messages( 'raw' );

			// All is fine ? start insertion
			if ( empty( $messages ) ) {

				// Default member insert args 
				$args = array();
				$args['post_status'] = 'publish';
				$args['password'] = $mptr['password'];
				$args['username'] = sanitize_text_field( $mptr['username'] );
				$args['email'] = sanitize_email( $mptr['email'] );
				$args['first_name'] = sanitize_text_field( $mptr['first_name'] );
				$args['last_name'] = sanitize_text_field( $mptr['last_name'] );

				// insert member
				$member_id = MPT_Member_Utility::insert_member( $args );

				// An wp error ?
				if ( is_wp_error( $member_id ) ) {
					parent::set_message( $member_id->get_error_code(), $member_id->get_error_message(), 'error' );
					return false;
				}

				// Send member notification
				$member = new MPT_Member( $member_id );
				$member->register_notification( $args['password'] );

				// Flush POST
				unset( $_POST['mptregistration'] );

				// Set success message
				parent::set_message( 'mptregistration', sprintf( __( 'Your account has been created. You can now log-in with your access. <a href="%s">Click here</a> ', 'mpt' ), home_url('/') ), 'success' );
				return true;
			}
		}
		return false;
	}
	/**
	 * Check if member click on validation link, verify key/email on DB
	 *
	 * @author Alexandre Sadowski
	 */
	public static function check_step_2_url(){
		if( !isset($_GET['mpt-action']) || $_GET['mpt-action'] != 'validation-member-registration' ){
			return false;
		}

		if ( !isset( $_GET['ID'] ) || !isset( $_GET['key'] ) || empty( $_GET['ID'] ) || empty( $_GET['key'] ) ) {
			wp_die( __( 'The link you clicked seems to be broken. Please contact the administrator of the site', 'mpt' ) );
		}

		// Try load member with this activation_key
		$member = new MPT_Member( );
		$member->fill_by( 'id', (int)$_GET['ID'] );
		if ( !$member->exists() || ($member->exists() && $member->id != $_GET['ID']) ) {
			wp_die( __( 'Cheatin&#8217; uh?', 'mpt' ) );
		}
		
		// Check valid key ?
		$key = get_post_meta($member->id, 'mpt_validation_registration_key', true);
		if( empty($key) ){
			wp_die( __( 'Registration key is not valide for this member', 'mpt' ), 'Error registration key' );
		}
		
		return true;
	}
	
	public static function check_step_2_form(){
		if ( isset( $_POST['mptregistration_s2'] ) ) {
			// Check _NONCE
			$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
			if ( !wp_verify_nonce( $nonce, 'mptregistration_s2' ) ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );
			}

			// Check password, confirmation, complexity
			if ( !empty( $_POST['mptregistration_s2']['password'] ) && !empty( $_POST['mptregistration_s2']['password_repeat'] ) ) {
				if ( $_POST['mptregistration_s2']['password'] != $_POST['mptregistration_s2']['password_repeat'] ) { // password is the same ?
					parent::set_message( 'password_repeat', __( 'The two password you filled doesn\'t match', 'mpt' ), 'error' );
				} elseif ( strlen( $_POST['mptregistration_s2']['password'] ) < 6 ) { // TODO: Hooks and function for test password security
					parent::set_message( 'password_security', __( 'You password need to be at least 6 characters long', 'mpt' ), 'error' );
				}
			} else {
				parent::set_message( 'password', __( 'You need to fill the two password fields', 'mpt' ), 'error' );
			}
			
			do_action( 'mpt_shortcode_check_registration_step_2', $_POST['mptregistration_s2'] );
			
			// Have messages ?
			$messages = parent::get_messages( 'raw' );

			// All is fine ? start insertion
			if ( empty( $messages ) ) {
				// Try load member with this ID
				$member = new MPT_Member( );
				$member->fill_by( 'id', (int)$_GET['ID'] );
				if ( !$member->exists() || ($member->exists() && $member->id != $_GET['ID']) ) {
					wp_die( __( 'Cheatin&#8217; uh?', 'mpt' ) );
				}
				
				$update_member = wp_update_post( array( 'post_type' => MPT_CPT_NAME, 'ID' => $member->id, 'post_status' => 'publish' ) );
				if( is_wp_error( $update_member ) ){
					wp_die( $update_member->get_error_message() , $update_member->get_error_code() );
				}
				
				$member->set_password( $_POST['mptregistration_s2']['password'] );
				if( isset($_POST['mptregistration_s2']['username']) && !empty( $_POST['mptregistration_s2']['username'] ) ){
					$member->set_meta_value( 'username', sanitize_text_field( $_POST['mptregistration_s2']['username'] ) );
				}
				
				if( isset($_POST['mptregistration_s2']['first_name']) && !empty( $_POST['mptregistration_s2']['first_name'] ) ){
					$member->set_meta_value( 'first_name', sanitize_text_field( $_POST['mptregistration_s2']['first_name'] ) );
				}
				
				if( isset($_POST['mptregistration_s2']['last_name']) && !empty( $_POST['mptregistration_s2']['last_name'] ) ){
					$member->set_meta_value( 'last_name', sanitize_text_field( $_POST['mptregistration_s2']['last_name'] ) );
				}
				
				//Update connection type
				update_post_meta( $member->id, 'connection_type', 'default' );
				
				do_action( 'mpt_shortcode_doing_registration_step_2', $member->id, $_POST['mptregistration_s2'] );
				
				//Send member notification
				$member->register_notification( $_POST['mptregistration_s2']['password'] );

				// Delete registration key
				delete_post_meta( $member->id, 'mpt_validation_registration_key');

				// Flush POST
				unset( $_POST['mptregistration_s2'] );

				// Set success message
				parent::set_message( 'mptregistration_s2', sprintf( __( 'Your account has been created. You can now log-in with your access. <a href="%s">Click here</a> ', 'mpt' ), home_url('/') ), 'success' );
				return true;
			}
			return false;
		}
	}
}
