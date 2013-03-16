<?php
class MPT_Widget extends WP_Widget {
    /**
     * __construct
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		parent::__construct(
			'mpt-form-login',
			__( 'Members authentification', 'mpt' ),
			array(
				'classname'		=>	'form-login',
				'description'	=>	__( 'Form for allow members to login/logout. Display also lost password and registration links', 'mpt' )
			)
		);

	}

    /**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		extract( $instance, EXTR_SKIP );
		

		// Conditional load template for logged-in members ?
		$template = ( mpt_is_member_logged_in() ) ? 'widget-mpt-connected.php' : 'widget-mpt-unconnected.php';

		// Get data from POST, cleanup it
		$member_data = ( !isset($_POST['mptlogin']) ) ? array() : $_POST['mptlogin'];
		
		// Parse vs defaults
		$member_data = wp_parse_args( $member_data, array('username' => '', 'rememberme' => '', 'redirect_to' => '0') );

		echo $before_widget;

		// Display the widget, allow take template from child or parent theme
		if ( is_file(STYLESHEETPATH .'/widget-views/'.$template) ) { // Use custom template from child theme
			include( STYLESHEETPATH .'/widget-views/'.$template );
		} elseif ( is_file(TEMPLATEPATH .'/widget-views/'.$template ) ) { // Use custom template from parent theme
			include( TEMPLATEPATH .'/widget-views/'.$template );
		} else { // Use builtin temlate
			include( MPT_DIR . 'views/client/' . $template );
		}

		echo $after_widget;
	}

    /**
     * Processes the widget's options to be saved.
     * 
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['is_lost_link'] = isset($new_instance['is_lost_link']) ? 1 : 0;
		$instance['is_register_link'] = isset($new_instance['is_register_link']) ? 1 : 0;
		$instance['mode'] = ($new_instance['mode'] == 'ajax') ? 'ajax' : 'html';

		return $instance;
	}

    /**
     * Generates the administration form for the widget.
     * 
     * @param	array	instance	The array of keys and values for the widget.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => __( 'Members authentification', 'mpt' ),
				'is_lost_link' => 1,
				'is_register_link' => 1,
				'mode' => 'html',
			)
		);

		// Display the admin form
		include( MPT_DIR . 'views/admin/widget.php' );	
	}
}