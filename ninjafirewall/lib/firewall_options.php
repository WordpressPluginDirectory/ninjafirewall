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

// Block immediately if user is not allowed :
nf_not_allowed( 'block', __LINE__ );

$nfw_options = nfw_get_option( 'nfw_options' );

?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) ?>">&nbsp;<?php _e('Firewall Options', 'ninjafirewall') ?></h1>
<?php

// Saved options ?
if ( isset( $_POST['nfw_options'] ) ) {
	if ( empty( $_POST['nfwnonce'] ) || ! wp_verify_nonce( $_POST['nfwnonce'], 'options_save' ) ) {
		wp_nonce_ays('options_save');
	}
	$res = nf_sub_options_save();
	$nfw_options = nfw_get_option( 'nfw_options' );
	if ($res) {
		echo '<div class="error notice is-dismissible"><p>' . $res . '.</p></div>';
	} else {
		echo '<div class="updated notice is-dismissible"><p>' . __('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	}
}

?>
	<br />
	<form method="post" name="option_form" enctype="multipart/form-data" onsubmit="return nfwjs_save_options();">

	<?php wp_nonce_field('options_save', 'nfwnonce', 0); ?>

	<table class="form-table nfw-table">

		<?php
		if ( empty( $nfw_options['enabled'] ) ) {
			$nfw_options['enabled'] = 0;
		} else {
			$nfw_options['enabled'] = 1;
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('Firewall protection', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'danger', 'nfw_options[enabled]', __('Enabled', 'ninjafirewall'), __('Disabled', 'ninjafirewall'), 'large', $nfw_options['enabled'] ) ?>
			</td>
		</tr>

		<?php
		if ( empty( $nfw_options['debug'] ) ) {
			$nfw_options['debug'] = 0;
		} else {
			$nfw_options['debug'] = 1;
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('Debugging mode', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'warning', 'nfw_options[debug]', __('Yes', 'ninjafirewall'), __('No', 'ninjafirewall'), 'small', $nfw_options['debug'] ) ?>
			</td>
		</tr>

		<?php
		// Get the HTTP error code to return
		if ( empty( $nfw_options['ret_code'] ) || ! preg_match( '/^(?:4(?:0[0346]|18)|50[03])$/', $nfw_options['ret_code'] ) ) {
			$nfw_options['ret_code'] = '403';
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('HTTP error code to return', 'ninjafirewall') ?></th>
			<td>
				<select name="nfw_options[ret_code]">
				<option value="400"<?php selected( $nfw_options['ret_code'], 400 ) ?>><?php _e('400 Bad Request', 'ninjafirewall') ?></option>
				<option value="403"<?php selected( $nfw_options['ret_code'], 403 ) ?>><?php _e('403 Forbidden (default)', 'ninjafirewall') ?></option>
				<option value="404"<?php selected( $nfw_options['ret_code'], 404 ) ?>><?php _e('404 Not Found', 'ninjafirewall') ?></option>
				<option value="406"<?php selected( $nfw_options['ret_code'], 406 ) ?>><?php _e('406 Not Acceptable', 'ninjafirewall') ?></option>
				<option value="418"<?php selected( $nfw_options['ret_code'], 418 ) ?>><?php _e("418 I'm a teapot", 'ninjafirewall') ?></option>
				<option value="500"<?php selected( $nfw_options['ret_code'], 500 ) ?>><?php _e('500 Internal Server Error', 'ninjafirewall') ?></option>
				<option value="503"<?php selected( $nfw_options['ret_code'], 503 ) ?>><?php _e('503 Service Unavailable', 'ninjafirewall') ?></option>
				</select>
			</td>
		</tr>

		<?php
		if ( empty( $nfw_options['anon_ip'] ) ) {
			$nfw_options['anon_ip'] = 0;
		} else {
			$nfw_options['anon_ip'] = 1;
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('IP anonymization', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[anon_ip]', __('Yes', 'ninjafirewall'), __('No', 'ninjafirewall'), 'small', $nfw_options['anon_ip'] ) ?>
				<p class="description"><?php printf( __('Does not apply to private IP addresses and the <a href="%s">Login Protection</a>.', 'ninjafirewall'), '?page=nfsubloginprot' ) ?></p>
			</td>
		</tr>

		<?php
		if (! empty( $nfw_options['blocked_msg'] ) ) {
			$msg = base64_decode( $nfw_options['blocked_msg'] );
		} else {
			$msg = NFW_DEFAULT_MSG;
		}

		$logo_uri = rawurlencode( '<img src="' . plugins_url() . '/ninjafirewall/images/ninjafirewall_75.png" width="75" height="75" />' );
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('Blocked user message', 'ninjafirewall') ?></th>
			<td>
				<textarea id="blocked-msg" name="nfw_options[blocked_msg]" class="large-text code" rows="10" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php echo htmlspecialchars( $msg ) ?></textarea>
				<p class="description"><?php _e('HTML code, including CSS and JS, is allowed.', 'ninjafirewall') ?></p>
				<input type="hidden" id="default-msg" value="<?php echo htmlspecialchars( NFW_DEFAULT_MSG ) ?>" />
				<p><input class="button-secondary" type="button" value="<?php _e('Default message', 'ninjafirewall') ?>" onclick="nfwjs_default_msg();" /></p>
			</td>
		</tr>
	</table>

	<br />
	<br />

	<h3><?php _e('Firewall configuration', 'ninjafirewall') ?></h3>

	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Export configuration', 'ninjafirewall') ?></th>
			<td>
				<input class="button-secondary" type="submit" name="nf_export" value="<?php _e('Download', 'ninjafirewall') ?>" />
				<p class="description"><?php _e( 'File Check configuration will not be exported/imported.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Import configuration', 'ninjafirewall') ?></th>
			<td>
				<input type="file" name="nf_imp" />
				<p class="description"><?php
				list ( $major_current ) = explode( '.', NFW_ENGINE_VERSION );
				printf( __( 'Imported configuration must match plugin version %s.', 'ninjafirewall'), (int) $major_current .'.x' );
				echo '<br />'. __('It will override all your current firewall options and rules.', 'ninjafirewall')
				?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Configuration backup', 'ninjafirewall') ?></th>
			<td><?php echo nf_sub_options_confbackup(); ?></td>
		</tr>
	</table>

	<br />
	<br />

	<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Firewall Options', 'ninjafirewall') ?>" />
	</form>
</div>

<?php

return;

// ---------------------------------------------------------------------

function nf_sub_options_confbackup() {

	$res		= '';
	$dir		= NFW_LOG_DIR .'/nfwlog/cache';
	$files	= NinjaFirewall_helpers::nfw_glob( $dir, 'backup_.+?\.php$', true, true );

	if (! empty( $files[0] ) ) {
		$res .= '<select name="backup_file" onchange="nfwjs_select_backup(this.value)">'.
			'<option selected value="">'.	esc_html__('Available backup files', 'ninjafirewall') .'</option>';
		foreach( $files as $file ) {
			if ( preg_match('`/(backup_(\d{10})_.+\.php)$`', $file, $match ) ) {

				$date = ucfirst( date_i18n('F d, Y @ g:i A', $match[2] ) );
				$size = ' ('. number_format_i18n( filesize( $file ) ) .' '.
							esc_html__('bytes', 'ninjafirewall') .')';
				$res .= '<option value="'. esc_attr( $match[1] ) .'" title="'. esc_attr( $file ) .'">'.
							esc_html( $date . $size ) .'</option>';
			}
		}
		$res .= '</select>';
		$res .= '<p class="description">'. sprintf(
			esc_html__("To restore NinjaFirewall's configuration to an earlier date, select it in ".
				"the list and click '%s'.", 'ninjafirewall'),
			esc_html__('Save Firewall Options', 'ninjafirewall') ) . '</p>';

	} else {
		// No backup files yet
		$res = esc_html__('There are no backup available yet, check back later.', 'ninjafirewall');
	}
	return $res;

}

// ---------------------------------------------------------------------

function nf_sub_options_save() {

	// Save options :

	// Check if we are uploading/importing the configuration... :
	if (! empty($_FILES['nf_imp']['size']) ) {
		return nf_sub_options_import( $_FILES['nf_imp']['tmp_name'] );
	}

	// ...or restoring the configuration to an earlier date and return:
	if (! empty( $_POST['backup_file'] ) && file_exists( NFW_LOG_DIR ."/nfwlog/cache/{$_POST['backup_file']}" ) ) {
		return nf_sub_options_import( NFW_LOG_DIR ."/nfwlog/cache/{$_POST['backup_file']}" );
	}

	$nfw_options = nfw_get_option( 'nfw_options' );

	if ( empty( $_POST['nfw_options']['enabled']) ) {
		if (! empty($nfw_options['enabled']) ) {
			// Alert the admin :
			nf_sub_options_alert(1);
		}
		$nfw_options['enabled'] = 0;

		// Disable brute-force protection
		if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php' ) ) {
			rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php');
		}

	} else {
		$nfw_options['enabled'] = 1;

		// Re-enable brute-force protection
		if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php' ) ) {
			rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php');
		}
	}

	if ( (isset( $_POST['nfw_options']['ret_code'])) &&
		(preg_match( '/^(?:4(?:0[0346]|18)|50[03])$/', $_POST['nfw_options']['ret_code'])) ) {
		$nfw_options['ret_code'] = (int)$_POST['nfw_options']['ret_code'];
	} else {
		$nfw_options['ret_code'] = '403';
	}

	if ( isset( $_POST['nfw_options']['anon_ip'] ) ) {
		$nfw_options['anon_ip'] = 1;
	} else {
		$nfw_options['anon_ip'] = 0;
	}

	if ( empty( $_POST['nfw_options']['blocked_msg']) ) {
		$nfw_options['blocked_msg'] = base64_encode(NFW_DEFAULT_MSG);
	} else {
		$nfw_options['blocked_msg'] = base64_encode(stripslashes($_POST['nfw_options']['blocked_msg']));
	}

	if ( empty( $_POST['nfw_options']['debug']) ) {
		$nfw_options['debug'] = 0;
	} else {
		if ( empty($nfw_options['debug']) ) {
			// Alert the admin :
			nf_sub_options_alert(2);
		}
		$nfw_options['debug'] = 1;
	}

	// Logo
	$nfw_options['logo'] = plugins_url() . '/ninjafirewall/images/ninjafirewall_75.png';
	$nfw_options['logo'] = preg_replace( '/^https?:/', '', $nfw_options['logo'] );

	// Save them :
	nfw_update_option( 'nfw_options', $nfw_options);

	// Update cronjobs
	if ( empty( $nfw_options['enabled'] ) ) {
		nfw_delete_scheduled_tasks();
	} else {
		nfw_create_scheduled_tasks();
	}

}
// ---------------------------------------------------------------------

function nf_sub_options_import( $file ) {

	// Import NF configuration from file :

	$data = file_get_contents( $file );
	$err_msg = __('Uploaded file is either corrupted or its format is not supported (#%s)', 'ninjafirewall');
	if (! $data) {
		return sprintf($err_msg, 1);
	}
	$data = str_replace( '<?php exit; ?>', '', $data );
	// Is it base64-encoded (since 4.3.5)?
	if ( $data[0] == 'B' ) {
		// Decode it
		$data = ltrim( $data, 'B' );
		$data = base64_decode( $data );
	}
	@list ($nfw_options, $rules, $bf) = @explode("\n:-:\n", $data . "\n:-:\n");

	// Detect and remove potential Unicode BOM:
	if ( preg_match( '/^\xef\xbb\xbf/', $nfw_options ) ) {
		$nfw_options = preg_replace( '/^\xef\xbb\xbf/', '', $nfw_options );
	}

	if (! $nfw_options || ! $rules) {
		return sprintf($err_msg, 2);
	}

	$nfw_options = @json_decode( $nfw_options, true );
	$nfw_rules = @json_decode( $rules, true );
	if (! empty( $bf ) ) {
		$bf_conf = json_decode( $bf, true );
	}

	if ( empty($nfw_options['engine_version']) ) {
		return sprintf($err_msg, 3);
	}

	// Make sure the major version numbers match (3.x, 4.x etc):
	list ( $major_current ) = explode( '.', NFW_ENGINE_VERSION );
	list ( $major_import ) = explode( '.', $nfw_options['engine_version'] );
	if ( $major_current != $major_import ) {
		return esc_html__('The imported file is not compatible with that version of NinjaFirewall', 'ninjafirewall');
	}
	if ( $major_import < '4' ) {
		if ( empty( $nfw_options['allow_local_ip'] ) ) {
			$nfw_options['allow_local_ip'] = 1;
		} else {
			$nfw_options['allow_local_ip'] = 0;
		}
	}

	// We cannot import WP+ config :
	if ( isset($nfw_options['shmop']) ) {
		return sprintf($err_msg, 4);
	}

	if ( empty($nfw_rules[1]) ) {
		return sprintf($err_msg, 5);
	}

	// Dropins code:
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

	// Fix paths and directories:
	$nfw_options['logo'] = plugins_url() . '/ninjafirewall/images/ninjafirewall_75.png';
	$nfw_options['logo'] = preg_replace( '/^https?:/', '', $nfw_options['logo'] );

	// We must preserve the previous option, but we still need to adjust
	// the paths because WP_CONTENT_DIR can be user-defined and thus different (e.g., server migration):
	if ( isset( $nfw_options['wp_dir'] ) ) {
		$nfw_options['wp_dir'] = preg_replace( '`(^|\|)/([^/]+)(/\(\?:uploads\|blogs\\\.dir\)/)`', "$1/" .basename(WP_CONTENT_DIR). "$3", $nfw_options['wp_dir'] );
	}

	if (! empty( $_FILES['nf_imp']['tmp_name'] ) && $file == $_FILES['nf_imp']['tmp_name'] ) {
		// We don't import the File Check 'snapshot directory' path
		// (applies to imported configuration, not to restoration of configuration backup):
		$nfw_options['snapdir'] = '';
		$nfw_options['sched_scan'] = '';
	}

	// Check compatibility before importing HSTS headers configration
	// or unset the option :
	if (! function_exists('header_register_callback') || ! function_exists('headers_list') || ! function_exists('header_remove') ) {
		if ( isset($nfw_options['response_headers']) ) {
			unset($nfw_options['response_headers']);
		}
	}

	// If brute force protection is enabled, we need to create a new config file :
	$nfwbfd_log = NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php';
	if (! empty($bf_conf) ) {
		$fh = fopen($nfwbfd_log, 'w');
		fwrite($fh, $bf_conf);
		fclose($fh);
	} else {
	// ...or delete the current one, if any :
		if ( file_exists($nfwbfd_log) ) {
			unlink($nfwbfd_log);
		}
	}
	// Save options :
	nfw_update_option( 'nfw_options', $nfw_options);

	// Add the correct DOCUMENT_ROOT :
	if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', $_SERVER['DOCUMENT_ROOT'] );
	} elseif ( strlen( getenv( 'DOCUMENT_ROOT' ) ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', getenv( 'DOCUMENT_ROOT' ) );
	} else {
		$nfw_rules[NFW_DOC_ROOT]['ena']  = 0;
	}

	// Save rules :
	nfw_update_option( 'nfw_rules', $nfw_rules);

	// Recreate cronjobs if needed
	nfw_create_scheduled_tasks();

	// Alert the admin :
	nf_sub_options_alert(3);

	return;
}

// ---------------------------------------------------------------------

function nf_sub_options_alert( $what ) {

	$nfw_options = nfw_get_option( 'nfw_options' );

	global $current_user;
	$current_user = wp_get_current_user();

	$subject = __('[NinjaFirewall] Alert: Firewall is disabled', 'ninjafirewall');
	if ( is_multisite() ) {
		$url = __('-Blog :', 'ninjafirewall') .' '. network_home_url('/') . "\n\n";
	} else {
		$url = __('-Blog :', 'ninjafirewall') .' '. home_url('/') . "\n\n";
	}
	// Disabled ?
	if ($what == 1) {
		$message = __('Someone disabled NinjaFirewall from your WordPress admin dashboard:', 'ninjafirewall') . "\n\n";
	// Debugging mode :
	} elseif ($what == 2) {
		$message = __('NinjaFirewall is disabled because someone enabled debugging mode from your WordPress admin dashboard:', 'ninjafirewall') . "\n\n";
	// Imported configuration ?
	} elseif ($what == 3) {
		$subject = __('[NinjaFirewall] Alert: Firewall override settings', 'ninjafirewall');
		$message = __('Someone imported a new configuration which overrode the firewall settings:', 'ninjafirewall') . "\n\n";
	} else {
		// Should never reach this line!
		return;
	}

	$message .= __('-User :', 'ninjafirewall') .' '. $current_user->user_login . ' (' . $current_user->roles[0] . ")\n" .
		__('-IP   :', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n" .
		__('-Date :', 'ninjafirewall') .' '. ucfirst( date_i18n('F j, Y @ H:i:s O') ) ."\n" .
		$url .
		NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

	nfw_mail( $subject, $message, 'unsubscribe' );
}

// ---------------------------------------------------------------------
// EOF
