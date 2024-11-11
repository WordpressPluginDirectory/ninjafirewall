<?php
/*
Plugin Name: NinjaFirewall (WP Edition)
Plugin URI: https://nintechnet.com/
Description: A true Web Application Firewall to protect and secure WordPress.
Version: 4.7
Author: The Ninja Technologies Network
Author URI: https://nintechnet.com/
License: GPLv3 or later
Network: true
Text Domain: ninjafirewall
Domain Path: /languages
*/
define('NFW_ENGINE_VERSION', '4.7');
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

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

/* ------------------------------------------------------------------ */

function nfw_load_txtdomain() {

	if ( defined('NFW_NOI18N') ) { return; }

	unload_textdomain('ninjafirewall');
	$nf_locale = ['fr_FR'];
	$this_user_locale = get_user_locale();
	if ( in_array( $this_user_locale, $nf_locale ) ) {
		if ( file_exists( __DIR__ ."/languages/ninjafirewall-{$this_user_locale}.mo") ) {
			load_textdomain('ninjafirewall', __DIR__ ."/languages/ninjafirewall-{$this_user_locale}.mo");
		}
	} elseif ( file_exists( WP_LANG_DIR ."/plugins/ninjafirewall-{$this_user_locale}.mo") ) {
		load_textdomain('ninjafirewall', WP_LANG_DIR ."/plugins/ninjafirewall-{$this_user_locale}.mo");
	}
}
add_action('plugins_loaded','nfw_load_txtdomain');

/* ------------------------------------------------------------------ */

$null = __('A true Web Application Firewall to protect and secure WordPress.', 'ninjafirewall');
define('NFW_NULL_BYTE', 2);
define('NFW_SCAN_BOTS', 531);
define('NFW_ASCII_CTRL', 500);
define('NFW_DOC_ROOT', 510);
define('NFW_WRAPPERS', 520);
define('NFW_OBJECTS', 525);
define('NFW_LOOPBACK', 540);
define( 'NFW_DEFAULT_MSG', '<br /><br /><br /><br /><center>' .
		sprintf( __('Sorry %s, your request cannot be processed.', 'ninjafirewall'), '<b>%%REM_ADDRESS%%</b>') .
		'<br />' . __('For security reasons, it was blocked and logged.', 'ninjafirewall') .
		'<br /><br />%%NINJA_LOGO%%<br /><br />' .
			__('If you believe this was an error please contact the<br />webmaster and enclose the following incident ID:', 'ninjafirewall') .
		'<br /><br />[ <b>#%%NUM_INCIDENT%%</b> ]</center>'
);
$err_fw = [
	1	=> __('Cannot find WordPress configuration file', 'ninjafirewall'),
	2	=>	__('Cannot read WordPress configuration file', 'ninjafirewall'),
	3	=>	__('Cannot retrieve WordPress database credentials', 'ninjafirewall'),
	4	=>	__('Cannot connect to WordPress database', 'ninjafirewall'),
	5	=>	__('Cannot retrieve user options from database (#2)', 'ninjafirewall'),
	6	=>	__('Cannot retrieve user options from database (#3)', 'ninjafirewall'),
	7	=>	__('Cannot retrieve user rules from database (#2)', 'ninjafirewall'),
	8	=>	__('Cannot retrieve user rules from database (#3)', 'ninjafirewall'),
	9	=>	__('The firewall has been disabled from the <a href="admin.php?page=nfsubopt">administration console</a>', 'ninjafirewall'),
	10	=> __('Unable to communicate with the firewall. Please check your settings', 'ninjafirewall'),
	11	=>	__('Cannot retrieve user options from database (#1)', 'ninjafirewall'),
	12	=>	__('Cannot retrieve user rules from database (#1)', 'ninjafirewall'),
	13 => sprintf( __("The firewall cannot access its log and cache folders. If you changed the name of WordPress %s or %s folders, you must define NinjaFirewall's built-in %s constant (see %s for more info)", 'ninjafirewall'), '<code>/wp-content/</code>', '<code>/plugins/</code>', '<code>NFW_LOG_DIR</code>', "<a href='https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/' target='_blank'>Path to NinjaFirewall's log and cache directory</a>"),
	14 => __('The PHP msqli extension is missing or not loaded.', 'ninjafirewall'),
	15	=>	__('Cannot retrieve user options from database (#4)', 'ninjafirewall'),
	16	=>	__('Cannot retrieve user rules from database (#4)', 'ninjafirewall')

];

