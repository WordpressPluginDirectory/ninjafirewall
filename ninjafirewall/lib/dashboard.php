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
 +---------------------------------------------------------------------+ i18n+ / sa / 2
*/

if (! defined('NFW_ENGINE_VERSION') ) {
	die('Forbidden');
}

nf_not_allowed('block', __LINE__ );

$nfw_options = nfw_get_option('nfw_options');

// Tab and div display
if ( empty( $_REQUEST['tab'] ) ) { $_REQUEST['tab'] = 'dashboard'; }

if ( $_REQUEST['tab'] == 'statistics' ) {
	$dashboard_tab = ''; $dashboard_div = ' style="display:none"';
	$statistics_tab = ' nav-tab-active'; $statistics_div = '';
	$about_tab = ''; $about_div = ' style="display:none"';

} elseif ( $_REQUEST['tab'] == 'about' ) {
	$dashboard_tab = ''; $dashboard_div = ' style="display:none"';
	$statistics_tab = ''; $statistics_div = ' style="display:none"';
	$about_tab = ' nav-tab-active'; $about_div = '';

} else {
	$_REQUEST['tab'] = 'dashboard';
	$dashboard_tab = ' nav-tab-active'; $dashboard_div = '';
	$statistics_tab = ''; $statistics_div = ' style="display:none"';
	$about_tab = ''; $about_div = ' style="display:none"';
}

if (! defined('NF_DISABLED') ) {
	is_nfw_enabled();
}

if (! defined( 'NFW_WPWAF' ) && defined( 'NFW_PID' ) ) {
	// Check if we have our PID. If we don't, that means there must
	// be a Full WAF instance of the firewall running in a parent
	// directory. Therefore, we need to allow Full WAF update from
	// this page:
	$nfw_pid = 0;
	if ( file_exists( NFW_LOG_DIR .'/nfwlog/cache/.pid' ) ) {
		$nfw_pid = trim( file_get_contents( NFW_LOG_DIR .'/nfwlog/cache/.pid' ) );
	}
	if ( NFW_PID != $nfw_pid ) {
		define('NFW_WPWAF', 2);
	}
}

