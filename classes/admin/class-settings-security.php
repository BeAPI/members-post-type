<?php
class MPT_Admin_Settings_Security {
	static $settings_group = 'mpt-security';

    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */	
	public function __construct( ) {
		//add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
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
		// Section 1
		add_settings_section( self::$settings_group . '-section', __( 'Input Examples', 'mpt' ), array(__CLASS__, 'pages_section_callback'), self::$settings_group );

		// Section 1 - Fields
		add_settings_field( 'Input Element', __( 'Input Element', 'mpt' ), array(__CLASS__, 'input_element_callback'), self::$settings_group, self::$settings_group . '-section' );
		add_settings_field( 'Textarea Element', __( 'Textarea Element', 'mpt' ), array(__CLASS__, 'textarea_element_callback'), self::$settings_group, self::$settings_group . '-section' );
		add_settings_field( 'Checkbox Element', __( 'Checkbox Element', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$settings_group, self::$settings_group . '-section' );
		add_settings_field( 'Radio Button Elements', __( 'Radio Button Elements', 'mpt' ), array(__CLASS__, 'radio_element_callback'), self::$settings_group, self::$settings_group . '-section' );
		add_settings_field( 'Select Element', __( 'Select Element', 'mpt' ), array(__CLASS__, 'select_element_callback'), self::$settings_group, self::$settings_group . '-section' );

		// Section 1 - DB options
		register_setting( self::$settings_group, self::$settings_group, array(__CLASS__, 'validate_input') );
	}
	
    /**
     * Get description for section
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function pages_section_callback() {
		echo '<p>' . __( 'Provides examples of the five basic element types.', 'mpt' ) . '</p>';
	}

    /**
     * Default values for options
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function get_default_options() {
		$defaults = array(
			'input_example'		=>	'',
			'textarea_example'	=>	'',
			'checkbox_example'	=>	'',
			'radio_example'		=>	'',
			'time_options'		=>	'default'	
		);
		
		return apply_filters( 'mpt_get_default_options', $defaults, self::$settings_group );
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
		$output = array();
		
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {
			
			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) ) {
			
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				
			} // end if
			
		} // end foreach
		
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$settings_group );
	}
}