if (! defined('NFW_LOG_DIR') ) {
	define('NFW_LOG_DIR', WP_CONTENT_DIR);
}
if (! empty($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != '/' ) {
	$_SERVER['DOCUMENT_ROOT'] = rtrim( $_SERVER['DOCUMENT_ROOT'] , '/' );
}

/* ------------------------------------------------------------------ */

/**
 * Select whether we want to use PHP or NF sessions.
 */
if ( defined('NFWSESSION') ) {
	if (! defined('NFWSESSION_DIR') ) {
		/**
		 * NFWSESSION_DIR can be defined in the .htninja.
		 */
		define('NFWSESSION_DIR', NFW_LOG_DIR .'/session');
	}
	require_once __DIR__ .'/lib/class-nfw-session.php';
} else {
	require_once __DIR__ .'/lib/class-php-session.php';
}

// class-helpers could be already loaded if the firewall is loaded
require_once __DIR__ . '/lib/class-helpers.php';

require __DIR__ . '/lib/custom_plugin.php';
require __DIR__ . '/lib/scheduled_tasks.php';
require __DIR__ . '/lib/utils.php';
require __DIR__ . '/lib/events.php';

if (! defined( 'NFW_REMOTE_ADDR') ) {
	nfw_select_ip();
}

add_action( 'nfwgccron', 'nfw_garbage_collector' );

/* ------------------------------------------------------------------ */			//s1:h0

function nfw_activate() {

	// Install/activate NinjaFirewall

	if ( defined('WP_CLI') && WP_CLI && PHP_SAPI === 'cli' ) {
		$php_cli = true;
	}

	if (! isset( $php_cli ) ) {
		// Warn if the user does not have the 'unfiltered_html' capability:
		if (! current_user_can('unfiltered_html') ) {
			exit( esc_html__('You do not have "unfiltered_html" capability. Please enable it in order to run NinjaFirewall (or make sure you do not have "DISALLOW_UNFILTERED_HTML" in your wp-config.php script).', 'ninjafirewall'));
		}

		nf_not_allowed( 'block', __LINE__ );
	}

	global $wp_version;
	if ( version_compare( $wp_version, '4.7.0', '<' ) ) {
		exit( sprintf( esc_html__('NinjaFirewall requires WordPress %s or greater but your current version is %s.', 'ninjafirewall'), '4.7.0', $wp_version) );
	}

	if ( version_compare( PHP_VERSION, '7.1.0', '<' ) ) {
		exit( sprintf( esc_html__('NinjaFirewall requires PHP 7.1 or greater but your current version is %s.', 'ninjafirewall'), PHP_VERSION) );
	}

	if (! function_exists('mysqli_connect') ) {
		exit( sprintf( esc_html__('NinjaFirewall requires the PHP %s extension.', 'ninjafirewall'), '<code>mysqli</code>') );
	}

	if ( ini_get( 'safe_mode' ) ) {
		exit( esc_html__('You have SAFE_MODE enabled. Please disable it, it is deprecated as of PHP 5.3.0 (see http://php.net/safe-mode).', 'ninjafirewall'));
	}

	if ( PATH_SEPARATOR == ';' ) {
		exit( esc_html__('NinjaFirewall is not compatible with Microsoft Windows.', 'ninjafirewall') );
	}

	if (! $nfw_options = nfw_get_option( 'nfw_options' ) ) {
		// First time we're running: download the security rules
		// and populate the options:
		require_once __DIR__ .'/lib/install_default.php';
		nfw_load_default_conf();
		// Reload them
		$nfw_options = nfw_get_option( 'nfw_options' );
	} else {
		// (Re)create the loader
		require_once __DIR__ .'/lib/install_default.php';
		nfw_create_loader();
	}

	$nfw_options['enabled'] = 1;
	nfw_update_option( 'nfw_options', $nfw_options);

	$res = nfw_enable_wpwaf();
	if (! empty( $res ) ){
		exit( $res );
	}

	// Create scheduled tasks.
	nfw_create_scheduled_tasks();

	// Re-enable brute-force protection
	if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php' ) ) {
		rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php');
	}
}

register_activation_hook( __FILE__, 'nfw_activate' );

/* ------------------------------------------------------------------ */

function nfw_deactivate() {

	if ( defined('WP_CLI') && WP_CLI && PHP_SAPI === 'cli' ) {
		$php_cli = true;
	}

	if (! isset( $php_cli ) ) {
		// Warn if the user does not have the 'unfiltered_html' capability:
		if (! current_user_can( 'unfiltered_html' ) ) {
			exit( esc_html__('You do not have "unfiltered_html" capability. Please enable it in order to run NinjaFirewall (or make sure you do not have "DISALLOW_UNFILTERED_HTML" in your wp-config.php script).', 'ninjafirewall'));
		}
		nf_not_allowed( 'block', __LINE__ );

		global $current_user;
		$current_user	= wp_get_current_user();
		$user_login		= $current_user->user_login;
		$user_roles		= $current_user->roles[0];
	} else {
		$user_login		= 'WP CLI';
		$user_roles		= '-';
	}

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Reused from Firewall Options
	if ( empty( $_REQUEST['action'] ) || strpos( $_REQUEST['action'], 'deactivate' ) === false ) {
		$subject = __('[NinjaFirewall] Alert: Firewall is disabled', 'ninjafirewall');
		if ( is_multisite() ) {
			$url = __('-Blog :', 'ninjafirewall') .' '. network_home_url('/') . "\n\n";
		} else {
			$url = __('-Blog :', 'ninjafirewall') .' '. home_url('/') . "\n\n";
		}
		$message = __('Someone disabled NinjaFirewall from your WordPress admin dashboard:', 'ninjafirewall') . "\n\n";
		$message .= __('-User :', 'ninjafirewall') ." $user_login ($user_roles)\n" .
			__('-IP   :', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n" .
			__('-Date :', 'ninjafirewall') .' '. ucfirst( date_i18n('F j, Y @ H:i:s O') ) ."\n" .
			$url .
			NF_PG_SIGNATURE ."\n";
		nfw_mail( $subject, $message, 'unsubscribe' );
	}

	$nfw_options['enabled'] = 0;
	nfw_disable_wpwaf();

	if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php' ) ) {
		rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php');
	}

	nfw_update_option( 'nfw_options', $nfw_options);

	// Remove any existing cron
	nfw_delete_scheduled_tasks();

}

register_deactivation_hook( __FILE__, 'nfw_deactivate' );

/* ------------------------------------------------------------------ */
// Load script/style files

