<?php

class MPT_Email {

	private $use_html;

	/**
	 * Create a new email instance using the configuration.
	 *
	 * @return self
	 */
	public static function from_configuration(): self {
		$mail_configuration = MPT_Options::get_option( 'mpt-emails' );
		$use_html_for_mails = $mail_configuration['use_html_for_mails'] ?? 'no';
		$send_as_html       = 'yes' === $use_html_for_mails;

		return new MPT_Email( $send_as_html );
	}

	/**
	 * Constructor.
	 *
	 * @param bool $send_as_html
	 */
	public function __construct( bool $send_as_html = false ) {
		$this->use_html = $send_as_html;
	}

	/**
	 * Send an email.
	 *
	 * If the option to send emails as HTML is enabled, the body will be formatted accordingly.
	 *
	 * @param string $to recipient's email
	 * @param string $subject email's subject
	 * @param string $body email's body
	 * @param MPT_Member $member member object
	 *
	 * @return bool
	 */
	public function send( string $to, string $subject, string $body, MPT_Member $member = null ) {
		if ( ! is_email( $to ) ) {
			return false;
		}

		$body = $this->format_body( $body, $member );

		add_filter( 'wp_mail_content_type', array( $this, 'maybe_set_content_type' ) );
		$result = wp_mail( $to, $subject, $body );
		remove_filter( 'wp_mail_content_type', array( $this, 'maybe_set_content_type' ) );

		return $result;
	}

	/**
	 * Format the body of the email.
	 *
	 * @param string $body email's body
	 * @param MPT_Member $member member object
	 *
	 * @return string
	 */
	private function format_body( string $body, MPT_Member $member = null ): string {
		if ( ! $this->use_html ) {
			return $body;
		}

		$html_template = locate_template( 'mpt/email/email.php' );
		if ( '' === $html_template ) {
			$html_template = MPT_DIR . 'views/email/email.php';
		}

		/**
		 * Filter HTML template use for the email.
		 *
		 * @param string $html_template
		 * @param string $body
		 */
		$html_template = apply_filters( 'mpt_email_html_template', $html_template, $body );
		if ( '' === $html_template ) {
			return $body;
		}

		$original_body = $body;
		$render        = static function ( $html_template, $template_args ): string {
			extract( $template_args, EXTR_SKIP );
			ob_start();
			include $html_template;

			return ob_get_clean();
		};

		$body          = wpautop( $body );
		$body          = make_clickable( $body );
		$template_args = array(
			'email_language' => apply_filters( 'mpt_email_language', get_bloginfo( 'language' ), $member ),
			'email_body'     => $body,
		);

		/**
		 * Filter arguments use for the email.
		 *
		 * @param array $template_args
		 * @param string $html_template
		 * @param string $original_body
		 */
		$template_args = apply_filters( 'mpt_email_template_args', $template_args, $html_template, $original_body );

		return $render( $html_template, $template_args );
	}

	/**
	 * Change email content_type is 
	 *
	 * @param string $content_type
	 *
	 * @return string
	 */
	public function maybe_set_content_type( $content_type ) {
		return $this->use_html ? 'text/html' : $content_type;
	}
}
