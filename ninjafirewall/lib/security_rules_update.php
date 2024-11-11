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

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// If your server can't remotely connect to a SSL port, add this
// to your wp-config.php script: `define('NFW_DONT_USE_SSL', 1);`
if ( defined( 'NFW_DONT_USE_SSL' ) ) {
	$proto = "http";
} else {
	$proto = "https";
}
$update_log = NFW_LOG_DIR . '/nfwlog/updates.php';

// Check which rules should be returned
if ( defined('NFW_WPWAF') ) {
	$rules_type = 0;
} else {
	$rules_type = 1;
}

$nfw_options = nfw_get_option('nfw_options');

if ( empty( $nfw_options['sched_updates'] ) || empty( $nfw_options['enable_updates'] ) ) {
	$sched_updates = 0;
} else {
	$sched_updates = (int) $nfw_options['sched_updates'];
}

if ( defined( 'NFUPDATESDO' ) && NFUPDATESDO == 2 ) {
	// Installation
	$update_url = array(
		$proto . '://plugins.svn.wordpress.org/ninjafirewall/updates/',
		'version3.txt',
		'rules4.txt'
	);
} else {
	// Scheduled updates or plugin update
	$caching_id = sha1( home_url() );
	$update_url = array(
		'https://api.nintechnet.com/ninjafirewall/rules-update',
		"?version=4&cid={$caching_id}&edn=wp&rt={$rules_type}&su={$sched_updates}",
		"?rules=4&cid={$caching_id}&edn=wp&rt={$rules_type}&su={$sched_updates}"
	);
}

// NFUPDATESDO: scheduled update (1), installation (2) or plugin update (3 - deprecated since v3.8)
if (defined('NFUPDATESDO') ) {

	$rules_lock = NFW_LOG_DIR .'/nfwlog/cache/rules';

	if ( NFUPDATESDO != 2 ) { // Shouldn't apply to (re)installation
		if ( $nfw_options['sched_updates'] == 1 ) {
			$interval = 3000; // 50mn
		} elseif ( $nfw_options['sched_updates'] == 2 ) {
			$interval = 39600; // 11h
		} else {
			$interval = 82800; // 23h
		}

		if ( file_exists( $rules_lock ) ) {
			$rules_lock_mtime = filemtime( $rules_lock );
			if ( time() - $interval < $rules_lock_mtime ) {
				return;
			}
			unlink( $rules_lock );
		}
	}

	touch( $rules_lock );

	define('NFW_RULES', nf_sub_do_updates( $update_url, $update_log, NFUPDATESDO ) );
	return;
}

// Block immediately if user is not allowed
nf_not_allowed( 'block', __LINE__ );

// We stop and warn the user if the firewall is disabled
if (! defined('NF_DISABLED') ) {
	is_nfw_enabled();
}
if (NF_DISABLED) {
	echo '<div class="error notice is-dismissible"><p>' . __('Security rules cannot be updated when NinjaFirewall is disabled.', 'ninjafirewall') . '</p></div>';
	return;
}