function nfw_load_ext( $hook ) {

	// Load the external JS script and CSS:
	// -Single site: to the admin only.
	// -Multi-site: to the superadmin and from the main network admin screen only.
	// -All: only if this is a NinjaFirewall menu page
	if (! current_user_can('activate_plugins') || ! is_main_site() ) { return; }
	if ( stripos( $hook, 'ninjafirewall' ) === false ) { return; }

	if ( strpos ( $hook, 'nfsubwplus' ) !== false ) {
		// Load thickbox JS and CSS (WP only for "WP+" menu page's screenshots)
		$extra_js = ['jquery', 'thickbox'];
		$extra_css = ['thickbox'];
	} else {
		$extra_js = ['jquery'];
		$extra_css = null;
	}

	// TipTip (WP Edition only)
	wp_enqueue_script(
		'jquery-tiptip',
		plugin_dir_url( __FILE__ ) .'static/jquery.tipTip.js',
		['jquery'],
		NFW_ENGINE_VERSION
	);


	wp_enqueue_script(
		'nfw_javascript',
		plugin_dir_url( __FILE__ ) .'static/ninjafirewall.js',
		$extra_js,
		NFW_ENGINE_VERSION
	);

	// Load Chart.js if we are viewing the statistics page:
	if ( strpos( $hook, 'NinjaFirewall' ) !== false ) {
		wp_enqueue_script(
			'nfw_charts',
			plugin_dir_url( __FILE__ ) . 'static/chart.min.js',
			['jquery'],
			NFW_ENGINE_VERSION,
			// We load it in the footer, because some plugins loads it too
			// on every pages and that could mess with our pages
			true
		);
	}

	wp_enqueue_style(
		'nfw_style',
		plugin_dir_url( __FILE__ ) .'static/ninjafirewall.css',
		$extra_css,
		NFW_ENGINE_VERSION,
		false
	);

	// Javascript i18n:
	$nfw_js_array = [

		// Generic
		'restore_default' =>
			__('All fields will be restored to their default values and any changes you made will be lost. Continue?', 'ninjafirewall'),

		// Full WAF/WordPress WAF
		'missing_nonce' =>
			__('Missing security nonce, try to reload the page.', 'ninjafirewall'),
		'missing_httpserver' =>
			__('Please select the HTTP server in the list.', 'ninjafirewall'),
		// Dashboard
		'del_errorlog' =>
			__('Delete the firewall\'s error log ?', 'ninjafirewall'),

		// Firewall Options
		'restore_warning' =>
			__('This action will restore the selected configuration file and will override all your current firewall options, policies and rules. Continue?', 'ninjafirewall'),

		// Firewall Policies
		'warn_sanitise' =>
			__('Any character that is not a letter [a-zA-Z], a digit [0-9], a dot [.], a hyphen [-] or an underscore [_] will be removed from the filename and replaced with the substitution character. Continue?', 'ninjafirewall'),
		'ssl_warning' =>
			__('Ensure that you can access your admin console over HTTPS before enabling this option, otherwise you will lock yourself out of your site. Continue?', 'ninjafirewall'),
		'woo_warning' =>
			__("WooCommerce is running: if you block accounts creation, your customers won't be able to sign up. Continue?", 'ninjafirewall'),
		'reguser_warning' =>
			__("Your blog has user registration enabled: if you block accounts creation, your customers won't be able to sign up. Continue?", 'ninjafirewall'),
		'regsite_warning' =>
			__("Your multisite installation allows users to register new sites: if you enable this option, they will likely get blocked when creating their blog. Continue?", 'ninjafirewall'),

		// File Check
		'del_snapshot' =>
			__('Delete the current snapshot ?', 'ninjafirewall'),

		// Login Protection
		'invalid_char' =>
			__('Invalid character.', 'ninjafirewall'),
		'no_admin' =>
			__('"admin" is not acceptable, please choose another user name.', 'ninjafirewall'),
		'max_char' =>
			__('Please enter max 1024 character only.', 'ninjafirewall'),
		'select_when' =>
			__('Select when to enable the login protection.', 'ninjafirewall'),
		'missing_auth' =>
			__('Enter a name and a password for the HTTP authentication.', 'ninjafirewall'),

		// Firewall Log
		'invalid_key' =>
			__('Your public key is not valid.', 'ninjafirewall'),

		// Live Log
		'live_log_desc' =>
			__('Live Log lets you watch your blog traffic in real time. To enable it, click on the button below.', 'ninjafirewall'),
		'no_traffic' =>
			__('No traffic yet, please wait', 'ninjafirewall'),
		'seconds' =>
			' ' . __('seconds...', 'ninjafirewall'),
		'err_unexpected' =>
			__('Error: Live Log did not receive the expected response from your server:', 'ninjafirewall'),
		'error_404' =>
			__('Error: URL does not seem to exist (404 Not Found):', 'ninjafirewall'),
		'log_not_found' =>
			__('Error: Cannot find your log file. Try to reload this page.', 'ninjafirewall'),
		'http_error' =>
			__('Error: The HTTP server returned the following error code:', 'ninjafirewall')
	];

	wp_localize_script( 'nfw_javascript', 'nfwi18n', $nfw_js_array );
}

add_action( 'admin_enqueue_scripts', 'nfw_load_ext' );

/* ------------------------------------------------------------------ */

