<?php class MPT_Admin_Export {

	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	public static function admin_menu() {
		$hook = add_submenu_page( 'edit.php?post_type=member', self::get_page_title(), self::get_page_title(), 'manage_options', 'member-export', array( __CLASS__, 'page' ) );
		add_action( 'admin_head-' . $hook, array( __CLASS__, 'admin_head' ) );
	}

	public static function admin_head() {
		wp_enqueue_style( MPT_CPT_NAME . '-post', MPT_URL . 'assets/css/admin.css', array(), MPT_VERSION, 'all' );
	}

	public static function page() {
		include( MPT_DIR . 'views/admin/page-export.php' );
	}

	public static function admin_init() {
		if ( isset( $_POST['mpt_action'] ) && $_POST['mpt_action'] == 'mpt_export_action' ) {
			return self::admin_init_export();
		}

		return false;
	}

	public static function admin_init_export() {
		// Check the nonce
		check_admin_referer( 'export-members' );

		$header_titles = array(
			__( 'Email', 'mpt' ),
			__( 'Lastname', 'mpt' ),
			__( 'Firstname', 'mpt' ),
			__( 'Username', 'mpt' ),
			__( 'Counter Sign On', 'mpt' ),
			__( 'Last Sign On Date', 'mpt' )
		);

		//Allow users to specify additionnal columns to export.
		$header_meta = apply_filters( 'mpt_export_meta_headers', array() );
		if ( ! empty( $header_meta ) ) {
			$header_meta   = array_map( array( __CLASS__, 'prefix_header_meta' ), $header_meta );
			$header_titles = array_merge( $header_titles, $header_meta );
		}

		$member_query = new WP_Query( array(
			'post_type'   => MPT_CPT_NAME,
			'post_status' => 'publish',
			'nopaging'    => true
		) );

		if ( ! $member_query->have_posts() ) {
			return false;
		}

		$list = array();
		while ( $member_query->have_posts() ) {
			$member_query->the_post();
			$member_id                = get_the_ID();
			$member_email             = get_post_meta( $member_id, 'email', true );
			$member_last_name         = get_post_meta( $member_id, 'last_name', true );
			$member_first_name        = get_post_meta( $member_id, 'first_name', true );
			$member_username          = get_post_meta( $member_id, 'username', true );
			$member_counter_sign_on   = get_post_meta( $member_id, '_counter_sign_on', true );
			$member_last_sign_on_date = get_post_meta( $member_id, '_last_sign_on_date', true );

			$current_member = array(
				$member_email,
				$member_last_name,
				$member_first_name,
				$member_username,
				$member_counter_sign_on,
				$member_last_sign_on_date
			);

			//Allow users to add meta value when exporting.
			$current_member_meta = apply_filters( 'mpt_export_meta_values', array(), $member_id );
			if ( ! empty( $current_member_meta ) ) {
				$current_member = array_merge( $current_member, $current_member_meta );
			}

			$list[] = $current_member;
		}

		// csv header
		header( 'Content-Type: text/csv;' );
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: private" );
		header( "Content-Disposition: attachment; filename=members_export-" . date( 'd-m-y' ) . ".csv" );
		header( "Accept-Ranges: bytes" );

		$outstream = fopen( "php://output", 'w' );

		//Put header titles
		fputcsv( $outstream, array_map( 'utf8_decode', $header_titles ), ';' );

		// Put lines in csv file
		foreach ( $list as $fields ) {
			fputcsv( $outstream, array_map( 'utf8_decode', $fields ), ';' );
		}

		fclose( $outstream );
		exit();
	}

	/**
	 * Prefix the additionnal headers with "meta:".
	 *
	 * @param string $name the column name.
	 *
	 * @return string
	 */
	public static function prefix_header_meta( $name ) {
		if ( empty( $name ) ) {
			return $name;
		}

		return 'meta:' . $name;
	}

	/**
	 * Get the import page title
	 *
	 * @author Maxime CULEA
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_page_title() {
		return esc_html( __( 'Export members', 'mpt' ) );
	}
}