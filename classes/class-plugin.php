<?php
class MPT_Plugin {
    /**
     * Callback when plugin is actived by user
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
	}

    /**
     * Callback when plugin is deactived by user
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
}