<!-- This file is used to markup the public facing widget. -->
<?php 
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if( isset($title) && !empty($title) )
	echo $before_title.$title.$after_title;
?>

<?php
if ( $mode == 'ajax' ) :
	echo '<div class="ajax-mpt-widget"><!-- This element will be replaced with AJAX content --></div>';
else : // Otherwise == 'html'
	$current_member = mpt_get_current_member();
	?>
	<p>
		<?php printf(__('Welcome %s! It\'s been long since we had not seen you!', 'mpt'), $current_member->get_display_name()); ?>
		<br />
		<?php printf(__('Do you want to <a href="%s">log out</a> ?', 'mpt'), mpt_get_logout_permalink()); ?>
	</p>
	<?php	
endif;
?>