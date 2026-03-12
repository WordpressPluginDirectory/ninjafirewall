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

if ( class_exists('NinjaFirewall_IP') ) {
	return;
}

class NinjaFirewall_IP {

	/**
	 * Return an IP address and its type (public/private).
	 * $nfw_options is only used here for compatibility with the WP+ version.
	 */
	public static function check_ip( $nfw_options ) {
		/**
		 * It could have been defined by the firewall, if already loaded.
		 */
		if ( defined('NFW_REMOTE_ADDR') ) {
			return;
		}

		/**
		 * Some command line cron jobs may return an 'Undefined array key REMOTE_ADDR' warning.
		 */
		if (! isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		}

		if ( strpos( $_SERVER['REMOTE_ADDR'], ',') !== false ) {
			$matches = array_map('trim', @ explode(',', $_SERVER['REMOTE_ADDR'] ) );
			foreach( $matches as $match ) {
				if ( filter_var( $match, FILTER_VALIDATE_IP ) )  {
					define('NFW_REMOTE_ADDR', $match );
					break;
				}
			}
		}
		if (! defined('NFW_REMOTE_ADDR') ) {
			/**
			 * Last hope.
			 */
			define('NFW_REMOTE_ADDR', htmlspecialchars( $_SERVER['REMOTE_ADDR'] ) );
		}

		/**
		 * Check if it's a private address.
		 */
		if (filter_var( NFW_REMOTE_ADDR, FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {

			define('NFW_REMOTE_ADDR_PRIVATE', false );
		} else {
			define('NFW_REMOTE_ADDR_PRIVATE', true );
		}
	}


	/**
	 * Anonymize an IP address by hidding it last 3 characters, unless it's a prive IP.
	 */
	public static function anonymize_ip( $ip, $nfw_options ) {

		if (! empty( $nfw_options['anon_ip'] ) && NFW_REMOTE_ADDR_PRIVATE === false ) {

			return substr( $ip, 0, -3 ) .'xxx';
		}

		return $ip;
	}

}

// =====================================================================
// EOF