//Saved options
if (! empty( $_POST['nfw_act'] ) ) {
	if ( empty( $_POST['nfwnonce'] ) || ! wp_verify_nonce( $_POST['nfwnonce'], 'updates_save' ) ) {
		wp_nonce_ays('updates_save');
	}
	// Check updates now
	if  ( isset( $_POST['check_updates'] ) ) {
		if ( $res = nf_sub_do_updates($update_url, $update_log, 0) ) {
			echo '<div class="updated notice is-dismissible"><p>' . __('Security rules have been updated.', 'ninjafirewall') . '</p></div>';
		} else {
			echo '<div class="updated notice is-dismissible"><p>' . __('No security rules update available.', 'ninjafirewall') . '</p></div>';
		}
		// Enable flag to display log
		$tmp_showlog = 1;
	} else {
		if ( isset( $_POST['save_options'] ) ) {
			nf_sub_updates_save();
		} elseif ( isset( $_POST['clear_log'] ) ) {
			nf_sub_updates_clearlog($update_log);
		}
		echo '<div class="updated notice is-dismissible"><p>' . __('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	}
	// Reload options:
	$nfw_options = nfw_get_option('nfw_options');
}

// If WP cron is disabled, we simply warn the user
if ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON == true ) {
	echo '<div class="notice-warning notice is-dismissible"><p>' . sprintf( __('It seems that %s is set. Ensure you have another way to run WP-Cron, otherwise NinjaFirewall automatic updates will not work.', 'ninjafirewall'), '<code>DISABLE_WP_CRON</code>' ) . '</p></div>';
	$cron_disabled = 1;
}

if ( empty($nfw_options['enable_updates']) ) {
	$enable_updates = 0;
} else {
	$enable_updates = 1;
}
if ( empty($nfw_options['sched_updates']) || ! preg_match('/^[2-3]$/', $nfw_options['sched_updates']) ) {
	$sched_updates = 1;
} else {
	$sched_updates = $nfw_options['sched_updates'];
}
if ( empty($nfw_options['notify_updates']) && isset($nfw_options['notify_updates']) ) {
	$notify_updates = 0;
} else {
	// Defaut if not set yet
	$notify_updates = 1;
}
?>
<form method="post" name="fupdates">

	<?php wp_nonce_field('updates_save', 'nfwnonce', 0); ?>

	<table class="form-table nfw-table">
		<tr style="background-color:#F9F9F9;border: solid 1px #DFDFDF;">
			<th scope="row" class="row-med"><?php _e('Automatically update NinjaFirewall security rules', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'danger', 'enable_updates', __('Enabled', 'ninjafirewall'), __('Disabled', 'ninjafirewall'), 'large', $enable_updates, false, 'onclick="nfwjs_up_down(\'upd_table\');"' ) ?>
			</td>
		</tr>
	</table>

	<br />

	<div id="upd_table"<?php echo $enable_updates == 1 ? '' : ' style="display:none"' ?>>
		<table class="form-table nfw-table">
			<tr>
				<th scope="row" class="row-med"><?php _e('Check for updates', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can check for security rules updates as often as every 15 minutes, versus one hour for the free WP Edition.', 'ninjafirewall' ) ?>"></span></th>
					<td>
						<select name="sched_updates">
							<option disabled><?php _e('Every 15 minutes', 'ninjafirewall') ?> (Premium)</option>
							<option disabled><?php _e('Every 30 minutes', 'ninjafirewall') ?> (Premium)</option>
							<option value="1"<?php selected($sched_updates, 1) ?>><?php _e('Hourly', 'ninjafirewall') ?></option>
							<option value="2"<?php selected($sched_updates, 2) ?>><?php _e('Twicedaily', 'ninjafirewall') ?></option>
							<option value="3"<?php selected($sched_updates, 3) ?>><?php _e('Daily', 'ninjafirewall') ?></option>
						</select>
						<?php
						if ( $nextcron = wp_next_scheduled('nfsecupdates') ) {
							$sched = new DateTime( date('M d, Y H:i:s', $nextcron) );
							$now = new DateTime( date('M d, Y H:i:s', time() ) );
							$diff = $now->diff($sched);
							// Ensure that the scheduled scan time is in the future,
							// not in the past, otherwise send a warning because wp-cron
							// is obviously not working as expected
							if ( $nextcron < time() ) {
								// Don't display any message if WP-CRON is disabled
								if ( empty( $cron_disabled ) ) {
							?>
								<p class="description" style="color:red"><?php _e('The next scheduled date is in the past! WordPress wp-cron may not be working, may have been disabled or is currently running. Try to reload this page in a few seconds.', 'ninjafirewall'); ?></p>
							<?php
								}
							} else {
							?>
								<p class="description"><?php printf( __('Next scheduled update will start in approximately %s day, %s hour(s), %s minute(s) and %s seconds.', 'ninjafirewall'), $diff->format('%a') % 7, $diff->format('%h'), $diff->format('%i'), $diff->format('%s') ) ?></p>
							<?php
							}
						}
						?>
					</td>
				</tr>
			<tr>
				<th scope="row" class="row-med"><?php _e('Notification', 'ninjafirewall') ?></th>
				<td>
					<p><label><input type="checkbox" name="notify_updates" value="1"<?php checked($notify_updates, 1) ?> /><?php _e('Send me a report by email when security rules have been updated.', 'ninjafirewall') ?></label></p>
					<p class="description"><?php _e('Reports will be sent to the contact email address defined in the Event Notifications menu.', 'ninjafirewall') ?></p>
				</td>
			</tr>

			<?php
			if (! empty($nfw_options['enable_updates']) || ! empty($tmp_showlog) ) {
				$log_data = array();
				if ( file_exists($update_log) ) {
					$log_data = file($update_log);
				} else {
					$log_data[] = __('The updates log is currently empty.', 'ninjafirewall');
				}
			?>
			<tr>
				<th scope="row" class="row-med"><?php _e('Updates Log', 'ninjafirewall') ?></th>
				<td>
					<textarea class="large-text code" style="height:200px;" wrap="off" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php
					$reversed = array_reverse($log_data);
					$count = 0;
					foreach ($reversed as $key) {
						if ( $key[0] == '<' ) { continue; }
						echo htmlentities($key);
						++$count;
					}
					if ( $count == 0 ) {
						_e('The updates log is currently empty.', 'ninjafirewall');
					}
					?></textarea>
					<p class="description"><?php _e('The log is deleted automatically.', 'ninjafirewall') ?></p>
				</td>
				</tr>
				<?php
			}
			?>
		</table>

	</div>
	<p>
		<input name="nfw_act" type="hidden" value="1" />
		<input name="save_options" type="submit" class="button-primary" value="<?php _e('Save Updates Options', 'ninjafirewall') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
		<input name="check_updates" type="submit" class="button-secondary" value="<?php _e('Check For Updates Now!', 'ninjafirewall') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
		<?php
		if ( empty( $enable_updates ) || ! file_exists( $update_log ) ) {
			$style = ' style="display:none"';
		} else {
			$style = '';
		}
		?>
		<input name="clear_log" type="submit" value="<?php _e('Delete Log', 'ninjafirewall') ?>" class="button-secondary"<?php echo $style ?> />
	</p>
	</form>
<?php

// ---------------------------------------------------------------------

function nf_sub_updates_save() {

	$nfw_options = nfw_get_option('nfw_options');

	if ( empty($_POST['sched_updates']) || ! preg_match('/^[2-3]$/', $_POST['sched_updates']) ) {
		$nfw_options['sched_updates'] = 1;
		$schedtype = 'hourly';
	} else {
		$nfw_options['sched_updates'] = $_POST['sched_updates'];
		if ($nfw_options['sched_updates'] == 2) {
			$schedtype = 'twicedaily';
		} else {
			$schedtype = 'daily';
		}
	}

	if ( empty($_POST['enable_updates']) ) {
		$nfw_options['enable_updates'] = 0;
	} else {
		$nfw_options['enable_updates'] = 1;
	}

	if ( empty($_POST['notify_updates']) ) {
		$nfw_options['notify_updates'] = 0;
	} else {
		$nfw_options['notify_updates'] = 1;
	}

	nfw_update_option('nfw_options', $nfw_options);

	// Recreate cronjobs if needed
	nfw_create_scheduled_tasks('nfsecupdates');

}

// ---------------------------------------------------------------------

function nf_sub_updates_clearlog($update_log) {

	if (file_exists($update_log) ) {
		@file_put_contents( $update_log, "<?php exit; ?>\n", LOCK_EX );
	}

}

// ---------------------------------------------------------------------

function nf_sub_do_updates($update_url, $update_log, $NFUPDATESDO = 1) {

	// Are we installing (2) or updating (3 - deprecated since v3.8) NinjaFirewall ?
	if ( $NFUPDATESDO > 1 ) {
		 return nf_sub_updates_download($update_url, $update_log, 0);
	}

	$nfw_options = nfw_get_option('nfw_options');

	// Don't do anything if NinjaFirewall is disabled :
	if ( empty( $nfw_options['enabled'] ) ) { return 0; }

	if (! $new_rules_version = nf_sub_updates_getversion($update_url, $nfw_options['rules_version'], $update_log) ) {
		// Error or nothing to update :
		return;
	}

	// There is a new version, let's fetch it:
	if (! $data = nf_sub_updates_download($update_url, $update_log, $new_rules_version) ) {
		// Error :
		return;
	}

	// Make sure we received the right format:
	if (! preg_match('/^a:\d+:{i:\d/', $data ) ) {
		nf_sub_updates_log(
			$update_log,
			__('Error: Wrong rules format.', 'ninjafirewall')
		);
		return 0;
	}

	// Unserialize the new rules :
	if (! $new_rules = @unserialize($data) ) {
		nf_sub_updates_log(
			$update_log,
			__('Error: Unable to unserialize the new rules.', 'ninjafirewall')
		);
		return 0;
	}
	// One more check...:
	if (! is_array($new_rules) || empty($new_rules[1]['cha'][1]['whe']) ) {
		nf_sub_updates_log(
			$update_log,
			__('Error: Unserialized rules seem corrupted.', 'ninjafirewall')
		);
		return 0;
	}

	// dropins code:
	if ( isset( $new_rules['dropins'] ) ) {
		if ( $new_rules['dropins'] == 'delete' ) {
			if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php' ) ) {
				@unlink( NFW_LOG_DIR .'/nfwlog/dropins.php' );
			}
		} else {
			$dropins = base64_decode( $new_rules['dropins'], true );
			if ( $dropins !== false ) {
				@file_put_contents( NFW_LOG_DIR .'/nfwlog/dropins.php', $dropins, LOCK_EX );
			}
		}
		unset( $new_rules['dropins'] );
	}

	$nfw_rules = nfw_get_option('nfw_rules');

	foreach ( $new_rules as $new_key => $new_value ) {
		foreach ( $new_value as $key => $value ) {
			// If that rule exists already, we keep its 'ena' flag value
			// as it may have been changed by the user with the rules editor:
			// v3.x:
			if ( ( isset( $nfw_rules[$new_key]['ena'] ) ) && ( $key == 'ena' ) ) {
				$new_rules[$new_key]['ena'] = $nfw_rules[$new_key]['ena'];
			}
			// v1.x:
			if ( ( isset( $nfw_rules[$new_key]['on'] ) ) && ( $key == 'ena' ) ) {
				$new_rules[$new_key]['ena'] = $nfw_rules[$new_key]['on'];
			}
		}
	}
	// v1.x:
	if ( isset( $nfw_rules[NFW_DOC_ROOT]['what'] ) ) {
		$new_rules[NFW_DOC_ROOT]['cha'][1]['wha']= str_replace( '/', '/[./]*', $nfw_rules[NFW_DOC_ROOT]['what'] );
		$new_rules[NFW_DOC_ROOT]['ena']	= $nfw_rules[NFW_DOC_ROOT]['on'];
	// v3.x:
	} else {
		$new_rules[NFW_DOC_ROOT]['cha'][1]['wha']= $nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'];
		$new_rules[NFW_DOC_ROOT]['ena']	= $nfw_rules[NFW_DOC_ROOT]['ena'];
	}

	// NFW_OBJECTS (Block serialized PHP objects): we must keep the
	// value defined by the user in the Firewall Policies page:
	$new_rules[NFW_OBJECTS]['cha'][1]['whe'] = $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'];

	// Update rules in the DB :
	nfw_update_option('nfw_rules', $new_rules);

	// Update rules version in the options table :
	$nfw_options['rules_version'] = $new_rules_version;
	nfw_update_option('nfw_options', $nfw_options);

	nf_sub_updates_log(
		$update_log,
		sprintf( __('Security rules updated to version %s.', 'ninjafirewall'),
		preg_replace('/(\d{4})(\d\d)(\d\d)/', '$1-$2-$3', $new_rules_version) )
	);

	// Email the admin ?
	if (! empty($nfw_options['notify_updates']) ) {
		nf_sub_updates_notification($new_rules_version);
	}
	return 1;
}

// ---------------------------------------------------------------------

function nf_sub_updates_getversion($update_url, $rules_version, $update_log) {

	global $wp_version;
	$res = wp_remote_get(
		$update_url[0] . $update_url[1],
		array(
			'timeout' => 20,
			'httpversion' => '1.1' ,
			'user-agent' => 'Mozilla/5.0 (compatible; NinjaFirewall/'.
									NFW_ENGINE_VERSION .'; WordPress/'. $wp_version . ')',
			'sslverify' => true
		)
	);
	if (! is_wp_error($res) ) {
		if ( $res['response']['code'] == 200 ) {
			// Get the rules version:
			$new_version =  explode('|', rtrim($res['body']), 2);

			// Ensure that the rules are compatible :
			if ( $new_version[0] != 3 ) {
				if (! isset( $new_version[1] ) ) { $new_version[1] = '004'; }
				// This version of NinjaFirewall may be too old :
				nf_sub_updates_log(
					$update_log,
					sprintf( __('Error: %s', 'ninjafirewall'), $new_version[1] )
				);
				return 0;
			}

			if (! preg_match('/^\d{8}\.\d+$/', $new_version[1]) ) {
				// Not what we were expecting:
				nf_sub_updates_log(
					$update_log,
					__('Error: Unable to retrieve the new rules version.', 'ninjafirewall')
				);
				return 0;
			}
			// Compare versions:
			if ( version_compare($rules_version, $new_version[1], '<') ) {
				return $new_version[1];

			} else {
				nf_sub_updates_log(
				$update_log,
				__('No security rules update available.', 'ninjafirewall')
				);
			}
		// Not a 200 OK ret code :
		} else {
			nf_sub_updates_log(
				$update_log,
				sprintf( __('Error: Server returned a %s HTTP error code (#1).', 'ninjafirewall'), htmlspecialchars($res['response']['code']))
			);
		}
	// Connection error :
	} else {
		nf_sub_updates_log(
			$update_log,
			__('Error: Unable to connect to the remote server', 'ninjafirewall') . htmlspecialchars(" ({$res->get_error_message()})")
		);
	}
	return 0;
}

// ---------------------------------------------------------------------

function nf_sub_updates_download($update_url, $update_log, $new_rules_version) {

	global $wp_version;
	$res = wp_remote_get(
		$update_url[0] . $update_url[2],
		array(
			'timeout' => 20,
			'httpversion' => '1.1' ,
			'user-agent' => 'Mozilla/5.0 (compatible; NinjaFirewall/'.
									NFW_ENGINE_VERSION .'; WordPress/'. $wp_version . ')',
			'sslverify' => true
		)
	);
	if (! is_wp_error($res) ) {
		if ( $res['response']['code'] == 200 ) {
			$data = explode('|', rtrim($res['body']), 3);

			// Rules version should match the one we just fetched
			// unless we are intalling NinjaFirewall ($new_rules_version==0) :
			if ( $new_rules_version && $new_rules_version != $data[0]) {
				nf_sub_updates_log(
					$update_log,
					sprintf( __('Error: The new rules versions do not match (%s != %s).', 'ninjafirewall'), $new_rules_version, htmlspecialchars($data[0]))
				);
				return 0;
			}

			// Verify rules digital signature:
			if ( function_exists( 'openssl_pkey_get_public') && function_exists( 'openssl_verify' ) && defined('OPENSSL_ALGO_SHA256') ) {

				$public_key = rtrim( file_get_contents( __DIR__ .'/sign.pub' ) );
				$pubkeyid = openssl_pkey_get_public( $public_key );
				$verify = openssl_verify( $data[2], base64_decode( $data[1] ), $pubkeyid, OPENSSL_ALGO_SHA256);
				if ( $verify != 1 ) {
					nf_sub_updates_log(
						$update_log,
						sprintf( __('Error: The new rules %s digital signature is not correct. Aborting update, rules may have been tampered with.', 'ninjafirewall'), htmlspecialchars($data[0]) )
					);
					return 0;
				}
			}

			// Save new rules version for install/upgrade:
			define('NFW_NEWRULES_VERSION', $data[0]);
			// Return the rules:
			return @$data[2];

		// Not a 200 OK ret code :
		} else {
			nf_sub_updates_log(
				$update_log,
				sprintf( __('Error: Server returned a %s HTTP error code (#2).', 'ninjafirewall'), htmlspecialchars($res['response']['code']))
			);
		}
	// Connection error :
	} else {
		nf_sub_updates_log(
			$update_log,
			__('Error: Unable to connect to the remote server', 'ninjafirewall') . htmlspecialchars(" ({$res->get_error_message()})")
		);
	}
	return 0;
}

// ---------------------------------------------------------------------

function nf_sub_updates_log($update_log, $msg) {

	// If the log is bigger than 50Kb (+/- one month old), we flush it :
	if ( file_exists($update_log) ) {
		$log_stat = stat($update_log);
		if ( $log_stat['size'] > 51200 ) {
			@file_put_contents( $update_log, "<?php exit; ?>\n", LOCK_EX );
		}
	} else {
		@file_put_contents( $update_log, "<?php exit; ?>\n", LOCK_EX );
	}
	@file_put_contents($update_log, date_i18n('[d/M/y:H:i:s O]') . " $msg\n", FILE_APPEND | LOCK_EX);

}

// ---------------------------------------------------------------------

function nf_sub_updates_notification($new_rules_version) {

	$nfw_options = nfw_get_option('nfw_options');

	$subject = __('[NinjaFirewall] Security rules update', 'ninjafirewall');
	$msg = __('NinjaFirewall security rules have been updated:', 'ninjafirewall') . "\n\n";
	if ( is_multisite() ) {
		$msg .=__('Blog:', 'ninjafirewall') .' '. network_home_url('/') . "\n";
	} else {
		$msg .=__('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
	}
	$msg .=__('Rules version:', 'ninjafirewall') .' '. preg_replace('/(\d{4})(\d\d)(\d\d)/', '$1-$2-$3', $new_rules_version) . "\n";
	$msg .= sprintf( __('Date: %s', 'ninjafirewall'), ucfirst(date_i18n('M d, Y @ H:i:s O')) ) . "\n\n" .
			sprintf( __('This notification can be turned off from NinjaFirewall "%s" page.', 'ninjafirewall'), __('Security Rules', 'ninjafirewall') ) ."\n\n" .
			NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

	nfw_mail( $subject, $msg, 'unsubscribe' );
}

// ---------------------------------------------------------------------
// EOF
