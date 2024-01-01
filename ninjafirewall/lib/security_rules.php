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

// NFUPDATESDO: scheduled update (1), installation (2) or plugin update (3 - deprecated since v3.8)?
// > Skip all HTML code below:
if (defined('NFUPDATESDO') ) {
	include __DIR__ .'/security_rules_update.php';
	return;
}

// Tab and div display
if ( empty( $_REQUEST['tab'] ) ) { $_REQUEST['tab'] = 'updates'; }

if ( $_REQUEST['tab'] == 'editor' ) {
	$updates_tab = ''; $updates_div = ' style="display:none"';
	$editor_tab = ' nav-tab-active'; $editor_div = '';

} else {
	$_REQUEST['tab'] = 'updates';
	$updates_tab = ' nav-tab-active'; $updates_div = '';
	$editor_tab = ''; $editor_div = ' style="display:none"';
}

?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) ?>">&nbsp;<?php _e('Security Rules', 'ninjafirewall') ?></h1>
	<br />
	<h2 class="nav-tab-wrapper wp-clearfix" style="cursor:pointer">
		<a id="tab-updates" class="nav-tab<?php echo $updates_tab ?>" onClick="nfwjs_switch_tabs('updates', 'updates:editor')"><?php _e( 'Rules Updates', 'ninjafirewall' ) ?></a>
		<a id="tab-editor" class="nav-tab<?php echo $editor_tab ?>" onClick="nfwjs_switch_tabs('editor', 'updates:editor')"><?php _e( 'Rules Editor', 'ninjafirewall' ) ?></a>
	</h2>
	<br />

	<!-- Security rules updates -->
	<div id="updates-options"<?php echo $updates_div ?>>
		<?php include __DIR__ .'/security_rules_update.php'; ?>
	</div>

	<!-- Security rules editor -->
	<div id="editor-options"<?php echo $editor_div ?>>
		<?php include __DIR__ .'/security_rules_editor.php'; ?>
	</div>

</div>
<?php
// ---------------------------------------------------------------------
// EOF
