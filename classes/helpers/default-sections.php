<?php
return array(
	array(
		'id' => 'mpt-main',
		'tab_label' => __( 'General', 'mpt' ),
		'title' => __( 'Features available', 'mpt' ),
		'desc' => false,
	),
	array(
		'id' => 'mpt-pages',
		'tab_label' => __( 'Feature Pages', 'mpt' ),
		'title' => __( 'Feature Pages', 'mpt' ),
		'desc' => __( 'You must define here the pages containing the WordPress shortcodes for different features (login, registration, etc).', 'mpt' ),
	),
	array(
		'id' => 'mpt-security',
		'tab_label' => __( 'Security', 'mpt' ),
		'title' => __( 'Password strength', 'mpt' ),
		'desc' => __( 'Enforce a specific password strength for your members.', 'mpt' ),
	),
	array(
		'id' => 'mpt-emails',
		'tab_label' => __( 'Mails', 'mpt' ),
		'title' => __( 'Admin mail for Member registration', 'mpt' ),
		'desc' => __( 'Management of mail notification to the site administrator when a new member joins the site.', 'mpt' ),
	)
);