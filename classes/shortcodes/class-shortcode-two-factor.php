<?php

class MPT_Shortcode_Two_Factor extends MPT_Shortcode {

	public const MEMBER_2FA_CHALLENGE_ID_META_NAME = '_mpt_two_factor_challenge_id';
	public const MEMBER_2FA_NONCE_META_NAME = '_mpt_two_factor_nonce';
	public const MEMBER_2FA_CODE_META_NAME = '_mpt_two_factor_code';

	/**
	 * Constructor, register hooks
	 */
	public function __construct() {
		$options = MPT_Options::get_option( 'mpt-security' );
		if ( ! isset( $options['enable-two-factor'] ) || false === (bool) $options['enable-two-factor'] ) {
			return;
		}
		add_shortcode( 'member-two-factor', [ __CLASS__, 'shortcode' ] );
		add_action( 'mpt_login', [ $this, 'mpt_login' ], 10, 2 );
		add_action( 'init', [ __CLASS__, 'init' ], 12 );
	}

	/**
	 * Intercepts members' log in to redirect them to the two-factor challenge.
	 *
	 * @param string $login member's login
	 * @param int $member_id member's id
	 *
	 * @return void
	 */
	public function mpt_login( $login, $member_id ) {
		$member = new MPT_Member( $member_id );
		if ( ! $member->exists() ) {
			return;
		}

		// Delete auth cookie before redirecting the member to two-factor challenge.
		MPT_Member_Auth::clear_auth_cookie();

		// Reset two-factor metadata for the member.
		delete_post_meta( $member->id, self::MEMBER_2FA_CHALLENGE_ID_META_NAME );
		delete_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME );
		delete_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME );

		// Create new challenge id to identify the member in two factor page.
		$challenge_id = wp_generate_uuid4();
		update_post_meta( $member->id, self::MEMBER_2FA_CHALLENGE_ID_META_NAME, $challenge_id );

		// Redirect member to two-factor page.
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
	 *
	 * @return string HTML of shortcode
	 */
	public static function shortcode() {
		// Skip render shortcode in the bo
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '';
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
		$member_data = isset( $_POST['mpttwofactor'] ) ? wp_unslash( $_POST['mpttwofactor'] ) : [];

		$challenge_id = isset( $member_data['challenge_id'] ) ? sanitize_text_field( $member_data['challenge_id'] ) : '';
		if ( empty( $challenge_id ) && ! $is_post_request ) {
			$challenge_id = isset( $_GET['challenge_id'] ) ? sanitize_text_field( $_GET['challenge_id'] ) : '';
		}

		if ( empty( $challenge_id ) ) {
			wp_safe_redirect( home_url( '/' ), 302, 'mpt' );
			exit;
		}

		$member = self::get_member_by_challenge_id( $challenge_id );
		if ( ! $member->exists() ) {
			wp_safe_redirect( home_url( '/' ), 302, 'mpt' );
			exit;
		}

		$login_nonce = self::create_login_nonce( $member );
		if ( ! $login_nonce ) {
			wp_die( esc_html__( 'Fail to create login nonce.', 'mpt' ) );
		}

		// Maybe print script to clean parameters from two-factor URL.
		self::maybe_clean_url();

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
	 * Process two-factor action.
	 */
	public static function init() {
		if ( ! isset( $_POST['mpttwofactor'] ) ) {
			return;
		}

		$mpttwofactor = wp_unslash( $_POST['mpttwofactor'] );

		$challenge_id = isset( $mpttwofactor['challenge_id'] ) ? $mpttwofactor['challenge_id'] : '';
		$nonce        = isset( $mpttwofactor['nonce'] ) ? $mpttwofactor['nonce'] : '';
		$code         = isset( $mpttwofactor['code'] ) ? $mpttwofactor['code'] : '';
		$member       = self::get_member_by_challenge_id( $challenge_id );
		if ( empty( $challenge_id ) || empty( $nonce ) || ! $member->exists() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( ! self::validate_login_nonce( $member, $nonce ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( isset( $_POST['mpt-two-factor-resend-code'] ) ) {
			self::generate_and_send_code( $member );
			MPT_Shortcode::set_message(
				'two-factor-code-refresh',
				esc_html__( 'A new authentication code has been sent.', 'mpt' ),
				'info'
			);

			return;
		}

		if ( ! self::validate_two_factor_code( $member, $code ) ) {
			MPT_Shortcode::set_message(
				'invalid-two-factor-code',
				esc_html__( 'Invalid two-factor authentication code.', 'mpt' )
			);

			return;
		}

		delete_post_meta( $member->id, self::MEMBER_2FA_CHALLENGE_ID_META_NAME );
		delete_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME );
		delete_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME );

		MPT_Member_Auth::set_auth_cookie( $member->id );
		wp_safe_redirect( MPT_Main::get_action_permalink( 'account' ) );
		exit;
	}

	/**
	 * Remove query param from URL.
	 *
	 * - challenge_id
	 *
	 * @return void
	 */
	protected static function maybe_clean_url() {
		if ( ! isset( $_GET['challenge_id'] ) ) {
			return;
		}

		$current_url  = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$filtered_url = remove_query_arg( 'challenge_id', $current_url );
		?>
        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, '<?php echo esc_url( $filtered_url ); ?>');
            }
        </script>
		<?php
		unset( $current_url );
		unset( $filtered_url );
	}

	/**
	 * Find member from two-factor challenge id.
	 *
	 * @param string $challenge_id
	 *
	 * @return MPT_Member|false;
	 */
	protected static function get_member_by_challenge_id( $challenge_id ) {
		if ( empty( $challenge_id ) ) {
			return false;
		}

		$member_id = MPT_Member::get_id_from_key_value( self::MEMBER_2FA_CHALLENGE_ID_META_NAME, $challenge_id );
		if ( empty( $member_id ) ) {
			return false;
		}

		$member = new MPT_Member( $member_id );
		if ( ! $member->exists() ) {
			return false;
		}

		return $member;
	}

	/**
	 * Create nonce to validate member two-factor operation.
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
	 * Create key from login nonce data.
	 *
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
	 * Generate a two-factor code metadata and send the code to the member by mail.
	 *
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
	 * Check if member has a valid two-factor code metadata.
	 *
	 * @param MPT_Member $member
	 *
	 * @return bool
	 */
	protected static function member_has_code( $member ) {
		$member_code = get_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME, true );

		return ! empty( $member_code ) && isset( $member_code['code'] ) && isset( $member_code['expiration'] ) && time() < (int) $member_code['expiration'];
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

	/**
	 * Validate two-factor login nonce.
	 *
	 * This custom nonce has a limited lifetime of 10 minutes.
	 *
	 * @param MPT_Member $member
	 * @param string $nonce
	 *
	 * @return bool
	 */
	protected static function validate_login_nonce( $member, $nonce ) {
		$user_login_nonce = get_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME, true );
		if ( empty( $user_login_nonce ) || ! isset( $user_login_nonce['expiration'] ) || ! isset( $user_login_nonce['key'] ) ) {
			return false;
		}

		$unverified_login_nonce  = [
			'member'     => $member->id,
			'expiration' => $user_login_nonce['expiration'],
			'key'        => $nonce,
		];
		$unverified_nonce_hashed = self::hash_key( $unverified_login_nonce );
		$nonce_is_valid          = $unverified_nonce_hashed && hash_equals( $user_login_nonce['key'], $unverified_nonce_hashed );

		if ( $nonce_is_valid && time() < (int) $user_login_nonce['expiration'] ) {
			return true;
		}

		delete_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME );

		return false;
	}

	/**
	 * Validate two-factor code.
	 *
	 * @param MPT_Member $member
	 * @param string $code
	 *
	 * @return bool
	 */
	protected static function validate_two_factor_code( $member, $code ) {
		$user_code = get_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME, true );
		if ( empty( $user_code ) || ! isset( $user_code['code'] ) || ! isset( $user_code['expiration'] ) ) {
			return false;
		}

		/**
		 * Validate the two-factor code match the stored value and is not expired.
		 * Expired code we'll be refreshed by {@see MPT_Shortcode_Two_Factor::generate_and_send_code()} when
		 * the two-factor form is displayed.
		 */
		$code_is_valid = hash_equals( $user_code['code'], wp_hash( $code ) );
		if ( ! $code_is_valid || time() > (int) $user_code['expiration'] ) {
			return false;
		}

		// Delete code after being successfully use.
		delete_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME );

		return true;
	}
}
