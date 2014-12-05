<?php
class MPT_Admin_Import {
	private static $_rapport_arr = array();
	const option_name = "mpt_last_import_report";
	
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__ , 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__ , 'admin_init' ) );
	}
	
	public static function admin_menu() {
		$hook = add_submenu_page('edit.php?post_type=member', __('Import / Export members', 'mpt'), __('Import / Export members', 'mpt'), 'manage_options', 'member-import-export', array( __CLASS__, 'page' ));
		add_action( 'admin_head-'.$hook, array( __CLASS__ , 'admin_head' ) );
	}
	
	public static function admin_head() {
		wp_enqueue_style ( MPT_CPT_NAME . '-post', MPT_URL . 'assets/css/admin.css', array( ), MPT_VERSION, 'all' );
	}
	
	public static function page() {
		$report = get_option( self::option_name );
		
		include (MPT_DIR . 'views/admin/page-import-export.php');
	}
	
	public static function admin_init( ) {
		if( isset( $_POST['mpt_action'] ) && $_POST['mpt_action'] == 'mpt_import_action' ) {
			self::admin_init_import( );
		}
		
		return false;
	}
	
	public static function admin_init_import() {
		// Check the nonce
		check_admin_referer('import-members');
		
		// If we have a file
		if( !isset( $_FILES['csv-file'] ) ) {
			return false;
		}
		
		// Setup new report
		self::$_rapport_arr = array(
			'report_date' => time(),
			'ignore_line' => array(),
			'import_status' => array(),
		);

		// Load CSV
		$rows = self::load_csv( $_FILES['csv-file']['tmp_name'], true );

		// Insert members from CSV data
		self::insert_members( $rows );
		
		// Save last report
		return update_option( self::option_name, self::$_rapport_arr );
	}
	
	/**
	 * Load a CSV file.
	 * 
	 * @param string $file path to the csv file.
	 * @param bool $has_header ignore the first line if it's the header.
	 *
	 * @return array an array containing all the line.
	 */
	private static function load_csv( $file, $has_header = false ) {
		$rows = array();
		$headers = array();
		$current_line = 1; // use to track current line of the CSV file in case of error
		
		$handle = fopen($file ,'r');
		while ( ($data = fgetcsv($handle) ) !== FALSE ) {
			// If the first line is the header, ignore it.
			if( $has_header ) {
				$headers = self::parse_headers( $data[0] );
				if( empty( $headers ) ) {
					return false;
				}
				$has_header = false;
				continue;
			}

			$tmp = explode(";", $data[0]);

			// If the email of the username are empty, abord and continue with the next line.
			if( empty($tmp[0]) || empty($tmp[3]) ) {
				self::$_rapport_arr['ignore_line'][] = array( 'line' => $current_line, 'content' => utf8_encode($data[0]), 'operation' => __('missing email and/or username', 'mpt'), 'status' => 'error' );
				$current_line++;
				continue;
			}

			$csv_line = array();
			foreach( $headers as $header_name => $col_index ) {
				$csv_line[ $header_name ] = utf8_encode( $tmp[ $col_index ] );
			}
			
			$rows[] = $csv_line;
			
			$current_line++;
		}
		fclose($handle);
		
		return $rows;
	}
	
	/**
	 * Insert/update the members.
	 * 
	 * @param array $rows an array containing the CSV line.
	 *
	 * @return array
	 */
	public static function insert_members( $rows ) {
		if( empty( $rows ) ) {
			return false;
		}
		
		foreach( $rows as $member ) {
			
			$tmp_member = new MPT_Member();
			$tmp_member->fill_by('email', $member['email']);
			
			if( $tmp_member->exists() ) {
				foreach( $member as $meta_name => $meta_value ) {
					if( 'email' == $meta_name || 'password' == $meta_name ) {
						continue;
					}

					$tmp_member->set_meta_value( $meta_name, $meta_value );
				}

				$tmp_member->regenerate_post_title();
				
				self::$_rapport_arr['import_status'][] = array( 'member' => $member['email'], 'operation' => 'updated', 'status' => 'success' );
			} else {
				$args = array();
				$args['password'] 	= wp_generate_password( 8 );
				$args['username'] 	= sanitize_text_field( $member['username'] );
				$args['email'] 		= sanitize_email( $member['email'] );
				$args['first_name'] = sanitize_text_field( $member['first_name'] );
				$args['last_name'] 	= sanitize_text_field( $member['last_name'] );

				$metas = array_diff_assoc( $member, $args );
				
				// insert member
				$member_id = MPT_Member_Utility::insert_member( $args );
				
				$tmp_member = new MPT_Member();
				$tmp_member->fill_by('id', $member_id);
				if( $tmp_member->exists() ) {

					//Insert remaining metas
					if( !empty( $metas ) ) {
						foreach( $metas as $meta_name => $meta_value ) {
							if( 'email' == $meta_name || 'password' == $meta_name ) {
								continue;
							}
							$tmp_member->set_meta_value( $meta_name, $meta_value );
						}
					}

					//Send member notification
					$tmp_member->register_notification( $args['password'] );
					
					// Send a mail to the new registered user.
					//$message  = sprintf(__('Account creation for [%s] :', 'mpt'), get_bloginfo( 'name' )) . "\r\n";
					//$message .= sprintf(__('Name: %s %s', 'mpt'), $args['last_name'], $args['first_name']) . "\r\n";
					//$message .= sprintf(__('Username: %s', 'mpt'), $args['username']) . "\r\n";
					//$message .= sprintf(__('Password: %s', 'mpt'), $args['password']) . "\r\n";
					//$message .= mpt_get_login_permalink() . "\r\n";
					//@wp_mail($args['email'], sprintf(__('[%s] Your username and password', 'mpt'), get_bloginfo( 'name' )), $message);
					
					self::$_rapport_arr['import_status'][] = array( 'member' => $member['email'], 'operation' => 'created', 'status' => 'success' );
				} else {
					self::$_rapport_arr['import_status'][] = array( 'member' => $member['email'], 'operation' => 'created', 'status' => 'error' );
				}
			}
		}
		
		return true;
	}

	/**
	 * Parse the CSV headers
	 *
	 * @param $headers
	 *
	 * @return array
	 */
	private static function parse_headers( $headers ) {
		$default_headers = array(
			'email' => 0,
			'last_name' => 1,
			'first_name' => 2,
			'username' => 3,
			'_counter_sign_on' => 4,
			'_last_sign_on_date' => 5,
		);
		$meta_headers = array();

		if( empty( $headers ) ) {
			return $default_headers;
		}

		$headers = explode( ";", $headers );
		if( count( $headers ) <= 6 ) {
			return $default_headers;
		}

		$nb_column = count( $headers );
		for( $i = 6; $i < $nb_column; $i++ ) {
			if( false !== strpos( $headers[$i], 'meta:' ) ) {
				$meta = explode( ':', $headers[$i] );
				if( !empty( $meta[1] ) ) {
					$meta_headers[ sanitize_title( $meta[1] ) ] = $i;
				}
			}
		}

		if( !empty( $meta_headers ) ) {
			return array_merge( $default_headers, $meta_headers );
		} else {
			return $default_headers;
		}
	}
}