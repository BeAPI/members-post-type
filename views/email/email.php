<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	<style media="all" type="text/css">

		@media only screen and (max-width: 640px) {
			.main p,
			.main td,
			.main span {
				font-size: 16px !important;
			}

			.wrapper {
				padding: 8px !important;
			}

			.content {
				padding: 0 !important;
			}

			.container {
				padding: 0 !important;
				padding-top: 8px !important;
				width: 100% !important;
			}

			.main {
				border-left-width: 0 !important;
				border-radius: 0 !important;
				border-right-width: 0 !important;
			}

			.btn table {
				max-width: 100% !important;
				width: 100% !important;
			}
		}

		@media all {
			.ExternalClass {
				width: 100%;
			}

			.ExternalClass,
			.ExternalClass p,
			.ExternalClass span,
			.ExternalClass font,
			.ExternalClass td,
			.ExternalClass div {
				line-height: 100%;
			}

			.apple-link a {
				color: inherit !important;
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				text-decoration: none !important;
			}

			#MessageViewBody a {
				color: inherit;
				text-decoration: none;
				font-size: inherit;
				font-family: inherit;
				font-weight: inherit;
				line-height: inherit;
			}
		}
	</style>
</head>
<body style="font-family: Helvetica, sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #f4f5f6; margin: 0; padding: 0;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f4f5f6; width: 100%;" width="100%" bgcolor="#f4f5f6">
	<tr>
		<td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
		<td class="container" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; max-width: 600px; padding: 0; padding-top: 24px; width: 600px; margin: 0 auto;" width="600" valign="top">
			<div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 0;">

				<!-- START CENTERED WHITE CONTAINER -->
				<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border: 1px solid #eaebed; border-radius: 16px; width: 100%;" width="100%">

					<!-- START MAIN CONTENT AREA -->
					<tr>
						<td class="wrapper" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 24px;" valign="top">
							<?php if ( has_site_icon() ) : ?>
								<p><img src="<?php echo esc_url( get_site_icon_url() ); ?>"
										alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="128" height="128"></p>
							<?php endif; ?>
							<p><strong><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></strong></p>
						</td>
					</tr>
					<tr>
						<td class="wrapper" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 24px;" valign="top">
							<?php echo wp_kses_post( $email_body ); ?>
						</td>
					</tr>

					<!-- END MAIN CONTENT AREA -->
				</table>

				<!-- START FOOTER -->
				<div class="footer" style="clear: both; padding-top: 24px; text-align: center; width: 100%;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
						<tr>
							<td class="content-block" style="font-family: Helvetica, sans-serif; vertical-align: top; color: #9a9ea6; font-size: 16px; text-align: center;" valign="top" align="center">
								<p><a href="<?php echo esc_url( home_url() ); ?>" style="text-decoration: underline; color: #9a9ea6; font-size: 16px; text-align: center;"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></a>.</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- END FOOTER -->

				<!-- END CENTERED WHITE CONTAINER --></div>
		</td>
		<td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
	</tr>
</table>
</body>
</html>
