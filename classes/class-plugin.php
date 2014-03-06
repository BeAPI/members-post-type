<?php
class MPT_Plugin {
    /**
     * Callback when plugin is actived
     * TODO: Create first member ?
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function activate() {	
		// Add role "Members Manager"
		add_role( 'members-manager', __('Members Manager', 'mpt') );

		// Add capabilities for administrator and editor default roles
		foreach( array('administrator', 'editor', 'members-manager') as $role_name ) {
			$role = get_role( $role_name );
			if ( $role != NULL ) {
				foreach( self::get_capabilities() as $capability ) {
					$role->add_cap( $capability );
				}
			}
			unset($role);
		}

		// Load builtin plugin "meta for taxo", if not already installed and actived
		if ( !function_exists('install_table_termmeta') ) {
			require_once(MPT_DIR.'libraries/meta-for-taxonomies/meta-for-taxonomies.php');
			install_table_termmeta();
		}
	}

    /**
     * Callback when plugin is deactived
     * 
     * @access public
     * @static
     *
     * @return void.
     */
	public static function deactivate() {
		remove_role( 'members-manager' );
	}

    /**
     * Get array with plugin capabilities for User/Role WP API, for post type, taxonomy or both.
     * 
     * @param string $target allow to choose type of capabilities.
     *
     * @access public
     * @static
     *
     * @return array capabilities.
     */
	public static function get_capabilities( $target = 'both ') {
		$capabilities = array();

		// Custom post type
		$capabilities['post_type'] = array(
			// Meta capabilities
			'edit_post'              => 'edit_'         . 'member',
			'read_post'              => 'read_'         . 'member',
			'delete_post'            => 'delete_'       . 'member',
			// Primitive capabilities used outside of map_meta_cap():
			'edit_posts'             => 'edit_'         . 'members',
			'edit_others_posts'      => 'edit_others_'  . 'members',
			'publish_posts'          => 'publish_'      . 'members',
			'read_private_posts'     => 'read_private_' . 'members',
			// Primitive capabilities used within map_meta_cap():
			'read'                   => 'read',
			'delete_posts'           => 'delete_'           . 'members',
			'delete_private_posts'   => 'delete_private_'   . 'members',
			'delete_published_posts' => 'delete_published_' . 'members',
			'delete_others_posts'    => 'delete_others_'    . 'members',
			'edit_private_posts'     => 'edit_private_'     . 'members',
			'edit_published_posts'   => 'edit_published_'   . 'members',
		);

		// Taxonomy
		$capabilities['taxonomy'] = array(
			'manage_terms'           => 'manage_' . 'members_roles',
			'edit_terms'             => 'edit_'   . 'members_roles',
			'delete_terms'           => 'delete_' . 'members_roles',
			'assign_terms'           => 'assign_' . 'members_roles',
		);

		if ( in_array($target, array('post_type', 'taxonomy') ) ) {
			return $capabilities[$target];
		}

		return array_merge($capabilities['post_type'], $capabilities['taxonomy']);
	}

	public static function _get_roles( ) {
		$roles = array( );

		// Add no default role
		$roles['none'] = __( 'No default role', 'mpt' );

		// Add registered roles
		$terms = MPT_Roles::get_roles( );
		if(empty($terms)){
			return $roles;
		}
		foreach( $terms as $term ) {
			$roles[$term->slug] = $term->name;
		}

		return $roles;
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	public static function _get_pages( ) {
		global $wpdb;
		
		$found = false;
		$pages_options = wp_cache_get('_get_pages', 'members-post-type', false, $found);
		if( $found == false ) {
			// Fix performances issues, use directly SQL
			$pages = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC" );

			$pages_options = array( 0 => __( 'Select a page', 'mpt' ) );
			if( !empty($pages) ) {
				foreach( $pages as $page ) {
					$pages_options[$page->ID] = $page->post_title;
				}
			}
		
			wp_cache_set('_get_pages', $pages_options, 'members-post-type');
		}
		

		
		return $pages_options;
	}
	
	/**
	 * Returns all the settings section
	 * 
	 * @return array
	 */
	public static function get_default_settings_sections( ) {
		return apply_filters( 'mpt_default_options_sections', (array) include( MPT_DIR . 'classes/helpers/default-sections.php' ) );
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public static function get_default_settings_fields( ) {
		// Get pages for build settings with list page
		$all_pages = MPT_Plugin::_get_pages( );
		
		return apply_filters( 'mpt_default_options_settings' , (array) include( MPT_DIR . 'classes/helpers/default-settings.php' ) );
	}
}