<?php
class MPT_Admin_Post_Type {
	static $errors = array();
	static $success = false;

    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );
		
		add_action( 'admin_init', array(__CLASS__, 'admin_init') );
		add_action( 'admin_head', array(__CLASS__, 'admin_head') );

		// Metabox member
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 11 );
		add_action( 'save_post', array( __CLASS__, 'save_post' ) );

		// Add param on URL redirect
		add_filter('redirect_post_location', array(__CLASS__, 'redirect_post_location'), 10, 2 );
	}
	
    /**
     * admin_enqueue_scripts
     * 
     * @param mixed $hook Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_enqueue_scripts( $hook ) {
		global $post;

		if ( in_array( $hook, array( 'edit.php', 'post-new.php' ) ) && isset( $_GET['post_type'] ) && $_GET['post_type'] == MPT_CPT_NAME || in_array( $hook, array( 'post.php' ) ) && isset( $post ) && $post->post_type == MPT_CPT_NAME ) {
			wp_enqueue_style ( MPT_CPT_NAME . '-post', MPT_URL . 'assets/css/admin-post.css', array( ), MPT_VERSION, 'all' );
			wp_enqueue_script( MPT_CPT_NAME . '-post', MPT_URL . 'assets/js/admin-post.js', array('jquery', 'password-strength-meter'), MPT_VERSION, true );
		}
	}

    /**
     * admin_init
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_init() {
		if ( isset($_GET['mpt-message']) ) {
			$message_codes = explode(',', $_GET['mpt-message']);
			
			// Password metabox
			if ( in_array('1', $message_codes) ) {
				add_settings_error( MPT_CPT_NAME.'-postbox-password', MPT_CPT_NAME.'-postbox-password', __('Password and confirmation must be the same.', 'mpt'), 'error' );
			}
			if ( in_array('3', $message_codes) ) {
				add_settings_error( MPT_CPT_NAME.'-postbox-password', MPT_CPT_NAME.'-postbox-password', __('The password does not meet the criteria required by the security policy.', 'mpt'), 'error' );
			}
			if ( in_array('4', $message_codes) ) {
				add_settings_error( MPT_CPT_NAME.'-postbox-password', MPT_CPT_NAME.'-postbox-password', __('The password is the same as that already active. No changes will be made to that member.', 'mpt'), 'error' );
			}
			
			// Main metabox
			if ( in_array('2', $message_codes) ) {
				add_settings_error( MPT_CPT_NAME.'-postbox-main', MPT_CPT_NAME.'-postbox-main', __('The email is already in use. Back to initial value.', 'mpt'), 'error' );
			}

			// Resend notification registration
			if ( in_array( '5', $message_codes, true ) ) {
				$message = __( 'An error has occurred, please check back later.', 'mpt' );
				$type_error = 'error';
				if ( $_GET['mpt-success'] === '1' ) {
					$message = __( 'The notification has been sent.', 'mpt' );
					$type_error = 'success';
				}
				add_settings_error( MPT_CPT_NAME . '-postbox-resend-registration-notification', MPT_CPT_NAME . '-postbox-resend-registration-notification', $message, $type_error );
			}
		}
	}

    /**
     * admin_head
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function admin_head() {
		global $wp_version;
		if( version_compare( $wp_version, '3.8', '<' ) ) {
			echo '<style type="text/css" media="screen">';
				echo '#menu-posts-'.MPT_CPT_NAME.' .wp-menu-image {background: transparent url('.MPT_URL.'/assets/images/toilet.png) no-repeat 6px -17px !important;}';
				echo '#menu-posts-'.MPT_CPT_NAME.':hover .wp-menu-image, #menu-posts-'.MPT_CPT_NAME.'.wp-has-current-submenu .wp-menu-image {background-position:6px 7px!important;}';
				echo '.icon32-posts-'.MPT_CPT_NAME.' {background: transparent url('.MPT_URL.'/assets/images/toilet.png) no-repeat 6px 12px !important;}';
			echo '</style>';
		} else {
			echo '<style type="text/css" media="screen">
			.icon16.icon-' . MPT_CPT_NAME . ':before, #adminmenu #menu-posts-' . MPT_CPT_NAME . ' div.wp-menu-image:before {
				content: "\f307";
			}
			</style>';
		}
	}
	
    /**
     * Add main info, password metaboxes
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function add_meta_boxes( ) {
		add_meta_box( MPT_CPT_NAME.'-postbox-main', __('Main information', 'mpt'), array( __CLASS__, 'metabox_main' ), MPT_CPT_NAME, 'normal', 'high' );
		add_meta_box( MPT_CPT_NAME.'-postbox-password', __('Change password', 'mpt'), array( __CLASS__, 'metabox_password' ), MPT_CPT_NAME, 'normal', 'high' );
		if ( 'pending' === get_post_status() ) {
			add_meta_box( MPT_CPT_NAME . '-postbox-resend-registration-notification', __( 'Resend registration notification', 'mpt' ), array( __CLASS__, 'metabox_resend_registration_notification' ), MPT_CPT_NAME, 'side', 'default' );
		}
		add_meta_box( MPT_CPT_NAME . '-postbox-last-user-activity', __( 'Last member activity', 'mpt' ), array( __CLASS__, 'metabox_user_activity' ), MPT_CPT_NAME, 'normal', 'high' );
	}

    /**
     * metabox_main
     * 
     * @param mixed $post Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function metabox_main( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-postbox-main' );

		// Get values from DB
		$member = array();
		foreach ( MPT_Member::$core_fields as $field ) {
			$member[$field] = get_post_meta($post->ID, $field, true);
		}

		// Show error messages
		settings_errors( MPT_CPT_NAME.'-postbox-main' );

		// Call Template
		include( MPT_DIR . 'views/admin/metabox-main.php');
	}

    /**
     * metabox_password
     * 
     * @param mixed $post Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function metabox_password( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-postbox-password' );
		
		// Show error messages
		settings_errors( MPT_CPT_NAME.'-postbox-password' );
		
		// Call Template
		include( MPT_DIR . 'views/admin/metabox-password.php');
	}

	/**
	 * Metabox to send new notification to continue registration
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public static function metabox_resend_registration_notification( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), MPT_CPT_NAME.'-postbox-resend-registration-notification' );

		settings_errors( MPT_CPT_NAME . '-postbox-resend-registration-notification' );

		// Call Template
		include( MPT_DIR . 'views/admin/metabox-resend-registration-notification.php');
	}

	/**
	 * Display user activity after logout
	 *
	 * @param $post
	 *
	 * @return void
	 */
	public static function metabox_user_activity($post){
		$member = new MPT_Member($post->ID);

		if ( ! $member->exists() ) {
			return;
		}

		include( MPT_DIR . 'views/admin/metabox-last-user-activity.php');
	}

    /**
     * save_post
     * 
     * @param mixed $post_id Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( !current_user_can( 'edit_member', $post_id ) ) {
			return false;
		}

		self::save_metabox_main( $post_id );
		self::save_metabox_password( $post_id );
		self::save_metabox_resend_registration_notification( $post_id );
		
		do_action( 'mpt_send_mail_to_member', $post_id );
		
		return true;
	}

    /**
     * save_metabox_main
     * 
     * @param mixed $post_id Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function save_metabox_main( $post_id ) {
		if ( !isset( $_POST[MPT_CPT_NAME.'-postbox-main'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-postbox-main'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}

		// Instanciate member
		$member = new MPT_Member( $post_id );

		// Sanitize member inputs
		foreach ( MPT_Member::$core_fields as $field ) {
			if ( !isset($_POST['member'][$field]) ) {
				continue;
			}

			if ( $field == 'email' ) {
				$value = sanitize_email( $_POST['member'][$field] );

				// Check if email is unique, when option is enabled, restore old value if already exist.
				if ( mpt_is_unique_email() && $member->email != $value && mpt_email_exists($value) ) {
					$value = $member->email;
					self::$errors[] = 2;
				}
			} else {
				$value = sanitize_text_field( $_POST['member'][$field] );
			}
			
			$member->set_meta_value( $field, $value );
		}
		
		// Force refresh
		$member->fill_by('id', $post_id);
		
		// Replace username by email
		if ( mpt_is_allowed_email_signon() ) {
			$member->set_meta_value( 'username', $member->email );
		}
		
		// Set proper post_title for WP
		$member->regenerate_post_title( true );

		return true;
	}

    /**
     * save_metabox_password
     * 
     * @param mixed $post_id Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function save_metabox_password( $post_id ) {
		if ( !isset( $_POST[MPT_CPT_NAME.'-postbox-password'] ) || !wp_verify_nonce( $_POST[MPT_CPT_NAME.'-postbox-password'], plugin_basename( __FILE__ ) ) ) {
			return false;
		}
		
		$pmp = $_POST['memberpwd'];

		if (  empty($pmp['password']) && empty( $pmp['confirm_password'] ) && empty($pmp['password-generate']) ) {
			return false;
		}
		
		if ( ( empty($pmp['password']) || empty($pmp['confirm_password']) || $pmp['password'] != $pmp['confirm_password'] ) && empty( $pmp['password-generate'] ) ) {
			self::$errors[] = 1; 
			return false;
		}
		
		// Instanciate member
		$member = new MPT_Member( $post_id );
		
		$password = '';
		
		if ( !empty($pmp['password']) ) {
			$password = $pmp['password'];
		}
		
		if( !empty( $pmp['password-generate'] ) ){
			$password = wp_generate_password( 12, false );
			$_REQUEST['memberpwd']['password-generate'] = $password;
		}
		
		// The password was really changed? 
		$result = MPT_Member_Auth::authenticate( $member->username, $password);
		if ( !is_wp_error($result) ) {
			self::$errors[] = 4;
			return false;
		}
		
		// Change password
		$result = $member->set_password( $password );
		
		// Not true ? Hook !
		if ( $result !== true ) {
			self::$errors[] = 3;
			return false;
		}
	
		
		return true;
	}

	/**
	 * Resend registration notification
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public static function save_metabox_resend_registration_notification( $post_id ) {
		$action = $_POST['mpt_resend_notification_registration'] ?? '';
		// Check action
		if ( empty( $action ) ) {
			return;
		}

		// Check nonce
		if ( ! isset( $_POST[ MPT_CPT_NAME . '-postbox-resend-registration-notification' ] ) || ! wp_verify_nonce( $_POST[ MPT_CPT_NAME . '-postbox-resend-registration-notification' ], plugin_basename( __FILE__ ) ) ) {
			self::$errors[] = 5;

			return;
		}

		$mpt_member = new \MPT_Member( $post_id );

		if ( ! $mpt_member->exists() ) {
			self::$errors[] = 5;

			return;
		}

		update_post_meta( $mpt_member->id, 'password', wp_generate_password() );
		// Generate validation registration key
		$mpt_member->set_activation_key();
		if ( false === $mpt_member->register_validation_notification() ) {
			self::$errors[] = 5;

			return;
		}

		self::$success = true;
	}

    /**
     * redirect_post_location
     * 
     * @param mixed $location Description.
     * @param mixed $post_id  Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function redirect_post_location( $location, $post_id ) {
		// Clear query arg to reload page
		$location = remove_query_arg( [ 'mpt-message', 'mpt-success' ], $location );

		if ( !empty(self::$errors) ) {
			return add_query_arg( array('mpt-message' => implode(',', self::$errors)), $location );
		}

		if ( self::$success === true ) {
			return add_query_arg( array( 'mpt-message' => 5, 'mpt-success' => 1 ), $location );
		}

		return $location;
	}
	
	public static function get_connection_type(){
		return apply_filters( 'mpt_connection_type', array( 'default' => __( 'Username / Mail', 'mpt' ) ) );
	}
}
