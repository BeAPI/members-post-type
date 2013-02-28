<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="member-password"><?php _e('Password', 'mpt'); ?></label></th>
			<td>
				<input id="member-password" class="regular-text" type="password" name="memberpwd[password]" value="" />
				<span class="description"><?php _e("If you would like to change the password type a new one. Otherwise leave this blank.", 'mpt'); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="member-confirm_password"><?php _e('Confirm password', 'mpt'); ?></label></th>
			<td>
				<input id="member-confirm_password" class="regular-text" type="password" name="memberpwd[confirm_password]" value="" />
				<span class="description"><?php _e("Type your new password again.", 'mpt'); ?></span>
			</td>
		</tr>
	</tbody>
</table>