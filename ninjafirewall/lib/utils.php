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
 +---------------------------------------------------------------------+
*/

if (! defined('NFW_ENGINE_VERSION') ) { die('Forbidden'); }

// --------------------------------------------------------------------- 2023-07-27
// The name of the MU plugin can be defined in wp-config.php.

if (! defined('NINJAFIREWALL_MU_PLUGIN') ) {
	define('NINJAFIREWALL_MU_PLUGIN', '0-ninjafirewall.php');
} else {
	// If defined and different, make sure to delete the old one
	if ( NINJAFIREWALL_MU_PLUGIN != '0-ninjafirewall.php' &&
		file_exists( WPMU_PLUGIN_DIR .'/0-ninjafirewall.php') ) {

		unlink( WPMU_PLUGIN_DIR .'/0-ninjafirewall.php');
	}
}

// --------------------------------------------------------------------- 2023-07-27
// Animated button/switch.

function nfw_toggle_switch( $type, $name, $text_on, $text_off,
	$size, $value = 0,	$disabled = false, $attr = false,
	$id = false, $align = false ) {

	if ( $size == 'large') {
		$size = 'style="width:150px;"';

	} elseif ( $size == 'small') {
		$size = 'style="width:80px"';

	} else {
		$size = 'style="width:'. (int) $size .'px"';
	}

	if ( $type == 'danger') {
		$type = 'tgl-danger';
	} elseif ( $type == 'warning') {
		$type = 'tgl-warning';
	} elseif ( $type == 'green') {
		$type = 'tgl-green';
	} else {
		$type = 'tgl-info';
	}

	$text_on		= esc_attr( $text_on );
	$text_off	= esc_attr( $text_off );

	if ( $id == false ) {
		$id = uniqid();
	}

	if ( $disabled == false ) {
		$disabled = '';
	} else {
		$disabled = ' disabled';
	}
	if ( $attr != false ) {
		$attr = ' '. $attr;
	}

	if ( $align == false ) {
		$align = '';
	} elseif ( $align == 'left') {
		$align = ' alignleft';
	} else {
		$align = ' alignright';
	}
	?>
	<div class="tg-list-item<?php echo $align ?>">
		<input class="tgl tgl-switch" name="<?php echo $name ?>"<?php checked( $value, 1 ) ?> id="<?php echo $id ?>" type="checkbox"<?php echo $disabled; ?><?php echo $attr ?> />
		<label class="tgl-btn <?php echo $type ?>"<?php nfw_aria_label( $value, 1, $text_on, $text_off ) ?> data-tg-on="<?php echo $text_on ?>" data-tg-off="<?php echo $text_off ?>" for="<?php echo $id ?>" <?php echo $size ?>></label>
	</div>
	<?php
}

// ---------------------------------------------------------------------
// Check for HTTPS. This function is also available in firewall.php
// and is used here only if the firewall is not loaded.

if (! function_exists('nfw_is_https') ) {

	function nfw_is_https() {
		// Can be defined in the .htninja:
		if ( defined('NFW_IS_HTTPS') ) { return; }

		if ( ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] == 443 ) ||
			( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||
			( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off') ) {
			define('NFW_IS_HTTPS', true);
		} else {
			define('NFW_IS_HTTPS', false);
		}
	}
}
nfw_is_https();

// ---------------------------------------------------------------------
// Check whether the user is whitelisted (.htninja etc).

function nfw_is_whitelisted() {

	if ( defined('NFW_UWL') && NFW_UWL == true ) {
		return true;
	}
}

// ---------------------------------------------------------------------

add_filter('wp_insert_post_empty_content', 'nf_wp_insert_post_empty_content', 10000, 2 );

function nf_wp_insert_post_empty_content( $maybe_empty, $postarr ) {

	$nfw_options = nfw_get_option('nfw_options');

	if (! empty( NinjaFirewall_session::read('nfw_goodguy') ) || nfw_is_whitelisted() ||
		empty( $nfw_options['enabled'] ) || empty( $nfw_options['disallow_publish'] ) ) {

		return false;
	}

	// We only care about page and post post_type:
	if (! empty( $postarr['post_type'] ) && ( $postarr['post_type'] == 'post' || $postarr['post_type'] == 'page') ) {

		if (! isset( $postarr['ID'] ) ) {
			$id = 0;
		} else {
			$id = $postarr['ID'];
		}

		// Ignore post if it isn't either already published or set to be published immediately:
		if ( get_post_status( $id ) != 'publish' && ( empty( $postarr['post_status'] ) || $postarr['post_status'] != 'publish') ) {
			return false;
		}

		// Ignore empty post whose ID is 0, including issue with the Quick Draft widget (#2140)
		if ( empty( $id ) && empty( $postarr['post_content'] ) ) {
			return false;
		}

		$old_post = get_post( $id );
		if ( $old_post->post_title == $postarr['post_title'] &&  $old_post->post_content == $postarr['post_content'] ) {
			return false;
		}

		// We must use meta capability (edit_post/edit_page), not capability (edit_postS/edit_pageS).
		$edit_post = "edit_{$postarr['post_type']}";
		if ( current_user_can( $edit_post, $id ) ) {
			return false;
		}

		if (! empty( $postarr['post_title'] ) ) {
			$post_title = $postarr['post_title'];
		} else {
			$post_title = __('N/A', 'ninjafirewall');
		}
		if (! empty( $postarr['post_content'] ) ) {
			if ( strlen( $postarr['post_content'] ) > 100 ) {
				$postarr['post_content'] = mb_substr( $postarr['post_content'], 0, 100, 'utf-8') . '...';
			}
			$post_content = $postarr['post_content'];
		} else {
			$post_content = __('N/A', 'ninjafirewall');
		}

		// Page or post creation:
		if ( empty( $id ) ) {
			/* Translators : "page" or "post" type */
			$action = sprintf( __('Attempt to create a new %s', 'ninjafirewall'), $postarr['post_type'] );
		// Edition:
		} else {
			/* Translators : "page" or "post" type and its numerical ID */
			$action = sprintf( __('Attempt to edit a published %s (ID: %s)', 'ninjafirewall'), $postarr['post_type'], $id );
		}

		// Check if user is authenticated
		$current_user = wp_get_current_user();
		if ( empty( $current_user->user_login ) ) {
			$user = __('Unauthenticated user', 'ninjafirewall');
		} else {
			$user = $current_user->user_login;
		}

		$subject = __('Blocked post/page edition attempt', 'ninjafirewall');
		nfw_log2('WordPress: ' . $subject, "post_content: $post_content", 3, 0);

		// Alert the admin:
		$subject = '[NinjaFirewall] ' . $subject;
		$message = __('NinjaFirewall has blocked an attempt to edit/create a post by a user who doesn\'t have the right capabilities:', 'ninjafirewall') . "\n\n";
		$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
		$message.= __('Username:', 'ninjafirewall') .' '. "$user\n";
		$message.= __('Action:', 'ninjafirewall') .' '. "$action\n";
		$message.= "post_title: $post_title\n";
		$message.= "post_content: $post_content\n";
		$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
		$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
		$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
		$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

		// Attach PHP backtrace
		$return  = nfw_debug_backtrace( $nfw_options );
		$message.= $return['message'];
		$message.= __('This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall') . "\n\n";
		$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC ."\n";
		if ( empty( $return['nftmpfname'] ) ) {
			nfw_mail( $subject, $message, 'unsubscribe');

		} else {
			// Attach backtrace and delete temp file:
			nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
			unlink( $return['nftmpfname'] );
		}

		// Block it
		NinjaFirewall_session::delete();
		wp_die(
			'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
			'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
			$nfw_options['ret_code']
		);
	}
	return false;
}

// ---------------------------------------------------------------------

add_filter('pre_delete_post', 'nf_pre_delete_post', 10000, 3 );

