<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="member-password"><?php _e('Password', 'mpt'); ?></label></th>
			<td>
				<input id="member-password" class="regular-text" type="password" name="memberpwd[password]" value="" />
				<p class="description"><?php _e("If you would like to change the password type a new one. Otherwise leave this blank.", 'mpt'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="member-confirm_password"><?php _e('Confirm password', 'mpt'); ?></label></th>
			<td>
				<input id="member-confirm_password" class="regular-text" type="password" name="memberpwd[confirm_password]" value="" />
				<p class="description"><?php _e("Type your new password again.", 'mpt'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<div id="pass-strength-result"><?php _e('Strength indicator'); ?></div>
				<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
			</td>
		</tr>
	</tbody>
</table>