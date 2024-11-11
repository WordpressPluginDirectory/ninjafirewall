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
 +---------------------------------------------------------------------+ i18n+ / sa
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// ---------------------------------------------------------------------
// Installation constants.

function nfw_get_constants() {

	if ( defined('NFW_HTACCESS_BEGIN') ) { return; }

	if (! function_exists( 'get_home_path' ) ) {
		include_once ABSPATH .'wp-admin/includes/file.php';
 	}
 	$NFW_ABSPATH = get_home_path();

 	if ( PHP_MAJOR_VERSION > 7 ) {
		$php_major_version = '';
	} else {
		$php_major_version = PHP_MAJOR_VERSION;
	}

	define( 'NFW_HTACCESS_BEGIN', '# BEGIN NinjaFirewall' );
	define( 'NFW_HTACCESS_DATA',  '<IfModule mod_php'. $php_major_version .'.c>' ."\n" .
								     '   php_value auto_prepend_file "'. NFW_LOG_DIR .'/nfwlog/ninjafirewall.php"' ."\n" .
								     '</IfModule>');
	define( 'NFW_LITESPEED_DATA', '<IfModule Litespeed>' ."\n" .
								     '   php_value auto_prepend_file "'. NFW_LOG_DIR .'/nfwlog/ninjafirewall.php"' ."\n" .
								     '</IfModule>');
	define( 'NFW_APACHELSAPI_DATA', '<IfModule lsapi_module>' ."\n" .
								     '   php_value auto_prepend_file "'. NFW_LOG_DIR .'/nfwlog/ninjafirewall.php"' ."\n" .
								     '</IfModule>');
	define( 'NFW_OPENLITESPEED_DATA', 'php_value auto_prepend_file "'. NFW_LOG_DIR .'/nfwlog/ninjafirewall.php"' );
	define( 'NFW_SUPHP_DATA',     '<IfModule mod_suphp.c>' ."\n" .
								     '   suPHP_ConfigPath '. rtrim( $NFW_ABSPATH, '/') ."\n" .
								     '</IfModule>');
	define( 'NFW_HTACCESS_END',   '# END NinjaFirewall' );
	define( 'NFW_PHPINI_BEGIN',   '; BEGIN NinjaFirewall' );
	define( 'NFW_PHPINI_DATA',    'auto_prepend_file = "'. NFW_LOG_DIR .'/nfwlog/ninjafirewall.php"' );
	define( 'NFW_PHPINI_END',     '; END NinjaFirewall' );

	// WordPress WAF in NinjaFirewall < 4.0
	define( 'NFW_WP_CONFIG_BEGIN', '// BEGIN NinjaFirewall' );
	define( 'NFW_WP_CONFIG_END', '// END NinjaFirewall' );
}

// ---------------------------------------------------------------------
// Add firewall's directive to the .htaccess.

function nfw_fullwaf_htaccess( $httpserver ) {

	nfw_get_constants();

	$htaccess_content = '';

	if (! function_exists( 'get_home_path' ) ) {
		include_once ABSPATH .'wp-admin/includes/file.php';
 	}
 	$NFW_ABSPATH = get_home_path();

	// Back-up existing .htaccess
	if ( file_exists( $NFW_ABSPATH .'.htaccess' ) ) {
		if (! is_writable( $NFW_ABSPATH .'.htaccess' ) ) {
			return sprintf(
				__('Error: Your .htaccess file is not writable, please change its permissions: %s', 'ninjafirewall' ),
				htmlspecialchars( $NFW_ABSPATH .'.htaccess' )
			);
		}
		$backup_file = time();
		@copy( $NFW_ABSPATH .'.htaccess', $NFW_ABSPATH .".htaccess.ninja{$backup_file}" );

		// Remove potential NF directives
		nfw_remove_directives();

		$htaccess_content = file_get_contents( $NFW_ABSPATH .'.htaccess' );
	}

	// Write new content depending on HTTP server type

	if ( $httpserver == 1 ) { // Apache mod_php
		$data = NFW_HTACCESS_BEGIN ."\n". NFW_HTACCESS_DATA ."\n". NFW_HTACCESS_END ."\n\n". $htaccess_content;

	} elseif ( $httpserver == 5 ) { // LiteSpeed
		$data = NFW_HTACCESS_BEGIN ."\n". NFW_LITESPEED_DATA ."\n". NFW_HTACCESS_END ."\n\n". $htaccess_content;

	} elseif ( $httpserver == 3 ) { // Apache + suPHP
		$data = NFW_HTACCESS_BEGIN ."\n". NFW_SUPHP_DATA ."\n". NFW_HTACCESS_END ."\n\n". $htaccess_content;

	} elseif ( $httpserver == 8 ) { // Apache + LSAPI
		$data = NFW_HTACCESS_BEGIN ."\n". NFW_APACHELSAPI_DATA ."\n". NFW_HTACCESS_END ."\n\n". $htaccess_content;

	} else {
		return sprintf( __('Error: wrong parameter value (%s).', 'ninjafirewall'), 'HTTP server' );
	}

	// Write content
	$res = @file_put_contents(	$NFW_ABSPATH .'.htaccess', $data, LOCK_EX );
	if ( $res === false ) {
		return sprintf(
			__('Error: The following file is not writable, please change its permissions: %s', 'ninjafirewall' ),
			htmlspecialchars( $NFW_ABSPATH .'.htaccess' )
		);
	}

	// Sandbox
	$res = nfw_waf_sandbox();
	if ( $res !== true ) {
		// Undo
		@file_put_contents(	$NFW_ABSPATH .'.htaccess', $htaccess_content, LOCK_EX );
		return $res;
	}

	return true;
}

