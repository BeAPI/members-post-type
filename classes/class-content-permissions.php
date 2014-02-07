<?php
class MPT_Content_Permissions {

	/**
	 * Register hooks
	 * 
	 * @access public
	 *
	 * @return mixed Value.
	 */
	public function __construct() {
		add_filter( 'after_setup_theme', array( __CLASS__, 'enable_feature' ), 1 );
	}
	
	/**
	 * Adds required filters for the content permissions feature if it is active.
	 * @return boolean
	 */
	public static function enable_feature() {
		$current_options = MPT_Options::get_option( 'mpt-main' );
		if ( $current_options == false ) {
			return false;
		}

		if ( isset( $current_options['features'] ) && isset( $current_options['features']['content-permissions'] ) ) {
			/* Filter the content and exerpts. */
			add_filter( 'the_content', array( __CLASS__, 'content_protect' ) );
			add_filter( 'get_the_excerpt', array( __CLASS__, 'content_protect' ) );
			add_filter( 'the_excerpt', array( __CLASS__, 'content_protect' ) );
			add_filter( 'the_content_feed', array( __CLASS__, 'content_protect' ) );
			add_filter( 'comment_text_rss', array( __CLASS__, 'content_protect' ) );

			/* Filter the comments template to make sure comments aren't shown to users without access. */
			add_filter( 'comments_template', array( __CLASS__, 'comments_template' ) );
			
			/* Use WP formatting filters on the post error message. */
			add_filter( 'mpt_post_error_message', 'wptexturize' );
			add_filter( 'mpt_post_error_message', 'convert_smilies' );
			add_filter( 'mpt_post_error_message', 'convert_chars' );
			add_filter( 'mpt_post_error_message', 'wpautop' );
			add_filter( 'mpt_post_error_message', 'shortcode_unautop' );
			add_filter( 'mpt_post_error_message', 'do_shortcode' );
		}
	}

	/**
	 * Disables the comments template if a user doesn't have permission to view the post the comments are associated with.
	 * 
	 * @global type $post
	 * @param string $content
	 * @return string
	 */
	public static function content_protect( $content ) {
		if ( self::can_current_member_view_post( get_the_ID() ) ) {
			return $content;
		}
		
		return self::members_get_post_error_message( get_the_ID() );
	}

	/**
	 *  Disables the comments template if a user doesn't have permission to view the post the comments are associated with.
	 * 
	 * @param type $template
	 * @return type
	 */
	public static function comments_template( $template ) {
		if ( !self::can_current_member_view_post( get_queried_object_id() ) ) {
			/* Look for a 'mpt-comments-no-access.php' template in the parent and child theme. */
			$has_template = locate_template( array( 'mpt-comments-no-access.php' ) );

			/* If the template was found, use it.  Otherwise, fall back to the mpt comments.php template. */
			$template = ( !empty( $has_template ) ? $has_template : MPT_DIR . 'views/client/content-permissions/comments.php' );

			/* Allow devs to overwrite the comments template. */
			$template = apply_filters( 'mpt_comments_template', $template );
		}
		
		return $template;
	}

	/**
	 * This method allow to check if current member can reach an protected content
	 * 
	 * @param integer $post_id
	 * @return boolean
	 */
	public static function can_current_member_view_post( $post_id = 0 ) {
		/* Get the current member object. */
		$current_member = mpt_get_current_member();
		
		/* Return the can_member_view_post() method, which returns true/false. */
		return self::can_member_view_post( $current_member->id, $post_id );
	}
	
	/**
	 * This method allow to check if current member can reach an protected content
	 * 
	 * @param integer $member_id
	 * @param integer $post_id
	 * @return boolean
	 */
	public static function can_member_view_post( $member_id = 0, $post_id = '' ) {
		/* If no post ID is given, assume we're in The Loop and get the current post's ID. */
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
	
		/* Assume the member can view the post at this point. */
		$can_view = true;
		
		// Get options from DB
		$current_options = MPT_Options::get_option( 'mpt-main' );
		if ( $current_options == false ) {
			return false;
		}
		
		/**
		* The plugin is only going to handle permissions if the 'content permissions' feature is active.  If 
		* not active, the member can always view the post.  However, developers can roll their own handling of
		* this and filter 'mpt_can_member_view_post'.
		*/
		if ( isset( $current_options['features'] ) && isset( $current_options['features']['content-permissions'] ) ) {
			$roles = get_post_meta( $post_id, '_mpt_role', true );
			if ( !empty( $roles ) && is_array( $roles ) ) {
				/**
				 * Since specific roles were given, let's assume the member can't view the post at 
				 * this point.  The rest of this functionality should try to disprove this.
				 */
				$can_view = false;
				
				if ( is_user_logged_in() ) { /* If WP user is logged, allow view. */
					$can_view = true;
				} elseif ( is_feed() || !mpt_is_member_logged_in() ) { /* If viewing a feed or if the user's not logged in, assume it's blocked at this point. */
					$can_view = false;
				} else { /* Else, let's check the member's role against the selected roles. */
					// Get terms obj from ids
					$terms = get_terms( array(MPT_TAXO_NAME), array( 'include' => $roles, 'hide_empty' => false ) );
					if ( !empty( $terms ) && is_array( $terms ) ) {
						foreach ( $terms as $term ) { /* Loop through each role and set $can_view to true if the member has one of the roles. */
							if ( current_member_can( $term->slug ) ) {
								$can_view = true;
								break;
							}
						}
					}
				}
			}
		}
		
		/* Allow developers to overwrite the final return value. */
		return apply_filters( 'mpt_can_member_view_post', $can_view, $member_id, $post_id );
	}
	
	public static function members_get_post_error_message( $post_id = 0 ) {
		/* Get the error message for the specific post. */
		$error_message = get_post_meta( $post_id, '_mpt_access_error', true );

		/* If an error message is found, return it. */
		if ( !empty( $error_message ) ) {
			$return = $error_message;
		} else { /* If no error message is found, return the default message. */
			$return = mpt_get_option_value( 'mpt-main', 'default-post-error-message' );
		}
		
		/* Return the error message. */
		return apply_filters( 'mpt_post_error_message', $return );
	}

}
