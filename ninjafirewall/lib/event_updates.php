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

if (! defined('NFW_ENGINE_VERSION') ) {
	die('Forbidden');
}

// ---------------------------------------------------------------------
// This function is called by NinjaFirewall's garbage collector
// which runs hourly.

function nfw_check_security_updates() {

	$nfw_checked = nfw_get_option( 'nfw_checked' );
	if ( empty( $nfw_checked ) ) { $nfw_checked = array(); }

	$nfw_options = nfw_get_option('nfw_options');
	if ( empty( $nfw_options['secupdates'] ) ) { return; }

	$found = [];

	$url = 'https://api.nintechnet.com/ninjafirewall/security-update';

	// Fetch latest data:
	$list = array();
	$list = nfw_fetch_security_updates( $url );

	set_transient( 'nfw_fetchsecupdates', 1, 6000 );

	if ( $list === false ) {
		return false;
	}

	if (! isset( $list['wordpress'] ) || ! isset( $list['themes'] ) || ! isset( $list['plugins'] ) ) {
		nfw_log_error(	__('Downloaded security rules are corrupted', 'ninjafirewall' ) );
		return false;
	}

	// Check WordPress updates
	global $wp_version;
	if ( isset( $list['wordpress']['version'] ) && version_compare( $wp_version, $list['wordpress']['version'], '<' ) ) {
		// Versions are different, check if the user was already warned about that
		if (! isset( $nfw_checked['wordpress']['version'] ) ||
			version_compare( $nfw_checked['wordpress']['version'], $list['wordpress']['version'], '<' ) ) {
			// Mark as checked
			$nfw_checked['wordpress']['version'] = $list['wordpress']['version'];

			$found['wordpress']['cur_version'] = $wp_version;
			$found['wordpress']['new_version'] = $list['wordpress']['version'];
			$found['wordpress']['level'] = $list['wordpress']['level'];
		}
	}

	// Check themes updates
	if ( ! function_exists( 'wp_get_themes' ) ) {
		require_once ABSPATH . 'wp-includes/theme.php';
	}
	$themes = wp_get_themes();

	foreach( $themes as $k => $v ) {
		// No name or no version (unlike plugins, we're dealing with objects here)
		if ( $v->Name == '' || $v->Version == '' ) {
			continue;
		}
		$hash = hash( 'sha256', $k );

		if ( isset( $list['themes'][$hash] ) && version_compare( $v->Version, $list['themes'][$hash]['version'], '<' ) ) {

			// Make sure we didn't inform the user yet
			if (! isset( $nfw_checked['themes'][$k] ) ||
				version_compare( $nfw_checked['themes'][$k]['version'], $list['themes'][$hash]['version'], '<' ) ) {

				// Mark as checked:
				$nfw_checked['themes'][$k]['version'] = $list['themes'][$hash]['version'];

				$found['themes'][$k]['name'] = $v->Name;
				$found['themes'][$k]['cur_version'] = $v->Version;
				$found['themes'][$k]['new_version'] = $list['themes'][$hash]['version'];
				$found['themes'][$k]['level'] = $list['themes'][$hash]['level'];
			}
		}
	}

	// Check plugins updates
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH .'wp-admin/includes/plugin.php';
	}
	$plugins = get_plugins();

	foreach( $plugins as $k => $v ) {
		// No name or no version (unlike themes, we're dealing with arrays here)
		if ( empty( $v['Name'] ) || empty( $v['Version'] ) ) {
			continue;
		}
		$hash = hash( 'sha256', $k );

		if ( isset( $list['plugins'][$hash] ) && version_compare( $v['Version'], $list['plugins'][$hash]['version'], '<' ) ) {
			// Make sure we didn't inform the user yet
			if (! isset( $nfw_checked['plugins'][$k] ) ||
				version_compare( $nfw_checked['plugins'][$k]['version'], $list['plugins'][$hash]['version'], '<' ) ) {

				// Mark as checked
				$nfw_checked['plugins'][$k]['version'] = $list['plugins'][$hash]['version'];

				$found['plugins'][$k]['name'] = $v['Name'];
				$found['plugins'][$k]['cur_version'] = $v['Version'];
				$found['plugins'][$k]['new_version'] = $list['plugins'][$hash]['version'];
				$found['plugins'][$k]['level'] = $list['plugins'][$hash]['level'];
			}
		}
	}

	// Nothing to do
	if ( empty( $found ) ) {
		return;
	}

	// Warn the user
	nfw_alert_security_updates( $found );

	// Update checked list
	nfw_update_option( 'nfw_checked', $nfw_checked, false );

	return;
}

