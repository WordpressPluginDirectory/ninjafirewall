<?php
/*
 +=====================================================================+
 |    _   _ _        _       _____ _                        _ _        |
 |   | \ | (_)_ __  (_) __ _|  ___(_)_ __ _____      ____ _| | |       |
 |   |  \| | | '_ \ | |/ _` | |_  | | '__/ _ \ \ /\ / / _` | | |       |
 |   | |\  | | | | || | (_| |  _| | | | |  __/\ V  V / (_| | | |       |
 |   |_| \_|_|_| |_|/ |\__,_|_|   |_|_|  \___| \_/\_/ \__,_|_|_|       |
 |                |__/                                                 |
 |  (c) NinTechNet Limited ~ https://nintechnet.com/                   |
 +=====================================================================+
*/

if (! class_exists('NinjaFirewall_mail') ) {
	return;
}

/***********************************************************************
 * Subject line tag for all email notification.
 * Ex: "Subject: [NinjaFirewall] My email subject"
 */
$template['subject_line_tag'] = '[NinjaFirewall]';

/**
 * Email signature.
 */
$template['signature'] = 'NinjaFirewall (WP Edition) - https://nintechnet.com/';


/***********************************************************************
 * Daily activity report.
 * NinjaFirewall > Event Notifications > Daily report.
 */

$template['daily_report']['subject'] = __('Daily Activity Report for %s', 'ninjafirewall');

