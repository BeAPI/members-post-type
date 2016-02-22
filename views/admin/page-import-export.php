<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-member"></div>
	<h2><?php echo MPT_Admin_Post_Type::get_import_export_title_page(); ?></h2>

	<?php if( ! apply_filters( 'mpt_admin_hide_import', false ) ) : ?>
		<h3><?php _e( 'Import CSV file', 'mpt' ); ?></h3>
		<form action="" method="post" id="import-diag" enctype="multipart/form-data">
			<p><?php _e( 'Import members from a CSV file. The format for the CSV file is "email; lastname; firstname; username". The password is automaticaly generated when the user is created.', 'mpt' ); ?></p>
			<label for='csv-file'><?php _e( 'Select a CSV file.', 'mpt' ); ?></label>
			<input type="file" name="csv-file" />
			<p class="submit">
				<input type="submit" name="import_members" class="button-primary export" value="<?php esc_attr_e( 'Import', 'mpt' ); ?>" />

				<?php wp_nonce_field( 'import-members' ); ?>
				<input type="hidden" name="mpt_action" value="mpt_import_action" />
			</p>

			<div class="report">
				<?php if( $report != false ) : ?>
					<p><strong><?php echo sprintf( __( 'Last import : %s', 'mpt'), date('d/m/Y H\hi', $report['report_date']) ); ?></strong></p>
					<?php if( !empty($report['ignore_line']) ) : ?>
					<p><?php _e( 'CSV formating error :', 'mpt' ); ?></p>
					<?php foreach( $report['ignore_line'] as $line => $result ) : ?>
					<p class="import-<?php echo $result['status']; ?>"><?php echo sprintf(__( 'Line %s : "%s" %s (%s)', 'mpt'), $result['line'], $result['content'], $result['operation'], $result['status']); ?></p>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php if( !empty($report['import_status']) ) : ?>
					<p><?php _e( 'Importing status :', 'mpt' ); ?></p>
					<?php foreach( $report['import_status'] as $line => $result ) : ?>
					<p class="import-<?php echo $result['status']; ?>"><?php echo sprintf(__( '%s) %s (%s) : %s', 'mpt'), $line+1, $result['member'], $result['operation'], $result['status']); ?></p>
					<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>
					<p><strong><?php _e( 'No report data available.', 'mpt' ); ?></strong></p>
				<?php endif; ?>
			</div>
		</form>
	<?php endif; ?>

	<?php if( ! apply_filters( 'mpt_admin_hide_export', false ) ) : ?>
		<h3><?php _e( 'Export CSV file', 'mpt' ); ?></h3>
		<form action="" method="post" id="export-diag">
			<p class="submit">
				<input type="submit" name="export_members" class="button-primary export" value="<?php esc_attr_e( 'Export', 'mpt' ); ?>" />

				<?php wp_nonce_field( 'export-members' ); ?>
				<input type="hidden" name="mpt_action" value="mpt_export_action" />
			</p>
		</form>
	<?php endif; ?>
</div><!-- /.wrap -->