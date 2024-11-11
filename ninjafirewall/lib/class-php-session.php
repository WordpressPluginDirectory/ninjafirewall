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

if ( class_exists('NinjaFirewall_session') ) {
	return;
}

class NinjaFirewall_session {


	/**
	 * Start a PHP session.
	 */
	public static function start() {
		/**
		 * Make sure no header was sent already, no session exists
		 * and that sessions are enabled.
		 */
		if ( headers_sent() || session_status() !== PHP_SESSION_NONE ) {
			return false;
		}
		return session_start();
	}


	/**
	 * Read session data.
	 */
	public static function read( $key ) {

		if ( isset( $_SESSION[ $key ] ) ) {
			return $_SESSION[ $key ];
		}
		return null;
	}


	/**
	 * Write session data.
	 */
	public static function write( $data = [] ) {

		foreach( $data as $key => $value ) {
			$_SESSION[ $key ] = $value;
		}
	}


	/**
	 * Unset a key or the whole session array.
	 */
	public static function delete( $key = '') {

		if ( $key ) {
			unset ( $_SESSION[ $key ] );
		} else {
			$_SESSION = [];
		}
	}


	/**
	 * Write session data and end session.
	 */
	public static function close() {

		return session_write_close();
	}


	/**
	 * Return the session name.
	 */
	public static function name() {

		return ini_get('session.name');
	}

}

// =====================================================================
// EOF
