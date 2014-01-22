<?php
class MPT_Options {
	static $options = null;
	static $default_options = null;
	
	private static function _load_option() {
		// Get all options, for get all option_name
		$sections = (array) include( MPT_DIR . 'classes/helpers/default-sections.php' );
		$settings = (array) include( MPT_DIR . 'classes/helpers/default-settings.php' );
		$defaults = array();
		
		// Merge all current DB option
		$current_options = array();
		
		// Loop on each section
		foreach( $sections as $section ) {
			$settings_names = wp_list_pluck( $settings[$section['id']], 'name' );
			
			foreach( $settings_names as $index => $name ) {
				$defaults[$section['id']][$name] = isset( $settings[$section['id']][$index]['default'] ) ? $settings[$section['id']][$index]['default'] : '' ;
			}
			
			$current_options[$section['id']] = wp_parse_args( (array) get_option( $section['id'] ), $defaults[$section['id']] );
		}
		
		self::$options = $current_options;
	}
	
	private static function _load_default_option() {
		self::$default_options = (array) include( MPT_DIR . 'classes/helpers/default-settings.php' );	
	}

	public static function get_option( $option_name  ) {		
		if ( self::$options === null ) {
			self::_load_option();
		}

		return self::$options[$option_name];
	}

	public static function get_option_value( $option_name, $key, $failback_default = false ) {
		if ( self::$options === null ) {
			self::_load_option();
		}
		
		if( $failback_default === true && isset( self::$options[$option_name][$key] ) && empty( self::$options[$option_name][$key] ) ){
			return self::get_default_value_from_default_options( $option_name, $key ); 
		}
		
		return self::$options[$option_name][$key];
	}

	public static function get_field_from_default_options( $option_name, $field ) {
		if ( self::$default_options === null ) {
			self::_load_default_option();
		}
		
		foreach( self::$default_options[$option_name] as $key => $value ) {
			if( $value['name'] == $field ) {
				return self::$default_options[$option_name][$key];
			}
		}

		return false;
	}

	public static function get_default_value_from_default_options( $option_name, $key ) {
		if ( self::$default_options === null ) {
			self::_load_default_option();
		}
		
		$field_data = self::get_field_from_default_options( $option_name, $key );
		if( empty( $field_data ) || !is_array( $field_data ) || !isset( $field_data['default'] ) ) {
			return false;
		}

		return $field_data['default'];
	}
}
