<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<label><?php mpt_is_allowed_email_signon() ? _e( 'E-mail', 'mpt' ) : _e( 'Username or E-mail', 'mpt' ); ?></label>
	<input required="required" type="text" name="mptlostpwd_s1[username]" value="" />

	<?php mpt_nonce_field( 'mptlostpwd_s1' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>