<?php
class MPT_Options {
	static $options = null;
	
	private static function _load_option( $option_name  ) {
		self::$options = wp_parse_args( (array) get_option( $option_name ), (array) include( MPT_DIR . 'classes/helpers/options-default.php' ) );
	}

	public static function get_option( $option_name = ''  ) {
		if( empty($option_name) ){
			return false;
		}
		
		if ( self::$options === null ) {
			self::_load_option( $option_name );
		}

		return self::$options;
	}

	public static function get_option_value( $option_name = '', $key = '' ) {
		if( empty($option_name) ){
			return false;
		}
		
		if ( self::$options === null ) {
			self::_load_option( $option_name );
		}
		
		if( isset( self::$options[$key] ) && empty( self::$options[$key] ) ){
			return self::get_default_value_from_default_options( $option_name, $key ); 
		}
		
		return self::$options[$key];
	}

	public static function get_field_from_default_options( $option_name = '', $field = '' ) {
		if( empty($option_name) ){
			return false;
		}
		
		if ( self::$options === null ) {
			self::_load_option( $option_name );
		}

		if( empty( self::$options ) || !is_array( self::$options ) || !isset( self::$options[$option_name] ) ) {
			return false;
		}
		
		foreach( self::$options[$option_name] as $key => $value ) {
			if( $value['name'] == $field ) {
				return self::$options[$option_name][$key];
			}
		}

		return false;
	}

	public static function get_default_value_from_default_options( $option_name = '', $key = '' ) {
		if( empty($option_name) ){
			return false;
		}
		
		if ( self::$options === null ) {
			self::_load_option( $option_name );
		}
		
		$field_data = self::get_field_from_default_options( $option_name, $key );
		if( empty( $field_data ) || !is_array( $field_data ) || !isset( $field_data['default'] ) ) {
			return false;
		}

		return $field_data['default'];
	}
}