function nfw_admin_init() {

	// We must make sure that the current PHP session is always
	// updated even for whitelisted non-admin users (must be logged-in
	// to prevent unauthenticated AJAX calls to trigger it):
	if ( is_user_logged_in() ) {
		NinjaFirewall_session::start();
		// Save user's capabilities
		$nf_user = wp_get_current_user();
		if ( $nf_user instanceof WP_User ) {
			NinjaFirewall_session::write( ['allcaps' => $nf_user->allcaps ] );
		}
	}

	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_rules = nfw_get_option( 'nfw_rules' );

	// Post-update adjustment:
	require plugin_dir_path(__FILE__) . 'lib/init_update.php';

	// Make sure cronjobs are running as expected
	nfw_verify_scheduled_tasks();

	// --------------------------------------------
	// Anything below requires admin authentication
	// --------------------------------------------

	if ( nf_not_allowed(0, __LINE__) ) { return; }

	// Create our unique PID
	$nfw_pid = NFW_LOG_DIR .'/nfwlog/cache/.pid';
	if (! file_exists( $nfw_pid ) ) {
		file_put_contents( $nfw_pid, uniqid('', true) );
	}

	// Update fallback loader if needed
	if ( wp_doing_ajax() == false ) {
		nfw_enable_wpwaf();
	}

	// Security update in WP plugins:
	global $pagenow;
	if ( $pagenow == 'plugins.php' && current_user_can( 'update_plugins' ) ) {
		nfw_verify_secupdates();
	}

	// Export configuration:
	if ( isset($_POST['nf_export']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'options_save') ) {
			wp_nonce_ays('options_save');
		}
		$nfwbfd_log = NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php';
		if ( file_exists($nfwbfd_log) ) {
			$bd_data = json_encode( file_get_contents($nfwbfd_log) );
		} else {
			$bd_data = '';
		}
		// Dropins
		if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php' ) ) {
			$nfw_rules['dropins'] = base64_encode( file_get_contents( NFW_LOG_DIR .'/nfwlog/dropins.php' ) );
		}
		$data = json_encode($nfw_options) . "\n:-:\n" . json_encode($nfw_rules) . "\n:-:\n" . $bd_data;
		header('Content-Type: text/plain');
		header('Content-Length: '. strlen( $data ) );
		header('Content-Disposition: attachment; filename="nfwp.' . NFW_ENGINE_VERSION . '.dat"');
		echo $data;
		exit;
	}

	// Download File Check modified files list:
	if ( isset($_POST['dlmods']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'filecheck_save') ) {
			wp_nonce_ays('filecheck_save');
		}
		if (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php') ) {
			$download_file = NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php';
		} elseif (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php.php') ) {
			$download_file = NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php.php';
		} else {
			wp_nonce_ays('filecheck_save');
		}
		$stat = stat($download_file);
		$data = '== NinjaFirewall File Check (diff)'. "\n";
		$data.= '== ' . site_url() . "\n";
		$data.= '== ' . date_i18n('M d, Y @ H:i:s O', $stat['ctime']) . "\n\n";
		$data.= '[+] = ' . __('New file', 'ninjafirewall') .
					'      [!] = ' . __('Modified file', 'ninjafirewall') .
					'      [-] = ' . __('Deleted file', 'ninjafirewall') .
					"\n\n";
		$fh = fopen($download_file, 'r');
		while (! feof($fh) ) {
			$res = explode('::', fgets($fh) );
			if ( empty($res[1]) ) { continue; }
			if ($res[1] == 'N') {
				$data .= '[+] ' . $res[0] . "\n";
			} elseif ($res[1] == 'D') {
				$data .= '[-] ' . $res[0] . "\n";
			} elseif ($res[1] == 'M') {
				$data .= '[!] ' . $res[0] . "\n";
			}
		}
		fclose($fh);
		$data .= "\n== EOF\n";

		header('Content-Type: text/plain');
		header('Content-Length: '. strlen( $data ) );
		header('Content-Disposition: attachment; filename="'. $_SERVER['SERVER_NAME'] .'_diff.txt"');
		echo $data;
		exit;
	}

	// Download File Check snapshot:
	if ( isset($_POST['dlsnap']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'filecheck_save') ) {
			wp_nonce_ays('filecheck_save');
		}
		if (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php') ) {
			$stat = stat(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php');
			$data = '== NinjaFirewall File Check (snapshot)'. "\n";
			$data.= '== ' . site_url() . "\n";
			$data.= '== ' . date_i18n('M d, Y @ H:i:s O', $stat['ctime']) . "\n\n";
			$fh = fopen(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php', 'r');
			while (! feof($fh) ) {
				$res = explode('::', fgets($fh) );
				if (! empty($res[0][0]) && $res[0][0] == '/') {
					$data .= $res[0] . "\n";
				}
			}
			fclose($fh);
			$data .= "\n== EOF\n";
			header('Content-Type: text/plain');
			header('Content-Length: '. strlen( $data ) );
			header('Content-Disposition: attachment; filename="'. $_SERVER['SERVER_NAME'] .'_snapshot.txt"');
			echo $data;
			exit;
		} else {
			wp_nonce_ays('filecheck_save');
		}
	}

	// Applies to admin only (unlike the WP+ Edition):
	if (! empty( $nfw_options['wl_admin'] ) ) {
		if (! empty( $nfw_options['bf_enable'] ) && ! empty( $nfw_options['bf_rand'] ) ) {
			NinjaFirewall_session::write( ['nfw_goodguy' => true, 'nfw_bfd' => $nfw_options['bf_rand'] ] );
		} else {
			NinjaFirewall_session::write( ['nfw_goodguy' => true ] );
		}
		return;
	}
	NinjaFirewall_session::delete('nfw_goodguy');
}

add_action('admin_init', 'nfw_admin_init' );

