<?php echo MPT_Shortcode::get_messages(); ?>

<form method="post">
	<label><?php mpt_is_allowed_email_signon() ? _e( 'E-mail', 'mpt' ) : _e( 'Username or E-mail', 'mpt' ); ?></label>
	<input required="required" type="text" name="mptlostpwd_s1[username]" value="" />

	<?php wp_nonce_field( 'mptlostpwd_s1' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>