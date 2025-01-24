<?php

class MPT_Two_Factor {

	public const MEMBER_2FA_NONCE_META_NAME = '_mpt_two_factor_nonce';
	public const MEMBER_2FA_CODE_META_NAME = '_mpt_two_factor_code';

	public function __construct() {
		add_action( 'mpt_login', [ $this, 'mpt_login' ], 10, 2 );
		add_action( 'init', [ $this, 'process_2fa_request' ], 1 );
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
		self::show_two_factor_login( $member );
		exit();
	}

	public function process_2fa_request() {
		if ( ! isset( $_POST['mpt-action'] ) || 'validate_2fa' !== $_POST['mpt-action'] ) {
			return;
		}

		$member_id = absint( $_POST['mpt-auth-id'] ?? 0 );
		$nonce     = wp_unslash( $_POST['mpt-auth-nonce'] ?? '' );
		$code      = wp_unslash( $_POST['mpt-two-factor-code'] ?? '' );
		$member    = new MPT_Member( $member_id );
		if ( empty( $member_id ) || empty( $nonce ) || empty( $code ) || ! $member->exists() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( ! $this->validate_login_nonce( $member, $nonce ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		if ( ! $this->validate_2fa_code( $member, $code ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		delete_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME );
		delete_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME );

		MPT_Member_Auth::set_auth_cookie( $member->id );
		wp_safe_redirect( MPT_Main::get_action_permalink( 'account' ) );
		exit;
	}

	/**
	 * @param MPT_Member $member
	 *
	 * @return void
	 */
	public function show_two_factor_login( $member ) {
		$login_nonce = $this->create_login_nonce( $member );
		if ( ! $login_nonce ) {
			wp_die( esc_html__( 'Fail to create 2FA nonce.', 'mpt' ) );
		}

		if ( ! $this->member_has_code( $member ) ) {
			$this->generate_and_send_code( $member );
		}

        $remember_me = (bool) ( $_POST['mptlogin']['rememberme'] ?? false );
        $redirect_to = wp_unslash( $_POST['mptlogin']['redirect_to'] ?? '' );
        $redirect_to = wp_sanitize_redirect( $redirect_to );
        $redirect_to = wp_validate_redirect( $redirect_to, MPT_Main::get_action_permalink('account') );

		ob_start();
		?>
        <p class="two-factor-prompt"><?php esc_html_e( 'A verification code has been sent to the email address associated with your account.', 'mpt' ); ?></p>
        <form method="post" class="mpt-two-factor-form" action="<?php echo esc_url_raw( mpt_get_login_permalink() ); ?>">
            <div class="mpt-field">
                <label for="mpt-two-factor-code"><?php esc_html_e( 'Verification Code:', 'mpt' ); ?></label>
                <input id="mpt-two-factor-code" required="required" type="text" inputmode="numeric"
                       name="mpt-two-factor-code" placeholder="12345678"/>
            </div>
            <div class="mpt-field">
                <input type="hidden" name="mpt-action" value="validate_2fa"/>
                <input type="hidden" name="mpt-auth-id" value="<?php echo esc_attr( $member->id ); ?>"/>
                <input type="hidden" name="mpt-auth-nonce" value="<?php echo esc_attr( $login_nonce['key'] ); ?>"/>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>"/>
                <input type="hidden" name="remember_me" value="<?php echo esc_attr( (int) $remember_me ); ?>"/>
                <input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
            </div>
        </form>
		<?php
		$form_2fa = ob_get_clean();
		wp_die(
			(string) $form_2fa,
			'Two factor challenge',
			[
				'response' => 200,
			]
		);
	}

	/**
	 * Create nonce to validate member 2FA operation.
	 *
	 * @param MPT_Member $member
	 *
	 * @return array{member: int, expiration: int, key: string}|bool
	 */
	protected function create_login_nonce( $member ) {
		$login_nonce = [
			'member'     => $member->id,
			'expiration' => time() + ( 10 * MINUTE_IN_SECONDS ),
		];

		try {
			$login_nonce['key'] = bin2hex( random_bytes( 32 ) );
		} catch ( Exception $ex ) {
			$login_nonce['key'] = wp_hash( $member->id . wp_rand() . microtime(), 'nonce' );
		}

		$hash_key = $this->hash_key( $login_nonce );
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
	 * @param MPT_Member $member
	 * @param string $nonce
	 *
	 * @return bool
	 */
	protected function validate_login_nonce( $member, $nonce ) {
		$user_login_nonce = get_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME, true );
		if ( empty( $user_login_nonce ) || ! isset( $user_login_nonce['expiration'] ) || ! isset( $user_login_nonce['key'] ) ) {
			return false;
		}

		$unverified_login_nonce  = [
			'member'     => $member->id,
			'expiration' => $user_login_nonce['expiration'],
			'key'        => $nonce,
		];
		$unverified_nonce_hashed = $this->hash_key( $unverified_login_nonce );
		$nonce_is_valid          = $unverified_nonce_hashed && hash_equals( $user_login_nonce['key'], $unverified_nonce_hashed );

		if ( $nonce_is_valid && time() < (int) $user_login_nonce['expiration'] ) {
			return true;
		}

		delete_post_meta( $member->id, self::MEMBER_2FA_NONCE_META_NAME );

		return false;
	}

	/**
	 * @param MPT_Member $member
	 *
	 * @return void
	 */
	protected function generate_and_send_code( $member ) {
		$code        = $this->get_code();
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
	protected function member_has_code( $member ) {
		$member_code = get_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME, true );

		return ! empty( $member_code ) && isset( $member_code['code'] ) && isset( $member_code['expiration'] ) && (int) $member_code['expiration'] < time();
	}

	public function validate_2fa_code( $member, $code ) {
		$user_code = get_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME, true );
		if ( empty( $user_code ) || ! isset( $user_code['code'] ) || ! isset( $user_code['expiration'] ) ) {
			return false;
		}

		if ( ! hash_equals( $user_code['code'], wp_hash( $code ) ) || (int) $user_code['expiration'] > time() ) {
			return false;
		}

		delete_post_meta( $member->id, self::MEMBER_2FA_CODE_META_NAME );

		return true;
	}

	/**
	 * Generate random eight digit code.
	 *
	 * @return string
	 */
	protected function get_code() {
		$characters    = '0123456789';
		$random_string = '';
		for ( $i = 0; $i < 8; $i ++ ) {
			$random_string .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random_string;
	}

	/**
	 * @param array $data
	 *
	 * @return string|bool
	 */
	protected function hash_key( $data ) {
		$serialized_data = json_encode( $data );
		if ( ! $serialized_data ) {
			return false;
		}

		return wp_hash( $serialized_data, 'nonce' );
	}
}
