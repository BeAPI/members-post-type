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
	</tbody>
</table>