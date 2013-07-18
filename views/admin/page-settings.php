<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2><?php _e( 'Members - Settings', 'mpt' ); ?></h2>
	<?php //settings_errors(); ?>

	<?php
	self::$settings_api->show_navigation();
	self::$settings_api->show_forms();
	?>
</div><!-- /.wrap -->