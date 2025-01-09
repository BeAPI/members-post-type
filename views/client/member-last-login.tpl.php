<?php
$member_object = $member_data['member'];

if ( empty( $member_object ) ) {
	return;
}

$last_login_activity = $member_object->get_last_login_activity();

if ( empty( $last_login_activity ) ) {
	return;
}
?>
<div class="alert">
	<button class="alert__close btn">
		<span class="sr-only"><?php esc_html_e( 'Dismiss alert', 'cdc-habitat-uas' ); ?></span>
	</button>
	<div class="alert__inner container">
		<div class="alert__icon">
		</div>
		<div class="alert__content">
			<p><strong><?php echo esc_html( $member_data['message'] ); ?></strong></p>
			<?php foreach ( $last_login_activity as $key => $data ) : ?>
			<p><strong><?php echo esc_html( $data['label'] ); ?></strong><?php echo esc_html( $data['value'] ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</div>



