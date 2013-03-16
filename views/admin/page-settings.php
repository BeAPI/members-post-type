<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2><?php _e( 'Members - Settings', 'mpt' ); ?></h2>
	<?php //settings_errors(); ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('options-general.php?page=mpt-settings&tab=main'); ?>" class="nav-tab <?php echo $active_tab == 'main' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Main', 'mpt' ); ?></a>
		<a href="<?php echo admin_url('options-general.php?page=mpt-settings&tab=pages'); ?>" class="nav-tab <?php echo $active_tab == 'pages' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Pages', 'mpt' ); ?></a>
		<a href="<?php echo admin_url('options-general.php?page=mpt-settings&tab=security'); ?>" class="nav-tab <?php echo $active_tab == 'security' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Security', 'mpt' ); ?></a>
	</h2>
	
	<form method="post" action="options.php">
		<?php
		if( $active_tab == 'main' ) {
			settings_fields( 'mpt-main' );
			do_settings_sections( 'mpt-main' );
		} elseif( $active_tab == 'security' ) {
			settings_fields( 'mpt-security' );
			do_settings_sections( 'mpt-security' );
		} else {
			settings_fields( 'mpt-pages' );
			do_settings_sections( 'mpt-pages' );
		}
		submit_button();
		?>
	</form>
</div><!-- /.wrap -->