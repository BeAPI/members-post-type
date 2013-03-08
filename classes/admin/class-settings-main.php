<?php
class MPT_Admin_Settings_Main {
	static $id = 'mpt-main';
	
	public function __construct( ) {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}
	
	public static function admin_init( ) {
		// Section
		add_settings_section( self::$id . '-section', __( 'General features', 'mpt' ), array(__CLASS__, 'pages_section_callback'), self::$id );

		// Fields
		add_settings_field( 'allow-signon-email', __( 'Email sign-on', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'allow-signon-email', 'label' =>  __( 'Allow email sign-on ?', 'mpt'), 'description' => __('Do not change this value if you have already members! At the risk of breaking your site!', 'mpt' ) ) );

		// DB options
		register_setting( self::$id, self::$id, array(__CLASS__, 'validate_input') );
	}
	
	public static function checkbox_element_callback( $args ) {
		$options = get_option( self::$id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		$html = '<input type="checkbox" id="'.esc_attr( $args['id'] ).'" name="'.esc_attr( $args['name'] ) . '[' . esc_attr( $args['id'] ) . ']" value="1"' . checked( 1, (int) $options[$args['id']], false ) . '/>';
		$html .= '&nbsp;';
		$html .= '<label for="'.esc_attr( $args['id'] ).'">'.$args['label'].'</label>';
		if ( isset($args['description']) ) {
			$html .= '<p class="description">'.$args['description'].'</p>';
		}
		
		echo $html;
	}
	
	/**
	 * Get description for section
	 */
	public static function pages_section_callback() {
		//echo '<p>' . __( 'General description.', 'mpt' ) . '</p>';
	}
	
	/**
	 * Default values for options
	 */
	public static function get_default_options() {
		$defaults = array(
			'allow-signon-email' => '1',
		);
		
		return apply_filters( 'mpt_get_default_options', $defaults, self::$id );
	}
	
	public static function validate_input( $input ) {
		// Cleanup input
		$input = stripslashes_deep($input);
		
		// Create our array for storing the validated options
		$output = array();
		
		// Loop through each of the incoming options
		foreach( self::get_default_options() as $key => $value ) {
			if( isset( $input[$key] ) ) {
				$output[$key] = strip_tags( $input[ $key ] );
			} else {
				$output[$key] = 0;
			}
		}
		
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$id );
	}
}
