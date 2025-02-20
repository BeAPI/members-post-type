<!-- This file is used to markup the public facing widget. -->
<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( isset( $title ) && ! empty( $title ) ) {
	echo $before_title . $title . $after_title;
}
?>

<?php
if ( $mode == 'ajax' ) :
	echo '<div class="ajax-mpt-widget"><!-- This element will be replaced with AJAX content --></div>';
else : // Otherwise == 'html'
	?>
	<form method="post" action="<?php echo mpt_get_login_permalink(); ?>">
		<div class="mpt-field">
			<label for="mpt-username"><?php mpt_is_allowed_email_signon() ? _e( 'E-mail', 'mpt' ) : _e( 'Username', 'mpt' ); ?></label>
			<input id="mpt-username" required="required" type="<?php echo mpt_is_allowed_email_signon() ? 'email' : 'text'; ?>" name="mptlogin[username]" value="<?php echo esc_attr( $member_data['username'] ); ?>"/>
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

	<?php if ( $is_lost_link == 1 ) : ?>
	<p><a href="<?php echo mpt_get_lost_password_permalink(); ?>"><?php _e( 'Forgot password ?', 'mpt' ); ?></a></p>
<?php endif; ?>

	<?php if ( $is_register_link == 1 ) : ?>
	<p><a href="<?php echo mpt_get_register_permalink(); ?>"><?php _e( 'Register', 'mpt' ); ?></a></p>
<?php endif; ?>
<?php
endif;
?>
