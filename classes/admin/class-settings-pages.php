<?php
class MPT_Admin_Settings_Pages {
	static $id = 'mpt-pages';

    /**
     * Register hooks on WP
     * 
     * @access public
     *
     * @return void
     */
	public function __construct( ) {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

    /**
     * admin_init
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_init( ) {
		// Section
		add_settings_section( self::$id . '-section', __( 'Feature Pages', 'mpt' ), array( __CLASS__, 'pages_section_callback' ), self::$id );

		// Fields
		add_settings_field( 'page-registration', __( 'Registration', 'mpt' ), array( __CLASS__, 'select_pages_callback' ), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'page-registration' ) );
		add_settings_field( 'page-login', __( 'Login/logout', 'mpt' ), array( __CLASS__, 'select_pages_callback' ), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'page-login' ) );
		add_settings_field( 'page-change-password', __( 'Change password', 'mpt' ), array( __CLASS__, 'select_pages_callback' ), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'page-change-password' ) );
		add_settings_field( 'page-lost-password', __( 'Lost password', 'mpt' ), array( __CLASS__, 'select_pages_callback' ), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'page-lost-password' ) );

		// DB options
		register_setting( self::$id, self::$id, array( __CLASS__, 'validate_input' ) );
	}

    /**
     * Get description for section
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function pages_section_callback( ) {
		echo '<p>' . __( 'You must define here the pages containing the WordPress shortcodes for different features (login, registration, etc).', 'mpt' ) . '</p>';
	}

    /**
     * Default values for options
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function get_default_options( ) {
		$defaults = array( 'page-registration' => 0, 'page-login' => 0, 'page-change-password' => 0, 'page-lost-password' => 0 );
		return apply_filters( 'mpt_get_default_options', $defaults, self::$id );
	}

    /**
     * Generic callback allow to show SELECT html with WordPress pages
     * Args must have "id" and "name"
     * 
     * @param mixed $args Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function select_pages_callback( $args ) {
		$options = get_option( self::$id );
		$options = wp_parse_args( $options, self::get_default_options( ) );

		wp_dropdown_pages( array( 'selected' => $options[$args['id']], 'name' => esc_attr( $args['name'] ) . '[' . esc_attr( $args['id'] ) . ']', 'id' => $args['id'], 'show_option_none' => __( 'Please choose a page', 'mpt' ), 'option_none_value' => 0 ) );
	}

    /**
     * validate_input
     * 
     * @param mixed $input Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function validate_input( $input ) {
		// Create our array for storing the validated options
		$output = array( );

		// Loop through each of the incoming options
		foreach ( self::get_default_options() as $key => $value ) {
			if ( isset( $input[$key] ) ) {
				$output[$key] = absint( $input[$key] );
			}
		}

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$id );
	}

}
