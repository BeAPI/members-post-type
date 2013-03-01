<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'Member post type settings', 'mpt' ); ?></h2>
	<form action="options.php">
		<?php settings_fields( 'member-post-type' );
		
		do_settings_sections( 'member-post-type' ); ?>
		<?php do_settings_fields('member-post-type', 'default'); ?>
		<?php submit_button(); ?>
	</form>
</div>