function nf_pre_delete_post( $delete, $post, $force_delete ) {

	$nfw_options = nfw_get_option('nfw_options');

	if (! empty( NinjaFirewall_session::read('nfw_goodguy') ) || nfw_is_whitelisted() ||
		empty( $nfw_options['enabled'] ) || empty( $nfw_options['disallow_publish'] ) ) {

		return null;
	}

	if (! isset( $post->post_type ) || ! isset( $post->post_status ) || empty( $post->ID ) ) {
		return null;
	}
	if ( ( $post->post_type == 'post' || $post->post_type == 'page') && $post->post_status == 'publish') {
		if (! current_user_can( "delete_{$post->post_type}", $post->ID ) ) {

			// Check if user is authenticated
			$current_user = wp_get_current_user();
			if ( empty( $current_user->user_login ) ) {
				$user = __('Unauthenticated user', 'ninjafirewall');
			} else {
				$user = $current_user->user_login;
			}

			if (! empty( $post->post_title ) ) {
				$post_title = $post->post_title;
			} else {
				$post_title = __('N/A', 'ninjafirewall');
			}

			$subject = __('Blocked post/page deletion attempt', 'ninjafirewall');
			nfw_log2('WordPress: ' . $subject, "post ID: {$post->ID}", 3, 0);

			// Alert the admin:
			$subject = '[NinjaFirewall] ' . $subject;
			$message = __('NinjaFirewall has blocked an attempt to delete a post by a user who doesn\'t have the right capabilities:', 'ninjafirewall') . "\n\n";
			$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
			$message.= __('Username:', 'ninjafirewall') .' '. "$user\n";
			$message.= "post ID: {$post->ID}\n";
			$message.= "post_title: $post_title\n";
			$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
			$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
			$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
			$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

			// Attach PHP backtrace
			$return  = nfw_debug_backtrace( $nfw_options );
			$message.= $return['message'];
			$message.= __('This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall') . "\n\n";
			$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC ."\n";
			if ( empty( $return['nftmpfname'] ) ) {
				nfw_mail( $subject, $message, 'unsubscribe');

			} else {
				// Attach backtrace and delete temp file:
				nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
				unlink( $return['nftmpfname'] );
			}

			// Block it
			NinjaFirewall_session::delete();
			wp_die(
				'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
				'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
				$nfw_options['ret_code']
			);
		}
	}
	return null;
}

// ---------------------------------------------------------------------
// Write session to disk to prevent cURL time-out which may occur with
// WordPress (since 4.9.2, see https://core.trac.wordpress.org/ticket/43358),
// or plugins such as "Health Check".

add_filter('pre_http_request', 'nf_pre_http_request', 10, 3 );

function nf_pre_http_request( $preempt, $r, $url ) {

	// NFW_DISABLE_SWC can be defined in wp-config.php (undocumented):
	if (! defined('NFW_DISABLE_SWC') && isset( $_SESSION ) ) {
		if ( function_exists('get_site_url') ) {
			$parse = parse_url( get_site_url() );
			$s_url = @$parse['scheme'] . "://{$parse['host']}";
			if ( strpos( $url, $s_url ) === 0 ) {
				@session_write_close();
			}
		}
	}
	return false;
}

// ---------------------------------------------------------------------
// Return backtrace verbosity.

function nfw_verbosity( $nfw_options ) {

	if (! isset( $nfw_options['a_61'] ) || $nfw_options['a_61'] == 1 ) {
		// Medium verbosity:
		return 0;

	} elseif ( $nfw_options['a_61'] == -1 ) {
		// Disabled:
		return false;

	} elseif ( $nfw_options['a_61'] == 2 ) {
		// High verbosity:
		return  1;
	}

	// Low verbosity:
	return 2;
}

// --------------------------------------------------------------------- 2023-07-26
// Prevent account deletion.

function nfw_delete_user( $user_id ) {

	$nfw_options	= nfw_get_option('nfw_options');
	$user_data		= get_userdata( $user_id );

	if ( current_user_can('delete_users') || empty( $nfw_options['disallow_deletion'] ) ||
		empty( $nfw_options['enabled'] ) ) {

		// Log and allow the request
		nfw_log2('Deleting user', "User: {$user_data->user_login}, ID: $user_id", 6, 0 );
		return;
	}

	$subject = __('Blocked user deletion attempt', 'ninjafirewall');

	nfw_log2('WordPress: ' . $subject, "User: {$user_data->user_login}, ID: $user_id", 3, 0 );

	// Alert the admin:
	$subject = '[NinjaFirewall] ' . $subject;
	$message = __('NinjaFirewall has blocked an attempt to delete a user account by a user who '.
					'doesn\'t have the right capabilities:', 'ninjafirewall') . "\n\n";
	$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
	$message.= __('User to delete:', 'ninjafirewall') .' '.
					"{$user_data->user_login} (ID: $user_id)\n";
	$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
	$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
	$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
	$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

	// Attach PHP backtrace
	$return  = nfw_debug_backtrace( $nfw_options );
	$message.= $return['message'];
	$message.= __('This protection (and notification) can be turned off from NinjaFirewall '.
					'"Firewall Policies" page.', 'ninjafirewall') . "\n\n";
	$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC ."\n";
	if ( empty( $return['nftmpfname'] ) ) {
		nfw_mail( $subject, $message, 'unsubscribe');

	} else {
		// Attach backtrace and delete temp file:
		nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
		unlink( $return['nftmpfname'] );
	}

	// Block it
	NinjaFirewall_session::delete();
	wp_die(
		'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
		'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
		$nfw_options['ret_code']
	);
}

add_action('delete_user', 'nfw_delete_user');

// ---------------------------------------------------------------------
// Allow/disallow account creation.

function nfw_account_creation( $user_login ) {

	$nfw_options = nfw_get_option('nfw_options');

	// We must allow the request if the username exists too, otherwise we'll
	// block them from using the "Lost password" feature:
	if ( current_user_can('create_users') || empty( $nfw_options['disallow_creation'] ) ||
		empty( $nfw_options['enabled'] ) || username_exists( $user_login ) ) {

		// Do nothing:
		return $user_login;
	}

	$subject = __('Blocked user account creation', 'ninjafirewall');
	nfw_log2( "WordPress: {$subject}", "Username: {$user_login}", 3, 0);

	// Alert the admin:
	$subject = '[NinjaFirewall] ' . $subject;
	$message = __('NinjaFirewall has blocked an attempt to create a user account:', 'ninjafirewall') . "\n\n";
	// Show current blog, not main site (multisite):
	$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
	$message.= __('Username:', 'ninjafirewall') ." {$user_login} (blocked)\n";
	$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
	$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
	$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
	$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

	// Attach PHP backtrace
	$return  = nfw_debug_backtrace( $nfw_options );
	$message.= $return['message'];
	$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

	if ( empty( $return['nftmpfname'] ) ) {
		nfw_mail( $subject, $message, 'unsubscribe');

	} else {
		// Attach backtrace and delete temp file:
		nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
		unlink( $return['nftmpfname'] );
	}

	// Block it
	NinjaFirewall_session::delete();
	wp_die(
		'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
		'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
		$nfw_options['ret_code']
	);

}

add_filter('pre_user_login' , 'nfw_account_creation');

// ---------------------------------------------------------------------
// Clean/delete cache folder & temp files (hourly cron job).

