<?php
return array(
	'mpt-main'     => array(
		array(
			'name'    => 'features',
			'label'   => __( 'Features', 'mpt' ),
			'options' => array(
				'role-manager'        => __( 'Role manager', 'mpt' ),
				'content-permissions' => __( 'Content permissions', 'mpt' ),
				'private-website'     => __( 'Private website', 'mpt' )
			),
			'type'    => 'multicheck',
			'desc'    => __( 'Do not change this value if you have already members! At the risk of breaking your site!', 'mpt' )
		),
		array(
			'name'  => 'authentification',
			'label' => __( 'Authentification settings', 'mpt' ),
			'desc'  => __( 'You can adjust the way your members connect to the site.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'allow-signon-email',
			'label'   => __( 'Sign-on method', 'mpt' ),
			'options' => __( 'Allow email sign-on ?', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 1,
			'desc'    => __( 'Do not change this value if you have already members! At the risk of breaking your site!', 'mpt' )
		),
		array(
			'name'    => 'unique-email',
			'label'   => __( 'Email constraint', 'mpt' ),
			'options' => __( 'Email must be unique ?', 'mpt' ),
			'desc'    => __( 'Do not change this value if you have already members! At the risk of breaking your site! This option is automatically enabled when you allow email sign-on.', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 1
		),
		array(
			'name'    => 'subscription-member-validation',
			'label'   => __( 'Member validation subscription', 'mpt' ),
			'options' => __( 'Member validate his registration by an email ?', 'mpt' ),
			'desc'    => __( 'If you check this box, members can not connect until they have not validated their account.', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 1
		),
		array(
			'name'  => 'role-manager',
			'label' => __( 'Role manager', 'mpt' ),
			'desc'  => __( 'Create roles, permissions, such as WordPress.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'default-role',
			'label'   => __( 'Default role', 'mpt' ),
			'desc'    => __( 'You can choose to set as a default role or not during membership registration.', 'mpt' ),
			'type'    => 'select',
			'options' => MPT_Plugin::_get_roles(),
			'default' => 'none'
		),
		array(
			'name'  => 'content-permissions',
			'label' => __( 'Content permissions', 'mpt' ),
			'desc'  => __( 'You can restrict access to your content only for your members.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'default-post-error-message',
			'label'   => __( 'Default post error message:', 'mpt' ),
			'desc'    => __( 'You can use HTML and/or shortcodes to create a custom error message for users that don\'t have permission to view posts.', 'mpt' ),
			'type'    => 'textarea',
			'default' => __( '<p class="restricted">Sorry, but you do not have permission to view this content.</p>', 'mpt' )
		),
		array(
			'name'  => 'private-website',
			'label' => __( 'Private website', 'mpt' ),
			'desc'  => __( 'You can restrict access to your site only for your members.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'redirect-logged-out-users',
			'label'   => __( 'Redirect ?', 'mpt' ),
			'options' => __( 'Redirect all logged-out users to the login page before allowing them to view the site.', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'error-feed',
			'label'   => __( 'Allow feed ?', 'mpt' ),
			'options' => __( 'Show error message for feed items.', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'feed-error-message',
			'label'   => __( 'Feed error message:', 'mpt' ),
			'desc'    => __( 'You can use HTML and/or shortcodes to create a custom error message to display instead of feed item content.', 'mpt' ),
			'type'    => 'textarea',
			'default' => __( '<p class="restricted">You must be logged into the site to view this content.</p>', 'mpt' )
		),
	),
	'mpt-pages'    => array(
		array(
			'name'    => 'page-registration',
			'label'   => __( 'Registration', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-registration-step-2',
			'label'   => __( 'Registration step 2', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-login',
			'label'   => __( 'Login/logout', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-two-factor',
			'label'   => __( 'Two factor challenge', 'mpt' ),
			'desc' => __( 'Used if the two-factor authentication is enable.', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-change-password',
			'label'   => __( 'Change password', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-lost-password',
			'label'   => __( 'Lost password', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-change-profile',
			'label'   => __( 'Change profile', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
		array(
			'name'    => 'page-account',
			'label'   => __( 'My account', 'mpt' ),
			//'desc' => __( 'Dropdown', 'mpt' ),
			'type'    => 'select',
			'options' => $all_pages
		),
	),
	'mpt-security' => array(
		array(
			'name'  => 'mode',
			'label' => __( 'Password strength', 'mpt' ),
			'desc'  => __( 'Enforce a specific password strength for your members.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'mode-select',
			'label'   => __( 'Mode', 'mpt' ),
			'desc'    => __( 'You can apply one of three modes for password security, no constraint, automatic cloned from WordPress JS meter and full manual mode', 'mpt' ),
			'type'    => 'radio',
			'default' => 'none',
			'options' => array(
				'none'   => __( 'Disabled', 'mpt' ),
				'auto'   => __( 'Auto', 'mpt' ),
				'custom' => __( 'Custom', 'mpt' ),
			)
		),
		array(
			'name'              => 'force-refresh-login',
			'label'             => __( 'Force refresh login', 'mpt' ),
			'desc'              => __( 'When changing your password policy, this option forces members to change their password once they are logged in! If it does not meet your criteria of course!', 'mpt' ),
			'type'              => 'checkbox',
			'default'           => 0,
			'sanitize_callback' => 'intval'
		),
		array(
			'name'  => 'auto-mode',
			'label' => __( 'Password strength - auto mode', 'mpt' ),
			'desc'  => __( 'WordPress offers a interactive password meter (with JS) when editing passwords. This indicator score passwords on 4 levels: 1 = very weak; 2 = weak; 3 = medium; 4 = strong', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'    => 'auto-mode-level',
			'label'   => __( 'Level required', 'mpt' ),
			'type'    => 'radio',
			'default' => '1',
			'options' => array(
				'1' => __( 'Very weak', 'mpt' ),
				'2' => __( 'Weak', 'mpt' ),
				'3' => __( 'Medium', 'mpt' ),
				'4' => __( 'Strong', 'mpt' ),
			)
		),
		array(
			'name'  => 'custom-mode',
			'label' => __( 'Password strength - Custom mode', 'mpt' ),
			'desc'  => __( 'This mode allows you to fine tune the security policy password', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'              => 'minimum-length',
			'label'             => __( 'Minimum length', 'mpt' ),
			'desc'              => __( 'Tip: password longer than 6 characters is highly recommended!', 'mpt' ),
			'type'              => 'text',
			'default'           => 6,
			'sanitize_callback' => 'intval'
		),
		array(
			'name'    => 'user-data-password',
			'label'   => __( 'User data on password', 'mpt' ),
			'desc'    => __( 'Prohibit the presence of the username, email, first name, last name in the password', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'uppercase-character',
			'label'   => __( 'Uppercase character', 'mpt' ),
			'desc'    => __( 'Forces the presence of an uppercase character in the password', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'lowercase-character',
			'label'   => __( 'Lowercase character', 'mpt' ),
			'desc'    => __( 'Forces the presence of an lowercase character in the password', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'number-character',
			'label'   => __( 'Number character', 'mpt' ),
			'desc'    => __( 'Forces the presence of an number character in the password', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0
		),
		array(
			'name'    => 'special-character',
			'label'   => __( 'Special character', 'mpt' ),
			'options' => __( 'Forces the presence of an special character in the password', 'mpt' ),
			'type'    => 'checkbox',
			'default' => 0,
			'desc'    => __( 'Special characters are often assimilated to punctuation character. (?!&")', 'mpt' )
		),
		array(
			'name'    => 'blacklist-keywords',
			'label'   => __( 'Blacklist keywords', 'mpt' ),
			'desc'    => __( 'You must separate blacklist words with commas. These words can not be contained in the passwords of your members.', 'mpt' ),
			'type'    => 'text',
			'default' => ''
		),
		array(
			'name'  => 'password-history',
			'label' => __( 'Password Policies', 'mpt' ),
			'desc'  => __( 'A password policy is a set of rules designed to enhance security by encouraging users to employ strong passwords and use them properly', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name'              => 'aging',
			'label'             => __( 'Aging', 'mpt' ),
			'desc'              => __( 'How many days old can a password be before requiring it be changed? Not recommended. 0 disables this feature. Default: 0.', 'mpt' ),
			'type'              => 'text',
			'default'           => 0,
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'history',
			'label'             => __( 'History', 'mpt' ),
			'desc'              => __( 'How many passwords should be remembered? Prevents reuse of old passwords. 0 disables this feature. Default: 0.', 'mpt' ),
			'type'              => 'text',
			'default'           => 0,
			'sanitize_callback' => 'intval'
		),
		array(
			'name'  => 'last-login-details',
			'label' => __( 'Last login details', 'mpt' ),
			'desc'  => '',
			'type'  => 'metabox',
		),
		array(
			'name'              => 'user-activity',
			'label'             => __( 'User\'s activity', 'mpt' ),
			'desc'              => __( 'Display user\'s last connection information (date & time, operating system, browser, IP address).', 'mpt' ),
			'type'              => 'checkbox',
			'default'           => 0,
		),
		array(
			'name'  => 'two-factor-configuration',
			'label' => __( 'Two-factor', 'mpt' ),
			'desc'  => '',
			'type'  => 'metabox',
		),
		array(
			'name'              => 'enable-two-factor',
			'label'             => __( 'Enable two-factor authentication', 'mpt' ),
			'desc'              => __( 'Members will need to enter a code send by mail when login in.', 'mpt' ),
			'type'              => 'checkbox',
			'default'           => 0,
		),
	),
	'mpt-emails'   => array(
		array(
			'name'  => 'register_member_admin_mail',
			'label' => __( 'Admin mail for Member registration', 'mpt' ),
			'desc'  => __( 'Management of mail notification to the site administrator when a new member joins the site.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'register_member_admin' ),
			'desc' => MPT_Options::description_setting_desc( 'register_member_admin' ),
			'type' => 'html',
		),
		array(
			'name'    => 'registration_member_admin_mail_to',
			'label'   => __( 'Recipient Mail', 'mpt' ),
			'default' => get_option( 'admin_email' ),
			'desc'    => __( 'By default this is the administrator\'s email of the site but you can change the recipient. You can add more recipients, separating them with a comma', 'mpt' ),
			'type'    => 'text',
		),
		array(
			'name'    => 'registration_member_admin_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] New Member Registration', 'mpt' ),
		),
		array(
			'name'              => 'registration_member_admin_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( "New member registration on your site %%blog_name%% :\n\nUsername: %%user_name%%\nMail: %%user_email%%", 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
		array(
			'name'  => 'register_member_mail',
			'label' => __( 'Member registration email', 'mpt' ),
			'desc'  => __( 'Management of notification\'s mail by a Member upon registration.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'register_member' ),
			'desc' => MPT_Options::description_setting_desc( 'register_member' ),
			'type' => 'html',
		),
		array(
			'name'    => 'register_member_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] Your username and password', 'mpt' ),
		),
		array(
			'name'              => 'register_member_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( "Username: %%user_name%%\nPassword: %%user_password%%\n\n%%login_url%%", 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
		array(
			'name'  => 'register_member_validation_mail',
			'label' => __( 'Member registration email validation', 'mpt' ),
			'desc'  => __( 'Registration confirmation email with link validation counted', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'register_member_validation' ),
			'desc' => MPT_Options::description_setting_desc( 'register_member_validation' ),
			'type' => 'html',
		),
		array(
			'name'    => 'register_member_validation_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] Validate your registration', 'mpt' ),
		),
		array(
			'name'              => 'register_member_validation_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( "Thank you for registering on the site:\n\n%%site_url%%\n\nTo validate your account and complete the registration please click on the following link:\n\n%%confirm_register_link%%", 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
		array(
			'name'  => 'lost_password_admin',
			'label' => __( 'Admin mail for Member lost password', 'mpt' ),
			'desc'  => __( 'Management of notification\'s mail to the administrator when a member has lost his password.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'lost_password_admin' ),
			'desc' => MPT_Options::description_setting_desc( 'lost_password_admin' ),
			'type' => 'html',
		),
		array(
			'name'    => 'lost_password_admin_mail',
			'label'   => __( 'Recipient mail', 'mpt' ),
			'type'    => 'text',
			'default' => get_option( 'admin_email' ),
			'desc'    => __( 'By default this is the administrator\'s mail of the site but you can change the recipient. You can add more recipients, separating them with a comma', 'mpt' )
		),
		array(
			'name'    => 'lost_password_admin_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] Password Lost/Changed', 'mpt' ),
		),
		array(
			'name'              => 'lost_password_admin_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( 'Password Lost and Changed for member: %%user_name%%', 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
		array(
			'name'  => 'lost_password_member',
			'label' => __( 'Member lost password mail', 'mpt' ),
			'desc'  => __( 'Management of notification\'s mail by a Member lost his password.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'lost_password_member' ),
			'desc' => MPT_Options::description_setting_desc( 'lost_password_member' ),
			'type' => 'html',
		),
		array(
			'name'    => 'lost_password_member_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] Password Reset', 'mpt' ),
		),
		array(
			'name'              => 'lost_password_member_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( "Someone requested that the password be reset for the following account:\n\n%%site_url%%\n\nUsername: %%user_name%%\n\nIf this was a mistake, just ignore this email and nothing will happen.\n\nTo reset your password, visit the following address:\n\n%%reset_pwd_link%%", 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
		array(
			'name'  => 'validate_new_email_member',
			'label' => __( 'Validate new email member', 'mpt' ),
			'desc'  => __( 'Manage member notification mail for email change.', 'mpt' ),
			'type'  => 'metabox',
		),
		array(
			'name' => MPT_Options::description_setting_name( 'validate_new_email_member' ),
			'desc' => MPT_Options::description_setting_desc( 'validate_new_email_member' ),
			'type' => 'html',
		),
		array(
			'name'    => 'validate_new_email_member_subject',
			'label'   => __( 'Subject mail', 'mpt' ),
			'type'    => 'text',
			'default' => __( '[%%blog_name%%] New Admin Email Address', 'mpt' ),
		),
		array(
			'name'              => 'validate_new_email_member_content',
			'label'             => __( 'Content mail', 'mpt' ),
			'type'              => 'textarea',
			'default'           => __( "Hello,\nA request to change your email address has been sent to :\n%%site_url%%\n\nThe request concerns the account of %%display_name%%.\n\nTo confirm this change, please click on the following link:\n %%validate_email_link%%\n\nYou can safely ignore and delete this email if you do not wish to take this action.\n\nYours sincerely", 'mpt' ),
			'sanitize_callback' => 'strip_tags'
		),
	),
);
