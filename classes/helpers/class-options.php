<?php
class MPT_Options {
	static $options = null;
	static $default_options = null;
	
	private static function _load_option() {
		// Get all options, for get all option_name
		$sections = MPT_Plugin::get_default_settings_sections();
		$settings = MPT_Plugin::get_default_settings_fields();
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

		$current_options = apply_filters( 'mpt_load_option', $current_options );

		self::$options = $current_options;
	}
	
	private static function _load_default_option() {
		self::$default_options = MPT_Plugin::get_default_settings_sections();
	}

	public static function get_option( $option_name  ) {
		if ( self::$options === null ) {
			self::_load_option();
		}

		$option_value = apply_filters( 'mpt_option', self::$options[ $option_name ], $option_name );

		return apply_filters( "mpt_option_{$option_name}", $option_value );
	}

	public static function get_option_value( $option_name, $key, $failback_default = false ) {
		if ( self::$options === null ) {
			self::_load_option();
		}
		
		if( $failback_default === true && isset( self::$options[$option_name][$key] ) && empty( self::$options[$option_name][$key] ) ){
			return self::get_default_value_from_default_options( $option_name, $key );
		}

		$option_value = apply_filters( "mpt_option_value_{$option_name}", self::get_option($option_name), $key, $option_name );

		return apply_filters( "mpt_option_value_{$option_name}_{$key}", $option_value[$key], $option_name, $key );
	}

	public static function get_field_from_default_options( $option_name, $field ) {
		if ( self::$default_options === null ) {
			self::_load_default_option();
		}
		
		foreach( self::$default_options[$option_name] as $key => $value ) {
			if( $value['name'] == $field ) {
				return apply_filters( "mpt_default_option_{$option_name}", self::$default_options[ $option_name ][ $key ], $option_name, $field );
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

		return apply_filters( "mpt_default_value_option_{$option_name}", $field_data['default'], $option_name, $key );
	}

	/**
	 * Find out if to display the setting's description or not.
	 *
	 * @since 1.0.0
	 * @author Maxime CULEA
	 *
	 * @param $context, where it has been called from.
	 *
	 * @return bool, whatever to display the setting description or not.
	 */
	public static function can_display_setting_description( $context ) {

		/**
		 * Allow to hide or display th current admin's setting description.
		 *
		 * The dynamic portion of the hook name, `$context`, refers to
		 * Where it has been called from.
		 *
		 * @since 1.0.0
		 *
		 * @param string $context Where it has been called from.
		 *
		 * @param bool $can_display, True or False for choosing whatever to display this setting description.
		 */
		$can_display = apply_filters( 'mpt_admin\setting\display_description_' . $context, true );

		/**
		 * Allow to hide or display multiple admin's settings descriptions.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $can_display, True or False for choosing whatever to display this setting description.
		 * @param string $context Where it has been called from.
		 */
		$can_display = apply_filters( 'mpt_admin\setting\display_descriptions', $can_display, $context );

		/**
		 * Allow to hide or display all admin's settings descriptions.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $can_display, True or False for choosing whatever to display all settings descriptions.
		 */
		$can_display = apply_filters( 'mpt_admin\setting\display_all_descriptions', $can_display );

		return $can_display;
	}

	/**
	 * Handle the admin's setting description name.
	 *
	 * @since 1.0.0
	 * @author Maxime CULEA
	 *
	 * @param string $context : Where the method has been called from.
	 *
	 * @return string : If empty, setting description will not show up.
	 */
	public static function description_setting_name( $context ) {
		return self::can_display_setting_description( $context ) ? sprintf( '%s_description', $context ) : '';
	}

	/**
	 * Handle the admin's setting description desc.
	 *
	 * @since 1.0.0
	 * @author Maxime CULEA
	 *
	 * @param string $context : Where the method has been called from.
	 *
	 * @return string $html
	 */
	public static function description_setting_desc( $context ) {
		// Default value
		$html = '';

		if ( ! self::can_display_setting_description( $context ) ) {
			return $html;
		}

		/**
		 * Get the available replacement_values for the current context.
		 *
		 * The dynamic portion of the hook name, `$context`, refers to
		 * Where it has been called from.
		 *
		 * @since 1.0.0
		 *
		 * @param array $replacement_values, All the available replacements values and their descriptions.
		 * @param string $context Where it has been called from.
		 */
		$replacement_values = apply_filters( 'mpt_admin\setting\replacement_values', array(), $context );

		if ( empty( $replacement_values ) ) {
			return $html;
		}

		$html  = '<h4>' . esc_html__( 'The available values are :', 'mpt' ) . '</h4>';
		$html .= '<p class="description">' . esc_html__( 'Values between "%% %%" will be dynamically replaced before email send.', 'mpt' ). '</p>';
		$html .= '<table><tbody>';
		foreach ( $replacement_values as $replacement_value => $replacement_label ) {
			$html .= sprintf( '<tr><td>%2$s : </td><td>%1$s</td></tr>', sprintf( '%%%%%s%%%%', esc_html( $replacement_value ) ), esc_html( $replacement_label ) );
		}
		$html .= '</tbody></table>';

		return $html;
	}
}
