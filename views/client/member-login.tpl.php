<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<div class="mpt-field">
		<label for="mpt-email"><?php mpt_is_allowed_email_signon() ? _e( 'E-mail', 'mpt' ) : _e( 'Username', 'mpt' ); ?></label>
		<input id="mpt-email" required="required" type="<?php echo mpt_is_allowed_email_signon() ? 'email' : 'text'; ?>" name="mptlogin[username]" value="<?php echo esc_attr( $member_data['username'] ); ?>"/>
	</div>
	<div class="mpt-field">
		<label for="mpt-password"><?php _e( 'Password', 'mpt' ); ?></label>
		<div class="mpt-password">
			<input id="mpt-password" required="required" type="password" name="mptlogin[password]" value=""/>
			<?php echo MPT_Shortcode::load_template( 'member-password-toggle-button' ); ?>
		</div>
	</div>
	<div class="mpt-field">
		<input id="mpt-remember" name="mptlogin[rememberme]" type="checkbox" value="forever" <?php checked( $member_data['rememberme'], 'forever' ); ?> />
		<label for="mpt-remember">
			<?php _e( 'Remember me', 'mpt' ); ?>
		</label>
		<input type="hidden" name="mptlogin[redirect_to]" value="<?php echo esc_attr( $member_data['redirect_to'] ); ?>"/>
		<?php mpt_nonce_field( 'mptlogin' ); ?>
	</div>
	<div class="mpt-field">
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
	</div>
</form>
<p>
	<a href="<?php echo mpt_get_lost_password_permalink(); ?>"><?php _e( 'Forgot password ?', 'mpt' ); ?></a>
	<a href="<?php echo mpt_get_register_permalink(); ?>"><?php _e( 'Register', 'mpt' ); ?></a>
</p>
