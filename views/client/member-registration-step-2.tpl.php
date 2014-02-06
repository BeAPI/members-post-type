<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<?php if ( !mpt_is_allowed_email_signon() ) : ?>
		<label><?php _e( 'Username', 'mpt' ) ; ?></label>
		<input required="required" type="text" name="mptregistration_s2[username]" value="<?php echo esc_attr($member_data['username']); ?>" />
	<?php endif; ?>

	<label><?php _e( 'Firstname', 'mpt' ) ; ?></label>
	<input required="required" type="text" name="mptregistration_s2[first_name]" value="<?php echo esc_attr($member_data['first_name']); ?>" />

	<label><?php _e( 'Last name', 'mpt' ) ; ?></label>
	<input required="required" type="text" name="mptregistration_s2[last_name]" value="<?php echo esc_attr($member_data['last_name']); ?>" />

	<label><?php _e( 'Password', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptregistration_s2[password]" value="" />

	<label><?php _e( 'Password (confirmation)', 'mpt' ) ; ?></label>
	<input required="required" type="password" name="mptregistration_s2[password_repeat]" value="" />

	<?php wp_nonce_field( 'mptregistration_s2' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>