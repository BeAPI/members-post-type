<?php class MPT_Admin_Settings_Main {
	static $settings_api;
	static $id = 'mpt-main';

	/**
	 * __construct
	 *
	 * @access public
	 *
	 * @return mixed Value.
	 */
	public function __construct() {
		self::$settings_api = new WeDevs_Settings_API();

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );

		add_filter( 'mpt_admin\setting\replacement_values', array(
			$this,
			'add_default_available_replacements_values'
		), 20, 2 );
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
		if ( $hook == 'settings_page_' . 'mpt-settings' ) {
			wp_enqueue_script( MPT_CPT_NAME . '-admin-settings', MPT_URL . 'assets/js/admin-settings.js', array( 'jquery' ), MPT_VERSION, true );
		}
	}

	/**
	 * admin_menu
	 *
	 * @param mixed $hook Description.
	 *
	 * @access public
	 * @static
	 *
	 * @return mixed Value.
	 */
	public static function admin_menu() {
		add_options_page( __( 'Members Post Type', 'mpt' ), __( 'Members Post Type', 'mpt' ), 'manage_options', 'mpt-settings', array(
			__CLASS__,
			'render_page_settings'
		) );
	}

	/**
	 * render_page_settings
	 *
	 * @access public
	 * @static
	 *
	 * @return mixed Value.
	 */
	public static function render_page_settings() {
		include( MPT_DIR . 'views/admin/page-settings.php' );
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
		//set the settings
		self::$settings_api->set_sections( MPT_Plugin::get_default_settings_sections() );
		self::$settings_api->set_fields( MPT_Plugin::get_default_settings_fields() );

		//initialize settings
		self::$settings_api->admin_init();
	}

	/**
	 * TODO: Keep logic
	 *
	 * @param mixed $input Description.
	 *
	 * @access public
	 * @static
	 *
	 * @return mixed Value.
	 */
	public static function validate_input( $input ) {
		// Cleanup input
		$input = stripslashes_deep( $input );

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach ( self::get_default_options() as $key => $value ) {
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = strip_tags( $input[ $key ] );
				// TODO : Remove striptags depending fields
			} else {
				$output[ $key ] = 0;
			}
		}

		// Constraint & Signon
		if ( (int) $output['allow-signon-email'] == 1 ) {
			$output['unique-email'] = 1;
		}

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$id );
	}

	/**
	 * Add default replacement values and their description.
	 * As context is the second argument, only wanted data will be returned.
	 *
	 * @since 1.0.0
	 * @author Maxime CULEA
	 *
	 * @param array $available_values , By default it is empty
	 * @param string $context , Where it has been called from.
	 *
	 * @return array, key / value corresponding to replacement value and description.
	 */
	public function add_default_available_replacements_values( $available_values, $context ) {
		switch ( $context ) {

			case 'lost_password_admin' :
				$available_values = array(
					'blog_name' => __( "Blog's name", 'mpt' ),
					'user_name' => __( "User's name", 'mpt' ),
				);
				break;

			case 'lost_password_member' :
				$available_values = array(
					'blog_name'      => __( "Blog's name", 'mpt' ),
					'user_name'      => __( "User's name", 'mpt' ),
					'user_firstname' => __( "User's first name", 'mpt' ),
					'user_lastname'  => __( "User's last name", 'mpt' ),
					'reset_pwd_link' => __( "The password reset link", 'mpt' ),
					'site_url'       => __( "Current network's url", 'mpt' ),
					'blog_url'       => __( "Current site's url", 'mpt' ),
				);
				break;

			case 'register_member' :
				$available_values = array(
					'blog_name'     => __( "Blog's name", 'mpt' ),
					'user_name'     => __( "User's name", 'mpt' ),
					'user_password' => __( "User's password", 'mpt' ),
					'login_url'     => __( "The site's login url", 'mpt' ),
				);
				break;

			case 'register_member_admin' :
				$available_values = array(
					'blog_name'  => __( "Blog's name", 'mpt' ),
					'user_name'  => __( "User's name", 'mpt' ),
					'user_email' => __( "User's email", 'mpt' )
				);
				break;

			case 'register_member_validation' :
				$available_values = array(
					'blog_name'             => __( "Blog's name", 'mpt' ),
					'site_url'              => __( "Current network's url", 'mpt' ),
					'blog_url'              => __( "Current site's url", 'mpt' ),
					'confirm_register_link' => __( "The register confirmation link", 'mpt' ),
				);
				break;
			case 'validate_new_email_member':
				$available_values = array(
					'blog_name'           => __( "Blog's name", 'mpt' ),
					'site_url'            => __( "Current network's url", 'mpt' ),
					'blog_url'            => __( "Current site's url", 'mpt' ),
					'display_name'        => __( "User's display name", 'mpt' ),
					'validate_email_link' => __( "The validate email link", 'mpt' ),
				);
				break;
		}

		return $available_values;
	}
}