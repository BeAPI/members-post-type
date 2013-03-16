<!-- This file is used to markup the public facing widget. -->
<?php 
if( isset($title) && !empty($title) )
	echo $before_title.$title.$after_title;
?>

<?php
if ( $mode == 'ajax' ) :
	echo '<div class="ajax-mpt-widget"><!-- This element will be replaced with AJAX content --></div>';
else : // Otherwise == 'html'
	?>
	<form method="post" action="<?php echo mpt_get_login_permalink(); ?>">
		<label><?php mpt_is_allowed_email_signon() ? _e( 'E-mail', 'mpt' ) : _e( 'Username', 'mpt' ); ?></label>
		<input required="required" type="<?php echo mpt_is_allowed_email_signon() ? 'email' : 'text'; ?>" name="mptlogin[username]" value="<?php echo esc_attr($user_data['username']); ?>" />
		
		<label><?php _e( 'Password', 'mpt' ); ?></label>
		<input required="required" type="password" name="mptlogin[password]" value="" />
		
		<label>
			<input name="mptlogin[rememberme]" type="checkbox" value="forever" <?php checked($user_data['rememberme']); ?> />
			<?php _e('Remember me', 'mpt'); ?>
		</label>
		
		<input type="hidden" name="mptlogin[redirect_to]" value="<?php echo esc_attr($user_data['redirect_to']); ?>" />
		
		<?php wp_nonce_field( 'mptlogin' ); ?>
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
	</form>

	<?php if ( $is_lost_link == 1 ) : ?>
		<p><a href="<?php echo mpt_get_lost_password_permalink(); ?>"><?php _e( 'Forgot password ?', 'mpt' ) ; ?></a></p>
	<?php endif; ?>

	<?php if ( $is_register_link == 1 ) : ?>
		<p><a href="<?php echo mpt_get_register_permalink(); ?>"><?php _e( 'Register', 'mpt' ) ; ?></a></p>
	<?php endif; ?>
	<?php
endif;
?>