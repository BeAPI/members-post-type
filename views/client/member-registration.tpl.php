<?php echo MPT_Shortcode::get_messages(); ?>

<form method="post">
	<?php if ( !mpt_is_allowed_email_signon() ) : ?>
		<label><?php _e( 'Username', 'mpt' ) ; ?></label>
		<input type="text" name="mptregistration[username]" value="<?php echo esc_attr($user_data['username']); ?>" />
	<?php endif; ?>
	
	<label><?php _e( 'Firstname', 'mpt' ) ; ?></label>
	<input type="text" name="mptregistration[first_name]" value="<?php echo esc_attr($user_data['first_name']); ?>" />

	<label><?php _e( 'Last name', 'mpt' ) ; ?></label>
	<input type="text" name="mptregistration[last_name]" value="<?php echo esc_attr($user_data['last_name']); ?>" />

	<label><?php _e( 'Email', 'mpt' ) ; ?></label>
	<input type="email" name="mptregistration[email]" value="<?php echo esc_attr($user_data['email']); ?>" />

	<label><?php _e( 'Password', 'mpt' ) ; ?></label>
	<input type="password" name="mptregistration[password]" value="" />

	<label><?php _e( 'Password (confirmation)', 'mpt' ) ; ?></label>
	<input type="password" name="mptregistration[password_repeat]" value="" />

	<?php wp_nonce_field( 'mptregistration' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>