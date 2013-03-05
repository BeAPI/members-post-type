<?php
class MPT_Admin_Settings_Pages {
	static $settings_id = 'mpt-pages';
	
	public function __construct( ) {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}
	
	public static function admin_init( ) {
		// Section 1
		add_settings_section( self::$settings_id . '-section', __( 'Input Examples', 'mpt' ), array(__CLASS__, 'pages_section_callback'), self::$settings_id );

		// Section 1 - Fields
		add_settings_field( 'Input Element', __( 'Input Element', 'mpt' ), array(__CLASS__, 'input_element_callback'), self::$settings_id, self::$settings_id . '-section' );
		add_settings_field( 'Textarea Element', __( 'Textarea Element', 'mpt' ), array(__CLASS__, 'textarea_element_callback'), self::$settings_id, self::$settings_id . '-section' );
		add_settings_field( 'Checkbox Element', __( 'Checkbox Element', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$settings_id, self::$settings_id . '-section' );
		add_settings_field( 'Radio Button Elements', __( 'Radio Button Elements', 'mpt' ), array(__CLASS__, 'radio_element_callback'), self::$settings_id, self::$settings_id . '-section' );
		add_settings_field( 'Select Element', __( 'Select Element', 'mpt' ), array(__CLASS__, 'select_element_callback'), self::$settings_id, self::$settings_id . '-section' );

		// Section 1 - DB options
		register_setting( self::$settings_id, self::$settings_id, array(__CLASS__, 'validate_input') );
	}
	
	/**
	 * Get description for section
	 */
	public static function pages_section_callback() {
		echo '<p>' . __( 'Provides examples of the five basic element types.', 'mpt' ) . '</p>';
	}
	
	/**
	 * Default values for options
	 */
	public static function get_default_options() {
		$defaults = array(
			'input_example'		=>	'',
			'textarea_example'	=>	'',
			'checkbox_example'	=>	'',
			'radio_example'		=>	'',
			'time_options'		=>	'default'	
		);
		
		return apply_filters( 'mpt_get_default_options', $defaults, self::$settings_id );
	}
	
	/* ------------------------------------------------------------------------ *
	 * Field Callbacks
	 * ------------------------------------------------------------------------ */ 
	public static function input_element_callback() {
		$options = get_option( self::$settings_id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		// Render the output
		echo '<input type="text" id="input_example" name="theme_pages[input_example]" value="' . $options['input_example'] . '" />';
	}
	
	public static function textarea_element_callback() {
		$options = get_option( self::$settings_id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		// Render the output
		echo '<textarea id="textarea_example" name="theme_pages[textarea_example]" rows="5" cols="50">' . $options['textarea_example'] . '</textarea>';
	}
	
	public static function checkbox_element_callback() {
		$options = get_option( self::$settings_id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		$html = '<input type="checkbox" id="checkbox_example" name="theme_pages[checkbox_example]" value="1"' . checked( 1, $options['checkbox_example'], false ) . '/>';
		$html .= '&nbsp;';
		$html .= '<label for="checkbox_example">This is an example of a checkbox</label>';
		
		echo $html;
	}
	
	public static function radio_element_callback() {
		$options = get_option( self::$settings_id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		$html = '<input type="radio" id="radio_example_one" name="theme_pages[radio_example]" value="1"' . checked( 1, $options['radio_example'], false ) . '/>';
		$html .= '&nbsp;';
		$html .= '<label for="radio_example_one">Option One</label>';
		$html .= '&nbsp;';
		$html .= '<input type="radio" id="radio_example_two" name="theme_pages[radio_example]" value="2"' . checked( 2, $options['radio_example'], false ) . '/>';
		$html .= '&nbsp;';
		$html .= '<label for="radio_example_two">Option Two</label>';
		
		echo $html;
	}
	
	public static function select_element_callback() {
		$options = get_option( self::$settings_id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		$html = '<select id="time_options" name="theme_pages[time_options]">';
			$html .= '<option value="default">' . __( 'Select a time option...', 'mpt' ) . '</option>';
			$html .= '<option value="never"' . selected( $options['time_options'], 'never', false) . '>' . __( 'Never', 'mpt' ) . '</option>';
			$html .= '<option value="sometimes"' . selected( $options['time_options'], 'sometimes', false) . '>' . __( 'Sometimes', 'mpt' ) . '</option>';
			$html .= '<option value="always"' . selected( $options['time_options'], 'always', false) . '>' . __( 'Always', 'mpt' ) . '</option>';
		$html .= '</select>';
		
		echo $html;
	}
	
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
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$settings_id );
	} // end theme_validate_pages
}
