<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<?php if ( ! mpt_is_allowed_email_signon() ) : ?>
		<div class="mpt-field">
			<label for="mpt-username"><?php _e( 'Username', 'mpt' ); ?></label>
			<input id="mpt-username" required="required" type="text" name="mptregistration_s2[username]" value="<?php echo esc_attr( $member_data['username'] ); ?>"/>
		</div>
	<?php endif; ?>
	<div class="mpt-field">
		<label for="mpt-firstname"><?php _e( 'Firstname', 'mpt' ); ?></label>
		<input id="mpt-firstname" required="required" type="text" name="mptregistration_s2[first_name]" value="<?php echo esc_attr( $member_data['first_name'] ); ?>"/>
	</div>
	<div class="mpt-field">
		<label for="mpt-lastname"><?php _e( 'Last name', 'mpt' ); ?></label>
		<input id="mpt-lastname" required="required" type="text" name="mptregistration_s2[last_name]" value="<?php echo esc_attr( $member_data['last_name'] ); ?>"/>
	</div>
	<div class="mpt-field">
		<label for="mpt-password"><?php _e( 'Password', 'mpt' ); ?></label>
		<div class="mpt-password">
		<input id="mpt-password" required="required" type="password" name="mptregistration_s2[password]" value=""/>
			<?php echo MPT_Shortcode::load_template( 'member-password-toggle-button' );

			?>
		</div>
	</div>
	<div class="mpt-field">
		<label for="mpt-confirm-password"><?php _e( 'Password (confirmation)', 'mpt' ); ?></label>
		<div class="mpt-password">
			<input for="mpt-confirm-password" required="required" type="password" name="mptregistration_s2[password_repeat]" value=""/>
			<?php echo MPT_Shortcode::load_template( 'member-password-toggle-button' ); ?>
		</div>
	</div>
	<div class="mpt-field">
		<?php mpt_nonce_field( 'mptregistration_s2' ); ?>
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
	</div>
</form>
