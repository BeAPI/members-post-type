<?php
class MPT_Taxonomy {
	public function __construct() {
		add_action('init', array(__CLASS__, 'init'), 11 );
	}

	public static function init() {
		$labels = array(
			'name'                       => _x( 'Roles', 'Taxonomy General Name', 'mpt' ),
			'singular_name'              => _x( 'Role', 'Taxonomy Singular Name', 'mpt' ),
			'menu_name'                  => __( 'Role', 'mpt' ),
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
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => false,
			// TODO: Manage custom capabilities
		);

		register_taxonomy( 'member-role', MPT_CPT_NAME, $args );
	}
}