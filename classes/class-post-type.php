<?php
class MPT_Post_Type {
	public function __construct() {
		add_action('init', array(__CLASS__, 'init') );
	}

	public static function init() {
		$labels = array(
			'name'                => _x( 'Members', 'Post Type General Name', 'mpt' ),
			'singular_name'       => _x( 'Member', 'Post Type Singular Name', 'mpt' ),
			'menu_name'           => __( 'Members', 'mpt' ),
			'parent_item_colon'   => __( 'Parent Member:', 'mpt' ),
			'all_items'           => __( 'All Members', 'mpt' ),
			'view_item'           => __( 'View Member', 'mpt' ),
			'add_new_item'        => __( 'Add New Member', 'mpt' ),
			'add_new'             => __( 'New Member', 'mpt' ),
			'edit_item'           => __( 'Edit Member', 'mpt' ),
			'update_item'         => __( 'Update Member', 'mpt' ),
			'search_items'        => __( 'Search members', 'mpt' ),
			'not_found'           => __( 'No members found', 'mpt' ),
			'not_found_in_trash'  => __( 'No members found in Trash', 'mpt' ),
		);

		$args = array(
			'label'               => __( 'Members', 'mpt' ),
			'description'         => __( 'Members as post type', 'mpt' ),
			'labels'              => $labels,
			'supports'            => array( 'thumbnail' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'menu_position'       => 70,
			'menu_icon'           => '',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post' // TODO: Use custom capability
		);

		register_post_type( MPT_CPT_NAME, $args );
	}
}