function nfw_garbage_collector() {

	$path = NFW_LOG_DIR .'/nfwlog/cache';
	$now = time();
	// Make sure the cache folder exists, i.e, we have been
	// through the whole installation process
	if (! is_dir( $path ) ) {
		return;
	}

	// Don't do anything if the garbage collector was executed less than 45mn ago
	$gc = $path .'/garbage_collector.php';
	if ( file_exists( $gc ) ) {
		$nfw_mtime = filemtime( $gc ) ;
		if ( $now - $nfw_mtime < 45*60 ) {
			return;
		}
		unlink( $gc );
	}
	touch( $gc );

	// Fetch options
	$nfw_options = nfw_get_option('nfw_options');

	// ------------------------------------------------------------------
	// If nfw_options is corrupted (e.g., failed update etc) we try to restore it
	// from a backup file otherwise we restore it from the default settings.
	if ( nfw_validate_option( $nfw_options ) === false ) {

		$files			= NinjaFirewall_helpers::nfw_glob( $path, 'backup_.+?\.php$', true, true );
		$valid_option	= 0;

		// Make sure we have a backup file
		while ( is_array( $files ) && ! empty( $files[0] ) ) {
			$content = [];
			$last_file = array_pop( $files );
			$data = file_get_contents( $last_file );
			$data = str_replace('<?php exit; ?>', '', $data );
			// Is it base64-encoded (since 4.3.5)?
			if ( $data[0] == 'B') {
				// Decode it
				$data = ltrim( $data, 'B');
				$data = base64_decode( $data );
			}
			$content		= @explode("\n:-:\n", $data . "\n:-:\n");
			$content[0]	= json_decode( $content[0], true );

			if ( nfw_validate_option( $content[0] ) === true ) {
				// We can use that backup to restore our options
				$valid_option = 1;
				break;

			// Delete this corrupted backup file
			} else {
				nfw_log_error(
					sprintf(__('Backup file is corrupted, deleting it (%s)','ninjafirewall'), $last_file )
				);
				unlink( $last_file );
			}
		}

		// Restore the last good backup
		if (! empty( $valid_option ) ) {
			nfw_update_option('nfw_options', $content[0] );
			nfw_log_error( sprintf( __('NinjaFirewall\'s options are corrupted, restoring them from '.
				'last known good backup file (%s)', 'ninjafirewall'), $last_file ) );

		// Restore the default settings if no backup file was found
		// (this action will also restore the firewall rules)
		} else {
			require_once __DIR__ .'/install_default.php';
			nfw_log_error( __('NinjaFirewall\'s options are corrupted, restoring their default values '.
				'(no valid backup found)', 'ninjafirewall') );
			nfw_load_default_conf();
		}

		$nfw_options = nfw_get_option('nfw_options');
	}

	// ------------------------------------------------------------------

	// Check if we must delete old firewall logs
	if (! empty( $nfw_options['auto_del_log'] ) ) {
		$auto_del_log = (int) $nfw_options['auto_del_log'] * 86400;

		// Retrieve the list of all logs
		$list = NinjaFirewall_helpers::nfw_glob(
			NFW_LOG_DIR .'/nfwlog', 'firewall_.+?\.php$', true, true
		);

		foreach( $list as $file ) {
			$lines = [];
			$lines = file( $file, FILE_SKIP_EMPTY_LINES );
			foreach( $lines as $k => $line ) {
				if ( preg_match('/^\[(\d{10})\]/', $line, $match ) ) {
					if ( $now - $auto_del_log > $match[1] ) {
						// This line is too old, remove it
						unset( $lines[ $k ] );
					}
				} else {
					// Not a proper firewall log line
					unset( $lines[ $k ] );
				}
			}
			if ( empty( $lines ) ) {
				// No lines left, delete the file
				unlink( $file );
			} else {
				// Save the last preserved lines to the log
				$fh = fopen( $file, 'w');
				fwrite( $fh, "<?php exit; ?>\n" );
				foreach( $lines as $line ) {
					fwrite( $fh, $line );
				}
				fclose( $fh );
			}
		}
	}

	// File Guard temp files
	$list = NinjaFirewall_helpers::nfw_glob( $path, 'fg_.+?\.php$', true, true );
	foreach( $list as $file ) {
		$nfw_ctime = filectime( $file );
		// Delete it, if it is too old
		if ( $now - $nfw_options['fg_mtime'] * 3660 > $nfw_ctime ) {
			unlink( $file );
		}
	}

	/**
	 * Remove older session files if they were untouched for 24mn (1440 sec).
	 * Note: NFWSESS_MAXLIFETIME can be defined in the wp-config.php or .htninja.
	 */
	if ( defined('NFWSESSION_DIR') ) {
		if (! defined('NFWSESS_MAXLIFETIME') ) {
			define('NFWSESS_MAXLIFETIME', 1440);
		}
		$list = NinjaFirewall_helpers::nfw_glob( NFWSESSION_DIR, '^sess_', true, true );
		foreach( $list as $file ) {
			$sess_time = filemtime( $file );
			if ( $sess_time + NFWSESS_MAXLIFETIME < $now ) {
				wp_delete_file( $file );
			}
		}
	}

	// Live Log
	$nfw_livelogrun = $path . '/livelogrun.php';
	if ( file_exists( $nfw_livelogrun ) ) {
		$nfw_mtime = filemtime( $nfw_livelogrun );
		// If the file was not accessed for more than 100s, we assume
		// the admin has stopped using live log from WordPress
		// dashboard (refresh rate is max 45 seconds)
		if ( $now - $nfw_mtime > 100 ) {
			unlink( $nfw_livelogrun );
		}
	}
	// If the log was not modified for the past 10mn, we delete it as well
	$nfw_livelog = $path . '/livelog.php';
	if ( file_exists( $nfw_livelog ) ) {
		$nfw_mtime = filemtime( $nfw_livelog ) ;
		if ( $now - $nfw_mtime > 600 ) {
			unlink( $nfw_livelog );
		}
	}

	// ------------------------------------------------------------------

	// NinjaFirewall's configuration backup. We create a new one daily
	$list = NinjaFirewall_helpers::nfw_glob( $path, 'backup_.+?\.php$', true, true );
	if (! empty( $list[0] ) ) {
		rsort( $list );
		// Check if last backup if older than one day
		if ( preg_match('`/backup_(\d{10})_.+\.php$`', $list[0], $match ) ) {
			if ( $now - $match[1] > 86400 ) {
				// Backup the configuration
				$nfw_rules = nfw_get_option('nfw_rules');
				if ( file_exists( $path .'/bf_conf.php') ) {
					$bd_data = json_encode( file_get_contents( $path .'/bf_conf.php') );
				} else {
					$bd_data = '';
				}
				$data = json_encode( $nfw_options ) ."\n:-:\n". json_encode($nfw_rules) ."\n:-:\n". $bd_data;
				$file = uniqid('backup_'. time() .'_', true) . '.php';
				// Since version 4.3.5, we base64-encode the data because
				// some hosts flag it as malicious
				@file_put_contents( "$path/$file", '<?php exit; ?>B' . base64_encode( $data ), LOCK_EX );
				array_unshift( $list, "$path/$file" );
			}
		}
		// Keep the last 5 backup only (value can be defined
		// in the wp-config.php)
		if ( defined('NFW_MAX_BACKUP') ) {
			$num = (int) NFW_MAX_BACKUP;
		} else {
			$num = 5;
		}
		$old_backup = array_slice( $list, $num );
		foreach( $old_backup as $file ) {
			unlink( $file );
		}
	} else {
		// Create first backup
		$nfw_rules = nfw_get_option('nfw_rules');
		if ( empty( $nfw_rules ) ) {
			return;
		}
		if ( file_exists( $path .'/bf_conf.php') ) {
			$bd_data = json_encode( file_get_contents( $path .'/bf_conf.php') );
		} else {
			$bd_data = '';
		}
		$data = json_encode( $nfw_options ) ."\n:-:\n". json_encode( $nfw_rules ) ."\n:-:\n". $bd_data;
		$file = uniqid('backup_'. time() .'_', true ) .'.php';
		// Since version 4.3.5, we base64-encode the data because
		// some hosts flag it as malicious
		@file_put_contents( "$path/$file", '<?php exit; ?>B' . base64_encode( $data ), LOCK_EX );
	}

	// ------------------------------------------------------------------
	// Security updates
	$nfw_fetchsecupdates = get_transient('nfw_fetchsecupdates');
	if ( $nfw_fetchsecupdates === false ) {
		require __DIR__ .'/event_updates.php';
		nfw_check_security_updates();
	}
}

