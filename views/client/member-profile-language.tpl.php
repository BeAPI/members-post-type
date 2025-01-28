<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>

<div class="mpt-field">
	<label for="mpt-language"><?php esc_html_e( 'Profile language' ); ?></label>
	<select id="mpt-language" name="mptchangeprofile[language]">
		<?php foreach ( $member_data['languages'] as $code => $language ) : ?>
			<option value='<?php echo esc_attr( $code ); ?>' <?php selected( $code, $member_data['current_language_post'] ); ?>><?php echo esc_html( $language['name'] ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