// ---------------------------------------------------------------------
// Send an email alert to the admin

function nfw_alert_security_updates( $found = [] ) {

	$message = '';

	/**
	 * WordPress.
	 */
	if (! empty( $found['wordpress'] ) ) {
		$message .= "WordPress:\n" .
			sprintf( __('Your version: %s', 'ninjafirewall'), $found['wordpress']['cur_version'] ) ."\n".
			sprintf( __('New version: %s', 'ninjafirewall'), $found['wordpress']['new_version'] ) ."\n";
		if ( $found['wordpress']['level'] == 2 ) {
			$message .= __('Severity: This is an important security update', 'ninjafirewall') ."\n";
		} elseif ( $found['wordpress']['level'] == 3 ) {
			$message .= __('Severity: **This is a critical security update**', 'ninjafirewall') ."\n";
		}
		$message .= "\n";
	}

	/**
	 * Plugins.
	 */
	if (! empty( $found['plugins'] ) ) {
		foreach( $found['plugins'] as $k => $v ) {
			$message .= sprintf( __('Plugin: %s', 'ninjafirewall'), $found['plugins'][$k]['name'] ) ."\n".
				sprintf( __('Your version: %s', 'ninjafirewall'), $found['plugins'][$k]['cur_version'] ) ."\n".
				sprintf( __('New version: %s', 'ninjafirewall'), $found['plugins'][$k]['new_version'] ) ."\n";

			if ( $found['plugins'][$k]['level'] == 2 ) {
				$message .= __('Severity: This is an important security update', 'ninjafirewall') ."\n";
			} elseif ( $found['plugins'][$k]['level'] == 3 ) {
				$message .= __('Severity: **This is a critical security update**', 'ninjafirewall') ."\n";
			}
			$message .= "\n";
		}
	}

	/**
	 * Themes.
	 */
	if (! empty( $found['themes'] ) ) {

		foreach( $found['themes'] as $k => $v ) {
			$message .= sprintf( __('Theme: %s', 'ninjafirewall'), $found['themes'][$k]['name'] ) ."\n".
				sprintf( __('Your version: %s', 'ninjafirewall'), $found['themes'][$k]['cur_version'] ) ."\n".
				sprintf( __('New version: %s', 'ninjafirewall'), $found['themes'][$k]['new_version'] ) ."\n";

			if ( $found['themes'][$k]['level'] == 2 ) {
				$message .= __('Severity: This is an important security update', 'ninjafirewall') ."\n";
			} elseif ( $found['themes'][$k]['level'] == 3 ) {
				$message .= __('Severity: **This is a critical security update**', 'ninjafirewall') ."\n";
			}
			$message .= "\n";
		}
	}

	if ( is_multisite() ) {
		$url = network_home_url('/');
	} else {
		$url = home_url('/');
	}

	/**
	 * Email notification.
	 */
	$subject = [];
	$content = [ ucfirst( date_i18n('F j, Y @ H:i:s T') ), $url, $message ];

	NinjaFirewall_mail::send('security_updates', $subject, $content, '', [], 1 );


}

// ---------------------------------------------------------------------
// Download list from remote server

function nfw_fetch_security_updates( $url ) {

	global $wp_version;
	$res = wp_remote_get(
		$url,
		array(
			'timeout' => 20,
			'httpversion' => '1.1' ,
			'user-agent' => 'Mozilla/5.0 (compatible; NinjaFirewall/'.
									NFW_ENGINE_VERSION .'; WordPress/'. $wp_version . ')',
			'sslverify' => true
		)
	);
	if ( is_wp_error( $res ) ) {
		nfw_log_error( __('Cannot download security rules: connection error. Will try again later', 'ninjafirewall') );
		return false;
	}

	if ( $res['response']['code'] != 200 ) {
		nfw_log_error(
			sprintf( __('Cannot download security rules: HTTP response error %s. Will try again later', 'ninjafirewall'), $res['response']['code'] )
		);
		return false;
	}

	return json_decode( $res['body'], true );
}

// ---------------------------------------------------------------------
// EOF