// ---------------------------------------------------------------------
// Check if the user wants to remove her email from the notification list.

function nfw_init_emailremoval() {

	if (! empty( $_GET['nfw_stop_notification'] ) ) {
		require_once 'lib/email_sodium.php';
		nfw_sodium_decrypt( $_GET['nfw_stop_notification'] );
	}

}
add_action('init', 'nfw_init_emailremoval' );

// ---------------------------------------------------------------------
// Check if the user is an admin and if we must whitelist them.

function nfw_login_hook( $user_login, $user ) {

	NinjaFirewall_session::start();

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Don't do anything if NinjaFirewall is disabled:
	if ( empty( $nfw_options['enabled'] ) ) { return; }

	// Fetch user roles:
	$whoami = '';
	foreach( $user->roles as $k => $v ) {
		if ( $v == 'administrator' ) {
			$admin_flag = 1;
		}
		$whoami .= "$v ";
	}
	$whoami = trim( $whoami );

	// Still nothing: Maybe an additional superadmin
	if ( empty( $whoami ) && is_multisite() ) {
		// $user->ID is required here
		if ( is_super_admin( $user->ID ) ) {
			$admin_flag = 1;
			$whoami = 'administrator';
		}
	}

	// Are we supposed to send an alert?
	if (! empty($nfw_options['a_0']) ) {
		if ( ( $nfw_options['a_0'] == 1 && isset( $admin_flag ) ) || $nfw_options['a_0'] == 2 ) {
			nfw_send_loginemail( $user_login, $whoami );
			// Write event to log?
			if (! empty($nfw_options['a_41']) ) {
				nfw_log2('Logged in user', "{$user_login} ({$whoami})", 6, 0);
			}
		}
	}

	//Whitelist:
	if (! empty( $nfw_options['wl_admin']) ) {
		if ( ( $nfw_options['wl_admin'] == 1 && isset( $admin_flag ) ) || $nfw_options['wl_admin'] == 2 ) {
			// Set the goodguy flag
			NinjaFirewall_session::write( ['nfw_goodguy' => true ] );
			return;
		}
	}
	NinjaFirewall_session::delete('nfw_goodguy');
}

// Hook priority can be defined in the wp-config.php or .htninja
if ( defined('NFW_LOGINHOOK') ) {
	$NFW_LOGINHOOK = (int) NFW_LOGINHOOK;
} else {
	$NFW_LOGINHOOK = -999999999;
}
add_action( 'wp_login', 'nfw_login_hook', $NFW_LOGINHOOK, 2 );

/* ------------------------------------------------------------------ */
function nfw_logout_hook() {

	NinjaFirewall_session::start();

	// Whoever it was, we clear the goodguy flag
	NinjaFirewall_session::delete('nfw_goodguy');
	// And the Live Log flag as well
	NinjaFirewall_session::delete('nfw_livelog');
	NinjaFirewall_session::delete('allcaps');
}

add_action( 'wp_logout', 'nfw_logout_hook' );

/* ------------------------------------------------------------------ */
// FullWAF upgrade AJAX function.

add_action( 'wp_ajax_nfw_fullwafsetup', 'nfw_fullwafsetup' );

function nfw_fullwafsetup() {

	nf_not_allowed( 'block', __LINE__ );

	if (! check_ajax_referer( 'events_save', 'nonce', false ) ) {
		esc_html_e('Error: Security nonces do not match. Reload the page and try again.', 'ninjafirewall');
		wp_die();
	}

	$nfw_options = nfw_get_option( 'nfw_options' );
	if ( empty( $nfw_options['enabled'] ) ) {
		esc_html_e('Error: NinjaFirewall is disabled', 'ninjafirewall');
		wp_die();
	}

	if ( empty( $_POST['httpserver'] ) ) {
		printf( esc_html__('Error: missing parameter (%s).', 'ninjafirewall'), 'httpserver' );
		wp_die();
	}
	if ( preg_match('/^[^1-8]$/', $_POST['httpserver'] ) ) {
		printf( esc_html__('Error: wrong parameter value (%s).', 'ninjafirewall'), 'httpserver' );
		wp_die();
	}
	if ( empty( $_POST['diy'] ) || ! preg_match( '/^(nfw|usr)$/', $_POST['diy'] ) ) {
		printf( esc_html__('Error: wrong parameter value (%s).', 'ninjafirewall'), 'diy' );
		wp_die();
	}

	// Retrieve the list of excluded folders, if any, and save it
	nfw_save_waf_exclusionlist( $_POST['exclude_waf_list'] );

	// Disable the sandbox?
	if ( empty( $_POST['sandbox'] ) ) {
		define('NFW_BYPASS_SANDBOX', true);
	}

	$time = time() + 300;

	// 1: Apache mod_php
	// 2: Apache + CGI/FastCGI or PHP-FPM
	// 3: Apache + suPHP
	// 4: Nginx + CGI/FastCGI or PHP-FPM
	// 5: Litespeed
	// 6: Openlitespeed
	// 7: Other webserver + CGI/FastCGI or PHP-FPM
	// 8: Apache + LSAPI
	$httpserver = (int) $_POST['httpserver'];

	// [6] Openlitespeed: nothing to do.
	if ( $httpserver == 6 ) {
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo '200';
		wp_die();
	}

	require_once __DIR__ .'/lib/install.php';

	// .htaccess mods only
	if ( $httpserver == 1 || $httpserver == 5 || $httpserver == 8 ) {
		// User wants to make the modification
		if ( $_POST['diy'] == 'usr' ) {
			// Nothing to do
			set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
			echo '200';
			wp_die();
		}
		// Make changes
		$ret = nfw_fullwaf_htaccess( $httpserver );
		if ( $ret !== true ) {
			echo $ret;
		} else {
			set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
			echo '200';
		}
		wp_die();
	}

	if ( $_POST['diy'] == 'usr' ) {
		// Nothing to do, but add 5-minute notice to the overview page
		// because an INI file is being used
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo '200';
		wp_die();
	}

	// [1] .user.ini
	// [2] php.ini
	if ( empty ( $_POST['initype'] ) || ! preg_match( '/^[12]$/', $_POST['initype'] ) ) {
		$initype = 1;
	} else {
		$initype = (int) $_POST['initype'];
	}

	if ( $httpserver == 3 ) { // Apache + suPHP
		// Set up the htaccess file
		$ret = nfw_fullwaf_htaccess( $httpserver );
		if ( $ret !== true ) {
			echo $ret;
			wp_die();
		}
	}
	// ini file
	$ret = nfw_fullwaf_ini( $httpserver, $initype );
	if ( $ret !== true ) {
		echo $ret;
		wp_die();
	} else {
		// Add 5-minute notice to the overview page
		// because an INI file is being used
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo 200;
	}
	wp_die();
}

