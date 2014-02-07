<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div style="overflow: hidden; margin-left: 5px;">
	<p>
		<?php _e( "Limit access to this post's content to members of the selected roles.", 'mpt' ); ?>
	</p>
	
	<?php foreach ( $mpt_roles as $role ) : ?>
		<div style="width: 32%; float: left; margin: 0 0 5px 0;">
			<label for="role-<?php echo $role->term_id; ?>">
				<input type="checkbox" name="mpt_role[]" id="role-<?php echo $role->term_id; ?>" <?php checked( true, (is_array( $current_roles ) && in_array( $role->term_id, $current_roles ) )); ?> value="<?php echo $role->term_id; ?>" /> 
				<?php echo esc_html($role->name); ?>
			</label>
		</div>
	<?php endforeach; ?>
		
	<p style="clear: left;">
		<span class="howto"><?php printf( __( 'If no roles are selected, everyone can view the content. The post author, any users who can edit this post, and users with the %s capability can view the content regardless of role.', 'mpt' ), '<code>mpt_restrict_content</code>' ); ?></span>
	</p>

	<p>
		<label for="mpt_access_error"><?php _e( 'Custom error messsage:', 'mpt' ); ?></label>
		<textarea id="mpt_access_error" name="mpt_access_error" cols="60" rows="2" tabindex="30" style="width: 99%;"><?php echo esc_html( $current_message ); ?></textarea>
		<br />
		<span class="howto"><?php _e( 'Message shown to users that do no have permission to view the post.', 'members' ); ?></span>
	</p>
</div>