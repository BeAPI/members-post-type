<?php
class MPT_Taxonomy {
    /**
     * Register hooks
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct() {
		add_action('init', array(__CLASS__, 'init'), 8 );
	}

    /**
     * Register taxonomy on WP
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function init() {
		$labels = array(
			'name'                       => _x( 'Roles', 'Taxonomy General Name', 'mpt' ),
			'singular_name'              => _x( 'Role', 'Taxonomy Singular Name', 'mpt' ),
			'menu_name'                  => __( 'Roles', 'mpt' ),
			'all_items'                  => __( 'All Roles', 'mpt' ),
			'parent_item'                => __( 'Parent Role', 'mpt' ),
			'parent_item_colon'          => __( 'Parent Role:', 'mpt' ),
			'new_item_name'              => __( 'New Role Name', 'mpt' ),
			'add_new_item'               => __( 'Add New Role ', 'mpt' ),
			'edit_item'                  => __( 'Edit Role ', 'mpt' ),
			'update_item'                => __( 'Update Role ', 'mpt' ),
			'separate_items_with_commas' => __( 'Separate roles with commas', 'mpt' ),
			'search_items'               => __( 'Search roles', 'mpt' ),
			'add_or_remove_items'        => __( 'Add or remove roles ', 'mpt' ),
			'choose_from_most_used'      => __( 'Choose from the most used roles', 'mpt' ),
		);

		$args = array(
			'capabilities'               => MPT_Plugin::get_capabilities('taxonomy'),
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'query_var'                  => false,
			'show_ui'                    => false,
			'show_admin_column'          => false,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => false,
			'update_count_callback'      => false,
			'show_in_rest'               => true,
		);
		
		$options = (array) MPT_Options::get_option( 'mpt-main' );
		if ( isset($options['features']) && isset($options['features']['role-manager']) ) {
			$args['show_ui'] = $args['show_admin_column'] = true;
		}

		register_taxonomy( MPT_TAXO_NAME, MPT_CPT_NAME, apply_filters( 'mpt_register_taxonomy', $args, MPT_TAXO_NAME, MPT_CPT_NAME )  );
	}
}