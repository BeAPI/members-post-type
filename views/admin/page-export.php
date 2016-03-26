<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-member"></div>
	<h2><?php echo MPT_Admin_Export::get_page_title(); ?></h2>

	<h3><?php _e( 'Export CSV file', 'mpt' ); ?></h3>
	<form action="" method="post" id="export-diag">
		<p class="submit">
			<input type="submit" name="export_members" class="button-primary export" value="<?php esc_attr_e( 'Export', 'mpt' ); ?>" />

			<?php wp_nonce_field( 'export-members' ); ?>
			<input type="hidden" name="mpt_action" value="mpt_export_action" />
		</p>
	</form>
</div><!-- /.wrap -->