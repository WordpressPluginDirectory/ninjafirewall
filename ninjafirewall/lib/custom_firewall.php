<?php
// +---------------------------------------------------------------------+
// | NinjaFirewall (WP Edition)                                          |
// |                                                                     |
// | (c) NinTechNet - https://nintechnet.com/                            |
// +---------------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or       |
// | modify it under the terms of the GNU General Public License as      |
// | published by the Free Software Foundation, either version 3 of      |
// | the License, or (at your option) any later version.                 |
// |                                                                     |
// | This program is distributed in the hope that it will be useful,     |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
// | GNU General Public License for more details.                        |
// +---------------------------------------------------------------------+ sa

// ---------------------------------------------------------------------
// WARNING: Do not use any WordPress function, including __() or _e().
// In "Full WAF" mode, this file will be loaded **before** WordPress.
// ---------------------------------------------------------------------

// Firewall's email signature
const NF_FW_SIGNATURE = 'NinjaFirewall (WP Edition) - https://nintechnet.com/' ."\n".
	'Support forum: http://wordpress.org/support/plugin/ninjafirewall';

// File Guard email body message
const NF_FW_FG_SUBJECT = '[NinjaFirewall] Alert: File Guard detection';
const NF_FW_FG_MSG = 'Someone accessed a script that was modified or created less than %s hour(s) ago:';
const NF_FW_FG_MSG_2 = 'Last changed on:';

// ---------------------------------------------------------------------
// EOF
