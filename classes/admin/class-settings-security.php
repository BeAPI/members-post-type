<?php
class MPT_Admin_Settings_Security {
	static $id = 'mpt-security';

    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
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
		// Section 1
		add_settings_section( self::$id . '-section', __( 'Password strength', 'mpt' ), array(__CLASS__, 'pages_section_callback'), self::$id );

		// Section 1 - Fields
		add_settings_field( 'minimum-length', __( 'Minimum length', 'mpt' ), array(__CLASS__, 'input_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'minimum-length', 'description' => __('Tip: password longer than 6 characters is highly recommended!', 'mpt' ) ) );
		add_settings_field( 'username-password', __( 'Username on password', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'username-password', 'label' =>  __( 'Prohibit the presence of the username in the password', 'mpt'), 'description' => __('', 'mpt' ) ) );
		add_settings_field( 'uppercase-character', __( 'Uppercase character', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'uppercase-character', 'label' =>  __( 'Forces the presence of an uppercase character in the password', 'mpt'), 'description' => __('', 'mpt' ) ) );
		add_settings_field( 'lowercase-character', __( 'Lowercase character', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'lowercase-character', 'label' =>  __( 'Forces the presence of an lowercase character in the password', 'mpt'), 'description' => __('', 'mpt' ) ) );
		add_settings_field( 'number-character', __( 'Number character', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'number-character', 'label' =>  __( 'Forces the presence of an number character in the password', 'mpt'), 'description' => __('', 'mpt' ) ) );
		add_settings_field( 'special-character', __( 'Special character', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'special-character', 'label' =>  __( 'Forces the presence of an special character in the password', 'mpt'), 'description' => __('Special characters are often assimilated to punctuation character. (?!&")', 'mpt' ) ) );
		add_settings_field( 'blacklist-keywords', __( 'Blacklist keywords', 'mpt' ), array(__CLASS__, 'input_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'blacklist-keywords', 'description' => __('You must separate blacklist words with commas. These words can not be contained in the passwords of your members.', 'mpt' ) ) );
		add_settings_field( 'force-refresh-login', __( 'Force refresh login', 'mpt' ), array(__CLASS__, 'checkbox_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'force-refresh-login', 'label' =>  __( 'When changing your password policy, this option forces members to change their password once they are logged in! If it does not meet your criteria of course!', 'mpt'), 'description' => __('', 'mpt' ) ) );
		add_settings_field( 'timeout', __( 'Timeout', 'mpt' ), array(__CLASS__, 'input_element_callback'), self::$id, self::$id . '-section', array( 'name' => self::$id, 'id' => 'blacklist-keywords', 'description' => __('Value in days. Enter zero for the password never expires.', 'mpt' ) ) );

		// TODO: History password saved, how long time password is forbide


		// Section 1 - DB options
		register_setting( self::$id, self::$id, array(__CLASS__, 'validate_input') );
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
		echo '<p>' . __( 'Enforce a specific password strength for your members.', 'mpt' ) . '</p>';
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
			'minimum-length' => 0,
			'username-password' => 0,
			'uppercase-character' => 0,
			'lowercase-character' => 0,
			'number-character' => 0,
			'special-character' => 0,
			'blacklist-keywords' => '',
			'force-refresh-login' => 0,
			'timeout' => '0'
		);
		
		return apply_filters( 'mpt_get_default_options', $defaults, self::$id );
	}

    /**
     * checkbox_element_callback
     * 
     * @param mixed $args Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function checkbox_element_callback( $args ) {
		$options = get_option( self::$id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		$html = '<input type="checkbox" id="'.esc_attr( $args['id'] ).'" name="'.esc_attr( $args['name'] ) . '[' . esc_attr( $args['id'] ) . ']" value="1"' . checked( 1, (int) $options[$args['id']], false ) . '/>';
		$html .= '&nbsp;';
		$html .= '<label for="'.esc_attr( $args['id'] ).'">'.$args['label'].'</label>';
		if ( isset($args['description']) && !empty($args['description']) ) {
			$html .= '<p class="description">'.$args['description'].'</p>';
		}
		
		echo $html;
	}
	
	public static function input_element_callback( $args ) {
		$options = get_option( self::$id );
		$options = wp_parse_args( $options, self::get_default_options() );
		
		// Render the output
		$html = '<input type="text" id="'.esc_attr( $args['id'] ).'" name="'.esc_attr( $args['name'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr($options[$args['id']]) . '" />';
		if ( isset($args['description']) && !empty($args['description']) ) {
			$html .= '<p class="description">'.$args['description'].'</p>';
		}

		echo $html;
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
		return apply_filters( 'mpt_settings_validate_input', $output, $input, self::$id );
	}
}