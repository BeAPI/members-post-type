<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$last_user_activity = $member->get_last_login_activity();
$labels             = wp_list_pluck( $last_user_activity, 'label', );
$values             = wp_list_pluck( $last_user_activity, 'value' );

if ( ! empty( $last_user_activity ) ) :
	?>
	<table style="width:100%; border: 1px solid #ccc; border-collapse: collapse;">
		<thead>
		<tr>
			<?php foreach ( $labels as $label ) : ?>
				<th style="border: 1px solid #ccc; padding: 8px;"><?php echo esc_html( $label ); ?></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<tr>
			<?php foreach ( $values as $value ) : ?>
				<td style="border: 1px solid #ccc; padding: 8px;"><?php echo esc_html( $value ); ?></td>
			<?php endforeach; ?>
		</tr>
		</tbody>
	</table>
<?php else : ?>
	<p><?php echo esc_html_e( 'No data available.', 'mpt' ); ?></p>
<?php endif; ?>