// ---------------------------------------------------------------------
// Write potential errors to a specific log.

function nfw_log_error( $message ) {

	$log = NFW_LOG_DIR . '/nfwlog/error_log.php';

	if (! file_exists( $log ) ) {
		@file_put_contents( $log, "<?php exit; ?>\n", LOCK_EX );
	}
	@file_put_contents( $log, date('[d/M/y:H:i:s O]') . " $message\n", FILE_APPEND | LOCK_EX );

}

// ---------------------------------------------------------------------

function nfw_select_ip() {
	// Ensure we have a proper and single IP (a user may use the .htninja file
	// to redirect HTTP_X_FORWARDED_FOR, which may contain more than one IP,
	// to REMOTE_ADDR):
	if (! isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}
	if (strpos($_SERVER['REMOTE_ADDR'], ',') !== false) {
		$nfw_match = array_map('trim', @explode(',', $_SERVER['REMOTE_ADDR']));
		foreach($nfw_match as $nfw_m) {
			if ( filter_var($nfw_m, FILTER_VALIDATE_IP) )  {
				define('NFW_REMOTE_ADDR', $nfw_m);
				break;
			}
		}
	}
	if (! defined('NFW_REMOTE_ADDR') ) {
		define('NFW_REMOTE_ADDR', htmlspecialchars($_SERVER['REMOTE_ADDR']) );
	}
}

// ---------------------------------------------------------------------

function nfw_admin_notice() {

	// Warn about Site Health if needed
	if ( strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/site-health.php') !== FALSE ) {
		// This bug was fixed in WordPress 5.6.1
		global $wp_version;
		if ( version_compare( $wp_version, '5.6.1', '<') ) {
			if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php') ) {
				include NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php';
				if (! empty( $bf_enable ) ) {
					echo '<div class="notice-warning notice is-dismissible"><p>'. __('Warning: Because NinjaFirewall\'s Login Protection is enabled, Site Health may return an error message regarding the loopback test (e.g., 404 or 401 HTTP status code). You can safely ignore it.', 'ninjafirewall') .'</p></div>';
				}
			}
		}
	}

	if (nf_not_allowed( 0, __LINE__ ) ) { return; }

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}

	if (! file_exists(NFW_LOG_DIR . '/nfwlog') ) {
		@mkdir( NFW_LOG_DIR . '/nfwlog', 0755);
		@touch( NFW_LOG_DIR . '/nfwlog/index.html');
		@file_put_contents(NFW_LOG_DIR . '/nfwlog/.htaccess', "Order Deny,Allow\nDeny from all", LOCK_EX);
		if (! file_exists(NFW_LOG_DIR . '/nfwlog/cache') ) {
			@mkdir( NFW_LOG_DIR . '/nfwlog/cache', 0755);
			@touch( NFW_LOG_DIR . '/nfwlog/cache/index.html');
			@file_put_contents(NFW_LOG_DIR . '/nfwlog/cache/.htaccess', "Order Deny,Allow\nDeny from all", LOCK_EX);
		}
	}
	if (! file_exists(NFW_LOG_DIR . '/nfwlog') ) {
		echo '<div class="error notice is-dismissible"><p><strong>' . __('NinjaFirewall error', 'ninjafirewall') . ' :</strong> ' .
			sprintf( __('%s directory cannot be created. Please review your installation and ensure that %s is writable.', 'ninjafirewall'), '<code>'. esc_html(NFW_LOG_DIR) .'/nfwlog/</code>',  '<code>/wp-content/</code>') . '</p></div>';
	}
	if (! is_writable(NFW_LOG_DIR . '/nfwlog') ) {
		echo '<div class="error notice is-dismissible"><p><strong>' . __('NinjaFirewall error', 'ninjafirewall') . ' :</strong> ' .
			sprintf( __('%s directory is read-only. Please review your installation and ensure that %s is writable.', 'ninjafirewall'), '<code>'. esc_html(NFW_LOG_DIR) .'/nfwlog/</code>', '<code>/nfwlog/</code>') . '</p></div>';
	}

	if (! NF_DISABLED) {
		return;
	}

	$nfw_options = nfw_get_option('nfw_options');
	if ( empty($nfw_options['ret_code']) && NF_DISABLED != 11 ) {
		return;
	}

	if (! empty($GLOBALS['err_fw'][NF_DISABLED]) ) {
		$msg = $GLOBALS['err_fw'][NF_DISABLED];
	} else {
		$msg = __('unknown error', 'ninjafirewall') . ' #' . NF_DISABLED;
	}
	echo '<div class="error notice is-dismissible"><p><strong>' . __('NinjaFirewall fatal error:', 'ninjafirewall') . '</strong> ' . $msg .
		'. ' . __('Review your installation, your site is not protected.', 'ninjafirewall') . '</p></div>';
}

add_action('admin_head', 'nfw_hide_admin_notices');

function nfw_hide_admin_notices() {
	if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'NinjaFirewall' || preg_match('/^nfsub/', $_GET['page'] ) ) ) {
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
	}
	add_action('all_admin_notices', 'nfw_admin_notice');
}

// ---------------------------------------------------------------------

function nfw_send_loginemail( $user_login, $whoami ) {

	$nfw_options = nfw_get_option('nfw_options');

	$subject = '[NinjaFirewall] ' . __('Alert: WordPress console login', 'ninjafirewall');
	// Show current blog, not main site (multisite):
	$url = __('-Blog:', 'ninjafirewall') .' '. home_url('/') . "\n\n";
	if (! empty( $whoami ) ) {
		$whoami = " ($whoami)";
	}
	$message = __('Someone just logged in to your WordPress admin console:', 'ninjafirewall') . "\n\n".
				__('-User:', 'ninjafirewall') .' '. $user_login . $whoami . "\n" .
				__('-IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n" .
				__('-Date:', 'ninjafirewall') .' '. ucfirst(date_i18n('F j, Y @ H:i:s T')) . "\n" .
				$url .
				NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

	nfw_mail( $subject, $message, 'unsubscribe');
}

// ---------------------------------------------------------------------			s1:h0

function nfw_query( $query ) {

	if (! empty( NinjaFirewall_session::read('nfw_goodguy') ) || nfw_is_whitelisted() ) {
		return;
	}

	$nfw_options = nfw_get_option('nfw_options');
	// Return if not enabled, or if we are accessing the dashboard (e.g., /wp-admin/edit.php):
	if ( empty($nfw_options['enum_archives']) || empty($nfw_options['enabled']) || is_admin() ) {
		return;
	}
	if ( $query->is_main_query() && $query->is_author() ) {
		if ( $query->get('author_name') ) {
			$tmp = 'author_name=' . $query->get('author_name');
		} elseif ( $query->get('author') ) {
			$tmp = 'author=' . $query->get('author');
		} else {
			$tmp = 'author';
		}
		NinjaFirewall_session::delete();
		$query->set('author_name', '0');
		nfw_log2('User enumeration scan (author archives)', $tmp, 2, 0);
		wp_safe_redirect( home_url('/') );
		exit;
	}
}

add_action('pre_get_posts','nfw_query');

// ---------------------------------------------------------------------
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {

	if (! empty( NinjaFirewall_session::read('nfw_goodguy') ) || nfw_is_whitelisted() ) {
		return $provider;
	}
	$nfw_options = nfw_get_option('nfw_options');
	if ( empty( $nfw_options['enum_sitemap'] ) || empty( $nfw_options['enabled'] ) ) {
		return $provider;
	}

  if ( $name == 'users') {
	  return false;
  }
  return $provider;

}, 999, 2);
// ---------------------------------------------------------------------

