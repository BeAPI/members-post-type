<?php
/*
 Plugin Name: Members post type
 Version: 1.0.10
 Plugin URI: http://www.beapi.fr
 Description: Manage members on WordPress as post type. Implement: post type, authentification, role, clone from WP.
 Author: BE API Technical team
 Author URI: https://beapi.fr/
 Domain Path: languages
 Network: false
 Text Domain: mpt

 TODO:
 	Custom Role API
 		Custom metabox for taxonomy

 	Custom Post Status
 		Pending
 		Unconfirmed

 	Widget
 		AJAX Mode
	
	Social integration
		Facebook / Twitter / Google+
	
	Security
		Login lock (http://plugins.svn.wordpress.org/simple-login-lockdown/trunk/)
		Force HTTPs ?
	+	Reset all password
	+	New random password (https://github.com/soulseekah/Random-New-User-Passwords-for-WordPress)
	
	Content restriction via roles
	Browse as
	
 ----
 
 Copyright 2017 BE API Technical team (human@beapi.fr)
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for                if ( mpt_is_member_logged_in() ) {
18
r more details.
 
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define('MPT_VERSION', '1.0.10');
define('MPT_CPT_NAME', 'member');
define('MPT_TAXO_NAME', 'members-role');
define( 'MPT_LAST_LOGIN_ACTIVITY', 'last_login_activity' );

// Plugin URL and PATH
define( 'MPT_URL', plugin_dir_url( __FILE__ ) );
define( 'MPT_DIR', plugin_dir_path( __FILE__ ) );

// Used to guarantee unique hash cookies
if ( ! defined( 'COOKIEHASH' ) ) {
	$siteurl = home_url( '/' );
	if ( $siteurl ) {
		define( 'COOKIEHASH', md5( $siteurl ) );
	} else {
		define( 'COOKIEHASH', '' );
	}
}

// Auth constants
if ( ! defined( 'MPT_AUTH_COOKIE' ) ) {
	define( 'MPT_AUTH_COOKIE', 'mpt_wordpress_' . constant( 'COOKIEHASH' ) );
}
if ( ! defined( 'MPT_SECURE_AUTH_COOKIE' ) ) {
	define( 'MPT_SECURE_AUTH_COOKIE', 'mpt_wordpress_sec_' . constant( 'COOKIEHASH' ) );
}
if ( ! defined( 'MPT_LOGGED_IN_COOKIE' ) ) {
	define( 'MPT_LOGGED_IN_COOKIE', 'mpt_wordpress_logged_in_' . constant( 'COOKIEHASH' ) );
}

// Function for easy load files
function _mpt_load_files( $dir, $files, $prefix = '' ) {
	foreach ( $files as $file ) {
		if ( is_file( MPT_DIR . $dir . $prefix . $file . ".php" ) ) {
			require_once( MPT_DIR . $dir . $prefix . $file . ".php" );
		}
	}
}

// Plugin functions
_mpt_load_files( 'functions/', array(
	'api',
	'template' ) );

// Plugin client classes
_mpt_load_files( 'classes/', array(
	'main',
	'plugin',
	'content-permissions',
	'post-type', 'private-website',
	'security', 'shortcode',
	'taxonomy',
	'widget',
	'no-cache'
), 'class-');

// Plugin compat classes
_mpt_load_files( 'classes/compat/', array(
	'polylang',
	'gravityforms',
), 'class-');


// Plugin helper classes
_mpt_load_files( 'classes/helpers/', array(
	'member-auth',
	'member-utility',
	'options',
	'nonces'
), 'class-');

// Plugin model classes
_mpt_load_files( 'classes/models/', array(
	'member',
	'roles',
	'role'
), 'class-' );

// Plugin admin classes
if ( is_admin() ) {
	_mpt_load_files( 'classes/admin/', array(
		'content-permissions',
		'main',
		'post-type',
		'taxonomy',
		'settings-main',
		'users-to-members',
		'welcome-message'
	), 'class-' );

	// Load class for API settings
	if ( ! class_exists( 'WeDevs_Settings_API' ) ) {
		require_once( MPT_DIR . 'libraries/wordpress-settings-api-class/class.settings-api.php' );
	}
}

// Plugin activate/desactive hooks
register_activation_hook( __FILE__, array( 'MPT_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MPT_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', 'init_mpt_plugin' );
function init_mpt_plugin() {
	// Client
	new MPT_Main();
	new MPT_Post_Type();
	new MPT_Taxonomy();
	new MPT_Content_Permissions();
	new MPT_Private_Website();
	new MPT_Shortcode();
	new MPT_Security();
	new MPT_No_Cache();
	// Compat
	if ( function_exists( 'PLL' ) &&  function_exists('pll_is_translated_post_type') && pll_is_translated_post_type( MPT_CPT_NAME ) ) {
		new MPT_Polylang();
	}
	if ( class_exists( 'GFForms' ) ) {
		new MPT_Gravity_Forms();
	}

	if( is_admin() ) {

		// Class admin
		new MPT_Admin_Content_Permissions();
		new MPT_Admin_Main();
		new MPT_Admin_Post_Type();
		new MPT_Admin_Taxonomy();
		new MPT_Admin_Users_To_Members();
		new MPT_Admin_Welcome_Message();

		/**
		 * Handle import/export feature :
		 * - mpt_admin_use_import
		 * - mpt_admin_use_export
		 * To deactivate feature, declare the hook in a mu-plugin to be before "plugins_loaded"
		 *
		 * @since 0.6.0
		 * @author Maxime CULEA
		 */
		foreach( array( 'import', 'export' ) as $feature ) {
			if( ! apply_filters( 'mpt_admin_use_' . $feature, true ) ) {
				continue;
			}
			_mpt_load_files( 'classes/admin/', array( $feature ), 'class-' );

			$class = 'MPT_Admin_' . ucfirst( $feature );
			new $class;
		}
	}

	// Widget
	add_action( 'widgets_init', function() {
		return register_widget( 'MPT_Widget' );
	} );
}
