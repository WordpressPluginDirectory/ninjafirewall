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

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// Block immediately if user is not allowed :
nf_not_allowed( 'block', __LINE__ );

$nfw_options = nfw_get_option( 'nfw_options' );

/**
 * Import, export and backup restoration class.
 */
require_once __DIR__ .'/class-import-export.php';

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
	nfw_contextual_help();
?>

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
				<input class="button-secondary" type="submit" name="ninjafirewall_export" value="<?php _e('Download', 'ninjafirewall') ?>" />
				<p class="description"><?php _e( 'File Check configuration will not be exported/imported.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Import configuration', 'ninjafirewall') ?></th>
			<td>
				<input type="file" name="ninjafirewall_import" />
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

	/**
	 * Save options.
	 */

	$nfw_options = nfw_get_option('nfw_options');

	/**
	 * Check if we are uploading/importing the configuration.
	 */
	if (! empty( $_FILES['ninjafirewall_import']['size'] ) ) {
		if ( empty( $_FILES['ninjafirewall_import']['tmp_name'] ) ||
			! is_uploaded_file( $_FILES['ninjafirewall_import']['tmp_name'] ) ) {

			return esc_html__('Invalid configuration upload', 'ninjafirewall');
		}
		return NinjaFirewall_ImpExp::import( $_FILES['ninjafirewall_import']['tmp_name'] );
	}
	/**
	 * Or restoring the configuration to an earlier date and return.
	 */
	$backup_file = '';
	if (! empty( $_POST['backup_file'] ) ) {
		$backup_file = basename( wp_unslash( $_POST['backup_file'] ) );
	}
	if ( $backup_file && preg_match( '/^backup_\d{10}_.+\.php$/', $backup_file ) &&
		file_exists( NFW_LOG_DIR . "/nfwlog/cache/$backup_file" ) ) {

		return NinjaFirewall_ImpExp::import( NFW_LOG_DIR . "/nfwlog/cache/$backup_file" );
	}

	if ( empty( $_POST['nfw_options']['enabled']) ) {
		if (! empty($nfw_options['enabled']) ) {
			// Alert the admin :
			NinjaFirewall_ImpExp::email_admin('disabled');
		}
		$nfw_options['enabled'] = 0;

		// Disable brute-force protection
		if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php') ) {
			rename(NFW_LOG_DIR .'/nfwlog/cache/bf_conf.php', NFW_LOG_DIR .'/nfwlog/cache/bf_conf_off.php');
		}

	} else {
		$nfw_options['enabled'] = 1;

		// Re-enable brute-force protection
		if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php') ) {
			rename(NFW_LOG_DIR .'/nfwlog/cache/bf_conf_off.php', NFW_LOG_DIR .'/nfwlog/cache/bf_conf.php');
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
			NinjaFirewall_ImpExp::email_admin('debugging');
		}
		$nfw_options['debug'] = 1;
	}

	/**
	 * Logo.
	 */
	$nfw_options['logo'] = plugins_url('images/ninjafirewall_75.png', dirname( __FILE__ ) );
	$nfw_options['logo'] = preg_replace('/^https?:/', '', $nfw_options['logo'] );

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
// EOF
