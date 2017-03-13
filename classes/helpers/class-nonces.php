<?php
class MPT_Nonces {

	/**
	 * Get the time-dependent variable for nonce creation.
	 *
	 * A nonce has a lifespan of two ticks. Nonces in their second tick may be
	 * updated, e.g. by autosave.
	 *
	 * @since 0.6.0
	 *
	 * @return float Float value rounded up to the next highest integer.
	 */
	public static function nonce_tick() {
		/**
		 * Filter the lifespan of nonces in seconds.
		 *
		 * @since 0.6.0
		 *
		 * @param int $lifespan Lifespan of nonces in seconds. Default 86,400 seconds, or one day.
		 */
		$nonce_life = apply_filters( 'mpt_nonce_life', DAY_IN_SECONDS );

		return ceil(time() / ( $nonce_life / 2 ));
	}

	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @since 0.6.0
	 *
	 * @param string     $nonce  Nonce that was used in the form to verify
	 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
	 * @return bool Whether the nonce check passed or failed.
	 */
	public static function verify_nonce( $nonce, $action = -1 ) {
		$nonce = (string) $nonce;
		$member = mpt_get_current_member();
		$mid = !is_a( $member, 'MPT_Member' ) ? 0 : (int) $member->id;
		if ( ! $mid ) {
			/**
			 * Filter whether the user who generated the nonce is logged out.
			 *
			 * @since 0.6.0
			 *
			 * @param int    $mid    ID of the nonce-owning member.
			 * @param string $action The nonce action.
			 */
			$mid = apply_filters( 'nonce_mpt_logged_out', $mid, $action );
		}

		if ( empty( $nonce ) ) {
			return false;
		}

		$token = self::get_session_token();
		$i = self::nonce_tick();

		// Nonce generated 0-12 hours ago
		$expected = substr( wp_hash( 'member-'.$i . '|' . $action . '|' . $mid . '|' . $token, 'nonce'), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		// Nonce generated 12-24 hours ago
		$expected = substr( wp_hash( 'member-'.( $i - 1 ) . '|' . $action . '|' . $mid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 2;
		}

		// Invalid nonce
		return false;
	}

	/**
	 * @param int $action
	 *
	 * @return string
	 * @author Nicolas Juen
	 */
	public static function create_nonce($action = -1) {
		$member = mpt_get_current_member();
		$mid = !is_a( $member, 'MPT_Member' ) ? 0 : (int) $member->id;
		if ( ! $mid ) {
			$mid = apply_filters( 'nonce_mpt_logged_out', $mid, $action );
		}

		$token = self::get_session_token();
		$i = self::nonce_tick();

		return substr( wp_hash( 'member-'.$i . '|' . $action . '|' . $mid . '|' . $token, 'nonce' ), -12, 10 );
	}

	/**
	 * Retrieve URL with nonce added to URL query.
	 *
	 * @since 0.6.0
	 *
	 * @param string     $actionurl URL to add nonce action.
	 * @param int|string $action    Optional. Nonce action name. Default -1.
	 * @param string     $name      Optional. Nonce name. Default '_mptnonce'.
	 * @return string Escaped URL with nonce action added.
	 */
	public static function nonce_url( $actionurl, $action = -1, $name = '_mptnonce' ) {
		$actionurl = str_replace( '&amp;', '&', $actionurl );
		return esc_html( add_query_arg( $name, self::create_nonce( $action ), $actionurl ) );
	}

	/**
	 * Retrieve or display nonce hidden field for forms.
	 *
	 * The nonce field is used to validate that the contents of the form came from
	 * the location on the current site and not somewhere else. The nonce does not
	 * offer absolute protection, but should protect against most cases. It is very
	 * important to use nonce field in forms.
	 *
	 * The $action and $name are optional, but if you want to have better security,
	 * it is strongly suggested to set those two parameters. It is easier to just
	 * call the function without any parameters, because validation of the nonce
	 * doesn't require any parameters, but since crackers know what the default is
	 * it won't be difficult for them to find a way around your nonce and cause
	 * damage.
	 *
	 * The input name will be whatever $name value you gave. The input value will be
	 * the nonce creation value.
	 *
	 * @since 0.6.0
	 *
	 * @param int|string $action  Optional. Action name. Default -1.
	 * @param string     $name    Optional. Nonce name. Default '_mptnonce'.
	 * @param bool       $referrer Optional. Whether to set the referrer field for validation. Default true.
	 * @param bool       $echo    Optional. Whether to display or return hidden form field. Default true.
	 * @return string Nonce field HTML markup.
	 */
	public static function nonce_field( $action = -1, $name = "_mptnonce", $referrer = true , $echo = true ) {
		$name = esc_attr( $name );
		$nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . self::create_nonce( $action ) . '" />';

		if ( $referrer ) {
			$nonce_field .= self::referrer_field( false );
		}

		if ( $echo ) {
			echo $nonce_field;
		}

		return $nonce_field;
	}

	/**
	 * Retrieve or display referrer hidden field for forms.
	 *
	 * The referrer link is the current Request URI from the server super global. The
	 * input name is '_mpt_http_referrer', in case you wanted to check manually.
	 *
	 * @since 0.6.0
	 *
	 * @param bool $echo Optional. Whether to echo or return the referrer field. Default true.
	 * @return string referrer field HTML markup.
	 */
	public static function referrer_field( $echo = true ) {
		$referrer_field = '<input type="hidden" name="_mpt_http_referrer" value="'. esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" />';

		if ( $echo ) {
			echo $referrer_field;
		}
		return $referrer_field;
	}

	/**
	 * Retrieve or display original referrer hidden field for forms.
	 *
	 * The input name is '_mpt_original_http_referrer' and will be either the same
	 * value of self::referrer_field(), if that was posted already or it will be the
	 * current page, if it doesn't exist.
	 *
	 * @since 0.6.0
	 *
	 * @param bool   $echo         Optional. Whether to echo the original http referrer. Default true.
	 * @param string $jump_back_to Optional. Can be 'previous' or page you want to jump back to.
	 *                             Default 'current'.
	 * @return string Original referrer field.
	 */
	public static function original_referrer_field( $echo = true, $jump_back_to = 'current' ) {
		if ( ! $ref = self::get_original_referrer() ) {
			$ref = 'previous' == $jump_back_to ? self::get_referrer() : wp_unslash( $_SERVER['REQUEST_URI'] );
		}
		$orig_referrer_field = '<input type="hidden" name="_mpt_original_http_referrer" value="' . esc_attr( $ref ) . '" />';
		if ( $echo ) {
			echo $orig_referrer_field;
		}
		return $orig_referrer_field;
	}

	/**
	 * Retrieve referrer from '_mpt_http_referrer' or HTTP referrer.
	 *
	 * If it's the same as the current request URL, will return false.
	 *
	 * @since 0.6.0
	 *
	 * @return false|string False on failure. referrer URL on success.
	 */
	public static function get_referrer() {

		$ref = false;
		if ( ! empty( $_REQUEST['_mpt_http_referrer'] ) ) {
			$ref = wp_unslash( $_REQUEST['_mpt_http_referrer'] );
		} else if ( ! empty( $_SERVER['HTTP_referrer'] ) ) {
			$ref = wp_unslash( $_SERVER['HTTP_referrer'] );
		}

		if ( $ref && $ref !== wp_unslash( $_SERVER['REQUEST_URI'] ) ) {
			return wp_validate_redirect( $ref, false );
		}

		return false;
	}

	/**
	 * Retrieve original referrer that was posted, if it exists.
	 *
	 * @since 0.6.0
	 *
	 * @return string|false False if no original referrer or original referrer if set.
	 */
	public static function get_original_referrer() {
		if ( ! empty( $_REQUEST['_wp_original_http_referrer'] ) ) {
			return wp_validate_redirect( wp_unslash( $_REQUEST['_mpt_original_http_referrer'] ), false );
		}
		return false;
	}

	/**
	 * Retrieve the current session token from the logged_in cookie.
	 *
	 * @since 0.6.0
	 *
	 * @return string Token.
	 * @author Nicolas Juen
	 */
	public static function get_session_token() {
		$cookie = MPT_Member_Auth::parse_auth_cookie( '', 'logged_in' );
		return ! empty( $cookie['token'] ) ? $cookie['token'] : '';
	}
}