function nfw_the_author( $display_name ) {

	if (! empty( NinjaFirewall_session::read('nfw_goodguy') ) || nfw_is_whitelisted() ) {
		return $display_name;
	}
	$nfw_options = nfw_get_option('nfw_options');
	if ( empty( $nfw_options['enum_feed'] ) || empty($nfw_options['enabled']) ) {
		return $display_name;
	}
	if ( is_feed() ) {
		return '';
	}
	return $display_name;
}

add_filter('the_author', 'nfw_the_author', 99999, 1 );

// ---------------------------------------------------------------------

function nfw_no_application_passwords() {

	$nfw_options = nfw_get_option('nfw_options');
	if (! empty( $nfw_options['no_appswd'] ) ) {
		// We don't log API accesses, only accesses to the script (in firewall.php).
		return false;
	}
	return true;
}

add_filter('wp_is_application_passwords_available', 'nfw_no_application_passwords');

// --------------------------------------------------------------------- +
// REST API access.

function nfwhook_rest_authentication_errors( $res ) {

	// Whitelisted user?
	if ( nfw_is_whitelisted() || ! empty( NinjaFirewall_session::read('nfw_goodguy') ) ) {
		return $res;
	}

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}
	if ( NF_DISABLED ) {
		return $res;
	}

	$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( strpos( $path, '/wp-json/wp/v2/pages/') === 0 && current_user_can('edit_pages') ) {
		return $res;
	}
	if ( strpos( $path, '/wp-json/wp/v2/posts/') === 0 && current_user_can('edit_posts') ) {
		return $res;
	}

	$nfw_options = nfw_get_option('nfw_options');

	// Allow logged-in users (since 4.5.7)
	if (! empty( $nfw_options['restapi_loggedin'] ) && is_user_logged_in() ) {
		return $res;
	}

	if (! empty( $nfw_options['no_restapi'] ) ) {
		nfw_log2('WordPress: Blocked access to the WP REST API', $_SERVER['REQUEST_URI'], 2, 0);
		return new WP_Error(
			'nfw_rest_api_access_restricted',
			esc_html__('Forbidden access', 'ninjafirewall'),
			['status' => $nfw_options['ret_code'] ]
		);
	}
	return $res;
}

add_filter('rest_authentication_errors', 'nfwhook_rest_authentication_errors');

// ---------------------------------------------------------------------			s1:h0

function nfwhook_rest_request_before_callbacks( $res, $hnd, $req ) {

	// Whitelisted user?
	if ( nfw_is_whitelisted() || ! empty( NinjaFirewall_session::read('nfw_goodguy') ) ) {
		return $res;
	}

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}
	if ( NF_DISABLED ) { return $res; }

	$nfw_options = nfw_get_option('nfw_options');

	if (! empty( $nfw_options['enum_restapi']) ) {

		if ( strpos( $req->get_route(), '/wp/v2/users') !== false && ! current_user_can('list_users') ) {
			nfw_log2('User enumeration scan (WP REST API)', $_SERVER['REQUEST_URI'], 2, 0);
			return new WP_Error('nfw_rest_api_access_restricted', __('Forbidden access', 'ninjafirewall'), array('status' => $nfw_options['ret_code']) );
		}
	}
	return $res;
}
add_filter('rest_request_before_callbacks', 'nfwhook_rest_request_before_callbacks', 999, 3);

// ---------------------------------------------------------------------

function nfw_authenticate( $user ) {

	$nfw_options = nfw_get_option('nfw_options');

	if ( empty( $nfw_options['enum_login']) || empty($nfw_options['enabled']) ) {
		return $user;
	}

	if ( is_wp_error( $user ) ) {
		if ( preg_match('/^(?:in(?:correct_password|valid_(?:username|email))|authentication_failed)$/', $user->get_error_code() ) ) {
			$lostpass = esc_attr( wp_lostpassword_url() );
			$user = new WP_Error('denied',
				__('Invalid username, email address or password.', 'ninjafirewall') .
				"<br /><a href=\"$lostpass\">".
				__('Lost your password?', 'ninjafirewall').
				'</a>'
			);
			add_filter('shake_error_codes', 'nfw_err_shake');
		}
	}
	return $user;
}

add_filter('authenticate', 'nfw_authenticate', 90, 3 );

function nfw_err_shake( $shake_codes ) {
	$shake_codes[] = 'denied';
	return $shake_codes;
}

// ---------------------------------------------------------------------