// Search for Full WAF post-install
$res = get_transient( 'nfw_fullwaf' );
if ( $res !== false ) {
	if ( defined( 'NFW_WPWAF' ) ) {
		// 1: Apache mod_php
		// 2: Apache + CGI/FastCGI or PHP-FPM
		// 3: Apache + suPHP
		// 4: Nginx + CGI/FastCGI or PHP-FPM
		// 5: Litespeed
		// 6: Openlitespeed
		// 7: Other webserver + CGI/FastCGI or PHP-FPM
		list( $httpserver, $time ) = explode( ':', $res );
		$message = '';

		if ( $httpserver == 6 ) {
			$message = __('Make sure you followed the instructions and restarted Openlitespeed.', 'ninjafirewall' );
			delete_transient( 'nfw_fullwaf' );

		} elseif ( $httpserver == 1 || $httpserver == 5 ) {
			$message = sprintf( __('Make sure your HTTP server support the %s directive in .htaccess files. Maybe you need to restart your HTTP server to apply the change, or simply to wait a few seconds and reload this page?', 'ninjafirewall' ), '<code>php_value auto_prepend_file</code>' );
			delete_transient( 'nfw_fullwaf' );

		} else {
			$now = time();
			// <5 minutes
			if ( $now < $time ) {
				$time_left = $time - $now;
				$message = sprintf( __('Because PHP caches INI files, you may need to wait up to five minutes before the changes are reloaded by the PHP interpreter. <strong>Please wait for <font id="nfw-waf-count">%d</font> seconds</strong> before trying again (you can navigate away from this page and come back in a few minutes).', 'ninjafirewall'), (int) $time_left );
				$countdown = 1;
			} else {
				delete_transient( 'nfw_fullwaf' );
			}
		}
		if (! empty( $message ) ) {
			echo '<div class="notice-warning notice is-dismissible"><p>'.
				__('Oops! Full WAF mode is not enabled yet.', 'ninjafirewall' ) .'<br />'.
				$message .
				'</p></div>';
			if ( isset( $countdown ) ) {
				echo '<script>fullwaf_count='. $time_left .';fullwaf=setInterval(nfwjs_fullwaf_countdown,1000);</script>';
			}
		}
	}
}
// Error log deletion:
if (! empty( $_POST['delete-error-log'] ) ){
	if ( empty( $_POST['nfwnonce_errorlog'] ) || ! wp_verify_nonce( $_POST['nfwnonce_errorlog'], 'delete_error_log' ) ) {
		wp_nonce_ays('delete_error_log');
	}
	if ( file_exists( NFW_LOG_DIR .'/nfwlog/error_log.php' ) ) {
		@unlink( NFW_LOG_DIR .'/nfwlog/error_log.php' );
	}
}
?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png') ?>">&nbsp;<?php _e('NinjaFirewall (WP Edition)', 'ninjafirewall') ?></h1>
	<?php

	// Display a one-time notice after two weeks of use
	nfw_rate_notice( $nfw_options );

	// Full WAF settings change
	if (! empty( $_GET['nfwafconfig'] ) ) {
		echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	}
	?>
	<br />
	<h2 class="nav-tab-wrapper wp-clearfix" style="cursor:pointer">
		<a id="tab-dashboard" class="nav-tab<?php echo $dashboard_tab ?>" onClick="nfwjs_switch_tabs('dashboard', 'dashboard:statistics:about')"><?php _e( 'Dashboard', 'ninjafirewall' ) ?></a>
		<a id="tab-statistics" class="nav-tab<?php echo $statistics_tab ?>" href="?page=NinjaFirewall&tab=statistics"><?php _e( 'Statistics', 'ninjafirewall' ) ?></a>
		<a id="tab-about" class="nav-tab<?php echo $about_tab ?>" onClick="nfwjs_switch_tabs('about', 'dashboard:statistics:about')"><?php _e( 'About...', 'ninjafirewall' ) ?></a>
	</h2>
	<br />

	<?php
	// One-time notice:
	if ( isset( $nfw_options['welcome'] ) ) {
	?>
	<div id="nfw-welcome">
		<table class="form-table nfw-table" style="background:#fff">
			<tr>
				<td style="padding:20px;text-align:center;vertical-align:middle;width:50%">
					<h3><?php _e('Thank you for using NinjaFirewall.', 'ninjafirewall' )?></h3>
					<p style="font-size: 1.1em;"><?php printf( __('Every page of NinjaFirewall has a contextual help: whenever you need help about an option or feature, click on the %s tab located in the upper right corner of the corresponding page.', 'ninjafirewall' ), '<strong style="border:1px solid #ccc;padding:2px">'. __('Help') .'</strong>' ) ?></p>
					<br />
					<p><input type="button" class="button-primary" value="<?php _e('Got it!', 'ninjafirewall' )?>" onClick="nfwjs_welcomeajax('<?php echo wp_create_nonce('welcome_save') ?>');nfwjs_up_down('nfw-welcome');" /></p>
				</td>
				<td style="padding:20px;text-align:center;vertical-align:middle;width:50%">
					<img class="wpplus img-fluid" src="<?php echo plugins_url( '/ninjafirewall/images/welcome.png') ?>" />
				</td>
			</tr>
		</table>
	</div>
	<?php
	}
	?>

	<!-- Dashboard -->

	<div id="dashboard-options"<?php echo $dashboard_div ?>>

		<h3><?php _e('Firewall Dashboard', 'ninjafirewall') ?></h3>

		<table class="form-table nfw-table">

		<?php
		if ( NF_DISABLED ) {
			// An instance of the firewall running in Full WAF (or Pro/Pro+ Edition)
			// in a parent directory will force us to run in Full WAF mode to override it.
			if ( defined( 'NFW_STATUS' ) && ( NFW_STATUS > 19 && NFW_STATUS < 24 ) ) {
				$msg = __('It seems that you may have another instance of NinjaFirewall running in a parent directory. Make sure to follow these instructions:', 'ninjafirewall');
				$msg.= '<ol><li>';
				$msg.= __('Temporarily disable the firewall in the parent folder by renaming its PHP INI or .htaccess file.', 'ninjafirewall');
				$msg.= '</li><li>';
				$msg.= __('Install NinjaFirewall on this site in Full WAF mode.', 'ninjafirewall');
				$msg.= '</li><li>';
				$msg.= __('Restore the PHP INI or .htaccess in the parent folder to re-enable the firewall.', 'ninjafirewall');
				$msg.= '</li></ol>';

			} elseif (! empty( $GLOBALS['err_fw'][NF_DISABLED] ) ) {
				$msg = $GLOBALS['err_fw'][NF_DISABLED];
			} else {
				$msg = __('Unknown error', 'ninjafirewall') .' #'. NF_DISABLED;
			}
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Firewall', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php echo $msg ?></td>
			</tr>

		<?php
		} else {
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Firewall', 'ninjafirewall') ?></th>
				<td><?php _e('Enabled', 'ninjafirewall') ?></td>
			</tr>
		<?php
		}

		?>
			<tr>
				<th scope="row" class="row-med"><?php esc_html_e('Mode', 'ninjafirewall') ?></th>
				<td>
				<?php
				if ( defined( 'NFW_WPWAF' ) ) {
					printf( esc_html__('NinjaFirewall is running in %s mode. For better protection, activate its Full WAF mode:', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/full_waf-vs-wordpress_waf/" target="_blank">WordPress WAF</a>');
					?>
					<p><input type="button" id="nfw-activate-thickbox" value="<?php esc_attr_e('Activate Full WAF mode', 'ninjafirewall') ?>" class="button-secondary"></p>
					<?php
				} else {
					if (! NF_DISABLED ) {
						printf( esc_html__('NinjaFirewall is running in %s mode.', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/full_waf-vs-wordpress_waf/" target="_blank">Full WAF</a>');
						?>
						<p><input type="button" id="nfw-configure-thickbox" value="<?php esc_attr_e('Configure', 'ninjafirewall') ?>" class="button-secondary"></p>
						<?php
					} else {
						echo '-';
					}
				}
				?>
				</td>
			</tr>
		<?php

		if (! empty( $nfw_options['debug'] ) ) {
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Debugging mode', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php _e('Enabled.', 'ninjafirewall') ?>&nbsp;<a href="?page=nfsubopt"><?php _e('Click here to turn Debugging Mode off', 'ninjafirewall') ?></a></td>
			</tr>
		<?php
		}
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Edition', 'ninjafirewall') ?></th>
				<td>WP Edition ~ <a href="?page=nfsubwplus"><?php _e('Need more security? Explore our supercharged premium version: NinjaFirewall (WP+ Edition)', 'ninjafirewall' ) ?></a></td>
			</tr>
			<tr>
				<th scope="row" class="row-med"><?php _e('Version', 'ninjafirewall') ?></th>
				<td><?php echo NFW_ENGINE_VERSION . ' ~ ' . __('Security rules:', 'ninjafirewall' ) . ' ' . preg_replace('/(\d{4})(\d\d)(\d\d)/', '$1-$2-$3', $nfw_options['rules_version']) ?></td>
			</tr>

			<tr>
				<th scope="row" class="row-med"><?php _e('PHP SAPI', 'ninjafirewall') ?></th>
				<td>
					<?php
					if ( defined('HHVM_VERSION') ) {
						echo 'HHVM';
					} else {
						echo strtoupper(PHP_SAPI);
					}
					echo ' ~ '. PHP_MAJOR_VERSION .'.'. PHP_MINOR_VERSION .'.'. PHP_RELEASE_VERSION;
					?>
				</td>
			</tr>
		<?php

		// If security rules updates are disabled, warn the user
		if ( empty( $nfw_options['enable_updates'] ) ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Updates', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-dismiss nfw-danger"></span><a href="?page=nfsubupdates&tab=updates"><?php _e( 'Security rules updates are disabled.', 'ninjafirewall' ) ?></a> <?php _e( 'If you want your blog to be protected against the latest threats, enable automatic security rules updates.', 'ninjafirewall' ) ?></td>
			</tr>
			<?php
		}

		if ( empty( NinjaFirewall_session::read('nfw_goodguy') ) ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Admin user', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-warning nfw-warning"></span><?php printf( __('You are not whitelisted. Ensure that the "Do not block WordPress administrator" option is enabled in the <a href="%s">Firewall Policies</a> menu, otherwise you could get blocked by the firewall while working from your administration dashboard.', 'ninjafirewall'), '?page=nfsubpolicies') ?></td>
			</tr>
		<?php
		} else {
			$current_user = wp_get_current_user();
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Admin user', 'ninjafirewall') ?></th>
				<td><code><?php echo htmlspecialchars( $current_user->user_login ) ?></code>: <?php _e('You are whitelisted by the firewall.', 'ninjafirewall') ?></td>
			</tr>
		<?php
		}
		if ( defined('NFW_ALLOWED_ADMIN') && ! is_multisite() ) {
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Restrictions', 'ninjafirewall') ?></th>
				<td><?php _e('Access to NinjaFirewall is restricted to specific users.', 'ninjafirewall') ?></td>
			</tr>
		<?php
		}

		// Try to find out if there is any "lost" session between the firewall
		// and the plugin part of NinjaFirewall (could be a buggy plugin killing
		// the session etc), unless we just installed it
		if ( defined( 'NFW_SWL' ) && ! empty( NinjaFirewall_session::read('nfw_goodguy') ) && empty( $_REQUEST['nfw_firstrun'] ) ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php esc_html_e('User session', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-warning nfw-warning"></span><?php esc_html_e('It seems that the user session set by NinjaFirewall was not found by the firewall script.', 'ninjafirewall') ?></td>
			</tr>
			<?php
		} else {
			?>
			<tr>
				<th scope="row" class="row-med"><?php esc_html_e('User session', 'ninjafirewall') ?></th>
				<?php
				if ( defined('NFWSESSION') ) {
					?>
					<td><?php
						printf(
							/* Translators: <a> and </a> anchor tags */
							esc_html__('You are using NinjaFirewall sessions. If you want to switch to PHP sessions, please %sconsult our blog%s.', 'ninjafirewall'),
							'<a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/#user_session" target="_blank" rel="noreferrer noopener">', '</a>'
						); ?>
					</td>
					<?php
				} else {
					?>
					<td><?php
						printf(
							/* Translators: <a> and </a> anchor tags */
							esc_html__('You are using PHP sessions. If you want to switch to NinjaFirewall sessions, please %sconsult our blog%s.', 'ninjafirewall'),
							'<a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/#user_session" target="_blank" rel="noreferrer noopener">', '</a>'
						); ?>
					</td>
					<?php
				}
			?>
			</tr>
			<?php
		}


		if ( ! empty( $nfw_options['clogs_pubkey'] ) ) {
			$err_msg = $ok_msg = '';
			if (! preg_match( '/^[a-f0-9]{40}:([a-f0-9:.]{3,39}|\*)$/', $nfw_options['clogs_pubkey'], $match ) ) {
				$err_msg = sprintf( __('the public key is invalid. Please <a href="%s">check your configuration</a>.', 'ninjafirewall'), '?page=nfsublog#clogs');

			} else {
				if ( $match[1] == '*' ) {
					$ok_msg = __( "No IP address restriction.", 'ninjafirewall');

				} elseif ( filter_var( $match[1], FILTER_VALIDATE_IP ) ) {
					$ok_msg = sprintf( __("IP address %s is allowed to access NinjaFirewall's log on this server.", 'ninjafirewall'), htmlspecialchars( $match[1]) );

				} else {
					$err_msg = sprintf( __('the whitelisted IP is not valid. Please <a href="%s">check your configuration</a>.', 'ninjafirewall'), '?page=nfsublog#clogs');
				}
			}
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Centralized Logging', 'ninjafirewall') ?></th>
			<?php
			if ( $err_msg ) {
				?>
					<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php printf( __('Error: %s', 'ninjafirewall'), $err_msg) ?></td>
				</tr>
				<?php
				$err_msg = '';
			} else {
				?>
					<td><a href="?page=nfsublog#clogs"><?php _e('Enabled', 'ninjafirewall'); echo "</a>. $ok_msg"; ?></td>
				</tr>
			<?php
			}
		}

		if (! filter_var(NFW_REMOTE_ADDR, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Source IP', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can use the IP Access Control section to easily configure all IP address related options (source, whitelist, blacklist, rate limiting etc).', 'ninjafirewall' ) ?>"></span></th>
				<td><span class="dashicons dashicons-warning nfw-warning"></span><?php printf( __('You have a private IP : %s', 'ninjafirewall') .'<br />'. __('If your site is behind a reverse proxy or a load balancer, ensure that you have setup your HTTP server or PHP to forward the correct visitor IP, otherwise use the NinjaFirewall %s configuration file.', 'ninjafirewall'), htmlentities(NFW_REMOTE_ADDR), '<code><a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/">.htninja</a></code>') ?></td>
			</tr>
			<?php
		}
		if (! empty( $_SERVER["HTTP_CF_CONNECTING_IP"] ) ) {
			if ( NFW_REMOTE_ADDR != $_SERVER["HTTP_CF_CONNECTING_IP"] ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('CDN detection', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can use the IP Access Control section to easily configure all IP address related options (source, whitelist, blacklist, rate limiting etc).', 'ninjafirewall' ) ?>"></span></th>
				<td><span class="dashicons dashicons-warning nfw-warning"></span><?php printf( __('%s detected: you seem to be using Cloudflare CDN services. Ensure that you have setup your HTTP server or PHP to forward the correct visitor IP, otherwise use the NinjaFirewall %s configuration file.', 'ninjafirewall'), '<code>HTTP_CF_CONNECTING_IP</code>', '<code><a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/">.htninja</a></code>') ?></td>
			</tr>
			<?php
			}
		}
		if (! empty( $_SERVER["HTTP_INCAP_CLIENT_IP"] ) ) {
			if ( NFW_REMOTE_ADDR != $_SERVER["HTTP_INCAP_CLIENT_IP"] ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('CDN detection', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can use the IP Access Control section to easily configure all IP address related options (source, whitelist, blacklist, rate limiting etc).', 'ninjafirewall' ) ?>"></span></th>
				<td><span class="dashicons dashicons-warning nfw-warning"></span><?php printf( __('%s detected: you seem to be using Incapsula CDN services. Ensure that you have setup your HTTP server or PHP to forward the correct visitor IP, otherwise use the NinjaFirewall %s configuration file.', 'ninjafirewall'), '<code>HTTP_INCAP_CLIENT_IP</code>', '<code><a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-the-htninja-configuration-file/">.htninja</a></code>') ?></td>
			</tr>
			<?php
			}
		}

		if (! is_writable( NFW_LOG_DIR . '/nfwlog' ) ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Log dir', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php printf( __('%s directory is not writable! Please chmod it to 0777 or equivalent.', 'ninjafirewall'), '<code>'. htmlspecialchars(NFW_LOG_DIR) .'/nfwlog/</code>') ?></td>
			</tr>
		<?php
		}

		if (! is_writable( NFW_LOG_DIR . '/nfwlog/cache') ) {
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Log dir', 'ninjafirewall') ?></th>
				<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php printf(__('%s directory is not writable! Please chmod it to 0777 or equivalent.', 'ninjafirewall'), '<code>'. htmlspecialchars(NFW_LOG_DIR) . '/nfwlog/cache/</code>') ?></td>
			</tr>
		<?php
		}


		if (! defined('NF_DISABLE_PHPINICHECK') && ! defined('NFW_WPWAF') ) {

			// Make sure the PHP INI is not viewable by webusers
			if ( file_exists( ABSPATH .'php.ini' ) ) {
				$res = nfw_is_inireadable( 'php.ini' );
				if ( $res !== false ) {
					?>
					<tr>
						<th scope="row" class="row-med">PHP INI</th>
						<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php printf( esc_html__('The php.ini file is readable by web users: %s', 'ninjafirewall'), '<code>'. htmlspecialchars( $res ) .'</code>' ) ?> <br /><a href="https://blog.nintechnet.com/protecting-ninjafirewalls-php-ini-file/" target="_blank"><?php esc_html_e('Consult our blog for more info.', 'ninjafirewall') ?></a></td>
					</tr>
					<?php
				}
			}
			if ( file_exists( ABSPATH .'.user.ini' ) ) {
				$res = nfw_is_inireadable( '.user.ini' );
				if ( $res !== false ) {
					?>
					<tr>
						<th scope="row" class="row-med">PHP INI</th>
						<td><span class="dashicons dashicons-dismiss nfw-danger"></span><?php printf( esc_html__('The .user.ini file is readable by web users:  %s', 'ninjafirewall'), '<code>'. htmlspecialchars( $res ) .'</code>' ) ?><br /><a href="https://blog.nintechnet.com/protecting-ninjafirewalls-php-ini-file/" target="_blank"><?php esc_html_e('Consult our blog for more info.', 'ninjafirewall') ?></a></td>
					</tr>
					<?php
				}
			}
		}

		// Error log
		$log = NFW_LOG_DIR . '/nfwlog/error_log.php';
		if ( file_exists( $log ) ) {
			$errlog_content = file( $log );
			array_shift( $errlog_content );
			if (! empty( $errlog_content ) ) {
				?>
				<tr id="error-log-alert">
					<th scope="row" class="row-med"><?php _e('Error log', 'ninjafirewall') ?></th>
					<td><span class="dashicons dashicons-dismiss nfw-danger"></span><input type="button" id="nfw-errorlog-thickbox" value="<?php _e('View error log', 'ninjafirewall') ?>" class="button-secondary"></td>
				</tr>
				<?php
			}
		}

		$doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
		if ( @file_exists( $file = dirname( $doc_root ) . '/.htninja') ||
			@file_exists( $file = $doc_root . '/.htninja') ) {
			echo '<tr><th scope="row" class="row-med">' . __('Optional configuration file', 'ninjafirewall') . '</th>
			<td><code>' .  htmlentities($file) . '</code></td>
			</tr>';

			// Check if we have a MySQLi link identifier defined in the .htninja
			if (! empty( $GLOBALS['nfw_mysqli'] ) && ! empty( $GLOBALS['nfw_table_prefix'] ) ) {
				echo '<tr>
				<th scope="row" class="row-med">' . __('MySQLi link identifier', 'ninjafirewall') . '</th>
				<td>' . __('A MySQLi link identifier was detected in your <code>.htninja</code>.', 'ninjafirewall') . '</td>
				</tr>';
			}
		}
		?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Help &amp; configuration', 'ninjafirewall') ?></th>
				<td><a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/">Securing WordPress with NinjaFirewall (WP Edition)</a></td>
			</tr>

		</table>
	</div>

	<!-- Monthly statistics -->
	<div id="statistics-options"<?php echo $statistics_div ?>>
		<?php include __DIR__ .'/dashboard_statistics.php'; ?>
	</div>

	<!-- About... -->
	<div id="about-options"<?php echo $about_div ?>>
		<?php include __DIR__ .'/dashboard_about.php'; ?>
	</div>

</div>
<?php

// Load thickbox
require __DIR__ .'/thickbox.php';

// ---------------------------------------------------------------------
// Verify if PHP INI file is readable by web users.

function nfw_is_inireadable( $ini ) {

	if ( is_multisite() ) {
		$url = network_home_url('/') . $ini;
	} else {
		$url = home_url('/') . $ini;
	}
	global $wp_version;
	$opts = array(
		'http' => array(
			// We only care about the returned HTTP code
			'ignore_errors' => true,
			// Max 2 seconds
			'timeout' => 2,
			'method' => "GET",
			'header' =>
				"Accept-language: en-US,en;q=0.5\r\n" .
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
				"User-Agent: Mozilla/5.0 (compatible; NinjaFirewall/". NFW_ENGINE_VERSION ."; WordPress/$wp_version)\r\n"
		)
	);

	if ( empty( $_SERVER['SERVER_ADDR'] ) ) {
		return false;
	}
	$addr = $_SERVER['SERVER_ADDR'];
	if (! filter_var( $addr, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		// We don't want a fatal error if we're running on localhost e.g., dev site etc
		$opts['ssl']['verify_peer'] = false;
		$opts['ssl']['verify_peer_name'] = false;
	}
	$context  = stream_context_create( $opts );
	// As we don't want monitoring/debugging plugins to throw a warning or error
	// in the backend because the server returned a 403 error, we don't use
	// the WordPress's API
	@file_get_contents( $url, false, $context );
	if ( empty( $http_response_header ) ) {
		return false;
	}
	$response = explode( ' ', $http_response_header[0] );
	if (! empty( $response[1] ) && (int) $response[1] == 200 ) {
		return $url;
	}
	return false;

}
// ---------------------------------------------------------------------
// EOF
