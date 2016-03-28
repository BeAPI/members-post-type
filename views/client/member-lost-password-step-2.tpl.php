<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

echo MPT_Shortcode::get_messages(); 
?>

<form method="post">
	<label><?php _e( 'Your new password', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptlostpwd_s2[password]" value="" />

	<label><?php _e( 'Your new password (confirmation)', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptlostpwd_s2[password_confirmation]" value="" />

	<?php mpt_nonce_field( 'mptlostpwd_s2' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>