// ---------------------------------------------------------------------
// Sandbox.

function nfw_waf_sandbox() {

	// Bypass (undocumented):
	if ( defined('NFW_BYPASS_SANDBOX') && NFW_BYPASS_SANDBOX == true ) {
		return true;
	}

	NinjaFirewall_session::close();

	$sandbox_error = __('NinjaFirewall detected that the requested changes seemed to crash your blog. %s', 'ninjafirewall') ."\n".
		__('Changes have been undone. You may need to modify your selection and try again.', 'ninjafirewall' );
	$headers['Cache-Control'] = 'no-cache';

	$url = home_url( '/' ) .'?'. time();
	global $wp_version;
	$opts = array(
		'timeout' => 20,
		'httpversion' => '1.1' ,
		'user-agent' => 'Mozilla/5.0 (compatible; NinjaFirewall/'.
								NFW_ENGINE_VERSION .'; WordPress/'. $wp_version . ')',
		// We don't want a fatal error if we're running on localhost e.g., dev site etc
		'sslverify' => apply_filters( 'https_local_ssl_verify', false )
	);
	$res = wp_remote_get( $url, $opts );

	$blogname = get_option( 'blogname' );
	if (! is_wp_error( $res ) ) {
		// Look for HTTP error
		if ( $res['response']['code'] >= 400 ) {
			$error_msg = sprintf(
				$sandbox_error,
				sprintf(
					__('The website front-end returned: HTTP %s %s.', 'ninjafirewall'),
					(int) $res['response']['code'],
					esc_js( $res['response']['message'] )
				)
			);
			return $error_msg;
		}
		if ( stripos( $res['body'], $blogname ) === false ) {
			$error_msg = sprintf(
				$sandbox_error,
				__('The website front-end did not return the expected page.', 'ninjafirewall')
			);
			return $error_msg;
		}

	} else {
		$error_msg = sprintf(
			$sandbox_error,
			sprintf(
				__('The website front-end returned a fatal error: %s.', 'ninjafirewall'),
				esc_js( $res->get_error_message() )
			)
		);
		return $error_msg;
	}

	return true;
}

// ---------------------------------------------------------------------

function nfw_fullwaf_ini( $httpserver, $initype ) {

	nfw_get_constants();

	if (! function_exists( 'get_home_path' ) ) {
		include_once ABSPATH .'wp-admin/includes/file.php';
 	}
 	$NFW_ABSPATH = get_home_path();

	$ini_content = '';

	// [1] .user.ini
	// [2] php.ini
	if ( $initype == 2 ) {
		$initype = 'php.ini';
	} else {
		$initype = '.user.ini';
	}

	// Back-up existing INI file
	if ( file_exists( $NFW_ABSPATH . $initype ) ) {
		if (! is_writable( $NFW_ABSPATH . $initype ) ) {
			return sprintf(
				__('Error: The following file is not writable, please change its permissions: %s', 'ninjafirewall' ),
				htmlspecialchars( $NFW_ABSPATH . $initype )
			);
		}
		$backup_file = time();
		@copy( $NFW_ABSPATH .$initype, $NFW_ABSPATH ."{$initype}.ninja{$backup_file}" );

		// Remove potential NF directives
		nfw_remove_directives();

		$ini_content = file_get_contents( $NFW_ABSPATH . $initype );
	}

	// Write new content
	$res = @file_put_contents(
		$NFW_ABSPATH . $initype,
		NFW_PHPINI_BEGIN . "\n" . NFW_PHPINI_DATA . "\n" . NFW_PHPINI_END . "\n\n" . $ini_content,
		LOCK_EX
	);
	if ( $res === false ) {
		return sprintf(
			__('Error: The following file is not writable, please change its permissions: %s', 'ninjafirewall' ),
			htmlspecialchars( $NFW_ABSPATH . $initype )
		);
	}

	// Sandbox
	$res = nfw_waf_sandbox();
	if ( $res !== true ) {
		// Undo
		@file_put_contents(	$NFW_ABSPATH . $initype, $ini_content, LOCK_EX );
		return $res;
	}

	return true;
}

// ---------------------------------------------------------------------
// Remove all directives from .htaccess, INI files and wp-config.php.

