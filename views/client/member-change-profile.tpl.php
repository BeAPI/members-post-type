<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
$email_waiting = $member_data->get_email_waiting_for_validation();
if ( ! empty( $email_waiting ) ) {
	$link_cancel = add_query_arg(
		[
			'dismiss'   => 'new_email',
			'action'    => 'cancel-change-email',
			'_mptnonce' => MPT_Nonces::create_nonce( 'mptnewemail' ),
		]
	);
}
?>

<form method="post" id="form1">

	<div class="mpt-filed">
		<label for="mpt-username"><?php _e( 'Your username', 'mpt' ); ?></label>
		<input id="mpt-username" type="text" disabled name="mptchangeprofile[username]" value="<?php echo esc_attr( $member_data->username ); ?>"/>
	</div>
	<div class="mpt-filed">
		<label for="mpt-email"><?php _e( 'Your email', 'mpt' ); ?>*</label>
		<input id="mpt-email" type="email" required name="mptchangeprofile[email]" value="<?php echo esc_attr( $member_data->email ); ?>"/>
	</div>
	<?php if ( ! empty( $email_waiting ) ) : ?>
		<div class="notice-warning">
			<p>
				<?php printf( __( 'There is a pending change of the member email to <strong>%s</strong>.', 'mpt' ), sanitize_email( $email_waiting ) ); ?>
				<a href="<?php echo esc_url( $link_cancel ); ?>"><?php esc_html_e( 'Cancel', 'mpt' ); ?></a>
			</p>
		</div>
	<?php endif; ?>
	<div class="mpt-filed">
		<label for="mpt-firstname"><?php _e( 'Your first name', 'mpt' ); ?></label>
		<input id="mpt-firstname" type="text" name="mptchangeprofile[first_name]" value="<?php echo esc_attr( $member_data->first_name ); ?>"/>
	</div>
	<div class="mpt-filed">
		<label for="mpt-lastname"><?php _e( 'Your last name', 'mpt' ); ?></label>
		<input id="mpt-lastname" type="text" name="mptchangeprofile[last_name]" value="<?php echo esc_attr( $member_data->last_name ); ?>"/>
	</div>
	<div class="mpt-filed">
		<?php do_action( 'member_change_profile_field', $member_data ); ?>
		<?php mpt_nonce_field( 'mptchangeprofile' ); ?>
		<input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
	</div>
</form>
