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
 +---------------------------------------------------------------------+ sa / 2
*/

if (! defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

if (! headers_sent() ) {
	if (! function_exists('session_status') ) {
		if (! session_id() ) {
			session_start();
		}
	} else {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}
}

nfw_uninstall();

// ---------------------------------------------------------------------

function nfw_uninstall() {

	if (! defined( 'NFW_ENGINE_VERSION' ) ) { define( 'NFW_ENGINE_VERSION', true ); }

	if (! defined('NINJAFIREWALL_MU_PLUGIN') ) {
		define('NINJAFIREWALL_MU_PLUGIN', '0-ninjafirewall.php');
	}

	// Unset the goodguy flag :
	if ( isset( $_SESSION['nfw_goodguy'] ) ) {
		unset( $_SESSION['nfw_goodguy'] );
	}

	if (! function_exists( 'get_home_path' ) ) {
		include_once ABSPATH .'wp-admin/includes/file.php';
 	}
 	$NFW_ABSPATH = get_home_path();

	define( 'HTACCESS_BEGIN', '# BEGIN NinjaFirewall' );
	define( 'HTACCESS_END', '# END NinjaFirewall' );
	define( 'PHPINI_BEGIN', '; BEGIN NinjaFirewall' );
	define( 'PHPINI_END', '; END NinjaFirewall' );
	define( 'WP_CONFIG_BEGIN', '// BEGIN NinjaFirewall' );
	define( 'WP_CONFIG_END', '// END NinjaFirewall' );

	// Retrieve installation info :
	if ( is_multisite() ) {
		$nfw_install = get_site_option('nfw_install');
	} else {
		$nfw_install = get_option('nfw_install');
	}


	// Clean-up wp-config.php:
	if (! empty( $nfw_install['wp_config'] ) && file_exists( $nfw_install['wp_config'] ) && is_writable( $nfw_install['wp_config'] ) ) {
		$wp_config_content = @file_get_contents( $nfw_install['wp_config'] );
		$wp_config_content = preg_replace( '`\s?'. WP_CONFIG_BEGIN .'.+?'. WP_CONFIG_END .'[^\r\n]*\s?`s' , "\n", $wp_config_content);
		@file_put_contents( $nfw_install['wp_config'], $wp_config_content, LOCK_EX );
	}


	// Clean-up .htaccess :
	if (! empty($nfw_install['htaccess']) && file_exists($nfw_install['htaccess']) ) {
		$htaccess_file = $nfw_install['htaccess'];
	} elseif ( file_exists( $NFW_ABSPATH . '.htaccess' ) ) {
		$htaccess_file = $NFW_ABSPATH . '.htaccess';
	} else {
		$htaccess_file = '';
	}

	// Ensure it is writable :
	if (! empty($htaccess_file) && is_writable( $htaccess_file ) ) {
		$data = file_get_contents( $htaccess_file );
		// Find / delete instructions :
		$data = preg_replace( '`\s?'. HTACCESS_BEGIN .'.+?'. HTACCESS_END .'[^\r\n]*\s?`s' , "\n", $data);
		@file_put_contents( $htaccess_file,  $data, LOCK_EX );
	}

	// Clean up PHP INI file :
	$phpini = array();
	if (! empty($nfw_install['phpini']) && file_exists($nfw_install['phpini']) ) {
		if ( is_writable( $nfw_install['phpini'] ) ) {
			$phpini[] = $nfw_install['phpini'];
		}
	}
	if ( file_exists( $NFW_ABSPATH . 'php.ini' ) ) {
		if ( is_writable( $NFW_ABSPATH . 'php.ini' ) ) {
			$phpini[] = $NFW_ABSPATH . 'php.ini';
		}
	}
	if ( file_exists( $NFW_ABSPATH . 'php5.ini' ) ) {
		if ( is_writable( $NFW_ABSPATH . 'php5.ini' ) ) {
			$phpini[] = $NFW_ABSPATH . 'php5.ini';
		}
	}
	if ( file_exists( $NFW_ABSPATH . '.user.ini' ) ) {
		if ( is_writable( $NFW_ABSPATH . '.user.ini' ) ) {
			$phpini[] = $NFW_ABSPATH . '.user.ini';
		}
	}
	foreach( $phpini as $ini ) {
		$data = file_get_contents( $ini );
		$data = preg_replace( '`\s?'. PHPINI_BEGIN .'.+?'. PHPINI_END .'[^\r\n]*\s?`s' , "\n", $data);
		@file_put_contents( $ini, $data, LOCK_EX );
	}

	// Remove any scheduled cron job
	include_once 'lib/scheduled_tasks.php';
	nfw_delete_scheduled_tasks();

	// Delete DB rows :
	delete_option('nfw_options');
	delete_option('nfw_rules');
	delete_option('nfw_install');
	delete_option('nfw_tmp');
	delete_option('nfw_checked');
	if ( is_multisite() ) {
		// Delete those ones too :
		delete_site_option('nfw_options');
		delete_site_option('nfw_rules');
		delete_site_option('nfw_install');
		delete_site_option('nfw_tmp');
		delete_site_option('nfw_checked');
	}

	// Clear session flag:
	if ( isset( $_SESSION['nfw_goodguy'] ) ) {
		unset( $_SESSION['nfw_goodguy'] );
	}

	// Remove fallback loader
	if ( file_exists( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN ) ) {
		unlink( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN );
	}
}

// ---------------------------------------------------------------------
// EOF
