<?php
/*
 +---------------------------------------------------------------------+
 | NinjaFirewall (WP Edition)                                          |
 |                                                                     |
 | (c) NinTechNet - https://nintechnet.com/                            |
 +---------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or       |
 | modify it under the terms of the GNU General Public License as      |
 | published by the Free Software Foundation, either version 3 of      |
 | the License, or (at your option) any later version.                 |
 |                                                                     |
 | This program is distributed in the hope that it will be useful,     |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
 | GNU General Public License for more details.                        |
 +---------------------------------------------------------------------+ i18n+ / sa / 2
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// File Check scheduled scan?
if (defined('NFSCANDO') ) {
	include __DIR__ .'/monitoring_file_check.php';
	return;
}

// Tab and div display
if ( empty( $_REQUEST['tab'] ) ) { $_REQUEST['tab'] = 'filecheck'; }

if ( $_REQUEST['tab'] == 'filecheck' ) {
	$fileguard_tab = ''; $fileguard_div = ' style="display:none"';
	$filecheck_tab = ' nav-tab-active'; $filecheck_div = '';

} else {
	$_REQUEST['tab'] = 'fileguard';
	$fileguard_tab = ' nav-tab-active'; $fileguard_div = '';
	$filecheck_tab = ''; $filecheck_div = ' style="display:none"';
}

?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) ?>">&nbsp;<?php _e('Monitoring', 'ninjafirewall') ?></h1>
	<br />
	<h2 class="nav-tab-wrapper wp-clearfix" style="cursor:pointer">
		<a id="tab-filecheck" class="nav-tab<?php echo $filecheck_tab ?>" onClick="nfwjs_switch_tabs('filecheck', 'fileguard:filecheck')"><?php _e( 'File Check', 'ninjafirewall' ) ?></a>
		<a id="tab-fileguard" class="nav-tab<?php echo $fileguard_tab ?>" onClick="nfwjs_switch_tabs('fileguard', 'fileguard:filecheck')"><?php _e( 'File Guard', 'ninjafirewall' ) ?></a>
		<a class="nav-tab"><span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can also monitor the response body, i.e., the output of the HTML page, and search it for some specific keywords, which can be useful to identify errors, hacked content and data leakage issues on your website.', 'ninjafirewall' ) ?>"></span></a>
	</h2>
	<br />

	<!-- File Guard -->
	<div id="fileguard-options"<?php echo $fileguard_div ?>>
		<?php include __DIR__ .'/monitoring_file_guard.php'; ?>
	</div>

	<!-- File Check -->
	<div id="filecheck-options"<?php echo $filecheck_div ?>>
		<?php include __DIR__ .'/monitoring_file_check.php'; ?>
	</div>

</div>
<?php

// ---------------------------------------------------------------------
// EOF
