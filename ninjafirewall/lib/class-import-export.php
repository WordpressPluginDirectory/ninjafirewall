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

if ( class_exists('NinjaFirewall_ImpExp') ) {
	return;
}

class NinjaFirewall_ImpExp {

	/**
	 * Import the firewall configuration.
	 */
	public static function import( $file ) {

		/**
		 * Security nonce is checked in the calling function.
		 */

		$err_msg = __('Uploaded file is either corrupted or its format is not supported (#%s)',
			'ninjafirewall');

		$data = file_get_contents( $file );
		if (! $data) {
			return sprintf( $err_msg, 1 );
		}

		$data = str_replace('<?php exit; ?>', '', $data );
		/**
		 * Base64-encoded (since v4.3.5) ?
		 */
		if ( $data[0] == 'B') {
			/**
			 * Decode it.
			 */
			$data = ltrim( $data, 'B');
			$data = base64_decode( $data );
		}
		@ list ( $nfw_options, $rules, $bf ) = @ explode("\n:-:\n", "$data\n:-:\n");

		/**
		 * Remove any potential Unicode BOM.
		 */
		if ( preg_match('/^\xef\xbb\xbf/', $nfw_options ) ) {
			$nfw_options = preg_replace('/^\xef\xbb\xbf/', '', $nfw_options );
		}

		if (! $nfw_options || ! $rules ) {
			return sprintf( $err_msg, 2 );
		}

		$nfw_options = json_decode( $nfw_options, true );
		$nfw_rules   = json_decode( $rules, true );
		if (! empty( $bf ) ) {
			$bf_conf = json_decode( $bf, true );
		}

		if ( empty( $nfw_options['engine_version'] ) ) {
			return sprintf( $err_msg, 3 );
		}

		/**
		 * Make sure the major version numbers match (3.x, 4.x etc).
		 */
		list ( $major_current ) = explode('.', NFW_ENGINE_VERSION );
		list ( $major_import )  = explode('.', $nfw_options['engine_version'] );
		if ( $major_current != $major_import ) {
			return __('The imported file is not compatible with that version of NinjaFirewall',
				'ninjafirewall');
		}
		if ( $major_import < '4' ) {
			if ( empty( $nfw_options['allow_local_ip'] ) ) {
				$nfw_options['allow_local_ip'] = 1;
			} else {
				$nfw_options['allow_local_ip'] = 0;
			}
		}

		/**
		 * We cannot import WP+ config.
		 */
		if ( isset( $nfw_options['shmop'] ) ) {
			return sprintf( $err_msg, 4 );
		}

		if ( empty( $nfw_rules[1] ) ) {
			return sprintf( $err_msg, 5 );
		}

		/**
		 * Dropins rules.
		 */
		if ( isset( $nfw_rules['dropins'] ) ) {
			if ( $nfw_rules['dropins'] == 'delete') {
				if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php') ) {
					@ unlink( NFW_LOG_DIR .'/nfwlog/dropins.php');
				}
			} else {
				$dropins = base64_decode( $nfw_rules['dropins'], true );
				if ( $dropins !== false ) {
					@ file_put_contents( NFW_LOG_DIR .'/nfwlog/dropins.php', $dropins, LOCK_EX );
				}
			}
			unset( $nfw_rules['dropins'] );
		}

		/**
		 * Fix paths and directories.
		 */
		$nfw_options['logo'] = plugins_url('images/ninjafirewall_75.png', dirname( __FILE__ ) );
		$nfw_options['logo'] = preg_replace('/^https?:/', '', $nfw_options['logo'] );

		/**
		 * We must preserve the previous option, but we still need to adjust
		 * the paths because WP_CONTENT_DIR can be user-defined and thus different
		 * (e.g., server migration).
		 */
		if ( isset( $nfw_options['wp_dir'] ) ) {

			$nfw_options['wp_dir'] = preg_replace(
				'`(^|\|)/([^/]+)(/\(\?:uploads\|blogs\\\.dir\)/)`',
				"$1/" .basename(WP_CONTENT_DIR). "$3",
				$nfw_options['wp_dir']
			);
		}

		if (! empty( $_FILES['ninjafirewall_import']['tmp_name'] ) &&
			$file == $_FILES['ninjafirewall_import']['tmp_name'] ) {

			/**
			 * We don't import the File Check 'snapshot directory' path
			 * (applies to imported configuration, not to restoration of configuration backup).
			 */
			$nfw_options['snapdir']    = '';
			$nfw_options['sched_scan'] = '';
		}

