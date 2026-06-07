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

if (! current_user_can( 'manage_network' ) ) {
	die( '<br /><br /><br /><div class="error notice is-dismissible"><p>' .
		sprintf( __('You are not allowed to perform this task (%s).', 'ninjafirewall'), __LINE__) .
		'</p></div>' );
}

$nfw_options = nfw_get_option( 'nfw_options' );

echo '
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="'. plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) .'">&nbsp;' . __('Network', 'ninjafirewall') . '</h1>';

if (! is_multisite() ) {
	echo '<div class="updated notice is-dismissible"><p>' . __('You do not have a multisite network.', 'ninjafirewall') . '</p></div></div>';
	return;
}

// Saved?
if (! empty( $_POST['nf-network'] ) ) {

	if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'network_save') ) {
		wp_nonce_ays('network_save');
	}
	if ( empty( $_POST['nfw_options']['nt_show_status'] ) ) {
		$nfw_options['nt_show_status'] = 2;
	} else {
		$nfw_options['nt_show_status'] = 1;
	}
	// Update options
	nfw_update_option( 'nfw_options', $nfw_options );
	echo '<div class="updated notice is-dismissible"><p>' . __('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	$nfw_options = nfw_get_option( 'nfw_options' );
}

if ( empty( $nfw_options['nt_show_status'] ) || $nfw_options['nt_show_status'] == 2 ) {
	$nt_show_status = 0;
} else {
	$nt_show_status = 1;
}
?>
	<br />
	<form method="post" name="nfwnetwork">
	<?php wp_nonce_field('network_save', 'nfwnonce', 0); ?>
	<h3><?php _e('NinjaFirewall Status', 'ninjafirewall') ?></h3>
		<table class="form-table nfw-table">
			<tr>
				<th scope="row" class="row-med"><?php _e('Display NinjaFirewall status icon in the WordPress ToolBar of all sites in the network', 'ninjafirewall') ?></th>
				<td>
					<?php nfw_toggle_switch( 'info', 'nfw_options[nt_show_status]', __('Yes', 'ninjafirewall'), __('No', 'ninjafirewall'), 'small', $nt_show_status ) ?>
				</td>
			</tr>
		</table>

		<br />
		<br />
		<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Network options', 'ninjafirewall') ?>" />
		<input type="hidden" name="nf-network" value="1" />
	</form>
</div>
<?php
// ---------------------------------------------------------------------
// EOF
