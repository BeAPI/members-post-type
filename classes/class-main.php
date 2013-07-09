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
		add_action('init', array(__CLASS__, 'init_roles'), 12);

		// Init AJAX hook
		add_action('init', array(__CLASS__, 'init_ajax_hooks'), 15);

		// Manage redirections
		add_action('init', array(__CLASS__, 'init'), 10);
		add_action('template_redirect', array(__CLASS__, 'template_redirect'), 10);

		// Bodyclass for theme
		add_action('body_class', array(__CLASS__, 'body_class'));

		// Counter/date connection
		add_action('mpt_login', array(__CLASS__, 'mpt_login'), 10, 2);
	}

	/**
	 * Init MPT Roles
	 */
	public static function init_roles() {
		MPT_Roles::init();
	}

	/**
	 * Implement hooks for AJAX call
	 * Clone wp_ajax_ and wp_ajax_nopriv_
	 */
	public static function init_ajax_hooks() {
		if ( mpt_is_member_logged_in() ) {
			do_action('mpt_ajax_' . $_REQUEST['action']); // Authenticated actions
		} else {
			do_action('mpt_ajax_nopriv_' . $_REQUEST['action']); // Non-member actions
		}
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
		if (isset($_GET['mpt-action']) && $_GET['mpt-action'] == 'logout') {
			if (MPT_Member_Auth::is_logged_in()) {
				MPT_Member_Auth::logout();
				$redirect_to = home_url('/#logout-success');
			} else {
				$redirect_to = home_url('/#logout-error');
			}

			// Check if request want redirect to somewehre
			$redirect_to = (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to'])) ? $_REQUEST['redirect_to'] : $redirect_to;

			wp_redirect($redirect_to);
			exit();
		}
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

		// URL
		$return_url = '';

		// Different action possible
		switch ($action) {
			case 'registration' :
			case 'login' :
			case 'change-password' :
			case 'lost-password' :
				if (isset($current_options['page-' . $action]) && absint($current_options['page-' . $action]) > 0) {
					$return_url = get_permalink($current_options['page-' . $action]);
				} else {
					$return_url = home_url('/#no-page-id-for-' . $action);
				}
				break;
			case 'logout' :
				$return_url = home_url('/?mpt-action=logout');
				break;
			default :
				$return_url = home_url('/#no-known-action');
				break;
		}

		return apply_filters('mpt_action_permalink', $return_url, $action);
	}

}