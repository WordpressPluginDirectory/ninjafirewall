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

if (! isset( $nfw_['nfw_options']['enabled'] ) ) {
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	exit;
}

/* ------------------------------------------------------------------ */
function fw_fileguard() {

	global $nfw_;

	/**
	 * Look for exclusion.
	 */
	if ( empty( $nfw_['nfw_options']['fg_exclude'] ) ||
		! @preg_match( "`{$nfw_['nfw_options']['fg_exclude']}`", $_SERVER['SCRIPT_FILENAME'] ) ) {
		/**
		 * Stat() the requested script.
		 */
		if ( $nfw_['nfw_options']['fg_stat'] = stat( $_SERVER['SCRIPT_FILENAME'] ) ) {
			/**
			 * Was it created/modified lately ?
			 */
			if ( time() - $nfw_['nfw_options']['fg_mtime'] * 3660 < $nfw_['nfw_options']['fg_stat']['ctime'] ) {
				/**
				 * Did we check it already ?
				 */
				if (! is_file( $nfw_['log_dir'] .'/cache/fg_'. $nfw_['nfw_options']['fg_stat']['ino'] .'.php') ) {
					/**
					 * Log it.
					 */
					nfw_log('Access to a script modified/created less than '.
						$nfw_['nfw_options']['fg_mtime'] .' hour(s) ago', $_SERVER['SCRIPT_FILENAME'], 6, 0 );
					/**
					 * Send the notification.
					 */
					$headers = 'From: "NinjaFirewall" <postmaster@'. $_SERVER['SERVER_NAME'] . ">\r\n";
					$subject = [];
					$content = [ $nfw_['nfw_options']['fg_mtime'], $_SERVER['SERVER_NAME'],
									NFW_REMOTE_ADDR, $_SERVER['SCRIPT_FILENAME'], $_SERVER['REQUEST_URI'],
									date('F j, Y @ H:i:s T', $nfw_['nfw_options']['fg_stat']['ctime'] ) ];

					require_once __DIR__ .'/class_mail.php';
					NinjaFirewall_mail::PHPsend(
						$nfw_['nfw_options']['alert_email'], 'fileguard',
						$subject, $content, $nfw_['log_dir'], $headers
					);
					/**
					 * Remember it so that we don't spam the admin each time the script is requested.
					 */
					touch( "{$nfw_['log_dir']}/cache/fg_{$nfw_['nfw_options']['fg_stat']['ino']}.php" );
				}
				/**
				 * Undocumented: if 'NFW_FG_BLOCK' is defined in the .htninja, we block the request.
				 */
				if ( defined('NFW_FG_BLOCK') ) {
					nfw_log('File Guard: blocked request', $_SERVER['SCRIPT_FILENAME'], 6, 0 );
					nfw_block();
				}
			}
		}
	}
}
/* ------------------------------------------------------------------ */
// EOF
