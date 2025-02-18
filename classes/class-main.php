<?php

class MPT_Main {

	/**
	 * Register hooks
	 *
	 * @access public
	 *
	 * @return void.
	 */
	public function __construct() {
		// Init once MPT roles
		add_action('init', array('MPT_Roles', 'init'), 9);

		// load the textdomain
		add_action('init', array( __CLASS__, 'init_textdomain'), 9);

		// Init AJAX hook
		add_action('wp_loaded', array(__CLASS__, 'wp_loaded'), 15);

		// Manage redirections
		add_action('init', array(__CLASS__, 'init'), 10);
		add_action('template_redirect', array(__CLASS__, 'template_redirect'), 10);

		// Bodyclass for theme
		add_action('body_class', array(__CLASS__, 'body_class'));

		// Counter/date connection
		add_action('mpt_login', array(__CLASS__, 'mpt_login'), 10, 2);

		//add fornt assets enqueue_front_assets
		add_action('wp_enqueue_scripts', array($this, 'enqueue_front_assets'));
	}

	/**
	 * Implement hooks for AJAX call
	 * Clone wp_ajax_ and wp_ajax_nopriv_
	 */
	public static function wp_loaded() {
		if ( !defined('DOING_AJAX') ) {
			return false;
		}

		// Require an action mptaction
		if ( !isset($_REQUEST['mptaction']) || empty( $_REQUEST['mptaction'] ) ) {
			return false;
		}

		/** Allow for cross-domain requests (from the frontend). */
		send_origin_headers();

		/** Load WordPress Administration APIs */
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		/** Load Ajax Handlers for WordPress Core */
		require_once( ABSPATH . 'wp-admin/includes/ajax-actions.php' );

		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );

		send_nosniff_header();
		nocache_headers();

		do_action( 'admin_init' );

		if ( mpt_is_member_logged_in() ) {
			do_action('mpt_ajax_' . $_REQUEST['mptaction']); // Authenticated actions
		} else {
			do_action('mpt_ajax_nopriv_' . $_REQUEST['mptaction']); // Non-member actions
		}

		die( '0' );
	}

	/**
	 * Manage logout
	 *
	 * @access public
	 * @static
	 *
	 * @return void.
	 */
	public static function init() {
		if ( isset( $_GET['mpt-action'] ) && $_GET['mpt-action'] == 'logout' ) {
			if ( MPT_Member_Auth::is_logged_in() ) {
				MPT_Member_Auth::logout();
				$redirect_to = home_url( '/#logout-success' );
			} else {
				$redirect_to = home_url( '/#logout-error' );
			}

			// Check if request want to redirect somewhere
			$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? wp_sanitize_redirect( $_REQUEST['redirect_to'] ) : $redirect_to;
			$redirect_to = wp_validate_redirect( $redirect_to, home_url( '/' ) );

			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	/**
	 * Load the textdomain
	 */
	public static function init_textdomain() {
		// Load translations
		load_plugin_textdomain( 'mpt', false, basename( MPT_DIR ) . '/languages' );
	}

	/**
	 * Redirect Lost Password Page of Member Logged to home
	 *
	 * @access public
	 * @static
	 *
	 * @return void.
	 */
	public static function template_redirect() {
		if (!MPT_Member_Auth::is_logged_in()) {
			return false;
		}

		$page_lost_password = mpt_get_option_value('mpt-pages', 'page-lost-password');
		if (!empty($page_lost_password)) {
			if (is_page($page_lost_password)) {
				wp_redirect(home_url('/'));
				exit();
			}
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
	public static function body_class($classes) {
		if (MPT_Member_Auth::is_logged_in()) {
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
	public static function mpt_login($member_name = '', $member_id = 0) {
		// Increment counter
		$counter = (int) get_post_meta($member_id, '_counter_sign_on', true);
		$counter++;
		update_post_meta($member_id, '_counter_sign_on', $counter);

		// Update latest date connection
		update_post_meta($member_id, '_last_sign_on_date', current_time('mysql'));
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
	public static function get_action_permalink($action = '') {
		// Get page ids from options
		$current_options = (array) MPT_Options::get_option('mpt-pages');

		// Build URL depending action
		if ( $action == 'logout' ) {
			$return_url = admin_url('/admin-ajax.php?mpt-action=logout');
		} else {
			if ( isset($current_options['page-' . $action]) && absint($current_options['page-' . $action]) > 0 ) {
				$return_url = get_permalink($current_options['page-' . $action]);
			} elseif ( isset($current_options['page-' . $action]) ) {
				$return_url = home_url('/#no-page-id-for-' . $action);
			} else {
				$return_url = home_url('/#no-known-action');
			}
		}

		return apply_filters('mpt_action_permalink', $return_url, $action);
	}

	/**
	 * Get page id for MPT actions
	 *
	 * @param string $action action ask by developper.
	 *
	 * @access public
	 * @static
	 *
	 * @return integer id of asked page
	 */
	public static function get_action_page_id($action = '') {
		// Get page ids from options
		$current_options = (array) MPT_Options::get_option('mpt-pages');

		// Default id
		$page_id = 0;

		// Different action possible
		switch ($action) {
			case 'registration' :
			case 'registration-step-2' :
			case 'login' :
			case 'account' :
			case 'change-password' :
			case 'change-profile' :
			case 'lost-password' :
			case 'two-factor' :
				if (isset($current_options['page-' . $action]) && absint($current_options['page-' . $action]) > 0) {
					$page_id = $current_options['page-' . $action];
				}
				break;
			case 'logout' :
			default :
				break;
		}

		return apply_filters('mpt_action_page_id', $page_id, $action);
	}


	/**
	 * Check if current page is a MPT action page.
	 *
	 * @param string $action specific action to check against.
	 *
	 * @return bool true if the current URL is a page and is set in MPT settings, false otherwise.
	 */
	public static function is_action_page( $action = '' ) {
		// Is page ?
		if ( ! is_page() ) {
			return false;
		}

		// Get current page ID asked
		$current_page_id = get_queried_object_id();
		if ( $current_page_id === 0 ) {
			return false;
		}

		if ( ! empty( $action ) ) {
			$current_options = [ (int) self::get_action_page_id( $action ) ];
		} else {
			// Get page ids from options
			$current_options = (array) MPT_Options::get_option( 'mpt-pages' );
		}

		foreach ( $current_options as $option_page_id ) {
			if ( $current_page_id === (int) $option_page_id ) {
				return true;
			}
		}

		return false;
	}

	/** add front assets
	 * @return void
	 */
	public function enqueue_front_assets() : void {
		wp_enqueue_style('mpt-front-css', MPT_URL . '/assets/css/mpt-front.css', array( ), MPT_VERSION, 'all' );
		wp_enqueue_script ('mpt-front-js', MPT_URL . '/assets/js/mpt-front.js', array(), MPT_VERSION, true );
	}
}
