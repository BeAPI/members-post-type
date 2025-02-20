<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<div class="mpt-field">
		<label for="mpt-new-password"><?php _e( 'Your new password', 'mpt' ); ?></label>
		<div class="mpt-password">
			<input id="mpt-new-password" required="required" type="password" name="mptlostpwd_s2[password]" value=""/>
			<?php echo MPT_Shortcode::load_template( 'member-password-toggle-button' );

			?>
		</div>
	</div>
	<div class="mpt-field">
		<label for="mpt-confirm-password"><?php _e( 'Your new password (confirmation)', 'mpt' ); ?></label>
		<div class="mpt-password">
			<input id="mpt-confirm-password" required="required" type="password" name="mptlostpwd_s2[password_confirmation]" value=""/>
			<?php echo MPT_Shortcode::load_template( 'member-password-toggle-button' ); ?>
		</div>
	</div>
	<div class="mpt-field">
		<?php mpt_nonce_field( 'mptlostpwd_s2' ); ?>
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
	</div>
</form>
