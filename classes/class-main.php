<?php
class MPT_Main {
	/**
     * Register hooks
     * 
     * @access public
     *
     * @return void.
     */
	public function __construct( ) {
		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'mpt_login', array( __CLASS__, 'mpt_login' ), 10, 2 );
		add_action( 'switch_blog', array( __CLASS__, 'switch_blog' ), 10, 2 );
	}

	/**
     * Manage logout
     *
     * @access public
     * @static
     *
     * @return void.
     */
	public static function init( ) {
		if ( isset( $_GET['mpt-action'] ) && $_GET['mpt-action'] == 'logout' ) {
			if ( MPT_Member_Auth::is_logged_in( ) ) {
				MPT_Member_Auth::logout( );
				$redirect_to = home_url( '/#logout-success' );
			} else {
				$redirect_to = home_url( '/#logout-error' );
			}

			// Check if request want redirect to somewehre
			$redirect_to = (isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] )) ? $_REQUEST['redirect_to'] : $redirect_to;

			wp_redirect( $redirect_to );
			exit( );
		}
	}

    /**
     * Add class to HTML body
     * 
     * @param array $classes Array with body classes.
     *
     * @access public
     * @static
     *
     * @return array.
     */
	public static function body_class( $classes ) {
		if ( MPT_Member_Auth::is_logged_in( ) ) {
			$classes[] = 'mpt-logged-in';
		}
		
		return $classes;
	}

    /**
     * Manage login counter, last connection
     * 
     * @param string $member_name Description.
     * @param int    $member_id   Description.
     *
     * @access public
     * @static
     *
     * @return void.
     */
	public static function mpt_login( $member_name = '', $member_id = 0 ) {
		// Increment counter
		$counter = (int) get_post_meta( $member_id, '_counter_sign_on', true );
		$counter++;
		update_post_meta( $member_id, '_counter_sign_on', $counter );

		// Update latest date connection
		update_post_meta( $member_id, '_last_sign_on_date', current_time( 'mysql' ) );
	}

    /**
     * Build action link for MPT actions
     * 
     * @param string $action action ask by developper.
     *
     * @access public
     * @static
     *
     * @return string link or anchor of action asked.
     */
	public static function get_action_permalink( $action = '' ) {
		// Get page ids from options
		$page_ids = (array) get_option( 'mpt-pages' );

		// URL
		$return_url = '';

		// Different action possible
		switch( $action ) {
			case 'registration' :
			case 'login' :
			case 'change-password' :
			case 'lost-password' :
				if ( isset( $page_ids['page-' . $action] ) && absint( $page_ids['page-' . $action] ) > 0 ) {
					$return_url = get_permalink( $page_ids['page-' . $action] );
				} else {
					$return_url = '#no-page-id-for-this-action';
				}
				break;
			case 'logout' :
				$return_url = home_url( '/?mpt-action=logout' );
				break;
			default :
				$return_url = '#no-known-action';
				break;
		}

		return apply_filters( 'mpt_action_permalink', $return_url, $action );
	}

    /**
     * switch_blog
     * 
     * @param mixed $new_blog     Description.
     * @param mixed $prev_blog_id Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	function switch_blog( $new_blog_id, $prev_blog_id ) {
		global $mpt_roles;

		if ( did_action( 'init' ) ) {
			if ( isset($mpt_roles) ) {
				$mpt_roles->reinit();
			}
		}
	}
}
