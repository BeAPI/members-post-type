<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

$page = get_current_screen();

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
		<?php if ($page->action == 'add') : ?>
		<tr valign='top'>
			<th scope="row"><label for="member-password-generate"><?php _e('Generate password', 'mpt'); ?></label></th>
			<td>
				<input type="checkbox" id="member-password-generate" name="memberpwd[password-generate]" />
			</td>
		</tr>
		<?php /* TODO 
		 * change passeword in next connections
		 * */ ?>
		<tr valign='top'>
			<th scope="row"><label for="member-change-next"><?php _e('Need to change password next connect', 'mpt'); ?></label></th>
			<td>
				<input type="checkbox" id="member-change-next" name="memberpwd[change-password-next]" disabled />
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>