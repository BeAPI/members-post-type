<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<div class="mpt-filed">
		<label for="mpt-password"><?php _e( 'Your old password', 'mpt' ); ?></label>
		<input id="mpt-password" required="required" type="password" name="mptchangepwd[old]" value=""/>
	</div>
	<div class="mpt-filed">
		<label for="mpt-new-password"><?php _e( 'Your new password', 'mpt' ); ?> *</label>
		<input id="mpt-new-password" required="required" type="password" name="mptchangepwd[new]" value=""/>
	</div>
	<div class="mpt-filed">
		<label for="mpt-confirm-password"><?php _e( 'Your new password (confirmation)', 'mpt' ); ?></label>
		<input id="mpt-confirm-password" required="required" type="password" name="mptchangepwd[new_confirmation]"/>
	</div>
	<div class="mpt-filed">
		<?php mpt_nonce_field( 'mptchangepwd' ); ?>
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
	</div>
</form>
