<?php
/*
// Code reference, file not load
class MPT_Helper_Settings {
	public function __construct( ) {
	}
	
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
}
*/