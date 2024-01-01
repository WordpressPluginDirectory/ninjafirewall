<?php
/*
 +---------------------------------------------------------------------+
 | NinjaFirewall (WP Edition)                                          |
 |                                                                     |
 | (c) NinTechNet - https://nintechnet.com/                            |
 +---------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or       |
 | modify it under the terms of the GNU General Public License as      |
 | published by the Free Software Foundation, either version 3 of      |
 | the License, or (at your option) any later version.                 |
 |                                                                     |
 | This program is distributed in the hope that it will be useful,     |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
 | GNU General Public License for more details.                        |
 +---------------------------------------------------------------------+ i18n++ / sa
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// ---------------------------------------------------------------------
// Load and save default config

function nfw_load_default_conf() {

	$nfw_rules = array();

	$logo = plugins_url() . '/ninjafirewall/images/ninjafirewall_75.png';
	$logo = preg_replace( '/^https?:/', '', $logo );

	$nfw_options = array(
		// ---------------------------------------------------------------
		// The next 6 keys must always be present because they are used
		// by the nfw_validate_option() function to check whether $nfw_options
		// is corrupted or not:
		'enabled'			=> 1,
		'blocked_msg'		=> base64_encode(NFW_DEFAULT_MSG),
		'logo'				=> $logo,
		'ret_code'			=> 403,
		'scan_protocol'	=> 3,
		'get_scan'			=> 1,
		'widgetnews'		=>	4,
		// ---------------------------------------------------------------
		'anon_ip'			=> 0,
		'debug'				=> 0,
		'uploads'			=> 1,
		'sanitise_fn'		=> 0,
		'get_sanitise'		=> 0,
		'post_scan'			=> 1,
		'post_sanitise'	=> 0,
		'cookies_scan'		=> 1,
		'cookies_sanitise'=> 0,
		'ua_scan'			=> 1,
		'ua_sanitise'		=> 1,
		'referer_scan'		=> 0,
		'referer_sanitise'=> 1,
		'referer_post'		=> 0,
		'no_host_ip'		=> 0,
		'allow_local_ip'	=> 1, // 1 == no !
		'php_superglobals'=> 1,
		'php_errors'		=> 1,
		'php_self'			=> 1,
		'php_path_t'		=> 1,
		'php_path_i'		=> 1,
		'wp_dir'				=> '/wp-admin/(?:css|images|includes|js)/|' .
									'/wp-includes/(?!ms-files\.php)(?:(?:css|images|js(?!/tinymce/wp-tinymce\.php)|theme-compat)/|[^/]+\.php)|' .
									'/'. basename(WP_CONTENT_DIR) .'/(?:uploads|blogs\.dir)/',
		'no_post_themes'	=> 0,
		'force_ssl'			=> 0,
		'disallow_edit'	=> 0,
		'disallow_mods'	=> 0,
		// 3.8.2:
		'disable_error_handler'	=> 0,

		'wl_admin'			=> 1,
		// v1.0.4
		'a_0' 				=> 1,
		'a_11' 				=> 1,
		'a_12' 				=> 1,
		'a_13' 				=> 0,
		'a_14' 				=> 0,
		'a_15' 				=> 1,
		'a_16' 				=> 0,
		'a_21' 				=> 1,
		'a_22' 				=> 1,
		'a_23' 				=> 0,
		'a_24' 				=> 0,
		'a_25' 				=> 0,
		'a_31' 				=> 1,
		// v1.3.3 :
		'a_41' 				=> 1,
		// v1.3.4 :
		'a_51' 				=> 1,
		'sched_scan'		=> 0,
		'report_scan'		=> 0,
		// 4.1
		'secupdates'		=>	1,
		// v1.7 (daily report cronjob) :
		'a_52' 				=> 1,
		// v3.8.3 :
		'a_61' 				=> 1,

		'alert_email'	 	=> get_option('admin_email'),
		// v1.1.0 :
		'alert_sa_only'	=> 1,
		'nt_show_status'	=> 1,
		'post_b64'			=> 1,
		// v3.6.7:
		'disallow_creation'	=> 0,
		// 4.5.9
		'disallow_deletion'	=> 0,
		// v3.7.2:
		'disallow_settings'	=> 1,
		// v4.0.6
		'disallow_privesc'	=> 1,
		// v4.2.6
		'disallow_privesc_mu'	=> 0,
		// v4.2
		'disallow_publish'	=> 0,

		// v1.1.2 :
		'no_xmlrpc'			=> 0,
		// v1.7 :
		'no_xmlrpc_multi'	=> 0,
		// v3.3.2
		'no_xmlrpc_pingback'=> 0,
		// 4.3.1
		'no_appswd'			=> 0,

		// v1.1.3 :
		'enum_archives'	=> 0,
		'enum_sitemap'		=> 0,
		'enum_login'		=> 0,
		// v4.2
		'enum_feed'			=> 0,
		'no_restapi'		=> 0,
		'restapi_loggedin'=> 0,
		// v1.1.6 :
		'request_sanitise'=> 0,
		// v1.2.1 :
		'fg_enable'			=>	0,
		'fg_mtime'			=>	10,
		'fg_exclude'		=>	'',
		// Log:
		'auto_del_log'		=>	0,
		// Updates :
		'enable_updates'	=>	1,
		'sched_updates'	=>	1,
		'notify_updates'	=>	1,
		// Centralized Logging:
		'clogs_enable'		=>	0,
		'clogs_pubkey'		=>	'',

		'rate_notice'		=>	time() + 86400 * 15,
		'welcome'			=>	1
	);
	// v1.3.1 :
	// Some compatibility checks:
	// 1. header_register_callback(): requires PHP >=5.4
	// 2. headers_list() and header_remove(): some hosts may disable them.
	if ( function_exists('header_register_callback') && function_exists('headers_list') && function_exists('header_remove') ) {
		// X-XSS-Protection:
		$nfw_options['response_headers'] = '0003000000';
	}
	$nfw_options['referrer_policy_enabled'] = 0;

	define('NFUPDATESDO', 2);
	@nf_sub_updates();

	if (! $nfw_rules = @unserialize(NFW_RULES) ) {
		$err_msg = esc_html__('Error: The installer cannot download the security rules from wordpress.org website.', 'ninjafirewall');
		$err_msg.= '<ol><li>'. esc_html__('The server may be temporarily down or you may have network connectivity problems? Please try again in a few minutes.', 'ninjafirewall') . '</li>';
		$err_msg.= '<li>'. esc_html__('NinjaFirewall downloads its rules over an HTTPS secure connection. Maybe your server does not support SSL? You can force NinjaFirewall to use a non-secure HTTP connection by adding the following directive to your <strong>wp-config.php</strong> file:', 'ninjafirewall') . ' <p><code>define("NFW_DONT_USE_SSL", 1);</code></p></li></ol>';
		exit( '<font style="font-size:14px;">'. $err_msg .'</font>' );
	}

	// dropins code:
	if ( isset( $nfw_rules['dropins'] ) ) {
		if ( $nfw_rules['dropins'] == 'delete' ) {
			if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php' ) ) {
				@unlink( NFW_LOG_DIR .'/nfwlog/dropins.php' );
			}
		} else {
			$dropins = base64_decode( $nfw_rules['dropins'], true );
			if ( $dropins !== false ) {
				@file_put_contents( NFW_LOG_DIR .'/nfwlog/dropins.php', $dropins, LOCK_EX );
			}
		}
		unset( $nfw_rules['dropins'] );
	}

	$nfw_options['engine_version'] = NFW_ENGINE_VERSION;
	$nfw_options['rules_version']  = NFW_NEWRULES_VERSION; // downloaded rules

	// If the user is using WP-CLI, we populate DOCUMENT_ROOT with ABSPATH:
	if ( defined('WP_CLI') && WP_CLI ) {
		$_SERVER['DOCUMENT_ROOT'] = ABSPATH;
	}
	// Create but disable by default "Block the DOCUMENT_ROOT server variable in HTTP request" rule
	if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', $_SERVER['DOCUMENT_ROOT'] );
	} elseif ( strlen( getenv( 'DOCUMENT_ROOT' ) ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', getenv( 'DOCUMENT_ROOT' ) );
	}
	$nfw_rules[NFW_DOC_ROOT]['ena'] = 0;

	// ------------------------------------------------------------------
	// Update DB options and rules **BEFORE** (re)enabling scheduled tasks
	// (the garbage collect should be ran/scheduled last):
	nfw_update_option( 'nfw_options', $nfw_options);
	nfw_update_option( 'nfw_rules', $nfw_rules);
	// Create conjobs
	nfw_create_scheduled_tasks();
	// ------------------------------------------------------------------

	nfw_create_log_dir();

}
// ---------------------------------------------------------------------
// Create NinjaFirewall's log & cache folders.

function nfw_create_log_dir() {

	$deny_rules = "<Files \"*\">
	<IfModule mod_version.c>
		<IfVersion < 2.4>
			Order Deny,Allow
			Deny from All
		</IfVersion>
		<IfVersion >= 2.4>
			Require all denied
		</IfVersion>
	</IfModule>
	<IfModule !mod_version.c>
		<IfModule !mod_authz_core.c>
			Order Deny,Allow
			Deny from All
		</IfModule>
		<IfModule mod_authz_core.c>
			Require all denied
		</IfModule>
	</IfModule>
</Files>";

	if (! is_writable(NFW_LOG_DIR) ) {
		$err_msg = sprintf( esc_html__('NinjaFirewall cannot create its <code>nfwlog/</code>log and cache folder; please make sure that the <code>%s</code> directory is writable', 'ninjafirewall'), htmlspecialchars( NFW_LOG_DIR ) );
		exit( '<font style="font-size:14px;">'. $err_msg .'</font>' );
	}

	if (! file_exists( NFW_LOG_DIR . '/nfwlog' ) ) {
		mkdir( NFW_LOG_DIR . '/nfwlog', 0755 );
	}
	if (! file_exists( NFW_LOG_DIR . '/nfwlog/cache' ) ) {
		mkdir( NFW_LOG_DIR . '/nfwlog/cache', 0755 );
	}

	touch( NFW_LOG_DIR . '/nfwlog/index.html' );
	touch( NFW_LOG_DIR . '/nfwlog/cache/index.html' );
	@file_put_contents(NFW_LOG_DIR . '/nfwlog/.htaccess', $deny_rules, LOCK_EX);
	@file_put_contents(NFW_LOG_DIR . '/nfwlog/cache/.htaccess', $deny_rules, LOCK_EX);
	@file_put_contents(
		NFW_LOG_DIR . '/nfwlog/readme.txt',
		"This is NinjaFirewall's logs, loader and cache directory. DO NOT alter or remove it as long as NinjaFirewall is running!\n\nIf you just uninstalled NinjaFirewall, WAIT 5 MINUTES before deleting this folder, otherwise your site will likely crash.",
		LOCK_EX
	);
	nfw_create_loader();
}

// ---------------------------------------------------------------------
// Create NF's loader.

function nfw_create_loader() {

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Firewall loader
	$loader = "<?php
// ===============================================================//
// NinjaFirewall's loader.                                        //
// DO NOT alter or remove it as long as NinjaFirewall is running. //
// If this file is corrupted or wrong, you can re-generate it     //
// by deactivating and reactivating NinjaFirewall from your       //
// WordPress dashboard.                                           //
// ===============================================================//";

	if (! empty( $nfw_options['exclude_waf_list'] ) ) {
		$string = '';
		$exclude_waf_list = json_decode( $nfw_options['exclude_waf_list'] );
		foreach( $exclude_waf_list as $folder ) {
			if ( is_dir( ABSPATH . $folder ) ) {
				$string .= "'$folder',";
			}
		}
		$string = rtrim( $string, ',' );
		if (! empty( $string ) ) {
			$loader .= "
\$nfw_exclude_waf_list = array($string);
foreach( \$nfw_exclude_waf_list as \$nfw_exclude_waf_folder ) {
	if (strpos(\$_SERVER['SCRIPT_FILENAME'], \"". ABSPATH ."\$nfw_exclude_waf_folder/\") === 0) {
		return;
	}
}";
		}
	}

	$loader .= "
if ( file_exists('". __DIR__ .'/firewall.php' . "') ) {
	@include_once '". __DIR__ .'/firewall.php' . "';
}
// EOF
";
	file_put_contents( NFW_LOG_DIR .'/nfwlog/ninjafirewall.php', $loader, LOCK_EX );
	return;

}

// ---------------------------------------------------------------------
// EOF //
