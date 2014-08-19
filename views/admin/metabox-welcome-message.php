<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div style="overflow: hidden; margin-left: 5px;">
	<p>
		<?php _e( "Send welcome message to this new member ", 'mpt' ); ?>
	</p>
	
	<label for="welcome-message-yes" ><?php _e( "Yes ", 'mpt' ); ?></label>
	<input type="radio" name="welcome-message" id="welcome-message-yes" value="yes" checked="checked" />
	<label for="welcome-message-no" ><?php _e( "No ", 'mpt' ); ?></label>
	<input type="radio" name="welcome-message" id="welcome-message-no" value="no" /> 
</div>