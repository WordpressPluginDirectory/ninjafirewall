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

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// ---------------------------------------------------------------------
// WordPress functions/API can be used here because this file
// is loaded by WordPress.
// ---------------------------------------------------------------------

// Plugin's email signature
define( 'NF_PG_SIGNATURE', 'NinjaFirewall (WP Edition) - https://nintechnet.com/' ."\n".
	__('Support forum:', 'ninjafirewall') . ' http://wordpress.org/support/plugin/ninjafirewall' );
define( 'NF_PG_MORESEC', sprintf(
		__('Need more security? Check out our supercharged NinjaFirewall (WP+ Edition): %s', 'ninjafirewall'),
		'https://nintechnet.com/ninjafirewall/wp-edition/?comparison' ) );

// ---------------------------------------------------------------------
// EOF