function nf_check_dbdata() {

	$nfw_options = nfw_get_option('nfw_options');

	// Don't do anything if NinjaFirewall is disabled or DB monitoring option is off :
	if ( empty( $nfw_options['enabled'] ) || empty($nfw_options['a_51']) ) { return; }

	// Don't run more than once every minute:
	if ( get_transient('nfw_db_check') !== false ) {
		return;
	}

	// This can be defined in the wp-config.php or .htninja script
	if ( defined('NFW_DBCHECK_INTERVAL') ) {
		$dbcheck_interval = (int) NFW_DBCHECK_INTERVAL;
		if ( $dbcheck_interval < 60 ) {
			$dbcheck_interval = 60;
		}
	} else {
		// Default is 60 seconds
		$dbcheck_interval = 60;
	}

	if ( is_multisite() ) {
		global $current_blog;
		$db_hash = NFW_LOG_DIR .'/nfwlog/cache/db_hash.'. $current_blog->site_id .'-'. $current_blog->blog_id .'.php';
	} else {
		global $blog_id;
		$db_hash = NFW_LOG_DIR .'/nfwlog/cache/db_hash.'. $blog_id .'.php';
	}

	$adm_users = nf_get_dbdata();
	// Some oject caching plugins can return an array with empty keys.
	if ( empty( $adm_users[0]->user_login ) ) {
		set_transient('nfw_db_check', 1, $dbcheck_interval );
		return;
	}

	// Sort by ID to prevent false alerts:
	usort( $adm_users, 'nfw_sort_by_id');

	if (! file_exists( $db_hash ) ) {
		// We don't have any hash yet, let's create one and quit
		// (md5 is faster than sha1 or crc32 with long strings) :
		@file_put_contents( $db_hash, md5( serialize( $adm_users) ), LOCK_EX );
		set_transient('nfw_db_check', 1, $dbcheck_interval );
		return;
	}

	$old_hash = trim ( file_get_contents( $db_hash ) );
	if (! $old_hash ) {
		@file_put_contents( $db_hash, md5( serialize( $adm_users) ), LOCK_EX );
		set_transient('nfw_db_check', 1, $dbcheck_interval );
		return;
	}

	// Compare both hashes:
	if ( $old_hash == md5( serialize( $adm_users ) ) ) {
		set_transient('nfw_db_check', 1, $dbcheck_interval );
		return;

	} else {
		// Create or update 60-second transient:
		set_transient('nfw_db_check', 1, $dbcheck_interval );

		// Save the new hash:
		$tmp = @file_put_contents( $db_hash, md5( serialize( $adm_users ) ), LOCK_EX );
		if ( $tmp === FALSE ) {
			return;
		}

		// Send an email to the admin:
		$subject = __('[NinjaFirewall] Alert: Database changes detected', 'ninjafirewall');
		$message = __('NinjaFirewall has detected that one or more administrator accounts were modified in the database:', 'ninjafirewall') . "\n\n";
		// Even if this is a multisite install, we display
		// the requested blog, not the main site:
		$message .=__('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
		$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";
		$message.= sprintf(__('Total administrators : %s', 'ninjafirewall'), count($adm_users) ) . "\n\n";
		foreach( $adm_users as $adm ) {
			$message.= 'Admin ID: ' . $adm->ID . "\n";
			$message.= '-user_login: ' . $adm->user_login . "\n";
			$message.= '-user_nicename: ' . $adm->user_nicename . "\n";
			$message.= '-user_email: ' . $adm->user_email . "\n";
			$message.= '-user_registered: ' . $adm->user_registered . "\n";
			$message.= '-display_name: ' . $adm->display_name . "\n\n";
		}
		$message.=  __('If you cannot see any modifications in the above fields, it is possible that the administrator password was changed.', 'ninjafirewall'). "\n\n";
		$message.= __('This notification can be turned off from NinjaFirewall "Event Notifications" page.', 'ninjafirewall') . "\n\n";
		$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;
		nfw_mail( $subject, $message, 'unsubscribe');

		// Log event if required:
		if (! empty($nfw_options['a_41']) ) {
			nfw_log2('Database changes detected', 'administrator account', 4, 0);
		}
	}
}

// ---------------------------------------------------------------------
// Get admin users (we don't want to use get_users()).

function nf_get_dbdata() {

	global $wpdb;
	return @$wpdb->get_results(
		"SELECT {$wpdb->base_prefix}users.ID,{$wpdb->base_prefix}users.user_login,{$wpdb->base_prefix}users.user_pass,{$wpdb->base_prefix}users.user_nicename,{$wpdb->base_prefix}users.user_email,{$wpdb->base_prefix}users.user_registered,{$wpdb->base_prefix}users.display_name
		FROM {$wpdb->base_prefix}users
		INNER JOIN {$wpdb->base_prefix}usermeta
		ON ( {$wpdb->base_prefix}users.ID = {$wpdb->base_prefix}usermeta.user_id )
		WHERE 1=1
		AND ( ( ( {$wpdb->base_prefix}usermeta.meta_key = '{$wpdb->prefix}capabilities'
		AND {$wpdb->base_prefix}usermeta.meta_value LIKE '%\"administrator\"%') ) )"
	);
}

// ---------------------------------------------------------------------

function nfw_sort_by_id( $a, $b ) {

  return strcmp( $a->ID, $b->ID );
}

// ---------------------------------------------------------------------

function nfw_get_option( $option ) {

	if ( is_multisite() ) {
		return get_site_option( $option );
	} else {
		return get_option( $option );
	}
}

// ---------------------------------------------------------------------

function nfw_update_option( $option, $new_value ) {

	if ( is_multisite() ) {
		update_site_option( $option, $new_value );
	}
	return update_option( $option, $new_value );
}

// ---------------------------------------------------------------------

function nfw_delete_option( $option ) {

	if ( is_multisite() ) {
		delete_site_option( $option );
	}
	return delete_option( $option );
}

// ---------------------------------------------------------------------
// Make sure nfw_options is valid.

function nfw_validate_option( $value ) {

	if (! isset( $value['enabled'] ) || ! isset( $value['blocked_msg'] ) ||
		! isset( $value['logo'] ) || ! isset( $value['ret_code'] ) ||
		! isset( $value['scan_protocol'] ) || ! isset( $value['get_scan'] ) ) {

		// Data is corrupted:
		return false;
	}

	return true;
}

// ---------------------------------------------------------------------

function nfwhook_update_user_meta( $user_id, $meta_key, $meta_value, $prev_value ) {

	nfwhook_user_meta( $meta_key, $meta_value, $prev_value );

}
add_filter('update_user_meta', 'nfwhook_update_user_meta', 1, 4);

// ---------------------------------------------------------------------

function nfwhook_add_user_meta( $user_id, $meta_key, $meta_value ) {

	nfwhook_user_meta( $user_id, $meta_key, $meta_value );

}
add_filter('add_user_meta', 'nfwhook_add_user_meta', 1, 3);

// ---------------------------------------------------------------------

function nfwhook_user_meta( $id, $key, $value ) {

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}

	$nfw_options = nfw_get_option('nfw_options');

	// Note: "NFW_DISABLE_PRVESC2" is now deprecated. Use the corresponding
	// firewall policy to disable it instead:
	if ( NF_DISABLED || defined('NFW_DISABLE_PRVESC2') || empty( $nfw_options['disallow_privesc'] ) ) { return; }

	global $wpdb;

	if ( is_array( $key ) ) {
		$key = serialize( $key );
	}

	// "current_user_can" must remain here, see https://wordpress.org/support/topic/rest-api-problem-2/page/2/#post-11789636
	if ( preg_match( "/{$wpdb->base_prefix}([0-9]+_)?capabilities/", $key ) && ! current_user_can('edit_users') ) {
		if ( is_array( $value ) ) {
			$value = serialize( $value );
		}

		if ( strpos( $value, 's:13:"administrator"') === FALSE &&
			strpos( $value, 's:6:"editor"') === FALSE &&
			strpos( $value, 's:12:"shop_manager"') === FALSE &&
			strpos( $value, 's:13:"bbp_keymaster"') === FALSE ) {

			return;
		}
		// If it's a subsite in a network, check what we are supposed to do
		if ( is_main_site() !== true && empty( $nfw_options['disallow_privesc_mu'] ) ) {
			return;
		}

		$user_info = get_userdata( $id );
		$whoisit = '';
		$check_user = array('subscriber', 'contributor', 'author', 'customer', 'bbp_participant', 'bbp_spectator');
		foreach( $user_info->roles as $k => $v ) {
			if ( in_array( $v, $check_user ) ) {
				$whoisit = $v;
				break;
			}
		}
		if ( empty( $whoisit ) && ! empty( $user_info->roles ) ) {
			return;
		}

		if ( strlen( $value ) > 200 ) { $value = mb_substr( $value, 0, 200, 'utf-8') . '...'; }
		$subject = __('Blocked privilege escalation attempt', 'ninjafirewall');
		nfw_log2('WordPress: ' . $subject, "$key: $value", 3, 0);

		// Alert the admin:
		$subject = '[NinjaFirewall] ' . $subject;

		$message = __('NinjaFirewall has blocked an attempt to modify a user capability by someone who does not have administrative privileges:', 'ninjafirewall') . "\n\n";

		$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";

		// Show current blog, not main site (multisite):
		if (! empty( $user_info->user_login ) ) {
			$message.= __('Username:', 'ninjafirewall') .' '. "{$user_info->user_login}, ID: $id\n";
		}
		$message.= "meta_key: $key\n";
		$message.= "meta_value: $value\n";
		$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
		$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
		$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
		$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

		// Attach PHP backtrace
		$return  = nfw_debug_backtrace( $nfw_options );
		$message.= $return['message'];
		$message.= __('This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall') . "\n\n";
		$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

		if ( empty( $return['nftmpfname'] ) ) {
			nfw_mail( $subject, $message, 'unsubscribe');

		} else {
			// Attach backtrace and delete temp file:
			nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
			unlink( $return['nftmpfname'] );
		}

		// Block it
		NinjaFirewall_session::delete();
		wp_die(
			'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
			'NinjaFirewall: '. __('You are not allowed to perform this task.', 'ninjafirewall'),
			$nfw_options['ret_code']
		);
	}
}

// ---------------------------------------------------------------------			s1:h0

function nfw_login_form_hook( $message ) {

	if (! empty( NinjaFirewall_session::read('nfw_bfd') ) ) {
		return '<p class="message" id="nfw_login_msg">'.
			esc_html__('NinjaFirewall brute-force protection is enabled and you are temporarily whitelisted.', 'ninjafirewall') .
			'</p><br />';
	}
	return $message;
}
add_filter('login_message', 'nfw_login_form_hook');

// ---------------------------------------------------------------------

function nfw_rate_notice( $nfw_options ) {

	// Display a one-time notice after two weeks of use:
	$now = time();
	if (! empty( $nfw_options['rate_notice'] ) && $nfw_options['rate_notice'] < $now ) {

		echo '<div class="notice-info notice is-dismissible"><p>'.	sprintf(
			__('Hey, it seems that you\'ve been using NinjaFirewall for some time. If you like it, please take <a href="%s">the time to rate it</a>. It took thousand of hours to develop it, but it takes only a couple of minutes to rate it. Thank you!', 'ninjafirewall'),
			'https://wordpress.org/support/view/plugin-reviews/ninjafirewall?rate=5#postform'
			) .'</p></div>';

		// Clear the reminder flag:
		unset( $nfw_options['rate_notice'] );
		// Update options:
		nfw_update_option('nfw_options', $nfw_options );
	}

}

