<?php
return array(
	array(
		'id'        => 'mpt-main',
		'tab_label' => __( 'General', 'mpt' ),
		'title'     => __( 'Features available', 'mpt' ),
		'desc'      => false,
	),
	array(
		'id'        => 'mpt-pages',
		'tab_label' => __( 'Feature pages', 'mpt' ),
		'title'     => __( 'Feature pages', 'mpt' ),
		'desc'      => __( 'Define here the pages containing WordPress\'s shortcodes for the different features as login, registration, etc.', 'mpt' ),
	),
	array(
		'id'        => 'mpt-security',
		'tab_label' => __( 'Security', 'mpt' ),
		'title'     => __( 'Security policy', 'mpt' ),
		'desc'      => __( 'Define your custom password policy by changing default behaviour about password strength, length, must-use characters and so on.', 'mpt' ),
	),
	array(
		'id'        => 'mpt-emails',
		'tab_label' => __( 'Mails', 'mpt' ),
		'title'     => __( 'Manage admin and member email\'s templates', 'mpt' ),
		'desc'      => __( 'Change however you want the admin\'s and member\'s email notifications. Please note that only described dynamic values will work for each notification.', 'mpt' ),
	)
);