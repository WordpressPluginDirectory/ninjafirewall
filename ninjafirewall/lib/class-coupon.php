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

if ( class_exists('NinjaFirewall_coupon') ) {
	return;
}

class NinjaFirewall_coupon {

	private $options		= [];
	private $frequency	= 86400; /** Daily check only */
	private $url			= 'https://api.nintechnet.com/coupons';
	private $cache 		= NFW_LOG_DIR . '/nfwlog/cache';
	private $file 			= 'coupon.png';

	/**
	 * Retrieve NinjaFirewall's options.
	 */
	function __construct() {

		$this->options = nfw_get_option('nfw_options');
	}


	/**
	 * Display any available coupon.
	 */
	function show() {

		if ( empty( $this->options['coupon']['date'] ) ) {
			return ['error' => 'no coupon'];
		}
		/**
		 * Make sure it didn't expire yet.
		 */
		$today = date('Y-m-d');
		if ( $today > $this->options['coupon']['date'] ) {
			return ['error' => 'expired coupon'];
		}

		if (! is_file( "{$this->cache}/{$this->file}" ) ) {
			return ['error' => 'missing file'];
		}
		$data = file_get_contents( "{$this->cache}/{$this->file}" );

		$until = 'This offer is valid until '.
					date('F d', strtotime( $this->options['coupon']['date'] ) );

		echo '<a href="https://nintechnet.com/" alt="Go Pro! Limited time offer" '.
			'title="Go Pro! Limited time offer" target="_blank" rel="noreferrer noopener">'.
			'<img style="max-width:250px" src="data:image/png;base64, '. esc_attr( $data ) .'" />'.
			'<br />'. esc_html( $until ) .'</a>';
	}


	/**
	 * Remote connection (WP-CRON).
	 */
	function run() {
		/**
		 * We run on the main site only.
		 */
		if (! is_main_site() ) {
			return ['error' => 'child site'];
		}

		/**
		 * It should not run more than once daily.
		 */
		if (! empty( $this->options['cronjobs']['coupon']['last'] ) &&
			$this->options['cronjobs']['coupon']['last'] + $this->frequency > time() ) {

			return ['error' => 'frequency'];
		}
		/**
		 * Update last checked time.
		 */
		$this->options['cronjobs']['coupon']['last'] = time();
		nfw_update_option('nfw_options', $this->options );
		/**
		 * Connect.
		 */
		global $wp_version;
		$res = wp_remote_get(
			$this->url,
			[
				'timeout'		=> 5,
				'httpversion'	=> '1.1' ,
				'user-agent'	=> 'Mozilla/5.0 (compatible; NinjaFirewall/'.
										NFW_ENGINE_VERSION ."; WordPress/$wp_version)",
				'sslverify'		=> true,
				'headers' => [
					'ntn-plugin'	=> 'nf',
					'ntn-cache'		=>	md5( network_site_url() )
				]
			]
		);
		if (! is_wp_error( $res ) && $res['response']['code'] == 200 ) {
			$coupon = json_decode( $res['body'], true );

			if ( empty( $coupon['nf']['img'] ) ) {
				/**
				 * Clear the old coupon.
				 */
				if (! empty( $this->options['coupon'] ) ) {
					unset( $this->options['coupon'] );
					nfw_update_option('nfw_options', $this->options );
				}
				return ['error' => 'no coupon'];
			}
			/**
			 * Save the image.
			 */
			@ file_put_contents( "{$this->cache}/{$this->file}", $coupon['nf']['img'] );
			$coupon['nf']['img'] = $this->file;

			if ( empty( $this->options['coupon'] ) || $this->options['coupon'] != $coupon['nf'] ) {
				/**
				 * Save/update the coupon.
				 */
				$this->options['coupon'] = $coupon['nf'];
				nfw_update_option('nfw_options', $this->options );
			}
			return $coupon['nf'];
		}
		return ['error' => 'HTTP error'];
	}

}

// =====================================================================
// EOF
