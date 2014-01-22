<?php
class MPT_Post_Type {
	/**
     * Register hooks
     * 
     * @access public
     *
     * @return void.
     */
	public function __construct() {
		add_action('init', array(__CLASS__, 'init') );
		add_filter('post_updated_messages', array(__CLASS__, 'post_updated_messages') );
	}

    /**
     * Register custom post type for Members 
     * 
     * @access public
     * @static
     *
     * @return void.
     */
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
			'delete_with_user'    => false,
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
			'menu_icon'           => null,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'map_meta_cap'        => true,
			'capability_type'     => 'member' // Let's WP do their job
		);

		register_post_type( MPT_CPT_NAME, apply_filters( 'mpt_register_post_type', $args, MPT_CPT_NAME ) );
	}

    /**
     * Customize message on CPT admin
     * 
     * @param array $messages Array with messages for admin interface.
     *
     * @access public
     *
     * @return array $messages.
     */
	public static function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages[MPT_CPT_NAME] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Member updated. <a href="%s">View member</a>', 'mpt'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.', 'mpt'),
			3 => __('Custom field deleted.', 'mpt'),
			4 => __('Member updated.', 'mpt'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Member restored to revision from %s', 'mpt'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Member published. <a href="%s">View member</a>', 'mpt'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Member saved.', 'mpt'),
			8 => sprintf( __('Member submitted. <a target="_blank" href="%s">Preview member</a>', 'mpt'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Member scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview member</a>', 'mpt'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Member draft updated. <a target="_blank" href="%s">Preview member</a>', 'mpt'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}
}