/* Translators: 1=domain, 2=date, 3-7=sum of threats and attacks. */
$template['daily_report']['content'] = __(
'Daily activity report for: %1$s
Date Range Processed: Yesterday, %2$s

Blocked threats: %3$s (critical: %4$s, high: %5$s, medium: %6$s)
Blocked brute-force attacks: %7$s

This notification can be turned off from NinjaFirewall "Event Notifications" page.', 'ninjafirewall');


/***********************************************************************
 * Firewall is disabled.
 * NinjaFirewall > Firewall Options > Firewall protection.
 */

$template['disabled']['subject'] = __('Alert: Firewall is disabled', 'ninjafirewall');

/* Translators: 1=username, 2=IP, 3=date, 4=blog url. */
$template['disabled']['content'] = __(
'Someone disabled NinjaFirewall from your WordPress admin dashboard:

User : %1$s
IP   : %2$s
Date : %3$s
Blog : %4$s', 'ninjafirewall');


/***********************************************************************
 * Firewall is disabled (debugging mode).
 * NinjaFirewall > Firewall Options > Debugging mode.
 */

$template['debugging']['subject'] = __('Alert: Firewall is disabled', 'ninjafirewall');

/* Translators: 1=username, 2=IP, 3=date, 4=blog url. */
$template['debugging']['content'] = __(
'NinjaFirewall is disabled because someone enabled debugging mode from your WordPress admin dashboard:

User : %1$s
IP   : %2$s
Date : %3$s
Blog : %4$s', 'ninjafirewall');


/***********************************************************************
 * Firewall override settings.
 * NinjaFirewall > Firewall Options > Import configuration.
 */

$template['fw_override']['subject'] = __('Alert: Firewall override settings', 'ninjafirewall');

/* Translators: 1=username, 2=IP, 3=date, 4=blog url. */
$template['fw_override']['content'] =__(
'Someone imported a new configuration which overrode the firewall settings:

User : %1$s
IP   : %2$s
Date : %3$s
Blog : %4$s', 'ninjafirewall');


/***********************************************************************
 * File Check detection.
 * NinjaFirewall > Monitoring > File Check.
 */

$template['fc_detection']['subject'] = __('Alert: File Check detection', 'ninjafirewall');

/* Translators: 1=blog url, 2=date, 3-5= sum of new, modified and deleted files */
$template['fc_detection']['content'] = __(
'NinjaFirewall File Check detected that changes were made to your files.

Blog: %1$s
Date: %2$s
New files: %3$s
Modified files: %4$s
Deleted files: %5$s

See attached file for details.', 'ninjafirewall');


/***********************************************************************
 * File Check report.
 * NinjaFirewall > Monitoring > File Check.
 */

$template['fc_report']['subject'] = __('File Check report', 'ninjafirewall');

/* Translators: 1=blog url, 2=date */
$template['fc_report']['content'] = __(
'NinjaFirewall File Check did not detect changes in your files.

Blog: %1$s
Date: %2$s', 'ninjafirewall');


/***********************************************************************
 * Security rules update.
 * NinjaFirewall > Security Rules > Rules Update.
 */

$template['rules_update']['subject'] = __('Security rules update', 'ninjafirewall');

/* Translators: 1=blog url, 2=version, 3=date */
$template['rules_update']['content'] = __(
'NinjaFirewall security rules have been updated:

Blog: %1$s
Rules version: %2$s
Date: %3$s

This notification can be turned off from NinjaFirewall "Security Rules" page.', 'ninjafirewall');


/***********************************************************************
 * Blocked attempt to edit/create a post.
 * NinjaFirewall > Firewall Policies > WordPress > Permissions.
 */

$template['perm_edit']['subject'] = __('Blocked post/page edition attempt', 'ninjafirewall');

/* Translators: 1=blog url, 2=username, 3=action, 4=post_title, 5=post_content,
 * 6=IP, 7=SCRIPT_FILENAME, 8=REQUEST_URI, 9=date, 10=PHP backtrace */
$template['perm_edit']['content'] = __(
'NinjaFirewall has blocked an attempt to edit/create a post by a user who doesn\'t have the right capabilities:

Blog: %1$s
Username: %2$s
Action: %3$s
post_title: %4$s
post_content: %5$s
User IP: %6$s
SCRIPT_FILENAME: %7$s
REQUEST_URI: %8$s
Date: %9$s

%10$s
This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall');


/***********************************************************************
 * Blocked post/page deletion attempt.
 * NinjaFirewall > Firewall Policies > WordPress > Permissions.
 */

$template['perm_delete']['subject'] = __('Blocked post/page deletion attempt', 'ninjafirewall');

/* Translators: 1=blog url, 2=username, 3=post ID, 4=post_title,
 * 5=IP, 6=SCRIPT_FILENAME, 7=REQUEST_URI, 8=date, 9=PHP backtrace */
$template['perm_delete']['content'] = __(
'NinjaFirewall has blocked an attempt to delete a post by a user who doesn\'t have the right capabilities:

Blog: %1$s
Username: %2$s
post ID: %3$s
post_title: %4$s
User IP: %5$s
SCRIPT_FILENAME: %6$s
REQUEST_URI: %7$s
Date: %8$s

%9$s
This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall');


/***********************************************************************
 * Blocked attempt to create a user account.
 * NinjaFirewall > Firewall Policies > WordPress > Permissions.
 */

$template['create_user']['subject'] = __('Blocked user account creation', 'ninjafirewall');

/* Translators: 1=blog url, 2=username, 3=IP, 4=SCRIPT_FILENAME,
 * 5=REQUEST_URI, 6=date, 7=PHP backtrace */
$template['create_user']['content'] = __(
'NinjaFirewall has blocked an attempt to create a user account:

Blog: %1$s
Username: %2$s (blocked)
User IP: %3$s
SCRIPT_FILENAME: %4$s
REQUEST_URI: %5$s
Date: %6$s

%7$s
This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.
', 'ninjafirewall');


/***********************************************************************
 * Blocked user deletion attempt.
 * NinjaFirewall > Firewall Policies > WordPress > Permissions.
 */

$template['delete_user']['subject'] = __('Blocked user deletion attempt', 'ninjafirewall');

/* Translators: 1=blog url, 2=username, 3=IP, 4=SCRIPT_FILENAME,
 * 5=REQUEST_URI, 6=date, 7=PHP backtrace */
$template['delete_user']['content'] = __(
'NinjaFirewall has blocked an attempt to delete a user account by a user who doesn\'t have the right capabilities:

Blog: %1$s
User to delete: %2$s
User IP: %3$s
SCRIPT_FILENAME: %4$s
REQUEST_URI: %5$s
Date: %6$s

%7$s
This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.
', 'ninjafirewall');


/***********************************************************************
 * User login.
 * NinjaFirewall > Event Notifications > WordPress admin dashboard.
 */

$template['user_login']['subject'] = __('Alert: WordPress console login', 'ninjafirewall');

/* Translators: 1=username, 2=IP, 3=date, 4=blog url */
$template['user_login']['content'] = __(
'Someone just logged in to your WordPress admin console:

User : %1$s
IP   : %2$s
Date : %3$s
Blog : %4$s

This notification can be turned off from NinjaFirewall "Event Notifications" page.',
'ninjafirewall');


/***********************************************************************
 * Database change.
 * NinjaFirewall > Event Notifications > Administrator account.
 */

$template['database_change']['subject'] = __('Alert: Database changes detected', 'ninjafirewall');

/* Translators: 1=blog url, 2=date, 3=sum admin users, 4=admin account data*/
$template['database_change']['content'] = __(
'NinjaFirewall has detected that one or more administrator accounts were modified in the database:

Blog: %1$s
Date: %2$s
Total administrators:  %3$s

%4$s

If you cannot see any modifications in the above fields, it is possible that the administrator password was changed.

This notification can be turned off from NinjaFirewall "Event Notifications" page.',
'ninjafirewall');


/***********************************************************************
 * Blocked privilege escalation attempt.
 * NinjaFirewall > Firewall Policies > Permissions.
 */

$template['privilege_escalation']['subject'] = __('Blocked privilege escalation attempt', 'ninjafirewall');

/* Translators: 1=blog url, 2=Username + user ID, 3=meta_key, 4=meta_value,
 * 5=IP, 6=SCRIPT_FILENAME, 7=REQUEST_URI, 8=Date, 9=PHP backtrace*/
$template['privilege_escalation']['content'] = __(
'NinjaFirewall has blocked an attempt to modify a user capability by someone who does not have administrative privileges:

Blog: %1$s
Username: %2$s
meta_key: %3$s
meta_value: %4$s
User IP: %5$s
SCRIPT_FILENAME: %6$s
REQUEST_URI: %7$s
Date: %8$s

%9$s
This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.',
'ninjafirewall');


/***********************************************************************
 * Attempt to modify WordPress settings.
 * NinjaFirewall > Firewall Policies > Permissions.
 */

$template['wp_settings']['subject'] = __('Attempt to modify WordPress settings', 'ninjafirewall');

/* Translators: 1=option name, 2=old value, 3=new value, 4=blog uri,
 * 5=IP, 6=SCRIPT_FILENAME, 7=REQUEST_URI, 8=Date */
$template['wp_settings']['content'] = __(
'NinjaFirewall has blocked an attempt to modify some important WordPress settings by a user that does not have administrative privileges:

Option: %1$s
Original value: %2$s
Modified value: %3$s
Action taken: The attempt was blocked and the option was reversed to its original value.

Blog: %4$s
User IP: %5$s
SCRIPT_FILENAME: %6$s
REQUEST_URI: %7$s
Date: %8$s',
'ninjafirewall');


/***********************************************************************
 * Security update available.
 * NinjaFirewall > Event Notifications > Security updates
 */

$template['security_updates']['subject'] = __('Warning: Security update available', 'ninjafirewall');

/* Translators: 1=date, 2=blog url, 3=update data */
$template['security_updates']['content'] = __(
'NinjaFirewall has detected that there are security updates available for your website:

Date: %1$s
Blog: %2$s

%3$s

Don\'t leave your blog at risk, make sure to update as soon as possible.

This notification can be turned off from NinjaFirewall "Event Notifications" page.
',
'ninjafirewall');


/***********************************************************************
 * WordPress|Plugin|Theme activated, deleted, update, installed etc.
 * NinjaFirewall > Event Notifications > Plugins|Themes|Core
 */

$template['events']['subject'] = __('Alert: %s', 'ninjafirewall');

/* Translators: 1:action (activation, installation...), 2=component,
 * 3=Username, 4=IP, 5=date, 6=blog url */
$template['events']['content'] = __(
'NinjaFirewall has detected the following activity on your account:

%1$s
%2$s

User: %3$s
IP: %4$s
Date: %5$s
Blog: %6$s
',
'ninjafirewall');


// -------- DO NOT EDIT BELOW ------------------------------------------

return $template;

