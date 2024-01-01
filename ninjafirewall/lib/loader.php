<?php
/*
Plugin Name: NinjaFirewall WP (mu-plugin)
Plugin URI: https://nintechnet.com/
Description: NinjaFirewall's fallback loader. Do not remove. This file will be deleted when uninstalling NinjaFirewall.
Version: 1.0
Author: The Ninja Technologies Network
Author URI: https://nintechnet.com/
License: GPLv3 or later
Network: true
*/
if (! defined('ABSPATH') || defined('NFW_STATUS') || defined('NFW_WPWAF') || ! file_exists( WP_PLUGIN_DIR .'/ninjafirewall/lib/firewall.php' ) ) {
	return false;
}
define('NFW_WPWAF', 2);
@include_once WP_PLUGIN_DIR .'/ninjafirewall/lib/firewall.php';
