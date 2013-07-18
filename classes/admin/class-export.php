<?php
class MPT_Admin_Export {
	static $rapport_arr = array( );
	private static $option_name = "mpt_last_import_report";

	public function __construct( ) {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	public static function admin_init( ) {
		if( isset( $_POST['mpt_action'] ) && $_POST['mpt_action'] == 'mpt_export_action' ) {
			self::admin_init_export( );
		}
		
		return false;
	}

	public static function admin_init_export( ) {
		// Check the nonce
		self::_check_nonce( 'export-members' );

		$header_titles = array( __( 'Email', 'mpt' ), __( 'Lastname', 'mpt' ), __( 'Firstname', 'mpt' ), __( 'Username', 'mpt' ), __( 'Counter Sign On', 'mpt' ), __( 'Last Sign On Date', 'mpt' ) );

		$member_query = new WP_Query( array(
			'post_type' => MPT_CPT_NAME,
			'post_status' => 'publish',
			'nopaging' => true
		) );

		if( !$member_query->have_posts( ) ) {
			return false;
		}

		$list = array( );
		while( $member_query->have_posts( ) ) {
			$member_query->the_post( );
			$member_id = get_the_ID( );
			$member_email = self::_get_meta( $member_id, 'email' );
			$member_last_name = self::_get_meta( $member_id, 'last_name' );
			$member_first_name = self::_get_meta( $member_id, 'first_name' );
			$member_username = self::_get_meta( $member_id, 'username' );
			$member_counter_sign_on = self::_get_meta( $member_id, '_counter_sign_on' );
			$member_last_sign_on_date = self::_get_meta( $member_id, '_last_sign_on_date' );

			$list[] = array(
				$member_email,
				$member_last_name,
				$member_first_name,
				$member_username,
				$member_counter_sign_on,
				$member_last_sign_on_date
			) ;
		}

		// csv header
		header( 'Content-Type: text/csv;' );
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: private" );
		header( "Content-Disposition: attachment; filename=members_export-".date( 'd-m-y' ).".csv" );
		header( "Accept-Ranges: bytes" );

		$outstream = fopen( "php://output", 'w' );
		//Put header titles
		fputcsv( $outstream, array_map( 'utf8_decode', $header_titles ), ';' );
		// Put lines in csv file
		foreach( $list as $fields ) {
			fputcsv( $outstream, array_map( 'utf8_decode', $fields ), ';' );
		}
		fclose( $outstream );
		die( );
	}

	/**
	 * Check the nonce.
	 *
	 * @param string $slug text to verify the nonce.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	private static function _check_nonce( $slug = '' ) {
		if( !isset( $_POST['wp_nonce'] ) || !wp_verify_nonce( $_POST['wp_nonce'], $slug ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Check the nonce.
	 *
	 * @param int $member_id the id of member.
	 * @param string $slug text to verify the nonce.
	 * @return string or empty.
	 */
	private static function _get_meta( $member_id = 0, $slug = '' ) {
		return get_post_meta( $member_id, $slug, true );
	}

}