/* ------------------------------------------------------------------ */
// Configure Full WAF mode or fallback to WP WAF mode. AJAX action.

add_action( 'wp_ajax_nfw_fullwafconfig', 'nfw_fullwafconfig' );

function nfw_fullwafconfig() {

	nf_not_allowed( 'block', __LINE__ );

	if (! check_ajax_referer( 'events_save', 'nonce', false ) ) {
		esc_html_e('Error: Security nonces do not match. Reload the page and try again.', 'ninjafirewall');
		wp_die();
	}

	if ( empty( $_POST['what'] ) || ! preg_match( '/^[12]$/', $_POST['what'] ) ) {
		printf( esc_html__('Error: missing parameter (%s).', 'ninjafirewall'), 'what' );
		wp_die();
	}

	// Downgrade to WP WAF
	if ( $_POST['what'] == 2 ) {

		require __DIR__ .'/lib/install.php';
		nfw_get_constants();
		nfw_remove_directives();

	// Full WAF directories exclusion
	} else {
		// Retrieve the list of excluded folders, if any, and save it
		nfw_save_waf_exclusionlist( $_POST['list'] );
	}

	wp_die(200);
}

/* ------------------------------------------------------------------ */
// Save new exclusion list.

function nfw_save_waf_exclusionlist( $input ) {

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Retrieve the list of excluded folders, if any, and save it
	$tmp_exclude_waf_list = json_decode( stripslashes( $input ) );
	if ( $tmp_exclude_waf_list === false || $tmp_exclude_waf_list === null ) {
		printf( esc_html__('Error: missing parameter (%s).', 'ninjafirewall'), 'list' );
		wp_die();
	}
	$exclude_waf_list = [];
	if (! empty( $tmp_exclude_waf_list ) ) {
		foreach( $tmp_exclude_waf_list as $folder ) {
			if ( is_dir( ABSPATH . $folder ) ) {
				$exclude_waf_list[] = $folder;
			}
		}
	}
	// Update/clear the list
	if (! empty( $exclude_waf_list ) ) {
		$nfw_options['exclude_waf_list'] = json_encode( $exclude_waf_list );
	} else {
		unset( $nfw_options['exclude_waf_list'] );
	}
	nfw_update_option( 'nfw_options', $nfw_options);
	// (Re)create the loader
	require_once __DIR__ .'/lib/install_default.php';
	nfw_create_loader();

}

/* ------------------------------------------------------------------ */
// Welcome screen.

add_action( 'wp_ajax_nfw_welcomescreen', 'nfw_welcomescreen' );

function nfw_welcomescreen() {

	nf_not_allowed( 'block', __LINE__ );

	if (! check_ajax_referer( 'welcome_save', 'nonce', false ) ) {
		esc_html_e('Error: Security nonces do not match. Reload the page and try again.', 'ninjafirewall');
		wp_die();
	}
	$nfw_options = nfw_get_option( 'nfw_options' );
	unset( $nfw_options['welcome'] );
	nfw_update_option( 'nfw_options', $nfw_options);
}

/* ------------------------------------------------------------------ */

function is_nfw_enabled() {

	$nfw_options = nfw_get_option( 'nfw_options' );

	if (! defined('NFW_STATUS') ) {
		define('NF_DISABLED', 10);
		return;
	}

	if ( isset($nfw_options['enabled']) && $nfw_options['enabled'] == '0' ) {
		define('NF_DISABLED', 9);
		return;
	}

	if (NFW_STATUS == 21 || NFW_STATUS == 22 || NFW_STATUS == 23) {
		define('NF_DISABLED', 10);
		return;
	}

	// OK
	if (NFW_STATUS == 20) {
		define('NF_DISABLED', 0);
		return;
	}

	define('NF_DISABLED', NFW_STATUS);
	return;

}

/* ------------------------------------------------------------------ */