// ---------------------------------------------------------------------			s1:h1

function nfw_session_debug() {

	// Make sure NinjaFirewall is running :
	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}
	if ( NF_DISABLED ) { return; }

	$show_session_icon = 0;
	$current_user = wp_get_current_user();
	// Check users first:
	if ( defined('NFW_SESSION_DEBUG_USER') ) {
		$users = explode(',', NFW_SESSION_DEBUG_USER );
		foreach ( $users as $user ) {
			if ( trim( $user ) == $current_user->user_login ) {
				$show_session_icon = 1;
				break;
			}
		}
	// Check capabilities:
	} elseif ( defined('NFW_SESSION_DEBUG_CAPS') ) {
		$caps = explode(',', NFW_SESSION_DEBUG_CAPS );
		foreach ( $caps as $cap ) {
			if (! empty( $current_user->caps[ trim( $cap ) ] ) ) {
				$show_session_icon = 1;
				break;
			}
		}
	}

	if ( empty( $show_session_icon ) ) { return; }

	// Check if the user whitelisted?
	if ( empty( NinjaFirewall_session::read('nfw_goodguy') ) ) {
		// No
		$font = 'ff0000';
	} else {
		// Yes
		$font = '00ff00';
	}

	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'id'    => 'nfw_session_dbg',
		'title' => "<font color='#{$font}'>NF</font>"
	) );

}

// Check if the session debug option is enabled:
if ( defined('NFW_SESSION_DEBUG_USER') || defined('NFW_SESSION_DEBUG_CAPS') ) {
	add_action('admin_bar_menu', 'nfw_session_debug', 500 );
}

// ---------------------------------------------------------------------

function nf_monitor_options( $value, $option, $old_value ) {

	// Admin check is done in nfw_load_optmon().

	// We're not interested in any object
	if ( is_object( $value ) || is_object( $old_value ) ) {
		return $value;
	}

	// Similarly to https://core.trac.wordpress.org/ticket/38903, an integer will
	// trigger a DB UPDATE query even if it matches the character stored in the DB
	// (e.g.: 0 vs '0'). We must not block that, hence will use '===' only on arrays
	// (and that will prevent "Nesting level too deep" error as well):
	if ( is_array( $value ) ) {
		if ( $value === $old_value ) {
			return $value;
		}
	} else {
		// Simple comparison operator for integers and strings:
		if ( $value == $old_value ) {
			return $value;
		}
	}

	$nfw_options = nfw_get_option('nfw_options');

	if ( empty( $nfw_options['enabled'] ) || empty( $nfw_options['disallow_settings'] ) ) {
		return $value;
	}

	// User-defined exclusion list (undocumented), NF options/rules (which are protected
	// by the firewall):
	if ( ( defined('NFW_OPTMON_EXCLUDE') && strpos( NFW_OPTMON_EXCLUDE, $option ) !== false ) ||
		$option === 'nfw_options' || $option === 'nfw_rules') {

		return $value;
	}

	global $wpdb;
	$monitor = array(
		'admin_email',
		'blog_public',
		'blogdescription',
		'blogname',
		'comment_moderation',
		'comments_notify',
		'comment_registration',
		'default_role',
		'home',
		'mailserver_login',
		'siteurl',
		'template',
		'stylesheet',
		'users_can_register'
	);

	// No changes detected or not what we are looking for:
	if (! in_array( $option, $monitor ) ) {
		return $value;
	}

	if ( is_array( $value ) ) {
		$tmp = serialize( $value );
		$value = '';
		if ( strlen( $tmp ) > 200 ) { $tmp = mb_substr( $tmp, 0, 200, 'utf-8') . '...'; }
		$value = $tmp;
	}
	if ( is_array( $old_value ) ) {
		$tmp = serialize( $old_value );
		$old_value = '';
		if ( strlen( $tmp ) > 200 ) { $tmp = mb_substr( $tmp, 0, 200, 'utf-8') . '...'; }
		$old_value = $tmp;
	}

	// Send a notification to the admin:
	nf_monitor_options_alert( $option, $value, $old_value, 'settings');

	// Log the request:
	nfw_log2('Blocked attempt to modify WordPress settings', "option: {$option}, value: {$value}", 3, 0);

	// Since 4.0.3 we don't close the connection anymore but
	// we block the modification by returning the previous value
	return $old_value;
}

// ---------------------------------------------------------------------

function nfw_load_optmon() {

	if (! nfw_is_whitelisted() && ! current_user_can('manage_options') ) {
		add_filter('pre_update_option', 'nf_monitor_options', 10, 3 );
	}
}

add_action('plugins_loaded', 'nfw_load_optmon');

// ---------------------------------------------------------------------
// $type = settings or injection.

function nf_monitor_options_alert( $option, $value, $old_value, $type ) {

	$nfw_options = nfw_get_option('nfw_options');

	$action = __('The attempt was blocked and the option was reversed to its original value.', 'ninjafirewall');

	// WP settings:
	if ( $type == 'settings') {

		$subject = '[NinjaFirewall] ' . __('Attempt to modify WordPress settings', 'ninjafirewall');
		$message = __('NinjaFirewall has blocked an attempt to modify some important WordPress settings by a user that does not have administrative privileges:', 'ninjafirewall') . "\n\n";
		$message.= sprintf( __('Option: %s', 'ninjafirewall') ."\n", $option );
		$message.= sprintf( __('Original value: %s', 'ninjafirewall') ."\n", $old_value );
		$message.= sprintf( __('Modified value: %s', 'ninjafirewall') ."\n", $value );
		$message.= sprintf( __('Action taken: %s', 'ninjafirewall') ."\n\n", $action );

	// Misc. injection:
	} else {
		$subject = '[NinjaFirewall] ' . __('Code injection attempt in WordPress options table', 'ninjafirewall');
		$message = __('NinjaFirewall has blocked an attempt to inject code in the WordPress options table by a user that does not have administrative privileges:', 'ninjafirewall') . "\n\n";
		$message.= sprintf( __('Option: %s', 'ninjafirewall') ."\n", $option );
		$message.= sprintf( __('Code: %s', 'ninjafirewall') ."\n", $value );
		$message.= sprintf( __('Action taken: %s', 'ninjafirewall') ."\n\n", $action );
	}

	// Attach PHP backtrace
	$return  = nfw_debug_backtrace( $nfw_options );
	$message.= $return['message'];

	// Show current blog, not main site (multisite):
	$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
	$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
	$message.= 'SCRIPT_FILENAME: ' . $_SERVER['SCRIPT_FILENAME'] . "\n";
	$message.= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
	$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";

	$message.= __('This protection (and notification) can be turned off from NinjaFirewall "Firewall Policies" page.', 'ninjafirewall') . "\n\n";
	$message.= NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

	if ( empty( $return['nftmpfname'] ) ) {
		nfw_mail( $subject, $message, 'unsubscribe');

	} else {
		// Attach backtrace and delete temp file:
		nfw_mail( $subject, $message, 'unsubscribe', $headers = '', $return['nftmpfname'] );
		unlink( $return['nftmpfname'] );
	}

}

// ---------------------------------------------------------------------
// Display a red notice if there's a pending security update
// in the plugins section.

