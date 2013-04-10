<?php
class MPT_Admin_Main {
	/**
     * Register hooks
     * 
     * @access public
     *
     * @return void.
     */
	public function __construct( ) {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 10, 1 );

          new MPT_Admin_Settings_Main();
	}

    /**
     * admin_enqueue_scripts
     * 
     * @param mixed $hook Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
	public static function admin_enqueue_scripts( $hook ) {
		global $post;

		if ( in_array( $hook, array( 'edit.php', 'post-new.php' ) ) && isset( $_GET['post_type'] ) && $_GET['post_type'] == MPT_CPT_NAME || in_array( $hook, array( 'post.php' ) ) && isset( $post ) && $post->post_type == MPT_CPT_NAME ) {
			wp_enqueue_style( MPT_CPT_NAME . '-admin', MPT_URL . '/assets/css/admin.css', array( ), MPT_VERSION, 'all' );
		}
	}	
}