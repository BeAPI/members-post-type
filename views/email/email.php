<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>"/>
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
</head>
<body>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<?php if ( has_site_icon() ) : ?>
				<p><img src="<?php echo esc_url( get_site_icon_url() ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></p>
			<?php else: ?>
				<p><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo wp_kses_post( $email_body ); ?>
		</td>
	</tr>
</table>
</body>