function nfw_remove_directives( $ini = true, $htaccess = true, $wp_config = true ) {

	if ( defined('NFW_REMOVED_DIRECTIVES') ) { return; }

	define('NFW_REMOVED_DIRECTIVES', true);

	if (! function_exists( 'get_home_path' ) ) {
		include_once ABSPATH .'wp-admin/includes/file.php';
 	}
 	$NFW_ABSPATH = get_home_path();

	$res = array( 'ini' => true, 'htaccess' => true, 'wp-config' => true );

	// wp-config.php
	if ( $wp_config == true ) {
		$wp_config = ABSPATH .'wp-config.php';
		if ( file_exists( $wp_config ) ) {
			if ( is_writable( $wp_config ) ) {
				$wp_config_content = file_get_contents( $wp_config );
				if ( preg_match( '`'. NFW_WP_CONFIG_BEGIN .'.+?'. NFW_WP_CONFIG_END .'`s', $wp_config_content ) ) {
					$wp_config_content = preg_replace( '`\s?'. NFW_WP_CONFIG_BEGIN .'.+?'. NFW_WP_CONFIG_END .'[^\r\n]*\s?`s' , "\n", $wp_config_content);
					file_put_contents( $wp_config, $wp_config_content, LOCK_EX );
				}
			} else {
				$res['wp-config'] = __('File is not writable', 'ninjafirewall');
			}
		}
	}

	// .htaccess
	if ( $htaccess == true ) {
		$htaccess = $NFW_ABSPATH .'.htaccess';
		$mods = 0;
		if ( file_exists( $htaccess ) ) {
			if ( is_writable( $htaccess ) ) {
				$htaccess_content = file_get_contents( $htaccess );
				if ( preg_match( '`'. NFW_HTACCESS_BEGIN .'.+?'. NFW_HTACCESS_END .'`s', $htaccess_content ) ) {
					$htaccess_content = preg_replace( '`\s?'. NFW_HTACCESS_BEGIN .'.+?'. NFW_HTACCESS_END .'[^\r\n]*\s?`s' , "\n", $htaccess_content);
					$mods = 1;
				}
				// Comment out existing directive(s) left:
				if ( preg_match( '`[^#]php_value\s*auto_prepend_file`', $htaccess_content ) ) {
					$htaccess_content = preg_replace( '`php_value\s*auto_prepend_file`' , '#php_value auto_prepend_file', $htaccess_content);
					$mods = 1;
				}
				if ( $mods == 1 ) {
					@file_put_contents( $htaccess, $htaccess_content, LOCK_EX );
				}
			} else {
				$res['htaccess'] = __('File is not writable', 'ninjafirewall');
			}
		}
	}

	// .ini
	if ( $ini == true ) {
		$ini = $NFW_ABSPATH .'php.ini';
		$mods = 0;
		if ( file_exists( $ini ) ) {
			if ( is_writable( $ini ) ) {
				$ini_content = file_get_contents( $ini );
				if ( preg_match( '`'. NFW_PHPINI_BEGIN .'.+?'. NFW_PHPINI_END .'`s', $ini_content ) ) {
					$ini_content = preg_replace( '`\s?'. NFW_PHPINI_BEGIN .'.+?'. NFW_PHPINI_END .'[^\r\n]*\s?`s' , "\n", $ini_content);
					$mods = 1;
				}
				// Comment out existing directive(s) left:
				if ( preg_match( '`[^;]auto_prepend_file`', $ini_content ) ) {
					$ini_content = preg_replace( '`auto_prepend_file`' , ';auto_prepend_file', $ini_content);
					$mods = 1;
				}
				if ( $mods == 1 ) {
					@file_put_contents( $ini, $ini_content, LOCK_EX );
				}
			} else {
				$res['ini'] = __('File is not writable', 'ninjafirewall');
			}
		}
		$ini = $NFW_ABSPATH .'.user.ini';
		$mods = 0;
		if ( file_exists( $ini ) ) {
			if ( is_writable( $ini ) ) {
				$ini_content = file_get_contents( $ini );
				if ( preg_match( '`'. NFW_PHPINI_BEGIN .'.+?'. NFW_PHPINI_END .'`s', $ini_content ) ) {
					$ini_content = preg_replace( '`\s?'. NFW_PHPINI_BEGIN .'.+?'. NFW_PHPINI_END .'[^\r\n]*\s?`s' , "\n", $ini_content);
					$mods = 1;
				}
				// Comment out existing directive(s) left:
				if ( preg_match( '`[^;]auto_prepend_file`', $ini_content ) ) {
					$ini_content = preg_replace( '`auto_prepend_file`' , ';auto_prepend_file', $ini_content);
					$mods = 1;
				}
				if ( $mods == 1 ) {
					@file_put_contents( $ini, $ini_content, LOCK_EX );
				}
			} else {
				$res['ini'] = __('File is not writable', 'ninjafirewall');
			}
		}
	}
}

// ---------------------------------------------------------------------
// EOF //