function ninjafirewall_admin_menu() {

	if ( nf_not_allowed( 0, __LINE__ ) ) { return; }

	if (! empty($_REQUEST['nfw_act']) && $_REQUEST['nfw_act'] == 99) {
		if ( empty($_GET['nfwnonce']) || ! wp_verify_nonce($_GET['nfwnonce'], 'show_phpinfo') ) {
			wp_nonce_ays('show_phpinfo');
		}
		phpinfo(33);
		exit;
	}

	add_menu_page( 'NinjaFirewall', 'NinjaFirewall', 'manage_options',
		'NinjaFirewall', 'nf_sub_main',	plugins_url( '/images/nf_icon.png', __FILE__ )
	);

	global $menu_hook;

	require_once plugin_dir_path(__FILE__) . 'lib/help.php';

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Dashboard', 'ninjafirewall'), __('Dashboard', 'ninjafirewall'), 'manage_options',
		'NinjaFirewall', 'nf_sub_main' );
	add_action( 'load-' . $menu_hook, 'help_nfsubmain' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Firewall Options', 'ninjafirewall'), __('Firewall Options', 'ninjafirewall'), 'manage_options',
		'nfsubopt', 'nf_sub_options' );
	add_action( 'load-' . $menu_hook, 'help_nfsubopt' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Firewall Policies', 'ninjafirewall'), __('Firewall Policies', 'ninjafirewall'), 'manage_options',
		'nfsubpolicies', 'nf_sub_policies' );
	add_action( 'load-' . $menu_hook, 'help_nfsubpolicies' );

	$menu_hook = add_submenu_page( 'NinjaFirewall',  __('NinjaFirewall: Monitoring', 'ninjafirewall'), __( 'Monitoring', 'ninjafirewall'), 'manage_options',
		'nfsubfileguard', 'nf_sub_monitoring' );
	add_action( 'load-' . $menu_hook, 'help_nfsubfileguard' );

	$nscan_options = get_option( 'nscan_options' );
	if ( defined('NSCAN_NAME') && defined('NSCAN_SLUG') && ! empty( $nscan_options['scan_nfwpintegration'] ) ) {
		$menu_hook = add_submenu_page( 'NinjaFirewall', NSCAN_NAME, NSCAN_NAME, 'manage_options', NSCAN_NAME, 'nscan_main_menu' );
		require_once dirname( __DIR__ ).'/'. NSCAN_SLUG .'/lib/help.php';
		add_action( 'load-' . $menu_hook, 'nscan_help' );
	} else {
		$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Anti-Malware', 'ninjafirewall'), __('Anti-Malware', 'ninjafirewall'), 'manage_options',
		'nfsubmalwarescan', 'nf_sub_malwarescan' );
	}

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Network', 'ninjafirewall'), __('Network', 'ninjafirewall'), 'manage_network',
		'nfsubnetwork', 'nf_sub_network' );
	add_action( 'load-' . $menu_hook, 'help_nfsubnetwork' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Event Notifications', 'ninjafirewall'), __('Event Notifications', 'ninjafirewall'), 'manage_options',
		'nfsubevent', 'nf_sub_event' );
	add_action( 'load-' . $menu_hook, 'help_nfsubevent' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Log-in Protection', 'ninjafirewall'), __('Login Protection', 'ninjafirewall'), 'manage_options',
		'nfsubloginprot', 'nf_sub_loginprot' );
	add_action( 'load-' . $menu_hook, 'help_nfsublogin' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Logs', 'ninjafirewall'), __('Logs', 'ninjafirewall'), 'manage_options',
		'nfsublog', 'nf_sub_log' );
	add_action( 'load-' . $menu_hook, 'help_nfsublog' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Security Rules', 'ninjafirewall'), __('Security Rules', 'ninjafirewall'), 'manage_options',
		'nfsubupdates', 'nf_sub_updates' );
	add_action( 'load-' . $menu_hook, 'help_nfsubupdates' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', 'NinjaFirewall: WP+ Edition', '<b style="color:#fcdc25">WP+ Edition</b>', 'manage_options',
		'nfsubwplus', 'nf_sub_wplus' );

}
// Must load before NinjaScanner (11):
if (! is_multisite() )  {
	add_action( 'admin_menu', 'ninjafirewall_admin_menu', 10 );
} else {
	add_action( 'network_admin_menu', 'ninjafirewall_admin_menu', 10 );
}

/* ------------------------------------------------------------------ */

function nf_admin_bar_status() {

	if (! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nfw_options = nfw_get_option( 'nfw_options' );
	if ( @$nfw_options['nt_show_status'] != 1 && ! current_user_can('manage_network') ) {
		return;
	}

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}
	if (NF_DISABLED) { return; }

	global $wp_admin_bar;
	$wp_admin_bar->add_menu( [
		'id'    => 'nfw_ntw1',
		'title' => '<img src="' . plugins_url() . '/ninjafirewall/images/ninjafirewall_20.png" ' .
				'style="vertical-align:middle;margin-right:5px" />'
	] );

	if ( current_user_can( 'manage_network' ) ) {
		$wp_admin_bar->add_menu( [
			'parent' => 'nfw_ntw1',
			'id'     => 'nfw_ntw2',
			'title'  => __( 'NinjaFirewall Settings', 'ninjafirewall'),
			'href'   => network_admin_url() . 'admin.php?page=NinjaFirewall'
		] );
	} else {
		if ( defined('NFW_STATUS') ) {
			$wp_admin_bar->add_menu( [
				'parent' => 'nfw_ntw1',
				'id'     => 'nfw_ntw2',
				'title'  => __( 'NinjaFirewall is enabled', 'ninjafirewall')
			] );
		}
	}
}

if ( is_multisite() )  {
	add_action('admin_bar_menu', 'nf_admin_bar_status', 95);
}

/* ------------------------------------------------------------------ */

