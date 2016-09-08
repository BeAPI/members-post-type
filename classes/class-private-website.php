<?php
class MPT_Private_Website {

	/**
	 * Register hooks
	 * 
	 * @access public
	 *
	 * @return mixed Value.
	 */
	public function __construct() {
		$current_options = MPT_Options::get_option( 'mpt-main' );

		if ( mpt_get_option_value( 'mpt-main', 'redirect-logged-out-users' ) === 'on' ) {
			/* Redirects users to the login page. */
			add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 1 );
		}
		
		if ( mpt_get_option_value( 'mpt-main', 'error-feed' ) == 'on' ) {
			/* Disable content in feeds if the feed should be private. */
			add_filter( 'the_content_feed', array( __CLASS__, 'private_feed' ) );
			add_filter( 'the_excerpt_rss', array( __CLASS__, 'private_feed' ) );
			add_filter( 'comment_text_rss', array( __CLASS__, 'private_feed' ) );
		}
	}

	/**
	 * Redirects members that are not logged in to the 'wp-login.php' page.
	 *
	 * @since 0.1.0
	 * @uses is_user_logged_in() Checks if the current user is logged in.
	 * @uses auth_redirect() Redirects people that are not logged in to the login page.
	 */
	public static  function template_redirect() {
		/* if the member is not logged in, redirect to the login page. */
		if ( !mpt_is_member_logged_in() && !MPT_Main::is_action_page() ) {
			// Try to get the login page
			$location = wp_validate_redirect( mpt_get_login_permalink() );
			
			// Invalid location ? Make a die
			if ( empty($location) ) {
				wp_die( __('You must be logged in to access the site, but could not find the login page. Contact the webmaster.', 'mpt') );
			}
			
			// Redirect
			wp_redirect( $location );
			exit();
		}
		
	}

	/**
	 * Blocks feed items if the user has selected the private feed feature.
	 *
	 * @since 0.2.0
	 * @param string $content The post or comment feed content.
	 * @return string $content Returns either the content or an error message.
	 */
	public static function private_feed( $content ) {
		if ( !mpt_is_member_logged_in() ) {
			$content = mpt_get_option_value( 'mpt-main', 'feed-error-message' );
		}
		
		return $content;
	}

}