function nfw_verify_secupdates() {

	$nfw_checked = nfw_get_option('nfw_checked');
	if ( empty( $nfw_checked['plugins'] ) ) {
		return;
	}
	// Check plugins updates
	if (! function_exists('get_plugins') ) {
		require_once ABSPATH .'wp-admin/includes/plugin.php';
	}
	$plugins = get_plugins();
	$cleared = 0;
	foreach( $plugins as $k => $v ) {
		// No name or no version (unlike themes, we're dealing with arrays here)
		if ( empty( $v['Name'] ) || empty( $v['Version'] ) ) {
			continue;
		}

		if ( isset( $nfw_checked['plugins'][$k] ) ) {
			// Compare current and available versions
			if ( version_compare( $v['Version'], $nfw_checked['plugins'][$k]['version'], '<') ) {
				add_action( "in_plugin_update_message-{$k}", 'nfw_in_plugin_update_message', 10, 2 );
			} else {
				// Remove if from our cache
				unset( $nfw_checked['plugins'][$k] );
				$cleared = 1;
			}

		}
	}

	// Flush plugins or themes that were uninstalled instead of updated
	if (! empty( $nfw_checked['plugins'] ) ) {
		foreach( $nfw_checked['plugins'] as $k => $v ) {
			if (! file_exists( WP_PLUGIN_DIR ."/$k" ) ) {
				unset( $nfw_checked['plugins'][$k] );
				$cleared = 1;
			}
		}
	}
	if (! empty( $nfw_checked['themes'] ) ) {
		foreach( $nfw_checked['themes'] as $k => $v ) {
			if (! is_dir( WP_CONTENT_DIR ."/themes/$k" ) ) {
				unset( $nfw_checked['themes'][$k] );
				$cleared = 1;
			}
		}
	}

	// Update our list if needed
	if (! empty( $cleared ) ) {
		nfw_update_option('nfw_checked', $nfw_checked );
	}
}

function nfw_in_plugin_update_message( $plugin_data, $r ) {

	// We need to add our style here because ninjafirewall.css
	// is not loaded on the plugins page:
	echo '<br /><br /><span style="display:block;background-color:#FFA4A4;color:#000;padding:5px;border:1px solid red">';

	echo esc_html__('Important: NinjaFirewall has detected that this is a security update.', 'ninjafirewall') . ' ' .
	esc_html__("Don't leave your blog at risk, make sure to update as soon as possible.", 'ninjafirewall')  . ' ' .
	'<a href="https://blog.nintechnet.com/how-to-get-informed-about-the-latest-security-updates-in-your-wordpress-plugins-and-themes/" target="_blank">' .
	esc_html__('More info about this warning.', 'ninjafirewall') .
	'</a></span>';
}

// ---------------------------------------------------------------------
// Send a notification email to one or multiple recipient with a unsubscribe
// link for admin users who don't have anymore access to the site.
// If the recipient's email was taken from the get_option('admin_email') option,
// we don't send any link as we don't want to remove that email.

function nfw_mail( $subject, $message, $unsubscribe = 0, $headers = '', $attachments = '') {

	$nfw_options = nfw_get_option('nfw_options');

	if ( is_multisite() && ( $nfw_options['alert_sa_only'] == 2 ) ) {
		$recipient = get_option('admin_email');
		// User is the main admin of the site, we can't help here:
		$unsubscribe = 0;
	} else {
		$recipient = $nfw_options['alert_email'];
	}

	if ( empty( $recipient ) ) {
		nfw_log_error( sprintf( __('Cannot send notification, no valid email found (%s)', 'ninjafirewall'), 'alert_email') );
		return;
	}

	require_once 'email_sodium.php';

	// In order to use Sodium, we must have WordPress >=5.2 or PHP >= 7.2.0
	if (! empty( $unsubscribe ) ) {
		$unsubscribe = nfw_check_sodium();
	}

	if (! empty( $unsubscribe ) ) {
		// Link will be valid for 12 hours
		$expire = time() + 60*60*12;
		$admin_email = get_option('admin_email');
		// We need to create a unique unsubscribe link for each recipients
		$recipients = explode(',', $recipient );
		foreach( $recipients as $to ) {
			$to = trim( $to );
			// If that's the admin email, we can't remove it:
			if ( $to == $admin_email ) {
				$click = '';
			} else {
				$link = nfw_sodium_encrypt( $to, $expire, $unsubscribe );
				$uri = home_url('/') ."?nfw_stop_notification=$link";
				$click = "\n\n". sprintf( __("If you don't have access to that site any longer, you can remove your email by clicking the following link (valid for 12 hours): %s", 'ninjafirewall'), $uri );
			}
			wp_mail( $to, $subject, $message . $click, $headers, $attachments );
		}

	} else {
		wp_mail( $recipient, $subject, $message, $headers, $attachments );
	}
}

// ---------------------------------------------------------------------
// Attach a backtrace if required.

function nfw_debug_backtrace( $nfw_options ) {

	$return = array();
	$return['message'] = '';
	$verbosity = nfw_verbosity( $nfw_options );
	if ( $verbosity !== false ) {
		$return['nftmpfname'] = NFW_LOG_DIR .'/nfwlog/backtrace_'. bin2hex( random_bytes( 8 ) ) .'.txt';
		$dbg = debug_backtrace( $verbosity );
		array_shift( $dbg );
		file_put_contents( $return['nftmpfname'], print_r( $dbg, true ) );
		$return['message'] = __('A PHP backtrace has been attached to this message for your convenience.', 'ninjafirewall') . "\n\n";
	}
	return $return;
}

// ---------------------------------------------------------------------
// Activate WPWAF mode.

function nfw_enable_wpwaf() {

	if ( file_exists( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN ) ) {
		// Quick files comparison. We used md5 as we're only looking for changes,
		// i.e., if there was an update.
		if ( md5_file( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN) === md5_file( __DIR__ .'/loader.php') ) {
			return;
		}
	}

	if (! is_dir( WPMU_PLUGIN_DIR ) ) {
		if (! @mkdir( WPMU_PLUGIN_DIR, 0755, true ) ) {
			return sprintf(
				esc_html__('Error, cannot create the %s folder.', 'ninjafirewall') .' '.
				esc_html__('Check your server permissions and try again.', 'ninjafirewall'),
				esc_html( WPMU_PLUGIN_DIR )
			);
		}
	}

	if (! is_writable( WPMU_PLUGIN_DIR ) ) {
		return sprintf(
			esc_html__('Error, the %s folder is not writable.', 'ninjafirewall') .' '.
			esc_html__('Check your server permissions and try again.', 'ninjafirewall'),
			esc_html( WPMU_PLUGIN_DIR )
		);
	}

	@copy( __DIR__ .'/loader.php', WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN);
	if (! file_exists( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN) ) {
		return sprintf(
			esc_html__('Error, cannot write %s.', 'ninjafirewall') .' '.
			esc_html__('Check your server permissions and try again.', 'ninjafirewall'),
			esc_html( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN)
		);
	}

	return;
}

// ---------------------------------------------------------------------
// Deactivate WPFAF mode.

function nfw_disable_wpwaf() {

	if ( file_exists( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN) ) {
		unlink( WPMU_PLUGIN_DIR .'/'. NINJAFIREWALL_MU_PLUGIN);
	}
}

// ---------------------------------------------------------------------
function nfw_dropins() {

	$nfw_options = nfw_get_option('nfw_options');
	if ( empty( $nfw_options['enabled'] ) ) { return; }

	if ( file_exists( NFW_LOG_DIR .'/nfwlog/dropins.php') ) {
		@include_once NFW_LOG_DIR .'/nfwlog/dropins.php';
	}
}

add_action('plugins_loaded', 'nfw_dropins', -1);

// ---------------------------------------------------------------------
// For WP <4.9.

if (! function_exists('wp_readonly') ) {
	function wp_readonly( $var, $val) {
		if ( $var == $val ) {
			echo " readonly='readonly'";
		}
	}
}

// ---------------------------------------------------------------------
// Used to display the toggle/switch's status to screenreaders.
function nfw_aria_label( $var, $val, $text_on, $text_off ) {
	if ( $var == $val ) {
		echo " aria-label='$text_on'";
	} else {
		echo " aria-label='$text_off'";
	}
}
// ---------------------------------------------------------------------
// EOF
