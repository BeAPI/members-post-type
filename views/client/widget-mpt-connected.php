<!-- This file is used to markup the public facing widget. -->
<?php 
if( isset($title) && !empty($title) )
	echo $before_title.$title.$after_title;
?>

<?php
if ( $mode == 'ajax' ) :
	echo '<div class="ajax-mpt-widget"><!-- This element will be replaced with AJAX content --></div>';
else : // Otherwise == 'html'
	$current_member = mpt_get_current_user();
	?>
	<p>
		<?php printf(__('Welcome %s! It\'s been long since we had not seen you!', 'mpt'), $current_member->get_display_name()); ?>
		<br />
		<?php printf(__('Do you want to <a href="%s">log out</a> ?', 'mpt'), mpt_get_logout_permalink()); ?>
	</p>
	<?php	
endif;
?>