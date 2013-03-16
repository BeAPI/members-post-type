<?php echo MPT_Shortcode::get_messages(); ?>

<form method="post">
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

<a href="<?php echo mpt_get_lost_password_permalink(); ?>"><?php _e( 'Forgot password ?', 'mpt' ) ; ?></a>
<a href="<?php echo mpt_get_register_permalink(); ?>"><?php _e( 'Register', 'mpt' ) ; ?></a>