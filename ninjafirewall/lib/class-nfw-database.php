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

if ( class_exists('NinjaFirewall_fwdatabase') ) {
	return;
}

class NinjaFirewall_fwdatabase {


	/**
	 * This is a fork of the WordPress parse_db_host() method.
	 * We use it when NF is loaded in "Full WAF Mode" only, i.e., before WordPress loads.
	 */
	public static function parse_db_host( $host ) {

		$socket  = null;
		$is_ipv6 = false;

		// First peel off the socket parameter from the right, if it exists.
		$socket_pos = strpos( $host, ':/' );
		if ( false !== $socket_pos ) {
			$socket = substr( $host, $socket_pos + 1 );
			$host   = substr( $host, 0, $socket_pos );
		}

		/*
		 * We need to check for an IPv6 address first.
		 * An IPv6 address will always contain at least two colons.
		 */
		if ( substr_count( $host, ':' ) > 1 ) {
			$pattern = '#^(?:\[)?(?P<host>[0-9a-fA-F:]+)(?:\]:(?P<port>[\d]+))?#';
			$is_ipv6 = true;
		} else {
			// We seem to be dealing with an IPv4 address.
			$pattern = '#^(?P<host>[^:/]*)(?::(?P<port>[\d]+))?#';
		}

		$matches = [];
		$result  = preg_match( $pattern, $host, $matches );

		if ( 1 !== $result ) {
			// Couldn't parse the address, bail.
			return false;
		}

		$host = ! empty( $matches['host'] ) ? $matches['host'] : '';
		// Port cannot be a string; must be null or an integer.
		$port = ! empty( $matches['port'] ) ? abs( (int) $matches['port'] ) : null;

		/*
		 * If using the `mysqlnd` library, the IPv6 address needs to be enclosed
		 * in square brackets, whereas it doesn't while using the `libmysqlclient` library.
		 * @see https://bugs.php.net/bug.php?id=67563
		 */
		if ( $is_ipv6 && extension_loaded( 'mysqlnd' ) ) {
			$host = "[$host]";
		}

		return [ $host, $port, $socket ];
	}

}

// =====================================================================
// EOF
