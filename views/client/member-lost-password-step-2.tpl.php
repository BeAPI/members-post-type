<?php echo MPT_Shortcode::get_messages(); ?>

<form method="post">
	<label><?php _e( 'Your new password', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptlostpwd_s2[password]" value="" />

	<label><?php _e( 'Your new password (confirmation)', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptlostpwd_s2[password_confirmation]" value="" />

	<?php wp_nonce_field( 'mptlostpwd_s2' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>