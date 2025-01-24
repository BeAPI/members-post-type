<?php

class MPT_Shortcode_Two_Factor extends MPT_Shortcode {

	public const MEMBER_2FA_CHALLENGE_ID_META_NAME = '_mpt_two_factor_challenge_id';
	public const MEMBER_2FA_NONCE_META_NAME = '_mpt_two_factor_nonce';
	public const MEMBER_2FA_CODE_META_NAME = '_mpt_two_factor_code';

	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		add_shortcode( 'member-two-factor', [ __CLASS__, 'shortcode' ] );
		add_action( 'mpt_login', [ $this, 'mpt_login' ], 10, 2 );
		add_action( 'init', [ __CLASS__, 'init' ], 12 );
	}

	/**
	 * @param string $login
	 * @param int $member_id
	 *
	 * @return void
	 */
	public function mpt_login( $login, $member_id ) {
		$member = new MPT_Member( $member_id );
		if ( ! $member->exists() ) {
			return;
		}

		MPT_Member_Auth::clear_auth_cookie();

		$challenge_id = wp_generate_uuid4();
		update_post_meta( $member->id, self::MEMBER_2FA_CHALLENGE_ID_META_NAME, $challenge_id );
		wp_safe_redirect(
			add_query_arg(
				[
					'challenge_id' => $challenge_id,
				],
				MPT_Main::get_action_permalink( 'two-factor' )
			)
		);
		exit();
	}

	/**
	 * Render shortcode, use local or theme template
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		if (
			( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
		) {
			return;
		}

		// Member logged-in ?
		if ( mpt_is_member_logged_in() ) {
			apply_filters( 'mpt_shortcode_login_member_logged_in', '<!-- Members already logged-in. -->', mpt_get_current_member() );

			// Skip render shortcode in the bo
			if ( is_admin() || ! empty( $_GET['_locale'] ) ) {
				return;
			}

			$account_link = MPT_Main::get_action_permalink( 'account' );

			if ( ! empty( $account_link ) ) {
				wp_safe_redirect( $account_link, 302, 'mpt' );
				exit;
			}
		}

		$is_post_request = ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) );

		// Get data from POST, cleanup it
		$member_data = isset( $_POST['mpttwofactor'] ) ? $_POST['mpttwofactor'] : [];

		$challenge_id = isset( $member_data['challenge_id'] ) ? $member_data['challenge_id'] : '';
		if ( empty( $challenge_id ) && ! $is_post_request ) {
			$challenge_id = isset( $_GET['challenge_id'] ) ? $_GET['challenge_id'] : '';
		}

		if ( empty( $challenge_id ) ) {
			wp_safe_redirect( home_url( '/' ), 302, 'mpt' );
			exit;
		}

		$member_id = MPT_Member::get_id_from_key_value( self::MEMBER_2FA_CHALLENGE_ID_META_NAME, $challenge_id );
		if ( empty( $member_id ) ) {
			wp_safe_redirect( home_url( '/' ), 302, 'mpt' );
			exit;
		}

		$member = new MPT_Member( $member_id );
		if ( ! $member->exists() ) {
			wp_safe_redirect( home_url( '/' ), 302, 'mpt' );
			exit;
		}

		$login_nonce = self::create_login_nonce( $member );
		if ( ! $login_nonce ) {
			wp_die( esc_html__( 'Fail to create login nonce.', 'mpt' ) );
		}

		if ( ! self::member_has_code( $member ) ) {
			self::generate_and_send_code( $member );
		}

		// Parse vs defaults
		$member_data = wp_parse_args(
			$member_data,
			[
				'rememberme'  => '',
				'redirect_to' => '',
			]
		);

		$member_data['challenge_id'] = $challenge_id;
		$member_data['nonce']        = $login_nonce['key'];

		return parent::load_template( 'member-two-factor', $member_data );
	}

	/**
	 * Test if the members try to login
	 */
	public static function init() {
		if ( isset( $_POST['mptlogin'] ) ) {
			// Cleanup data
			$_POST['mptlogin'] = stripslashes_deep( $_POST['mptlogin'] );

			// Check _NONCE
			$nonce = isset( $_POST['_mptnonce'] ) ? $_POST['_mptnonce'] : '';
			if ( ! mpt_verify_nonce( $nonce, 'mptlogin' ) ) {
				parent::set_message( 'check-nonce', 'Security check failed', 'error' );

				return false;
			}

			// Parse vs defaults
			$_POST['mptlogin'] = wp_parse_args( $_POST['mptlogin'], [
				'username'    => '',
				'rememberme'  => '',
				'redirect_to' => '',
				'rememberme'  => false,
			] );

			// Try sign-on
			$signon = MPT_Member_Auth::signon( [
				'm_login'    => $_POST['mptlogin']['username'],
				'm_password' => $_POST['mptlogin']['password'],
				'remember'   => $_POST['mptlogin']['rememberme'],
			] );

			// result sign-on are error ?
			if ( is_wp_error( $signon ) ) {
				parent::set_message( $signon->get_error_code(), $signon->get_error_message(), 'error' );

				return false;
			}

			// Failback redirect to home...
			$account_id   = MPT_Main::get_action_page_id( 'account' );
			$redirect_url = ! empty( $account_id ) ? get_permalink( $account_id ) : home_url( '/' );
			$redirect_to  = ( isset( $_POST['mptlogin']['redirect_to'] ) && ! empty( $_POST['mptlogin']['redirect_to'] ) ) ? $_POST['mptlogin']['redirect_to'] : $redirect_url;

			// Need to look at the URL the way it will end up in wp_redirect()
			$redirect_to = wp_sanitize_redirect( $redirect_to );
			$redirect_to = wp_validate_redirect( $redirect_to, home_url( '/' ) );

			wp_redirect( apply_filters( 'mpt_login_redirect', $redirect_to ) );
			exit();
		}

		return false;
	}

	/**
	 * Create nonce to validate member 2FA operation.
	 *
	 * @param MPT_Member $member
	 *
	 * @return array{member: int, expiration: int, key: string}|bool
	 */
	protected static function create_login_nonce( $member ) {
		$login_nonce = [
			'member'     => $member->id,
			'expiration' => time() + ( 10 * MINUTE_IN_SECONDS ),
		];

		try {
			$login_nonce['key'] = bin2hex( random_bytes( 32 ) );
		} catch ( Exception $ex ) {
			$login_nonce['key'] = wp_hash( $member->id . wp_rand() . microtime(), 'nonce' );
		}

		$hash_key = self::hash_key( $login_nonce );
		if ( ! $hash_key ) {
			return false;
		}
		$login_nonce_metadata = [
			'expiration' => $login_nonce['expiration'],
			'key'        => $hash_key,
		];
		if ( ! update_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME, $login_nonce_metadata ) ) {
			return false;
		}

		return $login_nonce;
	}

	/**
	 * @param array $data
	 *
	 * @return string|bool
	 */
	protected static function hash_key( $data ) {
		$serialized_data = json_encode( $data );
		if ( ! $serialized_data ) {
			return false;
		}

		return wp_hash( $serialized_data, 'nonce' );
	}

	/**
	 * @param MPT_Member $member
	 *
	 * @return void
	 */
	protected static function generate_and_send_code( $member ) {
		$code        = self::get_code();
		$member_code = [
			'code'       => wp_hash( $code ),
			'expiration' => time() + ( 10 * MINUTE_IN_SECONDS ),
		];

		update_post_meta(
			$member->id,
			self::MEMBER_2FA_CODE_META_NAME,
			$member_code
		);

		$mail_2fa_subject = wp_strip_all_tags( sprintf( 'Your confirmation code for %s', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) );
		$mail_2fa_message = wp_strip_all_tags( sprintf( 'Enter the code %s to log in.', $code ) );

		$mpt_mail_2fa_code_subject = apply_filters( 'mpt_mail_2fa_code_subject', $mail_2fa_subject, $code, $member->id );
		$mpt_mail_2fa_code_message = apply_filters( 'mpt_mail_2fa_code_message', $mail_2fa_message, $code, $member->id );

		wp_mail( $member->email, $mpt_mail_2fa_code_subject, $mpt_mail_2fa_code_message );
	}

	/**
	 * @param MPT_Member $member
	 *
	 * @return bool
	 */
	protected static function member_has_code( $member ) {
		$member_code = get_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME, true );

		return ! empty( $member_code ) && isset( $member_code['code'] ) && isset( $member_code['expiration'] ) && (int) $member_code['expiration'] < time();
	}

	/**
	 * Generate random eight digit code.
	 *
	 * @return string
	 */
	protected static function get_code() {
		$characters    = '0123456789';
		$random_string = '';
		for ( $i = 0; $i < 8; $i ++ ) {
			$random_string .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random_string;
	}
}