		/**
		 * Check compatibility before importing HSTS headers configuration or unset the option.
		 */
		if (! function_exists('header_register_callback') ||
			! function_exists('headers_list') || ! function_exists('header_remove') ) {

			if ( isset( $nfw_options['response_headers'] ) ) {
				unset( $nfw_options['response_headers'] );
			}
		}

		/**
		 * If brute force protection is enabled, we need to create a new config file.
		 */
		$nfwbfd_log = NFW_LOG_DIR .'/nfwlog/cache/bf_conf.php';
		if (! empty( $bf_conf ) ) {
			$fh = fopen( $nfwbfd_log, 'w');
			fwrite( $fh, $bf_conf );
			fclose( $fh );
		} else {
		/*
		 * ...or delete the current one, if any.
		 */
			if ( file_exists( $nfwbfd_log ) ) {
				unlink( $nfwbfd_log );
			}
		}

		/**
		 * Save options.
		 */
		nfw_update_option('nfw_options', $nfw_options );

		/**
		 * Add the correct DOCUMENT_ROOT.
		 */
		if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) > 5 ) {
			$nfw_rules[ NFW_DOC_ROOT ]['cha'][1]['wha'] =
				str_replace('/', '/[./]*', $_SERVER['DOCUMENT_ROOT'] );
		} elseif ( strlen( getenv( 'DOCUMENT_ROOT' ) ) > 5 ) {
			$nfw_rules[ NFW_DOC_ROOT ]['cha'][1]['wha'] =
				str_replace('/', '/[./]*', getenv( 'DOCUMENT_ROOT' ) );
		} else {
			$nfw_rules[ NFW_DOC_ROOT ]['ena'] = 0;
		}

		/**
		 * Save rules.
		 */
		nfw_update_option('nfw_rules', $nfw_rules );

		/**
		 * Recreate cron events if needed.
		 */
		nfw_create_scheduled_tasks();

		/**
		 * Alert the admin about the changes.
		 */
		self::email_admin('fw_override');

		return;
	}


	/**
	 * Export the firewall configuration.
	 */
	public static function export( $file = '') {

		$nfw_options = nfw_get_option('nfw_options');
		$nfw_rules   = nfw_get_option('nfw_rules');

		/**
		 * Check nonce.
		 */
		if ( empty( $_POST['nfwnonce'] ) ||
			! wp_verify_nonce( $_POST['nfwnonce'], 'options_save') ) {

			wp_nonce_ays('options_save');
		}

		/**
		 * Export login protection if it exists too.
		 */
		$nfwbfd_log = NFW_LOG_DIR .'/nfwlog/cache/bf_conf.php';
		if ( file_exists( $nfwbfd_log ) ) {
			$bd_data = json_encode( file_get_contents( $nfwbfd_log ) );
		} else {
			$bd_data = '';
		}

		/**
		 * Dropins, if applicable.
		 */
		if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php') ) {
			$nfw_rules['dropins'] = base64_encode(
				file_get_contents( NFW_LOG_DIR .'/nfwlog/dropins.php')
			);
		}
		$data = json_encode( $nfw_options ) ."\n:-:\n". json_encode( $nfw_rules ) ."\n:-:\n$bd_data";

		/**
		 * Download or save to disk (WP CLI).
		 */
		if (! defined('WP_CLI') ) {
			header('Content-Type: text/plain');
			header('Content-Length: '. strlen( $data ) );
			header('Content-Disposition: attachment; filename="nfwp.'. NFW_ENGINE_VERSION .'.dat"');
			echo $data;

			exit;
		}

		return file_put_contents( $file, $data );
	}


	/**
	 * Alert the admin if needed.
	 */
	public static function email_admin( $template ) {

		global $current_user;
		$current_user = wp_get_current_user();

		/**
		 * Home URL.
		 */
		if ( is_multisite() ) {
			$url = network_home_url('/');
		} else {
			$url = home_url('/');
		}

		/**
		 * Template to load.
		 */
		if ( $template != 'disabled' && $template != 'debugging') {
			$template = 'fw_override';
		}

		/**
		 * Email notification.
		 */
		$subject = [ ];
		$content = [ "{$current_user->user_login} ({$current_user->roles[0]})",
						NFW_REMOTE_ADDR, ucfirst( date_i18n('F j, Y @ H:i:s O') ), $url ];

		NinjaFirewall_mail::send( $template, $subject, $content, '', [], 1 );

	}
}

// =====================================================================
// EOF
