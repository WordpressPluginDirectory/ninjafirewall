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

	public static $SESSION_NAME		= 'NFWSESSID';
	public static $SESSION_DATA		= [];
	private static $session_dir		= '';
	private static $session_status	= false;
	private static $session_id			= 0;


	/**
	 * Start a PHP session.
	 */
	public static function start() {
		/**
		 * Make sure no header was sent already and no session exists.
		 */
		if ( headers_sent() || self::$session_status === true ) {
			return false;
		}
		/**
		 * Create session dir if it doesn't exist.
		 * Note: NFWSESSION_DIR can be defined in the .htninja file.
		 */
		if (! self::$session_dir ) {
			if ( defined('NFWSESSION_DIR') ) {
				self::$session_dir = NFWSESSION_DIR;
			} else {
				self::$session_dir = NFW_LOG_DIR .'/sessions';
			}
			if (! is_dir( self::$session_dir ) ) {
				$res = mkdir( self::$session_dir, 0700, true );
				if ( $res === false ) {
					return false;
				}
			}
			touch( self::$session_dir .'/index.html');
		}
		/**
		 * Callback function to close and save the session.
		 */
		register_shutdown_function( ['NinjaFirewall_session', 'close'] );
		/**
		 * Check whether the user already has a session cookie
		 * or if we need to create a new one.
		 */
		if (! empty( $_COOKIE[ self::$SESSION_NAME ] ) ) {
			self::$session_id = $_COOKIE[ self::$SESSION_NAME ];
			/**
			 * Validate session ID.
			 */
			if ( preg_match('`^[-,a-zA-Z0-9]{1,128}$`', self::$session_id ) ) {
				if ( is_file( self::$session_dir .'/sess_'. sha1( self::$session_id ) ) ) {
					self::$SESSION_DATA = json_decode(
						file_get_contents( self::$session_dir .'/sess_'. sha1( self::$session_id ) ),
						true
					);
					if ( self::$SESSION_DATA !== null ) {
						self::$session_status = true;
						return true;
					}
				}
			}
			/**
			 * Not the right cookie, ignore it.
			 */
			unset( $_COOKIE[ self::$SESSION_NAME ] );
		}
		/**
		 * Create a session ID and the corresponding file.
		 */
		self::$session_status	= true;
		self::$SESSION_DATA		= [];
		self::$session_id			= session_create_id();
		file_put_contents( self::$session_dir .'/sess_'. sha1( self::$session_id ), '[]');
		/**
		 * Set the cookie.
		 */
		setcookie( self::$SESSION_NAME, self::$session_id, 0, '/', '', self::is_ssl(), true );
		return true;
	}


	/**
	 * Read session data.
	 */
	public static function read( $key ) {

		if ( isset( self::$SESSION_DATA[ $key ] ) ) {
			return self::$SESSION_DATA[ $key ];
		}
		return null;
	}


	/**
	 * Write session data.
	 */
	public static function write( $data = [] ) {

		foreach( $data as $key => $value ) {
			self::$SESSION_DATA[ $key ] = $value;
		}
	}


	/**
	 * Unset a key or the whole session array.
	 */
	public static function delete( $key = '') {

		if ( $key ) {
			unset ( self::$SESSION_DATA[ $key ] );
		} else {
			self::$SESSION_DATA = [];
		}
	}


	/**
	 * Destroy a session (cookie, ID and file).
	 */
	public static function destroy() {
		/**
		 * User has a session cookie, delete it and the matching file.
		 */
		if ( isset( $_COOKIE[ self::$SESSION_NAME ] ) ) {
			if ( $_COOKIE[ self::$SESSION_NAME ] === self::$session_id ) {
				if ( is_file( self::$session_dir .'/sess_'. sha1( self::$session_id ) ) ) {
					unlink( self::$session_dir .'/sess_'. sha1( self::$session_id ) );
				}
			}
			unset( $_COOKIE[ self::$SESSION_NAME ] );
		}
		self::$SESSION_DATA		= [];
		self::$session_status	= false;
		self::$session_id			= 0;
	}


	/**
	 * Write session data and end session, but keep $SESSION_DATA.
	 */
	public static function close() {

		if ( isset( $_COOKIE[ self::$SESSION_NAME ] ) ) {
			if ( $_COOKIE[ self::$SESSION_NAME ] === self::$session_id ) {
				if ( is_file( self::$session_dir .'/sess_'. sha1( self::$session_id ) ) ) {
					file_put_contents(
						self::$session_dir .'/sess_'. sha1( self::$session_id ),
						json_encode( self::$SESSION_DATA )
					);
					self::$session_status = false;
					return true;
				}
			}
			/**
			 * Wrong cookie, unset it.
			 */
			unset( $_COOKIE[ self::$SESSION_NAME ] );
		}
		/**
		 * First run, no cookie has been set yet.
		 */
		if ( self::$session_id ) {
			file_put_contents(
				self::$session_dir .'/sess_'. sha1( self::$session_id ),
				json_encode( self::$SESSION_DATA )
			);
			self::$session_status = false;
			return true;
		}
		return false;
	}


	/**
	 * Return the session name.
	 */
	public static function name() {

		return self::$SESSION_NAME;
	}


	/**
	 * Check if we're over TLS.
	 * Note: code taken from WordPress wp-includes/load.php.
	 */
	private static function is_ssl() {
		if ( isset( $_SERVER['HTTPS'] ) ) {
			if ('on' === $_SERVER['HTTPS'] ) {
				return true;
			}
			if ('1' === $_SERVER['HTTPS'] ) {
				return true;
			}
		} elseif ( isset( $_SERVER['SERVER_PORT'] ) &&
			'443' === $_SERVER['SERVER_PORT'] ) {

			return true;
		}
		return false;
	}

}
// =====================================================================
// EOF
