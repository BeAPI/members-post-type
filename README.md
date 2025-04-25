# Members post type #

## Description ##

Manage members on WordPress as post type. Implement: post type, authentification, role, clone from WordPress.

## Important to know ##

### 0.7.2
Compatibility : 4.6

If you have implemented custom views before 0.7.2, you might update them. Especially because `_wpnonce` => `_mptnonce` and `wp_verify_nonce` => `mpt_verify_nonce`. Find an exemple [here](https://github.com/BeAPI/members-post-type/commit/2562b7e79feebf09967a2f964f3144e8f6d10930#diff-fac5c1b7350b8f3af605e75406b9c751).

### 0.7.0
Compatibility : 4.4

If you use the roles and capabilities you have to migrate all data from meta for taxonomies to WordPress native functions.
To do so, download the meta for taxonomies plugin and let the plugin migrate the data for you.

## Changelog ##
### 1.3.5 -  25 Apr 2025
* Add new filter `mpt_view_account_pages_query_args` to customize account's pages query args.

### 1.3.4 -  15 Apr 2025
* Add firstname and lastname constant for the validation new email

### 1.3.3 -  07 Apr 2025
* Hide registration link if option is empty

### 1.3.2 -  27 Mar 2025
* Trim 2FA code sended by user

### 1.3.1 - 26 Mar 2025
* Set "Role" taxonomy to be hierarchical.

### 1.3.0 - 18 Mar 2025
* Add options to customize and translate two-factor email.

### 1.2.0 - 14 Mar 2025
* Add option to send emails as HTML instead of plain text.

### 1.1.4 - 20 Feb 2025
* Add support for password toggle

### 1.1.3 - 19 Feb 2025
* fix resend notification for registration
* add new dynamic variable to display current site's url
* fix date format for the activity log
* fix user os for the activity log
* fix user browser for the activity log
* update translation
* hide fields after password reset
* add a redirection to the correct language when updating the language from the user profile. (PLL)

### 1.1.2 - 6 Feb 2025
* skip two-factor shortcode rendering for admin/ajax/rest requests
* fix extraneous chevron in shortcode template

### 1.1.1 - 5 Feb 2025
* fix condition in account shortcode

### 1.1.0 - 5 Feb 2025
* account editing for members
* email change validation
* support for polylang
* two-factor authentication

### 1.0.10 - 4 Nov 2017
* Ensure to send all admin notifications.

### 1.0.9 - 7 Sept 2017
* Add first name and last for user password reset notification.

### 1.0.8 - 25 April 2017
* replace do_action() with apply_filters() cause you MUST NOT echo in shortcode handler

### 1.0.7 - 19 April 2017
* add useful do_action() on login tpl
* increment versions and make it the same as release

### 1.0.5 - 13 Mar 2017
* fix undefined var

### 1.0.4.2 - 02 Mar 2017
* composer support

### 1.0.3 - 15 Feb 2017
* Fix textdomain for errors notification.

### 1.0.2 - 5 Dec 2016
* Fix wrong mapping %%user_name%% value.

### 1.0.1 - 5 Dec 2016
* Fix wrong default register notification.

### 1.0.0 - 28 Nov 2016
* Add for email notifications the available replacements values which are automatically replaced before email send.
* Update the .pot and French po/mo.

### 0.7.2 - 5 Oct 2016
* `mpt_nonce_field()` method for nounce generating has been integrated to decorrelate members and WordPress users on one hand, and to not share the same cookie for all connected members, as before, in other hand.

### 0.7.1 - 10 Mar 2016
* Fix missing `mpt_verify_nonce`.

### 0.7.0 - 10 Fev 2016
* Update the way roles and capabilities works with members.
