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

if ( class_exists('NinjaFirewall_log') ) {
	return;
}

class NinjaFirewall_log {

	/**
	 * Write event to the firewall log and return the incident ID.
	 */
	public static function write( $loginfo, $logdata, $loglevel, $ruleid, $nfw_options, $log ) {

		/**
		 * Create a random incident number.
		 */
		$incidentID = mt_rand( 1000000, 9000000 );

		/**
		 * INFO or sanitize: don't block.
		 */
		if ( $loglevel == NFWLOG_INFO ) {
			$http_ret_code = '200';

		} else {
			/**
			 * Debugging : don't block but set loglevel to NFWLOG_DEBUG
			 * (it will display 'DEBUG_ON' in log).
			 */
			if (! empty( $nfw_options['debug'] ) ) {
				$loglevel = NFWLOG_DEBUG;
				$http_ret_code = '200';

			} else {
				$http_ret_code = $nfw_options['ret_code'];
			}
		}

		/**
		 * Prepare the line to write to the log.
		 * Note: NFW_MAXPAYLOAD can be defined in the .htninja script.
		 */
		if ( defined('NFW_MAXPAYLOAD') ) {
			$max_payload = ( int ) NFW_MAXPAYLOAD;
		} else {
			$max_payload = 200;
		}
		if ( strlen( $logdata ) > $max_payload ) {
			$logdata = mb_substr( $logdata, 0, $max_payload, 'utf-8' ) .'...';
		}
		$res = '';
		$string = str_split( $logdata );
		foreach ( $string as $char ) {
			/**
			 * Allow only ASCII printable characters.
			 */
			if ( ord( $char ) < 32 || ord( $char ) > 126 ) {
				$res .= '%'. bin2hex( $char );
			} else {
				$res .= $char;
			}
		}

		$cur_month = date('Y-m');
		$stat_file = "$log/stats_{$cur_month}.php";
		$log_file = "$log/firewall_{$cur_month}.php";

		/**
		 * Update stats.
		 */
		if ( is_file( $stat_file ) ) {
			$stats = file_get_contents( $stat_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			$stats = str_replace('<?php exit; ?>', '', $stats );
		} else {
			$stats = '0:0:0:0:0:0:0:0:0:0';
		}
		$stat_arr = explode(':', $stats .':');
		++$stat_arr[ $loglevel ];

		@ file_put_contents(
			$stat_file,
			"<?php exit; ?>{$stat_arr[0]}:{$stat_arr[1]}:{$stat_arr[2]}:{$stat_arr[3]}:{$stat_arr[4]}:" .
			"{$stat_arr[5]}:{$stat_arr[6]}:{$stat_arr[7]}:{$stat_arr[8]}:{$stat_arr[9]}",
			LOCK_EX
		);

		/**
		 * Create the log if it doesn't exist.
		 */
		if (! is_file( $log_file ) ) {
			$tmp = "<?php exit; ?>\n";
		} else {
			$tmp = '';
		}

		/**
		 * If we reach this part during a brute-force attack,
		 * NFW_REMOTE_ADDR hasn't been initialized yet.
		 */
		if (! defined('NFW_REMOTE_ADDR') ) {
			define('NFW_REMOTE_ADDR', $_SERVER['REMOTE_ADDR'] );
		}

		/**
		 * Encoding: NFW_LOG_ENCODING can be defined in the .htninja script (b64|hex).
		 * Default: hex.
		 */
		if ( defined('NFW_LOG_ENCODING') ) {
			if ( NFW_LOG_ENCODING == 'b64') {
				$encoding = 'b64:'. base64_encode( $res );
			} elseif ( NFW_LOG_ENCODING == 'none') {
				$encoding = $res;
			} else {
				$unp = unpack('H*', $res );
				$encoding = 'hex:'. array_shift( $unp );
			}
		} else {
			$unp = unpack('H*', $res );
			$encoding = 'hex:'. array_shift( $unp );
		}

		/**
		 * Only used by the plugin (post-detection).
		 */
		if ( $loglevel == NFWLOG_POSTDETECT ) {
			$SCRIPT_NAME    = '-';
			$REQUEST_METHOD = 'N/A';
			$REMOTE_ADDR    = '0.0.0.0';
			$loglevel       = NFWLOG_INFO;
		} else {
			$SCRIPT_NAME    = $_SERVER['SCRIPT_NAME'];
			$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
			$REMOTE_ADDR    = NFW_REMOTE_ADDR;
		}

		$elapse = nfw_fc_metrics('stop');

		@ file_put_contents( $log_file,
			$tmp . '[' . time() . '] ' . "[$elapse] " .
			"[{$_SERVER['SERVER_NAME']}] [#$incidentID] [$ruleid] [$loglevel] " .
			'[' . NinjaFirewall_IP::anonymize_ip( $REMOTE_ADDR, $nfw_options ) . '] ' .
			"[$http_ret_code] [$REQUEST_METHOD] [$SCRIPT_NAME] [$loginfo] [$encoding]\n",
			FILE_APPEND | LOCK_EX
		);

		/**
		 * Return the incident ID that will be displayed to the user.
		 */
		return $incidentID;
	}

}

// =====================================================================
// EOF
