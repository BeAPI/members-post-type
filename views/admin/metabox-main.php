<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="member-email"><?php _e('Email', 'mpt'); ?></label></th>
			<td><input id="member-email" class="regular-text" type="email" name="member[email]" value="<?php echo esc_attr($member['email']); ?>" /></td>
		</tr>
		<?php if ( !mpt_is_allowed_email_signon() ) : ?>
		<tr valign="top">
			<th scope="row"><label for="member-username"><?php _e('Username', 'mpt'); ?></label></th>
			<td><input id="member-username" class="regular-text" type="text" name="member[username]" value="<?php echo esc_attr($member['username']); ?>" /></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<th scope="row"><label for="member-first_name"><?php _e('First name', 'mpt'); ?></label></th>
			<td><input id="member-first_name" class="regular-text" type="text" name="member[first_name]" value="<?php echo esc_attr($member['first_name']); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="member-last_name"><?php _e('Last name', 'mpt'); ?></label></th>
			<td><input id="member-last_name" class="regular-text" type="text" name="member[last_name]" value="<?php echo esc_attr($member['last_name']); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="member-connection-type"><?php _e('Connection type', 'mpt'); ?></label></th>
			<td>
				<select	class="form-control" name="member[connection_type]" id="member-connection_type" >
					<?php foreach( MPT_Admin_Post_Type::get_connection_type() as $key => $value): ?>
						<option <?php selected( $member['connection_type'], $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="member-social_id"><?php _e('Social ID', 'mpt'); ?></label></th>
			<td><input id="member-social_id" class="regular-text" type="text" name="member[social_id]" value="<?php echo esc_attr($member['social_id']); ?>" /></td>
		</tr>
		
		<?php do_action( 'mpt_metabox_main' ); ?>
	</tbody>
</table>