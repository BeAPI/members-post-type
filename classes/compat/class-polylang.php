<?php

class MPT_Polylang {

	public function __construct() {
		add_filter( 'init', [ $this, 'register_translate_string' ], 8 );
		add_filter( 'mpt_option', [ $this, 'translation_option_value' ], 19, 2 );
		// Password change
		add_filter( 'mpt_lost_password_admin_subject_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_lost_password_admin_content_default_option', [ $this, 'translation_email' ], 10, 2 );
		// Register notification to admin
		add_filter( 'mpt_registration_member_admin_content_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_registration_member_admin_subject_default_option', [ $this, 'translation_email' ], 10, 2 );
		// Register notification to member
		add_filter( 'mpt_register_notification_subject_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_register_notification_message_default_option', [ $this, 'translation_email' ], 10, 2 );
		// Validation account
		add_filter( 'mpt_register_validation_notification_subject_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_register_validation_notification_message_default_option', [ $this, 'translation_email' ], 10, 2 );
		// Retrieve password
		add_filter( 'mpt_retrieve_password_title_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_retrieve_password_message_default_option', [ $this, 'translation_email' ], 10, 2 );
		// Validate new email
		add_filter( 'mpt_validate_new_email_title_default_option', [ $this, 'translation_email' ], 10, 2 );
		add_filter( 'mpt_validate_new_email_message_default_option', [ $this, 'translation_email' ], 10, 2 );

		add_action( 'mpt_insert_member', [ $this, 'set_pll_language' ] );
		add_action( 'member_change_profile_field', [ $this, 'member_change_profile_field' ] );
		add_filter( 'mpt_action_page_id', [ $this, 'translate_page_action_id' ] );

		add_filter( 'mpt_need_to_update_member', [ $this, 'mpt_need_to_update_member' ], 10, 3 );
		add_action( 'mpt_update_member', [ $this, 'mpt_update_member' ], 10, 2 );
		add_action( 'mpt_redirect_after_profile_updated', [ $this, 'mpt_redirect_after_profile_updated' ], 10, 2 );
	}

	/**
	 * Register translatable options in Polylang.
	 *
	 * @return void
	 */
	public function register_translate_string() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		// Register string translations for mpt-main option
		$option_values = MPT_Options::get_option( 'mpt-main' );
		foreach ( $option_values as $field_name => $value ) {
			$allow_field = [ 'default-post-error-message', 'feed-error-message' ];

			if ( ! in_array( $field_name, $allow_field, true ) ) {
				continue;
			}
			pll_register_string( $field_name, $value, 'MPT' );
		}

		// Register string translations for mpt-emails option
		$option_values = MPT_Options::get_option( 'mpt-emails' );
		foreach ( $option_values as $field_name => $value ) {
			$skip_fields = [
				'register_member_admin_mail',
				'register_member_admin_description',
				'register_member_mail',
				'register_member_description',
				'register_member_validation_mail',
				'register_member_validation_description',
				'lost_password_admin',
				'lost_password_admin_description',
				'validate_new_email_member',
				'validate_new_email_member_description',
			];

			if ( in_array( $field_name, $skip_fields, true ) ) {
				continue;
			}

			pll_register_string( $field_name, $value, 'MPT', true );
		}
	}

	/**
	 * Translate option values.
	 *
	 * @param $option_value
	 * @param $option_name
	 *
	 * @return mixed
	 */
	public function translation_option_value( $option_value, $option_name ) {
		$current_language = pll_current_language();
		if ( 'mpt-pages' === $option_name ) {
			foreach ( $option_value as $field_name => $value ) {
				$value = absint( $value );
				if ( empty( $value ) ) {
					continue;
				}

				$option_value[ $field_name ] = (string) pll_get_post( $value, $current_language );
			}
		}

		if ( 'mpt-main' === $option_name ) {
			foreach ( $option_value as $field_name => $value ) {
				$allow_field = [ 'default-post-error-message', 'feed-error-message' ];

				if ( ! in_array( $field_name, $allow_field, true ) ) {
					continue;
				}
				$option_value[ $field_name ] = pll_translate_string( $value, $current_language );
			}
		}

		return $option_value;
	}

	/**
	 * Translate email subject/message.
	 *
	 * @param string $content
	 * @param MPT_Member $member
	 *
	 * @return string
	 */
	public function translation_email( $content, $member ) {
		return pll_translate_string( $content, $this->get_member_language( $member ) );
	}


	/**
	 * Set default language for member when created.
	 *
	 * @param int $member_id
	 *
	 * @return void
	 */
	public function set_pll_language( int $member_id ) {
		pll_set_post_language( $member_id, pll_current_language() );
	}

	/**
	 * Render language field in member profile settings page.
	 *
	 * @param MPT_Member $member
	 *
	 * @return void
	 */
	public function member_change_profile_field( MPT_Member $member ) {
		if ( ! pll_is_translated_post_type( MPT_CPT_NAME ) ) {
			return;
		}

		$languages = pll_the_languages(
			[
				'dropdown'   => 0,
				'show_names' => 1,
				'post_id'    => $member->id,
				'raw'        => true,
			]
		);

		$current_language_post = $this->get_member_language( $member );

		if ( empty( $current_language_post ) ) {
			return;
		}

		$output = MPT_Shortcode::load_template(
			'member-profile-language',
			[
				'current_language_post' => $current_language_post,
				'languages'             => $languages,
			]
		);

		echo apply_filters( 'mpt_language_dropdown_output', $output, $languages, $member );
	}

	/**
	 * Translate page action id
	 *
	 * @param $page_id
	 *
	 * @return int
	 */
	public function translate_page_action_id( $page_id ) {
		return (int) pll_get_post( $page_id, pll_current_language() );
	}

	/**
	 * Need to update member if language changed
	 *
	 * @param $need_to_update
	 * @param $member
	 * @param $data
	 *
	 * @return bool
	 */
	public function mpt_need_to_update_member( $need_to_update, $member, $data ) {
		return ( $data['language'] ?? '' ) !== $this->get_member_language( $member );
	}

	/**
	 * Set new language PLL
	 *
	 * @param $member
	 * @param $data
	 *
	 * @return void
	 */
	public function mpt_update_member( $member, $data ) {
		$current_language_post = $this->get_member_language( $member );
		$new_language          = $data['language'] ?? '';

		if ( $current_language_post === $new_language ) {
			return;
		}

		pll_set_post_language( $member->id, $new_language );
	}

	public function mpt_redirect_after_profile_updated( $member, $status ) {
		$member_language = $this->get_member_language( $member );

		if ( pll_current_language() === $member_language ) {
			return;
		}

		$translate_page_id = pll_get_post( MPT_Main::get_action_page_id( 'change-profile' ), $member_language );

		if ( empty( $translate_page_id ) ) {
			return;
		}

		wp_safe_redirect( add_query_arg( $status, get_permalink( $translate_page_id ) ) );
		exit;
	}

	/**
	 * Get member language
	 *
	 * @param MPT_Member $member
	 *
	 * @return string
	 */
	public function get_member_language( MPT_Member $member ) {
		return (string) pll_get_post_language( $member->id );
	}
}