function nf_sub_main() {

	// Main menu (Overview)
	require plugin_dir_path(__FILE__) . 'lib/dashboard.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_options() { // i18n

	require plugin_dir_path(__FILE__) . 'lib/firewall_options.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_policies() {

	// Firewall Policies menu
	require plugin_dir_path(__FILE__) . 'lib/firewall_policies.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_monitoring() {

	require plugin_dir_path(__FILE__) . 'lib/monitoring.php';

}
add_action('nfscanevent', 'nfscando');

function nfscando() {

	define('NFSCANDO', 1);
	nf_sub_monitoring();
}

/* ------------------------------------------------------------------ */

function nf_sub_network() {

	// Network menu (multi-site only)
	require plugin_dir_path(__FILE__) . 'lib/network.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_malwarescan() {

	require plugin_dir_path(__FILE__) . 'lib/anti_malware.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_event() {

	require plugin_dir_path(__FILE__) . 'lib/event_notifications.php';

}

add_action('shutdown', 'nf_check_dbdata', 1);

add_action('nfdailyreport', 'nfdailyreportdo');

function nfdailyreportdo() {
	define('NFREPORTDO', 1);
	nf_sub_event();
}

/* ------------------------------------------------------------------ */

function nf_sub_log() {

	require plugin_dir_path(__FILE__) . 'lib/logs.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_loginprot() {

	require plugin_dir_path(__FILE__) . 'lib/login_protection.php';

}

/* ------------------------------------------------------------------ */

function nfw_log2($loginfo, $logdata, $loglevel, $ruleid) {

	// Write incident to the firewall log
	require plugin_dir_path(__FILE__) . 'lib/nfw_log.php'; // Can be called multiple times
}

function nfw_anonymize_ip2( $ip ) {

	$nfw_options = nfw_get_option( 'nfw_options' );

	if (! empty( $nfw_options['anon_ip'] ) &&
		filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {

		return substr( $ip, 0, -3 ) .'xxx';
	}

	return $ip;
}

/* ------------------------------------------------------------------ */

function nf_sub_updates() {

	require plugin_dir_path(__FILE__) . 'lib/security_rules.php';

}

add_action('nfsecupdates', 'nfupdatesdo');

function nfupdatesdo() {
	define('NFUPDATESDO', 1);
	nf_sub_updates();
}

/* ------------------------------------------------------------------ */

function nf_sub_wplus() {

	require plugin_dir_path(__FILE__) . 'lib/wpplus.php';
}

/* ------------------------------------------------------------------ */

function ninjafirewall_settings_link( $links ) {

	// Check if access is restricted to one or more specific admins
	// See: https://blog.nintechnet.com/restricting-access-to-ninjafirewall-wp-edition-settings/
	if ( nf_not_allowed( 0, __LINE__ ) ) {
		unset( $links );
		$links[] = __('Access Restricted', 'ninjafirewall');
		return $links;
	}

	if ( is_multisite() ) {	$net = 'network/'; } else { $net = '';	}

	$links[] = '<a href="'. get_admin_url(null, $net .'admin.php?page=NinjaFirewall') .'">'. __('Settings', 'ninjafirewall') .'</a>';
	$links[] = '<a href="https://nintechnet.com/ninjafirewall/wp-edition/?pricing" target="_blank">'. __('Upgrade to Premium', 'ninjafirewall'). '</a>';
	$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/ninjafirewall?rate=5#postform" target="_blank">'. __('Rate it!', 'ninjafirewall'). '</a>';
	unset( $links['edit'] );
   return $links;

}

if ( is_multisite() ) {
	add_filter( 'network_admin_plugin_action_links_' . plugin_basename(__FILE__), 'ninjafirewall_settings_link' );
} else {
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ninjafirewall_settings_link' );
}

/* ------------------------------------------------------------------ */

function nfw_dashboard_widgets() {

	require plugin_dir_path(__FILE__) . 'lib/widget.php';

}

if ( is_multisite() ) {
	add_action( 'wp_network_dashboard_setup', 'nfw_dashboard_widgets' );
} else {
	add_action( 'wp_dashboard_setup', 'nfw_dashboard_widgets' );
}

/* ------------------------------------------------------------------ */

function nf_not_allowed($block, $line = 0) {

	if ( is_multisite() ) {
		if ( current_user_can('manage_network') && is_main_site() ) {
			return false;
		}
	} else {
		if ( current_user_can('manage_options') &&
		     current_user_can('unfiltered_html') ) {
			// Check if that admin is allowed to use NinjaFirewall
			// (see NFW_ALLOWED_ADMIN at http://nin.link/nfwaa ):
			if ( defined('NFW_ALLOWED_ADMIN') ) {
				$current_user = wp_get_current_user();
				$admins = explode(',', NFW_ALLOWED_ADMIN );
				foreach ( $admins as $admin ) {
					if ( trim( $admin ) == $current_user->user_login ) {
						return false;
					}
				}
			} else {
				return false;
			}
		}
	}

	if ( $block ) {
		if ( defined('WP_CLI') && WP_CLI ) {
			// Format text for WP-CLI:
			WP_CLI::error(
				sprintf(
					__('You are not allowed to perform this task (%s).', 'ninjafirewall'),
					"NinjaFirewall: $line"
				)
			);
		} else {
			die( '<br /><br /><br /><div class="error notice is-dismissible"><p>' .
				sprintf(
					esc_html__('You are not allowed to perform this task (%s).', 'ninjafirewall'),
					"NinjaFirewall: $line"
				) .'</p></div>'
			);
		}
	}
	return true;
}

/* ------------------------------------------------------------------ */
// EOF //
