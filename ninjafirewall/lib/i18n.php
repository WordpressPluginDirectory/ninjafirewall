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

/**
 * Since WP 6.7, translation loading must not be triggered too early.
 */
function nfw_wp_i18n_definitions() {

	global $err_fw;

	$null = esc_html__('A true Web Application Firewall to protect and secure WordPress.', 'ninjafirewall');
	$err_fw = [
		1	=> esc_html__('Cannot find WordPress configuration file', 'ninjafirewall'),
		2	=>	esc_html__('Cannot read WordPress configuration file', 'ninjafirewall'),
		3	=>	esc_html__('Cannot retrieve WordPress database credentials', 'ninjafirewall'),
		4	=>	esc_html__('Cannot connect to WordPress database', 'ninjafirewall'),
		5	=>	esc_html__('Cannot retrieve user options from database (#2)', 'ninjafirewall'),
		6	=>	esc_html__('Cannot retrieve user options from database (#3)', 'ninjafirewall'),
		7	=>	esc_html__('Cannot retrieve user rules from database (#2)', 'ninjafirewall'),
		8	=>	esc_html__('Cannot retrieve user rules from database (#3)', 'ninjafirewall'),
		9	=>	sprintf(
			esc_html__('The firewall has been disabled from the %1$sadministration console%2$s', 'ninjafirewall'),
			'<a href="admin.php?page=nfsubopt">', '</a>'
		),
		10	=> esc_html__('Unable to communicate with the firewall. Please check your settings', 'ninjafirewall'),
		11	=>	esc_html__('Cannot retrieve user options from database (#1)', 'ninjafirewall'),
		12	=>	esc_html__('Cannot retrieve user rules from database (#1)', 'ninjafirewall'),
		13 => sprintf(
			esc_html__('The firewall cannot access its log and cache folders. If you changed the name of WordPress %1$s or %2$s folders, you must define NinjaFirewall\'s built-in %3$s constant (see %4$s for more info)', 'ninjafirewall'),
			'<code>/wp-content/</code>',
			'<code>/plugins/</code>',
			'<code>NFW_LOG_DIR</code>',
			"<a href='https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/' target='_blank'>Path to NinjaFirewall's log and cache directory</a>"
		),
		14 => esc_html__('The PHP msqli extension is missing or not loaded.', 'ninjafirewall'),
		15	=>	esc_html__('Cannot retrieve user options from database (#4)', 'ninjafirewall'),
		16	=>	esc_html__('Cannot retrieve user rules from database (#4)', 'ninjafirewall')
	];
}

add_action('init', 'nfw_wp_i18n_definitions');

// ---------------------------------------------------------------------
// EOF
