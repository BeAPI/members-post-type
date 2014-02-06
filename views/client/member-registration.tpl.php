<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

echo MPT_Shortcode::get_messages();
?>

<form method="post">
	<?php if ( !mpt_is_allowed_email_signon() ) : ?>
		<label><?php _e( 'Username', 'mpt' ) ; ?></label>
		<input required="required" type="text" name="mptregistration[username]" value="<?php echo esc_attr($member_data['username']); ?>" />
	<?php endif; ?>
	<?php $register_validation = mpt_registration_with_member_validation();
	if( $register_validation === 'off' ) : 
	?>
		<label><?php _e( 'Firstname', 'mpt' ) ; ?></label>
		<input required="required" type="text" name="mptregistration[first_name]" value="<?php echo esc_attr($member_data['first_name']); ?>" />

		<label><?php _e( 'Last name', 'mpt' ) ; ?></label>
		<input required="required" type="text" name="mptregistration[last_name]" value="<?php echo esc_attr($member_data['last_name']); ?>" />

		<label><?php _e( 'Password', 'mpt' ) ; ?></label>
		<input required="required" type="password" name="mptregistration[password]" value="" />

		<label><?php _e( 'Password (confirmation)', 'mpt' ) ; ?></label>
		<input required="required" type="password" name="mptregistration[password_repeat]" value="" />
	<?php endif; ?>
	
	<label><?php _e( 'Email', 'mpt' ) ; ?></label>
	<input required="required" type="email" name="mptregistration[email]" value="<?php echo esc_attr($member_data['email']); ?>" />
	
	<?php wp_nonce_field( 'mptregistration' ); ?>
	<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" />
</form>