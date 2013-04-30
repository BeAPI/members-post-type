<?php
/*
 Plugin Name: Members post type
 Version: 0.5.1
 Plugin URI: https://github.com/herewithme/members-post-type
 Description: Manage members on WordPress as post type. Implement: post type, authentification, role, clone from WP.
 Author: Amaury Balmer
 Author URI: http://www.beapi.fr
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

 Copyright 2013 Amaury Balmer (amaury@beapi.fr)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Plugin constants
define('MPT_VERSION', '0.5.1');
define('MPT_CPT_NAME', 'member');
define('MPT_TAXO_NAME', 'members-role');

// Plugin URL and PATH
define('MPT_URL', plugin_dir_url ( __FILE__ ));
define('MPT_DIR', plugin_dir_path( __FILE__ ));

// Used to guarantee unique hash cookies
if ( !defined( 'COOKIEHASH' ) ) {
	$siteurl = home_url('/');
	if ( $siteurl )
		define( 'COOKIEHASH', md5( $siteurl ) );
	else
		define( 'COOKIEHASH', '' );
}

// Auth constants
if ( !defined( 'MPT_AUTH_COOKIE' ) ) {
	define('MPT_AUTH_COOKIE', 'mpt_wordpress_' . constant('COOKIEHASH'));
}
if ( !defined( 'MPT_SECURE_AUTH_COOKIE' ) ) {
	define('MPT_SECURE_AUTH_COOKIE', 'mpt_wordpress_sec_' . constant('COOKIEHASH'));
}
if ( !defined( 'MPT_LOGGED_IN_COOKIE' ) ) {
	define('MPT_LOGGED_IN_COOKIE', 'mpt_wordpress_logged_in_' . constant('COOKIEHASH'));
}

// Function for easy load files
function _mpt_load_files($dir, $files, $prefix = '') {
	foreach ($files as $file) {
		if ( is_file($dir . $prefix . $file . ".php") ) {
			require_once($dir . $prefix . $file . ".php");
		}
	}	
}

// Plugin functions
_mpt_load_files(MPT_DIR . 'functions/', array('api', 'template'));

// Plugin client classes
_mpt_load_files(MPT_DIR . 'classes/', array('main', 'plugin', 'post-type', 'roles', 'role', 'shortcode', 'taxonomy', 'member', 'member-auth', 'member-utility', 'widget', 'security'), 'class-');

// Plugin admin classes
if (is_admin()) {
	_mpt_load_files(MPT_DIR . 'classes/admin/', array('main', 'post-type', 'taxonomy', 'import', 'settings-main'), 'class-');

	// Load class for API settings
	if ( !class_exists('WeDevs_Settings_API') ) {
		require_once(MPT_DIR.'libraries/wordpress-settings-api-class/class.settings-api.php');
	}
}

// Plugin activate/desactive hooks
register_activation_hook(__FILE__, array('MPT_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('MPT_Plugin', 'deactivate'));

add_action('plugins_loaded', 'init_mpt_plugin');
function init_mpt_plugin() {
	// Load translations
	load_plugin_textdomain('mpt', false, basename(MPT_DIR) . '/languages');

	// Load builtin plugin "meta for taxo", if not already installed and actived
	if ( !function_exists('get_term_taxonomy_meta') ) {
		require_once(MPT_DIR.'libraries/meta-for-taxonomies/meta-for-taxonomies.php');
	}

	// Client
	new MPT_Main();
	new MPT_Post_Type();
	new MPT_Taxonomy();
	new MPT_Shortcode();
	new MPT_Security();

	// Admin
	if (is_admin()) {
		// Class admin
		new MPT_Admin_Main();
		new MPT_Admin_Post_Type();
		new MPT_Admin_Taxonomy();
		new MPT_Admin_Import();
	}

	// Widget
	add_action('widgets_init', create_function('', 'return register_widget("MPT_Widget");'));
}
