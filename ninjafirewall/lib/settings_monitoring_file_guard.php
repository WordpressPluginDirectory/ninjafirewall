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

nf_not_allowed( 'block', __LINE__ );

$nfw_options = nfw_get_option( 'nfw_options' );

if ( defined('NFW_WPWAF') ) {
	?>
	<div class="nfw-notice nfw-notice-orange">
		<p><?php printf( __('You are running NinjaFirewall in <i>WordPress WAF</i> mode. The %s feature will be limited to WordPress files only (e.g., index.php, wp-login.php, xmlrpc.php, admin-ajax.php, wp-load.php etc). If you want it to apply to any PHP script, please <a href="%s">go to the Dashboard page</a> and enable NinjaFirewall\'s Full WAF mode.', 'ninjafirewall'), 'File Guard', '?page=NinjaFirewall') ?></p>
	</div>
	<?php
}

// Ensure cache folder is writable
if (! is_writable( NFW_LOG_DIR . '/nfwlog/cache/') ) {
	echo '<div class="nfw-notice nfw-notice-red"><p>' .
		sprintf( __('The cache directory %s is not writable. Please change its permissions (0777 or equivalent).', 'ninjafirewall'), '('. htmlspecialchars(NFW_LOG_DIR) . '/nfwlog/cache/)' ) . '</p></div>';
}

if ( isset( $_POST['save_fileguard']) ) {
	if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'fileguard_save') ) {
		wp_nonce_ays('fileguard_save');
	}
	nf_sub_fileguard_save();
	$nfw_options = nfw_get_option( 'nfw_options' );
	echo '<div class="updated notice is-dismissible"><p>' . __('Your changes have been saved.', 'ninjafirewall') .'</p></div>';
}

if ( empty($nfw_options['fg_enable']) ) {
	$nfw_options['fg_enable'] = 0;
} else {
	$nfw_options['fg_enable'] = 1;
}
if ( empty($nfw_options['fg_mtime']) || ! preg_match('/^[1-9][0-9]?$/', $nfw_options['fg_mtime']) ) {
	$nfw_options['fg_mtime'] = 10;
}
if ( empty($nfw_options['fg_exclude']) ) {
	$fg_exclude = '';
} else {
	$tmp = str_replace('|', ',', $nfw_options['fg_exclude']);
	$fg_exclude = preg_replace( '/\\\([`.\\/\\\+*?\[^\]$(){}=!<>:-])/', '$1', $tmp );
}
?>
<form method="post" name="nfwfilefuard">
	<?php wp_nonce_field('fileguard_save', 'nfwnonce', 0); ?>
	<table class="form-table nfw-table">
		<tr style="background-color:#F9F9F9;border: solid 1px #DFDFDF;">
			<th scope="row" class="row-med"><?php _e('Enable File Guard', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'green', 'nfw_options[fg_enable]', __('Enabled', 'ninjafirewall'), __('Disabled', 'ninjafirewall'), 'large', $nfw_options['fg_enable'], false, 'onclick="nfwjs_up_down(\'fg_table\');"' ) ?>
			</td>
		</tr>
	</table>

	<br />

	<div id="fg_table"<?php echo $nfw_options['fg_enable'] == 1 ? '' : ' style="display:none"' ?>>
		<table class="form-table nfw-table">
			<tr>
				<th scope="row" class="row-med"><?php _e('Real-time detection', 'ninjafirewall') ?></th>
				<td>
				<?php
					printf( __('Monitor file activity and send an alert when someone is accessing a PHP script that was modified or created less than %s hour(s) ago.', 'ninjafirewall'), '<input maxlength="2" size="2" value="'. $nfw_options['fg_mtime'] .'" name="nfw_options[fg_mtime]" id="mtime" class="small-text" type="number" />');
				?>
				</td>
			</tr>
			<tr>
				<th scope="row" class="row-med"><?php _e('Exclude the following files/folders (optional)', 'ninjafirewall') ?></th>
				<td><input class="large-text" type="text" maxlength="255" name="nfw_options[fg_exclude]" value="<?php echo htmlspecialchars( $fg_exclude ); ?>" placeholder="<?php _e('e.g.,', 'ninjafirewall') ?> /foo/bar/cache/ <?php _e('or', 'ninjafirewall') ?> /cache/" /><br /><span class="description"><?php _e('Full or partial case-sensitive string(s), max. 255 characters. Multiple values must be comma-separated', 'ninjafirewall') ?> (<code>,</code>).</span></td>
			</tr>
		</table>
	</div>
	<br />
	<input class="button-primary" type="submit" name="Save" value="<?php _e('Save File Guard options', 'ninjafirewall') ?>" />
	<input type="hidden" name="tab" value="fileguard" />
	<input type="hidden" name="save_fileguard" value="1" />
</form>
<?php

// ---------------------------------------------------------------------

function nf_sub_fileguard_save() {

	nf_not_allowed( 'block', __LINE__ );

	$nfw_options = nfw_get_option( 'nfw_options' );

	if ( empty($_POST['nfw_options']['fg_enable']) ) {
		$nfw_options['fg_enable'] = 0;
	} else {
		$nfw_options['fg_enable'] = $_POST['nfw_options']['fg_enable'];
	}

	if ( empty($_POST['nfw_options']['fg_mtime']) || ! preg_match('/^[1-9][0-9]?$/', $_POST['nfw_options']['fg_mtime']) ) {
		$nfw_options['fg_mtime'] = 10;
	} else {
		$nfw_options['fg_mtime'] = $_POST['nfw_options']['fg_mtime'];
	}

	if ( empty($_POST['nfw_options']['fg_exclude']) || strlen($_POST['nfw_options']['fg_exclude']) > 255 ) {
		$nfw_options['fg_exclude'] = '';
	} else {
		$exclude = '';
		$fg_exclude =  explode(',', $_POST['nfw_options']['fg_exclude'] );
		foreach ($fg_exclude as $path) {
			if ( $path ) {
				$path = str_replace( array(' ', '\\', '|'), '', $path);
				$exclude .= preg_quote( rtrim($path, ','), '`') . '|';
			}
		}
		$nfw_options['fg_exclude'] = rtrim($exclude, '|');
	}

	nfw_update_option( 'nfw_options', $nfw_options );

}

// ---------------------------------------------------------------------
// EOF
