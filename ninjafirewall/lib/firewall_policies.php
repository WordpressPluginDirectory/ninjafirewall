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

// Block immediately if user is not allowed
nf_not_allowed( 'block', __LINE__ );

$yes = __('Yes', 'ninjafirewall');
$no =  __('No', 'ninjafirewall');

$full_waf_msg = '<p class="description"><span class="dashicons dashicons-warning nfw-warning"></span>'.
	sprintf( __('To use this feature, please <a href="%s">go to the Dashboard page</a> and enable NinjaFirewall\'s Full WAF mode.', 'ninjafirewall'), '?page=NinjaFirewall' ) .'</p>';

if ( defined('NFW_WPWAF') ) {
	$option_disabled = 1;
} else {
	$option_disabled = 0;
}

$nfw_options = nfw_get_option( 'nfw_options' );
$nfw_rules = nfw_get_option( 'nfw_rules' );

// Tab and div display
if ( empty( $_REQUEST['tab'] ) ) { $_REQUEST['tab'] = 'basic'; }

if ( $_REQUEST['tab'] == 'intermediate' ) {
	$basic_tab = ''; $basic_div = ' style="display:none"';
	$intermediate_tab = ' nav-tab-active'; $intermediate_div = '';
	$advanced_tab = ''; $advanced_div = ' style="display:none"';

} elseif ( $_REQUEST['tab'] == 'advanced' ) {
	$basic_tab = ''; $basic_div = ' style="display:none"';
	$intermediate_tab = ''; $intermediate_div = ' style="display:none"';
	$advanced_tab = ' nav-tab-active'; $advanced_div = '';

} else {
	$_REQUEST['tab'] = 'basic';
	$basic_tab = ' nav-tab-active'; $basic_div = '';
	$intermediate_tab = ''; $intermediate_div = ' style="display:none"';
	$advanced_tab = ''; $advanced_div = ' style="display:none"';
}

?>
<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="<?php echo plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) ?>">&nbsp;<?php _e('Firewall Policies', 'ninjafirewall') ?></h1>
<?php

if ( isset( $_POST['nfw_options']) ) {
	if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'policies_save') ) {
		wp_nonce_ays('policies_save');
	}
	if (! empty($_POST['Save']) ) {
		nf_sub_policies_save();
		echo '<div class="updated notice is-dismissible"><p>' . __('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	} elseif (! empty($_POST['Default']) ) {
		nf_sub_policies_default();
		echo '<div class="updated notice is-dismissible"><p>' . __('Default values were restored.', 'ninjafirewall') . '</p></div>';
	} else {
		echo '<div class="error notice is-dismissible"><p>' . __('No action taken.', 'ninjafirewall') . '</p></div>';
	}
	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_rules = nfw_get_option( 'nfw_rules' );
}

?>
<br />
<h2 class="nav-tab-wrapper wp-clearfix" style="cursor:pointer">
	<a id="tab-basic" class="nav-tab<?php echo $basic_tab ?>" onClick="nfwjs_switch_tabs('basic', 'basic:intermediate:advanced')"><?php _e( 'Basic Policies', 'ninjafirewall' ) ?></a>
	<a id="tab-intermediate" class="nav-tab<?php echo $intermediate_tab ?>" onClick="nfwjs_switch_tabs('intermediate', 'basic:intermediate:advanced')"><?php _e( 'Intermediate Policies', 'ninjafirewall' ) ?></a>
	<a id="tab-advanced" class="nav-tab<?php echo $advanced_tab ?>" onClick="nfwjs_switch_tabs('advanced', 'basic:intermediate:advanced')"><?php _e( 'Advanced Policies', 'ninjafirewall' ) ?></a>
</h2>
<br />
<?php

echo '<form method="post" name="fwrules">';
wp_nonce_field('policies_save', 'nfwnonce', 0);

// ---------------------------------------------------------------------
// Basic options:
?>
<div id="basic-options"<?php echo $basic_div ?>>
	<?php
	if ( ( isset( $nfw_options['scan_protocol']) ) &&
		( preg_match( '/^[123]$/', $nfw_options['scan_protocol']) ) ) {
		$scan_protocol = $nfw_options['scan_protocol'];
	} else {
		$scan_protocol = 3;
	}

	?>
	<h3>HTTP / HTTPS</h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Enable NinjaFirewall for', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="radio" name="nfw_options[scan_protocol]" value="3"<?php checked($scan_protocol, 3 ) ?>>&nbsp;<?php _e('HTTP and HTTPS traffic (default)', 'ninjafirewall') ?></label></p>
				<p><label><input type="radio" name="nfw_options[scan_protocol]" value="1"<?php checked($scan_protocol, 1 ) ?>>&nbsp;<?php _e('HTTP traffic only', 'ninjafirewall') ?></label></p>
				<p><label><input type="radio" name="nfw_options[scan_protocol]" value="2"<?php checked($scan_protocol, 2 ) ?>>&nbsp;<?php _e('HTTPS traffic only', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />
	<br />

	<?php
		if ( empty( $nfw_options['sanitise_fn']) ) {
		$sanitise_fn = 0;
	} else {
		$sanitise_fn = 1;
	}
	if ( empty( $nfw_options['uploads']) ) {
		$uploads = 0;
		$sanitise_fn = 0;
	} else {
		$uploads = 1;
	}
	if ( empty( $nfw_options['substitute'] ) || strlen( $nfw_options['substitute'] ) > 1 || $nfw_options['substitute'] == '/' ) {
		$substitute = 'X';
	} else {
		$substitute = htmlspecialchars( $nfw_options['substitute'] );
	}
	?>
	<h3><?php _e('Uploads', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('File Uploads', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can allow uploads while rejecting potentially dangerous files, even if they are compressed inside a ZIP archive: scripts (PHP, CGI, Ruby, Python, bash/shell), C/C++ source code, binaries (MZ/PE/NE and ELF formats), system files (.htaccess, .htpasswd and PHP INI) and SVG files containing Javascript/XML events. You can easily limit the size of each uploaded file too, without having to modify your PHP configuration.', 'ninjafirewall' ) ?>"></span></th>
			<td>
				<select name="nfw_options[uploads]" onchange="nfwjs_upload_onoff(this);">
					<option value="1"<?php selected( $uploads, 1 ) ?>><?php echo __('Allow uploads', 'ninjafirewall') .' '. __('(default)', 'ninjafirewall') ?></option>
					<option value="0"<?php selected( $uploads, 0 ) ?>><?php _e('Disallow uploads', 'ninjafirewall') ?></option>
				</select>
				<br />
				<label><input type="checkbox" onclick='return nfwjs_sanitise(this);' name="nfw_options[sanitise_fn]"<?php checked( $sanitise_fn, 1 ); disabled( $uploads, 0 ) ?> id="san">
				<?php _e('Sanitise filenames', 'ninjafirewall') ?> (<?php _e('substitution character:', 'ninjafirewall') ?></label> <input id="subs" maxlength="1" size="1" value="<?php echo $substitute ?>" name="nfw_options[substitute]" type="text" <?php disabled( $uploads, 0 ) ?>/> )
			</td>
		</tr>
	</table>

	<br />
	<br />

	<?php
	if (! isset( $nfw_options['wp_dir'] ) ) {
		$nfw_options['wp_dir'] = '';
	}
	if ( strpos( $nfw_options['wp_dir'], 'wp-admin' ) !== FALSE ) {
		$wp_admin = 1;
	} else {
		$wp_admin = 0;
	}
	if ( strpos( $nfw_options['wp_dir'], 'wp-includes' ) !== FALSE ) {
		$wp_inc = 1;
	} else {
		$wp_inc = 0;
	}
	if ( strpos( $nfw_options['wp_dir'], 'uploads' ) !== FALSE ) {
		$wp_upl = 1;
	} else {
		$wp_upl = 0;
	}
	if ( strpos( $nfw_options['wp_dir'], 'cache' ) !== FALSE ) {
		$wp_cache = 1;
	} else {
		$wp_cache = 0;
	}
	if ( empty( $nfw_options['disallow_creation']) ) {
		$disallow_creation = 0;
	} else {
		$disallow_creation = 1;
	}
	if ( empty( $nfw_options['disallow_deletion']) ) {
		$disallow_deletion = 0;
	} else {
		$disallow_deletion = 1;
	}
	if ( empty( $nfw_options['disallow_privesc']) ) {
		$disallow_privesc = 0;
	} else {
		$disallow_privesc = 1;
	}
	if ( empty( $nfw_options['disallow_privesc_mu']) ) {
		$disallow_privesc_mu = 0;
	} else {
		$disallow_privesc_mu = 1;
	}
	if ( empty( $nfw_options['disallow_settings']) ) {
		$disallow_settings = 0;
	} else {
		$disallow_settings = 1;
	}
	if ( empty( $nfw_options['enum_archives']) ) {
		$enum_archives = 0;
	} else {
		$enum_archives = 1;
	}
	if ( empty( $nfw_options['enum_sitemap']) ) {
		$enum_sitemap = 0;
	} else {
		$enum_sitemap = 1;
	}
	if ( empty( $nfw_options['enum_login']) ) {
		$enum_login = 0;
	} else {
		$enum_login = 1;
	}
	if ( empty( $nfw_options['enum_restapi']) ) {
		$enum_restapi = 0;
	} else {
		$enum_restapi = 1;
	}
	if ( empty( $nfw_options['enum_feed']) ) {
		$enum_feed = 0;
	} else {
		$enum_feed = 1;
	}
	if ( empty( $nfw_options['no_appswd']) ) {
		$no_appswd = 0;
	} else {
		$no_appswd = 1;
	}
	if ( empty( $nfw_options['no_restapi']) ) {
		$no_restapi = 0;
	} else {
		$no_restapi = 1;
	}
	if ( empty( $nfw_options['restapi_loggedin']) || $no_restapi == 0 ) {
		$restapi_loggedin = 0;
	} else {
		$restapi_loggedin = 1;
	}
	if ( empty( $nfw_options['no_xmlrpc']) ) {
		$no_xmlrpc = 0;
	} else {
		$no_xmlrpc = 1;
	}
	if ( empty( $nfw_options['no_xmlrpc_multi']) ) {
		$no_xmlrpc_multi = 0;
	} else {
		$no_xmlrpc_multi = 1;
	}
	if ( empty( $nfw_options['no_xmlrpc_pingback']) ) {
		$no_xmlrpc_pingback = 0;
	} else {
		$no_xmlrpc_pingback = 1;
	}
	if ( empty( $nfw_options['no_post_themes']) ) {
		$no_post_themes = 0;
	} else {
		$no_post_themes = 1;
	}

	if ( empty( $nfw_options['force_ssl']) ) {
		$force_ssl = 0;
	} else {
		$force_ssl = 1;
	}
	if ( empty( $nfw_options['disallow_edit']) ) {
		$disallow_edit = 0;
	} else {
		$disallow_edit = 1;
	}
	if ( empty( $nfw_options['disallow_mods']) ) {
		$disallow_mods = 0;
	} else {
		$disallow_mods = 1;
	}
	if ( empty( $nfw_options['disable_error_handler']) ) {
		$disable_error_handler = 0;
	} else {
		$disable_error_handler = 1;
	}
	if ( empty( $nfw_options['disallow_publish']) ) {
		$disallow_publish = 0;
	} else {
		$disallow_publish = 1;
	}

	$force_ssl_already_enabled = 0;
	$disallow_edit_already_enabled = 0;
	$disallow_mods_already_enabled = 0;
	$disable_error_handler_already_enabled = 0;
	if ( defined('DISALLOW_FILE_EDIT') && ! $disallow_edit ) {
		$disallow_edit_already_enabled = 1;
	}
	if ( defined('DISALLOW_FILE_MODS') && ! $disallow_mods ) {
		$disallow_mods_already_enabled = 1;
	}
	if ( defined('WP_DISABLE_FATAL_ERROR_HANDLER') && ! $disable_error_handler ) {
		$disable_error_handler_already_enabled = 1;
	}
	if ( defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN == true && ! $force_ssl ) {
		$force_ssl_already_enabled = 1;
	}
	?>
	<h3>WordPress</h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Block direct access to any PHP file located in one of these directories', 'ninjafirewall') ?></th>
			<td>
				<?php
				if ( defined('NFW_WPWAF') ) {
					echo $full_waf_msg;
				}
				?>
				<table class="form-table">
					<tr style="border: solid 1px #DFDFDF;">
						<td align="center" width="10"><input type="checkbox" name="nfw_options[wp_admin]" id="wp_01"<?php checked( $wp_admin, 1 ); disabled( $option_disabled, 1) ?>></td>
						<td>
						<label for="wp_01">
						<p><code>/wp-admin/css/*</code></p>
						<p><code>/wp-admin/images/*</code></p>
						<p><code>/wp-admin/includes/*</code></p>
						<p><code>/wp-admin/js/*</code></p>
						</label>
						</td>
					</tr>
					<tr style="border: solid 1px #DFDFDF;">
						<td align="center" width="10"><input type="checkbox" name="nfw_options[wp_inc]" id="wp_02"<?php checked( $wp_inc, 1 ); disabled( $option_disabled, 1) ?>></td>
						<td>
						<label for="wp_02">
						<p><code>/wp-includes/*.php</code></p>
						<p><code>/wp-includes/css/*</code></p>
						<p><code>/wp-includes/images/*</code></p>
						<p><code>/wp-includes/js/*</code></p>
						<p><code>/wp-includes/theme-compat/*</code></p>
						</label>
						<br />
						<p class="description"><?php _e('NinjaFirewall will not block access to the TinyMCE WYSIWYG editor even if this option is enabled.', 'ninjafirewall') ?></p>
						</td>
					</tr>
					<tr style="border: solid 1px #DFDFDF;">
						<td align="center" width="10"><input type="checkbox" name="nfw_options[wp_upl]" id="wp_03"<?php checked( $wp_upl, 1 ); disabled( $option_disabled, 1) ?>></td>
						<td><label for="wp_03">
							<p><code>/<?php echo basename(WP_CONTENT_DIR); ?>/uploads/*</code></p>
							<p><code>/<?php echo basename(WP_CONTENT_DIR); ?>/blogs.dir/*</code></p>
						</label></td>
					</tr>
					<tr style="border: solid 1px #DFDFDF;">
						<td align="center" style="vertical-align:top" width="10"><input type="checkbox" name="nfw_options[wp_cache]" id="wp_04"<?php checked( $wp_cache, 1 ); disabled( $option_disabled, 1) ?>></td>
						<td style="vertical-align:top"><label for="wp_04"><code>*/cache/*</code></label>
						<br />
						<br />
						<p class="description"><?php _e('Unless you have PHP scripts in a "/cache/" folder that need to be accessed by your visitors, we recommend to enable this option.', 'ninjafirewall') ?></p>
						</td>
					</tr>
				</table>
				<br />&nbsp;
			</td>
		</tr>

		<?php
		if ( defined('NFW_DISABLE_PRVESC2') ) {
			$msg = '<p class="description">'. sprintf( __('To enable this option, please remove the %s constant from your wp-config.php or .htninja script.', 'ninjafirewall'), '<code>NFW_DISABLE_PRVESC2</code>' ) .'</p>';
			$disabled = 1;
			$disabled_mu = 1;
			$disallow_privesc = 0;
			$disallow_privesc_mu = 0;
		} else {
			$msg ='';
			$disabled = 0;
			$disabled_mu = 0;
		}
		$reg_user = ''; $res_user_msg = ' onclick="return nfwjs_reguserwarning(this)"';
		$reg_site = ''; $res_site_msg = ' onclick="return nfwjs_regsitewarning(this)"';
		// Multisite
		if ( is_multisite() ) {
			// 'none', 'user', 'blog' or 'all'
			$reg = get_site_option( 'registration' );
			if ( in_array( $reg, array('user', 'blog', 'all') ) ) {
				if ( $reg != 'blog' ) {
					$reg_user = $res_user_msg;
				}
				if ( $reg != 'user' ) {
					$reg_site = $res_site_msg;
				}
			}
			$main_site = ' '.__('(main site only)', 'ninjafirewall');
			$network_only = ' '.__('(not recommended if you allow sites registration)', 'ninjafirewall');
		// Single site
		} else {
			$reg = get_option( 'users_can_register' );
			if (! empty( $reg ) ) {
				$reg_user = $res_user_msg;
			}
			// Disable policy on a non-multisite installation
			$disabled_mu = 1;
			$main_site = '';
			$network_only = ' '.__('(multisite installation only)', 'ninjafirewall');
		}
		if ( class_exists( 'woocommerce' ) ) {
			$reg_user = ' onclick="return nfwjs_woowarning(this)"';
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('Permissions', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[disallow_settings]" value="1"<?php checked( $disallow_settings, 1 ) ?>>&nbsp;<?php echo _e('Block attempts to modify important WordPress settings', 'ninjafirewall') ?></label></p>
				<p><label><input<?php echo $reg_user ?> type="checkbox" name="nfw_options[disallow_creation]" value="1"<?php checked( $disallow_creation, 1 ) ?>>&nbsp;<?php _e('Block user accounts creation', 'ninjafirewall') ?></label></p>
				<p><label><input type="checkbox" name="nfw_options[disallow_deletion]" value="1"<?php checked( $disallow_deletion, 1 ) ?>>&nbsp;<?php _e('Block user accounts deletion', 'ninjafirewall') ?></label></p>
				<p><label><input <?php disabled( $disabled, 1 ) ?>type="checkbox" name="nfw_options[disallow_privesc]" value="1"<?php checked( $disallow_privesc, 1 ) ?>>&nbsp;<?php _e('Block attempts to gain administrative privileges', 'ninjafirewall'); echo $main_site; ?></label></p>
				<?php echo $msg ?>
				<p style="margin-left:20px"><label><input<?php echo $reg_site ?> <?php disabled( $disabled_mu, 1 ) ?> type="checkbox" name="nfw_options[disallow_privesc_mu]" value="1"<?php checked( $disallow_privesc_mu, 1 ) ?>>&nbsp;<?php _e('Apply to all subsites in the network', 'ninjafirewall'); echo $network_only; ?></label></p>

				<p><label><input type="checkbox" name="nfw_options[disallow_publish]" value="1"<?php checked( $disallow_publish, 1 ) ?>>&nbsp;<?php echo _e('Block attempts to publish, edit or delete a published post by users who do not have the right capabilities', 'ninjafirewall') ?> <?php _e('(recommended)', 'ninjafirewall') ?></label></p>
			</td>
		</tr>

		<?php
		if ( empty( $nfw_options['admin_ajax'] ) ) {
			$admin_ajax = 0;
		} else {
			$admin_ajax = 1;
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php _e('WordPress AJAX', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[admin_ajax]" value="1"<?php checked( $admin_ajax, 1 ) ?>>&nbsp;<?php _e('Protect <code>admin-ajax.php</code> against bots', 'ninjafirewall') ?></label></p>
				<p class="description"><?php printf( __('Your server IP (%s), localhost and private IP addresses will not be affected by this policy.', 'ninjafirewall'), htmlspecialchars( $_SERVER['SERVER_ADDR'] ) ) ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row" class="row-med"><?php _e('Protect against username enumeration', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[enum_archives]" value="1"<?php checked( $enum_archives, 1 ) ?>>&nbsp;<?php _e('Through the author archives', 'ninjafirewall') ?></label></p>
				<p><label><input type="checkbox" name="nfw_options[enum_sitemap]" value="1"<?php checked( $enum_sitemap, 1 ) ?>>&nbsp;<?php _e('Through the author sitemap', 'ninjafirewall') ?></label></p>
				<p><label><input type="checkbox" name="nfw_options[enum_login]" value="1"<?php checked( $enum_login, 1 ) ?>>&nbsp;<?php _e('Through the login page', 'ninjafirewall') ?></label></p>
				<p><label><input type="checkbox" name="nfw_options[enum_feed]" value="1"<?php checked( $enum_feed, 1 ) ?>>&nbsp;<?php _e('Through the blog feed', 'ninjafirewall') ?></label></p>
				<p><label><input type="checkbox" name="nfw_options[enum_restapi]" value="1"<?php checked( $enum_restapi, 1 ) ?>>&nbsp;<?php _e('Through the WordPress REST API', 'ninjafirewall') ?></label> <font color="red">*</font></p>
			</td>
		</tr>

		<tr>
			<th scope="row" class="row-med"><?php _e('WordPress REST API', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" onClick="nfwjs_no_restapi(this);" name="nfw_options[no_restapi]" value="1"<?php checked( $no_restapi, 1 ) ?>>&nbsp;<?php _e('Block any access to the API', 'ninjafirewall') ?></label> <font color="red">*</font></p>
				<p style="margin-left:20px"><label><input type="checkbox"<?php disabled($no_restapi, 0) ?> name="nfw_options[restapi_loggedin]" value="1"<?php checked( $restapi_loggedin, 1 ) ?>>&nbsp;<?php _e('Allow logged-in users to access the API', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('WordPress XML-RPC API', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" onClick="nfwjs_no_xmlrpc(this);" name="nfw_options[no_xmlrpc]" value="1"<?php checked( $no_xmlrpc, 1 ) ?>>&nbsp;<?php _e('Block any access to the API', 'ninjafirewall') ?></label> <font color="red">*</font></p>
				<p><label><input type="checkbox"<?php disabled($no_xmlrpc, 1) ?> name="nfw_options[no_xmlrpc_multi]" value="1"<?php checked( $no_xmlrpc_multi, 1 ) ?>>&nbsp;<?php _e('Block <code>system.multicall</code> method', 'ninjafirewall') ?></label> <font color="red">*</font></p>
				<p><label><input type="checkbox"<?php disabled($no_xmlrpc, 1) ?> name="nfw_options[no_xmlrpc_pingback]" value="1"<?php checked( $no_xmlrpc_pingback, 1 ) ?>>&nbsp;<?php _e('Block Pingbacks', 'ninjafirewall') ?></label></p>
				<br />
				<p class="description" style="font-size:14px"><font color="red">*</font> <?php _e('Disabling access to the REST or XML-RPC API may break some functionality on your blog, its themes or plugins (e.g., Gutenberg editor, Jetpack, Contact Form 7 etc).', 'ninjafirewall') ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row" class="row-med"><?php _e('Application Passwords', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[no_appswd]" value="1"<?php checked( $no_appswd, 1 ) ?>>&nbsp;<?php printf( __('Disable <a %s>Application Passwords</a>', 'ninjafirewall'), 'href="https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/" target="_blank" rel="noreferrer noopener"') ?></label></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="row-med" style="vertical-align:top"><?php _e('Block <code>POST</code> requests in the themes folder', 'ninjafirewall') ?> <code>/<?php echo basename(WP_CONTENT_DIR); ?>/themes</code></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[no_post_themes]', $yes, $no, 'small', $no_post_themes, $option_disabled );
				if ( defined('NFW_WPWAF') ) {
					echo $full_waf_msg;
				}
				?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><a name="builtinconstants"></a><?php _e('Force HTTPS for admin and logins', 'ninjafirewall') ?> <code><a href="https://wordpress.org/support/article/editing-wp-config-php/#require-ssl-for-admin-and-logins" target="_blank" rel="noreferrer noopener">FORCE_SSL_ADMIN</a></code></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[force_ssl]', $yes, $no, 'small', $force_ssl, $force_ssl_already_enabled, 'onclick="return nfwjs_ssl_warn(this,'. NFW_IS_HTTPS .');"' ) ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Disable the plugin and theme editor', 'ninjafirewall') ?> <code><a href="https://wordpress.org/support/article/editing-wp-config-php/#disable-the-plugin-and-theme-editor" target="_blank" rel="noreferrer noopener">DISALLOW_FILE_EDIT</a></code></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[disallow_edit]', $yes, $no, 'small', $disallow_edit, $disallow_edit_already_enabled ) ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Disable plugin and theme update/installation', 'ninjafirewall') ?> <code><a href="https://wordpress.org/support/article/editing-wp-config-php/#disable-plugin-and-theme-update-and-installation" target="_blank" rel="noreferrer noopener">DISALLOW_FILE_MODS</a></code></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[disallow_mods]', $yes, $no, 'small', $disallow_mods, $disallow_mods_already_enabled ) ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Disable the fatal error handler', 'ninjafirewall') ?> <code><a href="https://make.wordpress.org/core/2019/01/14/php-site-health-mechanisms-in-5-1/" target="_blank" rel="noreferrer noopener">WP_DISABLE_FATAL_ERROR_HANDLER</a></code></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[disable_error_handler]', $yes, $no, 'small', $disable_error_handler, $disable_error_handler_already_enabled ) ?>
			</td>
		</tr>

	</table>
	<a name="donotblockadmin"></a>
	<br />
	<br />

	<?php
	if ( empty( $nfw_options['wl_admin']) ) {
		$wl_admin = 0;
	} elseif ( $nfw_options['wl_admin'] == 2 ) {
		$wl_admin = 2;
	} else {
		$wl_admin = 1;
	}
	?>
	<table class="form-table nfw-table">
		<tr style="background-color:#F9F9F9;border: solid 1px #DFDFDF;">
			<th scope="row" class="row-med"><?php _e('Users Whitelist', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, the Access Control section allows you to whitelist WordPress users depending on their role.', 'ninjafirewall' ) ?>"></span></th>
			<td>
				<p><label><input type="radio" name="nfw_options[wl_admin]" value="1"<?php checked( $wl_admin, 1 ) ?>>&nbsp;<?php _e('Add the Administrator to the whitelist (default).', 'ninjafirewall') ?></label></p>
				<p><label><input type="radio" name="nfw_options[wl_admin]" value="2"<?php checked( $wl_admin, 2 ) ?>>&nbsp;<?php _e('Add all logged in users to the whitelist (users must log out and log in back again to apply changes).', 'ninjafirewall') ?></label></p>
				<p><label><input type="radio" name="nfw_options[wl_admin]" value="0"<?php checked( $wl_admin, 0 ) ?>>&nbsp;<?php _e('Disable users whitelist.', 'ninjafirewall') ?></label></p>
				<p class="description"><?php _e('Note: This feature  does not apply to <code>FORCE_SSL_ADMIN</code>, <code>DISALLOW_FILE_EDIT</code>, <code>DISALLOW_FILE_MODS</code> and <code>WP_DISABLE_FATAL_ERROR_HANDLER</code> options which, if enabled, are always enforced.', 'ninjafirewall') ?></p>
			</td>
		</tr>
	</table>

</div>


<?php
// ---------------------------------------------------------------------
// Intermediate options:
?>
<div id="intermediate-options"<?php echo $intermediate_div ?>>
	<?php
	if ( empty( $nfw_options['get_scan']) ) {
		$get_scan = 0;
	} else {
		$get_scan = 1;
	}
	if ( empty( $nfw_options['get_sanitise']) ) {
		$get_sanitise = 0;
	} else {
		$get_sanitise = 1;
	}
	?>
	<h3><?php _e('HTTP GET variable', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Scan <code>GET</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[get_scan]', $yes, $no, 'small', $get_scan ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Sanitise <code>GET</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[get_sanitise]', $yes, $no, 'small', $get_sanitise ) ?>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_options['post_scan']) ) {
		$post_scan = 0;
	} else {
		$post_scan = 1;
	}
	if ( empty( $nfw_options['post_sanitise']) ) {
		$post_sanitise = 0;
	} else {
		$post_sanitise = 1;
	}
	if ( empty( $nfw_options['post_b64']) ) {
		$post_b64 = 0;
	} else {
		$post_b64 = 1;
	}
	?>
	<h3><?php _e('HTTP POST variable', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Scan <code>POST</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[post_scan]', $yes, $no, 'small', $post_scan ) ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Sanitise <code>POST</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'warning', 'nfw_options[post_sanitise]', $yes, $no, 'small', $post_sanitise ) ?>
				<p class="description">&nbsp;<?php _e('Do not enable this option unless you know what you are doing!', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Decode Base64-encoded <code>POST</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[post_b64]', $yes, $no, 'small', $post_b64 ) ?>
			</td>
		</tr>
	</table>
	<br /><br />

	<?php
	if ( empty( $nfw_options['request_sanitise']) ) {
		$request_sanitise = 0;
	} else {
		$request_sanitise = 1;
	}
	?>
	<h3><?php _e('HTTP REQUEST variable', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Sanitise <code>REQUEST</code> variable', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'warning', 'nfw_options[request_sanitise]', $yes, $no, 'small', $request_sanitise ) ?>
				<p class="description">&nbsp;<?php _e('Do not enable this option unless you know what you are doing!', 'ninjafirewall') ?></p>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_options['cookies_scan']) ) {
		$cookies_scan = 0;
	} else {
		$cookies_scan = 1;
	}
	if ( empty( $nfw_options['cookies_sanitise']) ) {
		$cookies_sanitise = 0;
	} else {
		$cookies_sanitise = 1;
	}
	?>
	<h3><?php _e('Cookies', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Scan cookies', 'ninjafirewall') ?></th>
			<td>
				 <?php nfw_toggle_switch( 'info', 'nfw_options[cookies_scan]', $yes, $no, 'small', $cookies_scan ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Sanitise cookies', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[cookies_sanitise]', $yes, $no, 'small', $cookies_sanitise ) ?>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_options['ua_scan']) ) {
		$ua_scan = 0;
	} else {
		$ua_scan = 1;
	}
	if ( empty( $nfw_options['ua_sanitise']) ) {
		$ua_sanitise = 0;
	} else {
		$ua_sanitise = 1;
	}
	if ( empty( $nfw_rules[NFW_SCAN_BOTS]['ena']) ) {
		$block_bots = 0;
	} else {
		$block_bots = 1;
	}
	?>
	<h3><?php _e('HTTP_USER_AGENT server variable', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Scan <code>HTTP_USER_AGENT</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[ua_scan]', $yes, $no, 'small', $ua_scan ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Sanitise <code>HTTP_USER_AGENT</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[ua_sanitise]', $yes, $no, 'small', $ua_sanitise ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Block suspicious bots/scanners', 'ninjafirewall') ?>  <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, the Bot Access Control section allows you to select, edit, add and remove bots.', 'ninjafirewall' ) ?>"></span></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[block_bots]', $yes, $no, 'small', $block_bots ) ?>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_options['referer_scan']) ) {
		$referer_scan = 0;
	} else {
		$referer_scan = 1;
	}
	if ( empty( $nfw_options['referer_sanitise']) ) {
		$referer_sanitise = 0;
	} else {
		$referer_sanitise = 1;
	}
	if ( empty( $nfw_options['referer_post']) ) {
		$referer_post = 0;
	} else {
		$referer_post = 1;
	}
	?>
	<h3><?php _e('HTTP_REFERER server variable', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Scan <code>HTTP_REFERER</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[referer_scan]', $yes, $no, 'small', $referer_scan ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Sanitise <code>HTTP_REFERER</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[referer_sanitise]', $yes, $no, 'small', $referer_sanitise ) ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="row-med"><?php _e('Block <code>POST</code> requests that do not have an <code>HTTP_REFERER</code> header', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[referer_post]', $yes, $no, 'small', $referer_post ) ?>
				<p class="description">&nbsp;<?php _e('Keep this option disabled if you are using scripts like Paypal IPN, WordPress WP-Cron etc', 'ninjafirewall') ?>.</p>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_rules[NFW_LOOPBACK]['ena']) ) {
		$no_localhost_ip = 0;
	} else {
		$no_localhost_ip = 1;
	}
	if ( empty( $nfw_options['no_host_ip']) ) {
		$no_host_ip = 0;
	} else {
		$no_host_ip = 1;
	}
	if ( empty( $nfw_options['allow_local_ip']) ) {
		$allow_local_ip = 0;
	} else {
		$allow_local_ip = 1;
	}
	?>
	<h3>IP <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall, you can use the IP Access Control section to easily configure all IP address related options (source, whitelist, blacklist, rate limiting etc).', 'ninjafirewall' ) ?>"></span></h3>
	<table class="form-table nfw-table" border=0>
		<tr>
			<th scope="row" class="row-med"><?php _e('Block localhost IP in <code>GET/POST</code> request', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[no_localhost_ip]', $yes, $no, 'small', $no_localhost_ip ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Block HTTP requests with an IP in the <code>HTTP_HOST</code> header', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[no_host_ip]', $yes, $no, 'small', $no_host_ip ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Scan traffic coming from localhost and private IP address spaces', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[allow_local_ip]', $yes, $no, 'small', $allow_local_ip ) ?>
			</td>
		</tr>
	</table>

</div>

<?php
// ---------------------------------------------------------------------
// Advanced options:
?>
<div id="advanced-options"<?php echo $advanced_div ?>>

	<?php
	if (! isset( $nfw_options['response_headers'][0]) ) { $nfw_options['response_headers'][0] = 0; }
	if (! isset( $nfw_options['response_headers'][1]) ) { $nfw_options['response_headers'][1] = 0; }
	if (! isset( $nfw_options['response_headers'][2]) ) { $nfw_options['response_headers'][2] = 0; }
	if (! isset( $nfw_options['response_headers'][3]) ) { $nfw_options['response_headers'][3] = 3; }
	if (! isset( $nfw_options['response_headers'][4]) ) { $nfw_options['response_headers'][4] = 0; }
	if (! isset( $nfw_options['response_headers'][5]) ) { $nfw_options['response_headers'][5] = 0; }
	if (! isset( $nfw_options['response_headers'][6]) ) { $nfw_options['response_headers'][6] = 0; }
	if (! isset( $nfw_options['response_headers'][7]) ) { $nfw_options['response_headers'][7] = 0; }
	if (! isset( $nfw_options['response_headers'][8]) ) { $nfw_options['response_headers'][8] = 0; }
	if (! isset( $nfw_options['response_headers'][9]) ) { $nfw_options['response_headers'][9] = 0; }
	$err_msg = ''; $err = 0;
	// Some compatibility checks:
	// 1. header_register_callback(): requires PHP >=5.4
	// 2. headers_list() and header_remove(): some hosts may disable them.
	$tpl = __('The "HTTP response headers" options below are disabled because the %s PHP function is not available on your server.', 'ninjafirewall');
	if (! function_exists( 'header_register_callback' ) ) {
		$err_msg = sprintf( $tpl, 'header_register_callback()' );
		$err = 1;

	} elseif (! function_exists( 'headers_list' ) ) {
		$err_msg = sprintf( $tpl, 'headers_list()' );
		$err = 1;

	} elseif (! function_exists( 'header_remove' ) ) {
		$err_msg = sprintf( $tpl, 'header_remove()' );
		$err = 1;
	}
	if ( empty( $nfw_options['response_headers'] ) || ! empty( $err_msg ) ||
		! preg_match( '/^\d+$/', $nfw_options['response_headers'] ) ) {

		$nfw_options['response_headers'] = '0000000000';
	}
	?>

	<h3><?php _e('HTTP response headers', 'ninjafirewall')  ?></h3>
	<?php
	if (! empty( $err_msg ) ) {
		echo '<p class="description" style="color:red;font-size:14px">'. $err_msg .'</p>';
	}
	?>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s to protect against MIME type confusion attacks', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">X-Content-Type-Options</a>') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[x_content_type_options]', $yes, $no, 'small', $nfw_options['response_headers'][1], $err ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s to protect against clickjacking attempts', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">X-Frame-Options</a>') ?></th>
			<td>
				<select name="nfw_options[x_frame_options]" <?php disabled( $err, 1 ) ?>>
					<option value="0"<?php selected( $nfw_options['response_headers'][2], 0 ) ?>><?php echo $no; ?></option>
					<option value="1"<?php selected( $nfw_options['response_headers'][2], 1 ) ?>>SAMEORIGIN</option>
					<option value="2"<?php selected( $nfw_options['response_headers'][2], 2 ) ?>>DENY</option>
				</select>
				<p class="description"><?php _e('Setting this option to <code>DENY</code> may break some functionality on your blog, its themes or plugins.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">X-XSS-Protection</a>') ?></th>
			<td>
				<select name="nfw_options[x_xss_protection]" <?php disabled( $err, 1 ) ?>>
					<option value="3"<?php selected( $nfw_options['response_headers'][3], 3 ) ?>><?php echo $no; ?></option>
					<option value="0"<?php selected( $nfw_options['response_headers'][3], 0 ) ?>><?php printf( __('Set to %s', 'ninjafirewall'), '"0"'); ?></option>
					<option value="2"<?php selected( $nfw_options['response_headers'][3], 2 ) ?>><?php printf( __('Set to %s', 'ninjafirewall'), '"1"'); ?></option>
					<option value="1"<?php selected( $nfw_options['response_headers'][3], 1 ) ?>><?php printf( __('Set to %s', 'ninjafirewall'), '"1; mode=block"') ?></option>
				</select>
				<p class="description"><span class="dashicons dashicons-warning nfw-warning"></span><?php esc_html_e('This header is deprecated and most browsers phased out support for it. Consider using Content-Security-Policy instead.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Force %s flag on all cookies to mitigate CSRF attacks', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">SameSite</a>' ) ?></th>
			<td>
				<select name="nfw_options[cookies_samesite]" <?php disabled( $err, 1 ) ?>>
					<option value="0"<?php selected( $nfw_options['response_headers'][9], 0 ) ?>><?php echo $no; ?></option>
					<option value="1"<?php selected( $nfw_options['response_headers'][9], 1 ) ?>>SameSite=Lax</option>
					<option value="2"<?php selected( $nfw_options['response_headers'][9], 2 ) ?>>SameSite=Strict</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Force %s flag on all cookies to mitigate XSS attacks', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">HttpOnly</a>') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[cookies_httponly]', $yes, $no, 'small', $nfw_options['response_headers'][0], $err ) ?>
				<p class="description"><?php _e('If your PHP scripts use cookies that need to be accessed from JavaScript, you should not enable this option.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<?php
		// We don't send HSTS headers over HTTP (only display this message if there
		// is no other warning to display, $err==0 ):
		$hsts_err = 0;
		if ( NFW_IS_HTTPS == false && ! $err ) {
			$hsts_err = 1;
			$hsts_msg = __('HSTS headers can only be set when you are accessing your site over HTTPS.', 'ninjafirewall');
		} else {
			$hsts_msg = '';
			$hsts_err = 0;
		}
		if ( $err == 1 ) { $hsts_err = 1; }
		?>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s (HSTS) to enforce secure connections to the server', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">Strict-Transport-Security</a>') ?></th>
			<td>
				<select name="nfw_options[strict_transport]" <?php disabled( $hsts_err, 1 ) ?>>
					<option value="0"<?php selected( $nfw_options['response_headers'][4], 0 ) ?>><?php echo $no; ?></option>
					<option value="4"<?php selected( $nfw_options['response_headers'][4], 4 ) ?>><?php _e('Set "max-age" to 0', 'ninjafirewall') ?></option>
					<option value="1"<?php selected( $nfw_options['response_headers'][4], 1 ) ?>><?php _e('1 month', 'ninjafirewall') ?></option>
					<option value="2"<?php selected( $nfw_options['response_headers'][4], 2 ) ?>><?php _e('6 months', 'ninjafirewall') ?></option>
					<option value="3"<?php selected( $nfw_options['response_headers'][4], 3 ) ?>><?php _e('1 year', 'ninjafirewall') ?></option>
					<option value="5"<?php selected( $nfw_options['response_headers'][4], 5 ) ?>><?php _e('2 years', 'ninjafirewall') ?></option>
				</select>
				<?php
					// includeSubDomains=1, preload=2, both=3
					$preload = 0; $subdom = 0;
					if ( $nfw_options['response_headers'][5] == 3 ) {
						$preload = 1;
						$subdom = 1;
					} elseif ( $nfw_options['response_headers'][5] == 2 ) {
						$preload = 1;
					} elseif ( $nfw_options['response_headers'][5] == 1 ) {
						$subdom = 1;
					}
				?>
				<p><label><input type="checkbox" name="nfw_options[strict_transport_sub]" value="1"<?php checked( $subdom, 1 );disabled($hsts_err, 1) ?>>&nbsp;<?php _e('Apply to subdomains', 'ninjafirewall') ?></label>&nbsp;&nbsp;<label><input type="checkbox" name="nfw_options[strict_transport_preload]" value="1"<?php checked( $preload, 1 );disabled($hsts_err, 1) ?>>&nbsp;<?php _e('Preload', 'ninjafirewall') ?></label></p>
				<?php
				if (! empty( $hsts_msg ) ) {
					echo '<p class="description"><span class="dashicons dashicons-warning nfw-warning"></span>'. $hsts_msg .'</p>';
				}
				?>
			</td>
		</tr>

		<?php
			if (! isset( $nfw_options['csp_frontend_data'] ) ) {
				$nfw_options['csp_frontend_data'] = '';
			}
			if (! isset( $nfw_options['csp_backend_data'] ) ) {
				$nfw_options['csp_backend_data'] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.videopress.com *.google.com *.wp.com *.youtu.be *.googleapis.com;";
			}
			if (! isset( $nfw_options['response_headers'][6] ) ) {
				$nfw_options['response_headers'][6] = 0;
			}
			if (! isset( $nfw_options['response_headers'][7] ) ) {
				$nfw_options['response_headers'][7] = 0;
			}
		?>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s for the website frontend', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">Content-Security-Policy</a>') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[csp_frontend]', $yes, $no, 'small', $nfw_options['response_headers'][6], $err, 'onclick="nfwjs_csp_onoff(\'csp1_switch\',\'csp1\');"', 'csp1_switch' ) ?>
				<br />
				<textarea autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="nfw_options[csp_frontend_data]" id="csp1" class="large-text code" rows="8"<?php wp_readonly( $err, 1 ); wp_readonly( $nfw_options['response_headers'][6], 0 ) ?>><?php echo htmlspecialchars( $nfw_options['csp_frontend_data'] ) ?></textarea>
				<p class="description"><?php _e('This CSP header will apply to the website frontend only.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php printf( __('Set %s for the WordPress admin dashboard', 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">Content-Security-Policy</a>') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[csp_backend]', $yes, $no, 'small', $nfw_options['response_headers'][7], $err, 'onclick="nfwjs_csp_onoff(\'csp2_switch\',\'csp2\');"', 'csp2_switch' ) ?>
				<br />
				<textarea autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="nfw_options[csp_backend_data]" id="csp2" class="large-text code" rows="8"<?php wp_readonly( $err, 1 ); wp_readonly( $nfw_options['response_headers'][7], 0 ) ?>><?php echo htmlspecialchars( $nfw_options['csp_backend_data'] ) ?></textarea>
				<p class="description"><?php _e('This CSP header will apply to the WordPress admin dashboard only.', 'ninjafirewall') ?></p>
				<?php echo $err_msg ?>
			</td>
		</tr>

		<?php
		if (! isset( $nfw_options['response_headers'][8] ) ) {
			$nfw_options['response_headers'][8] = 0;
		}
		if ( empty( $nfw_options['referrer_policy_enabled'] ) ) {
			$nfw_options['referrer_policy_enabled'] = 0;
		} else {
			$nfw_options['referrer_policy_enabled'] = 1;
		}
		?>
		<tr>
			<th scope="row"><?php printf( __("Set %s (Chrome, Opera and Firefox browsers)", 'ninjafirewall'), '<a href="https://blog.nintechnet.com/securing-wordpress-with-a-web-application-firewall-ninjafirewall/#advanced-policies" target="_blank" rel="noreferrer noopener">Referrer-Policy</a>') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[referrer_policy_enabled]', $yes, $no, 'small', $nfw_options['referrer_policy_enabled'], $err, 'onclick="nfwjs_referrer_onoff();"', 'referrer_switch' ) ?>
				<br />
				<select id="rp_select" name="nfw_options[referrer_policy]"<?php disabled($nfw_options['referrer_policy_enabled'], 0) ?>>
					<option value="1"<?php selected($nfw_options['response_headers'][8], 1) ?>>no-referrer</option>
					<option value="2"<?php selected($nfw_options['response_headers'][8], 2) ?>>no-referrer-when-downgrade</option>
					<option value="3"<?php selected($nfw_options['response_headers'][8], 3) ?>>origin</option>
					<option value="4"<?php selected($nfw_options['response_headers'][8], 4) ?>>origin-when-cross-origin</option>
					<option value="5"<?php selected($nfw_options['response_headers'][8], 5) ?>>strict-origin</option>
					<option value="6"<?php selected($nfw_options['response_headers'][8], 6) ?>>strict-origin-when-cross-origin</option>
					<option value="7"<?php selected($nfw_options['response_headers'][8], 7) ?>>same-origin</option>
					<option value="8"<?php selected($nfw_options['response_headers'][8], 8) ?>>unsafe-url</option>
				</select>
			</td>
		</tr>

		<?php
		$custom_headers = '';
		if (! empty( $nfw_options['custom_headers'] ) ) {
			$headers = json_decode( $nfw_options['custom_headers'], true );
			if (! empty( $headers ) ) {
				foreach( $headers as $key => $value ) {
					$custom_headers .= "$key: $value\n";
				}
			}
		}
		?>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Custom HTTP headers', 'ninjafirewall'); ?></th>
			<td>
				<textarea autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="nfw_options[custom_headers]" class="large-text code" rows="8"><?php echo esc_textarea( $custom_headers ) ?></textarea>
				<p class="description"><?php _e('You can enter one or more custom HTTP headers. The format is <code>header: value</code>, one per line.', 'ninjafirewall') ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('HTTP headers test', 'ninjafirewall'); ?></th>
			<td>
				<button type="button" class="button-secondary" onClick="nfwjs_test_headers('<?php echo esc_attr( home_url( '/' ) ) ?>');" /><?php esc_html_e('View your website\'s HTTP response headers', 'ninjafirewall' )?></button>
				&nbsp;&nbsp;&nbsp;
				<img style="vertical-align:middle;display:none" id="progress-gif" src="<?php echo plugins_url('/images/progress.gif', dirname (__FILE__ ) ) ?>" />
				<p class="description"><?php _e('Make sure to save your changes before running the test.', 'ninjafirewall') ?></p>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	if ( empty( $nfw_rules[NFW_WRAPPERS]['ena']) ) {
		$php_wrappers = 0;
	} else {
		$php_wrappers = 1;
	}
	if ( empty( $nfw_options['php_errors']) ) {
		$php_errors = 0;
	} else {
		$php_errors = 1;
	}
	if ( empty( $nfw_options['php_self']) ) {
		$php_self = 0;
	} else {
		$php_self = 1;
	}
	if ( empty( $nfw_options['php_path_t']) ) {
		$php_path_t = 0;
	} else {
		$php_path_t = 1;
	}
	if ( empty( $nfw_options['php_path_i']) ) {
		$php_path_i = 0;
	} else {
		$php_path_i = 1;
	}
	if ( empty( $nfw_options['php_superglobals']) ) {
		$php_superglobals = 0;
	} else {
		$php_superglobals = 1;
	}
	?>
	<h3>PHP</h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Block PHP built-in wrappers in <code>GET</code>, <code>POST</code>, <code>HTTP_USER_AGENT</code>, <code>HTTP_REFERER</code> and cookies', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[php_wrappers]', $yes, $no, 'small', $php_wrappers ) ?>
			</td>
		</tr>

		<?php
		if (! empty( $nfw_rules[NFW_OBJECTS]['ena'] ) ) {
			if ( strpos( $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'], 'GET' )  !== FALSE) {
				$NFW_OBJECTS_GET = ' checked="checked"';
			} else {
				$NFW_OBJECTS_GET = '';
			}
			if ( strpos( $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'], 'POST' )  !== FALSE) {
				$NFW_OBJECTS_POST = ' checked="checked"';
			} else {
				$NFW_OBJECTS_POST = '';
			}
			if ( strpos( $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'], 'COOKIE' )  !== FALSE) {
				$NFW_OBJECTS_COOKIE = ' checked="checked"';
			} else {
				$NFW_OBJECTS_COOKIE = '';
			}
			if ( strpos( $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'], 'HTTP_USER_AGENT' )  !== FALSE) {
				$NFW_OBJECTS_HTTP_USER_AGENT = ' checked="checked"';
			} else {
				$NFW_OBJECTS_HTTP_USER_AGENT = '';
			}
			if ( strpos( $nfw_rules[NFW_OBJECTS]['cha'][1]['whe'], 'HTTP_REFERER' )  !== FALSE) {
				$NFW_OBJECTS_HTTP_REFERER = ' checked="checked"';
			} else {
				$NFW_OBJECTS_HTTP_REFERER = '';
			}
		} else {
			$NFW_OBJECTS_GET = ''; $NFW_OBJECTS_POST = ''; $NFW_OBJECTS_COOKIE = '';
			$NFW_OBJECTS_HTTP_USER_AGENT = ''; $NFW_OBJECTS_HTTP_REFERER = '';
		}
		?>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Block serialized PHP objects in the following global variables', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_rules[php_objects_get]" value="1"<?php echo $NFW_OBJECTS_GET ?>><code>GET</code></label><p>
				<p><label><input type="checkbox" name="nfw_rules[php_objects_post]" value="1"<?php echo $NFW_OBJECTS_POST ?>><code>POST</code></label><p>
				<p><label><input type="checkbox" name="nfw_rules[php_objects_cookie]" value="1"<?php echo $NFW_OBJECTS_COOKIE ?>><code>COOKIE</code></label><p>
				<p><label><input type="checkbox" name="nfw_rules[php_objects_http_user_agent]" value="1"<?php echo $NFW_OBJECTS_HTTP_USER_AGENT ?>><code>HTTP_USER_AGENT</code></label><p>
				<p><label><input type="checkbox" name="nfw_rules[php_objects_http_referer]" value="1"<?php echo $NFW_OBJECTS_HTTP_REFERER ?>><code>HTTP_REFERER</code></label><p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Block attempts to override PHP Superglobals', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[php_superglobals]', $yes, $no, 'small', $php_superglobals ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Hide PHP notice and error messages', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[php_errors]', $yes, $no, 'small', $php_errors ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Sanitise <code>PHP_SELF</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[php_self]', $yes, $no, 'small', $php_self ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Sanitise <code>PATH_TRANSLATED</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[php_path_t]', $yes, $no, 'small', $php_path_t ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-mid"><?php _e('Sanitise <code>PATH_INFO</code>', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[php_path_i]', $yes, $no, 'small', $php_path_i ) ?>
			</td>
		</tr>
	</table>

	<br /><br />

	<?php
	// If the document root is < 5 characters, disable the option
	if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) < 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['ena'] = 0;
		$disabled_msg = '<p class="description">' .
							__('This option is not compatible with your actual configuration.', 'ninjafirewall') .
							'</p>';
	} else {
		$disabled_msg = '';
	}

	if ( empty( $nfw_rules[NFW_DOC_ROOT]['ena']) ) {
		$block_doc_root = 0;
	} else {
		$block_doc_root = 1;
	}
	if ( empty( $nfw_rules[NFW_NULL_BYTE]['ena']) ) {
		$block_null_byte = 0;
	} else {
		$block_null_byte = 1;
	}
	if ( empty( $nfw_rules[NFW_ASCII_CTRL]['ena']) ) {
		$block_ctrl_chars = 0;
	} else {
		$block_ctrl_chars = 1;
	}
	?>
	<h3><?php _e('Various', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Block the <code>DOCUMENT_ROOT</code> server variable in HTTP request', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[block_doc_root]', $yes, $no, 'small', $block_doc_root ) ?>
				<?php echo $disabled_msg ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Block ASCII character 0x00 (NULL byte)', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[block_null_byte]', $yes, $no, 'small', $block_null_byte ) ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php _e('Block ASCII control characters 1 to 8 and 14 to 31', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_rules[block_ctrl_chars]', $yes, $no, 'small', $block_ctrl_chars ) ?>
			</td>
		</tr>
	</table>

	</div>

	<br />
	<br />

	<input type="hidden" name="tab" id="tab-selected" value="<?php echo htmlspecialchars( $_REQUEST['tab']  ) ?>" />
	<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Firewall Policies', 'ninjafirewall') ?>" />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input class="button-secondary" type="submit" name="Default" value="<?php _e('Restore Default Values', 'ninjafirewall') ?>" onclick="return nfwjs_restore_default();" />
	</form>
</div>

<?php

// ---------------------------------------------------------------------
// Save firewall policies.

function nf_sub_policies_save() {

	nf_not_allowed( 'block', __LINE__ );

	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_rules = nfw_get_option( 'nfw_rules' );

	if ( (isset( $_POST['nfw_options']['scan_protocol'])) &&
		( preg_match( '/^[123]$/', $_POST['nfw_options']['scan_protocol'])) ) {
			$nfw_options['scan_protocol'] = $_POST['nfw_options']['scan_protocol'];
	} else {
		$nfw_options['scan_protocol'] = 3;
	}

	if ( empty( $_POST['nfw_options']['uploads']) ) {
		$nfw_options['uploads'] = 0;
	} else {
		$nfw_options['uploads'] = 1;
	}

	if ( (isset( $_POST['nfw_options']['sanitise_fn']) ) && ( $nfw_options['uploads'] == 1) ) {
		$nfw_options['sanitise_fn'] = 1;
	} else {
		$nfw_options['sanitise_fn'] = 0;
	}
	// Substitution character:
	// Don't allow the '/' character:
	if ( empty( $_POST['nfw_options']['substitute'] ) || strlen( $_POST['nfw_options']['substitute'] ) > 1 || $_POST['nfw_options']['substitute'] == '/' ) {
		$nfw_options['substitute'] = 'X';
	} else {
		$nfw_options['substitute'] = $_POST['nfw_options']['substitute'];
	}


	if ( empty( $_POST['nfw_options']['get_scan']) ) {
		$nfw_options['get_scan'] = 0;
	} else {
		$nfw_options['get_scan'] = 1;
	}
	if ( empty( $_POST['nfw_options']['get_sanitise']) ) {
		$nfw_options['get_sanitise'] = 0;
	} else {
		$nfw_options['get_sanitise'] = 1;
	}


	if ( empty( $_POST['nfw_options']['post_scan']) ) {
		$nfw_options['post_scan'] = 0;
	} else {
		$nfw_options['post_scan'] = 1;
	}
	if ( empty( $_POST['nfw_options']['post_sanitise']) ) {
		$nfw_options['post_sanitise'] = 0;
	} else {
		$nfw_options['post_sanitise'] = 1;
	}
	if ( empty( $_POST['nfw_options']['post_b64']) ) {
		$nfw_options['post_b64'] = 0;
	} else {
		$nfw_options['post_b64'] = 1;
	}


	if ( empty( $_POST['nfw_options']['request_sanitise']) ) {
		$nfw_options['request_sanitise'] = 0;
	} else {
		$nfw_options['request_sanitise'] = 1;
	}


	if ( function_exists('header_register_callback') && function_exists('headers_list') && function_exists('header_remove') ) {
		$nfw_options['response_headers'] = '0000000000';
		$nfw_options['csp_frontend_data'] = '';
		$nfw_options['csp_backend_data'] = '';
		if ( empty( $_POST['nfw_options']['x_content_type_options']) ) {
			$nfw_options['response_headers'][1] = 0;
		} else {
			$nfw_options['response_headers'][1] = 1;
		}
		if ( empty( $_POST['nfw_options']['x_frame_options']) ) {
			$nfw_options['response_headers'][2] = 0;
		} elseif ( $_POST['nfw_options']['x_frame_options'] == 1) {
			$nfw_options['response_headers'][2] = 1;
		} else {
			$nfw_options['response_headers'][2] = 2;
		}
		// XSS filter:
		// 	0 = 0
		// 	1 = 1; mode=block
		// 	2 = 1
		// 	3 = unset
		if ( empty( $_POST['nfw_options']['x_xss_protection'] ) ) {
			$nfw_options['response_headers'][3] = 0;
		} elseif ( $_POST['nfw_options']['x_xss_protection'] == 1 ) {
			$nfw_options['response_headers'][3] = 1;
		} elseif ( $_POST['nfw_options']['x_xss_protection'] == 2 ) {
			$nfw_options['response_headers'][3] = 2;
		} else {
			$nfw_options['response_headers'][3] = 3;
		}

		if ( empty( $_POST['nfw_options']['cookies_httponly']) ) {
			$nfw_options['response_headers'][0] = 0;
		} else {
			$nfw_options['response_headers'][0] = 1;
		}
		// SameSite cookie
		if ( empty( $_POST['nfw_options']['cookies_samesite'] ) ) {
			$nfw_options['response_headers'][9] = 0;
		} elseif ( $_POST['nfw_options']['cookies_samesite'] == 1 ) {
			$nfw_options['response_headers'][9] = 1;
		} elseif ( $_POST['nfw_options']['cookies_samesite'] == 2 ) {
			$nfw_options['response_headers'][9] = 2;
		}

		// Strict-Transport-Security

		// includeSubDomains=1, preload=2, both=3
		$rs5 = 0;
		if ( isset( $_POST['nfw_options']['strict_transport_sub'] ) ) {
			$rs5 = 1;
		}
		if ( isset( $_POST['nfw_options']['strict_transport_preload'] ) ) {
			$rs5 += 2;
		}
		$nfw_options['response_headers'][5] = $rs5;

		if ( empty( $_POST['nfw_options']['strict_transport'] ) ) {
			$nfw_options['response_headers'][4] = 0;
			$nfw_options['response_headers'][5] = 0;
		} elseif ( $_POST['nfw_options']['strict_transport'] == 1) {
			$nfw_options['response_headers'][4] = 1;
		} elseif ( $_POST['nfw_options']['strict_transport'] == 2) {
			$nfw_options['response_headers'][4] = 2;
		} elseif ( $_POST['nfw_options']['strict_transport'] == 3) {
			$nfw_options['response_headers'][4] = 3;
		} elseif ( $_POST['nfw_options']['strict_transport'] == 4) {
			$nfw_options['response_headers'][4] = 4;
		} else {
			$nfw_options['response_headers'][4] = 5;
		}


		$nfw_options['csp_frontend_data'] = stripslashes( str_replace( array( '<', '>', "\x0a", "\x0d", '%', '$', '&') , '', $_POST['nfw_options']['csp_frontend_data'] ) );
		if ( empty( $_POST['nfw_options']['csp_frontend']) || empty( $nfw_options['csp_frontend_data'] ) ) {
			$nfw_options['response_headers'][6] = 0;
		} else {
			$nfw_options['response_headers'][6] = 1;
		}
		$nfw_options['csp_backend_data'] = stripslashes( str_replace( array( '<', '>', "\x0a", "\x0d", '%', '$', '&') , '', $_POST['nfw_options']['csp_backend_data'] ) );
		if ( empty( $_POST['nfw_options']['csp_backend']) || empty( $nfw_options['csp_backend_data'] ) ) {
			$nfw_options['response_headers'][7] = 0;
		} else {
			$nfw_options['response_headers'][7] = 1;
		}
		if ( empty( $_POST['nfw_options']['referrer_policy_enabled'] ) ) {
			$nfw_options['referrer_policy_enabled'] = 0;
			$_POST['nfw_options']['referrer_policy'] = 0;
		} else {
			$nfw_options['referrer_policy_enabled'] = 1;
		}

		if ( empty( $_POST['nfw_options']['referrer_policy'] ) || ! preg_match('/^[1-8]$/', $_POST['nfw_options']['referrer_policy'] ) ) {
			$nfw_options['response_headers'][8] = 0;
			$nfw_options['referrer_policy_enabled'] = 0;
		} else {
			$nfw_options['response_headers'][8] = (int)$_POST['nfw_options']['referrer_policy'];
		}
	}

	// Custom HTTP headers
	$custom_headers = array();
	if (! empty( $_POST['nfw_options']['custom_headers'] ) ) {
		$headers = explode( "\r\n", stripslashes( $_POST['nfw_options']['custom_headers'] ) );
		if (! empty( $headers[0] ) ) {
			foreach( $headers as $header ) {
				list( $key, $value ) = explode( ':', "$header:" );
				// Lowercase key name
				$key		= strtolower( trim( $key ) );
				$value	= trim( $value );
				if (! empty( $key ) && ! empty( $value ) ) {
					$custom_headers[ $key ] = $value;
				}
			}
		}
	}
	if (! empty( $custom_headers ) ) {
		$nfw_options['custom_headers'] = json_encode( $custom_headers );
	} else {
		unset ( $nfw_options['custom_headers'] );
	}

	if ( empty( $_POST['nfw_options']['cookies_scan']) ) {
		$nfw_options['cookies_scan'] = 0;
	} else {
		$nfw_options['cookies_scan'] = 1;
	}
	if ( empty( $_POST['nfw_options']['cookies_sanitise']) ) {
		$nfw_options['cookies_sanitise'] = 0;
	} else {
		$nfw_options['cookies_sanitise'] = 1;
	}


	if ( empty( $_POST['nfw_options']['ua_scan']) ) {
		$nfw_options['ua_scan'] = 0;
	} else {
		$nfw_options['ua_scan'] = 1;
	}
	if ( empty( $_POST['nfw_options']['ua_sanitise']) ) {
		$nfw_options['ua_sanitise'] = 0;
	} else {
		$nfw_options['ua_sanitise'] = 1;
	}


	if ( empty( $_POST['nfw_options']['referer_scan']) ) {
		$nfw_options['referer_scan'] = 0;
	} else {
		$nfw_options['referer_scan'] = 1;
	}
	if ( empty( $_POST['nfw_options']['referer_sanitise']) ) {
		$nfw_options['referer_sanitise'] = 0;
	} else {
		$nfw_options['referer_sanitise'] = 1;
	}
	if ( empty( $_POST['nfw_options']['referer_post']) ) {
		$nfw_options['referer_post'] = 0;
	} else {
		$nfw_options['referer_post'] = 1;
	}


	if ( empty( $_POST['nfw_options']['no_host_ip']) ) {
		$nfw_options['no_host_ip'] = 0;
	} else {
		$nfw_options['no_host_ip'] = 1;
	}
	if ( empty( $_POST['nfw_options']['allow_local_ip']) ) {
		$nfw_options['allow_local_ip'] = 0;
	} else {
		$nfw_options['allow_local_ip'] = 1;
	}


	if ( empty( $_POST['nfw_options']['php_errors']) ) {
		$nfw_options['php_errors'] = 0;
	} else {
		$nfw_options['php_errors'] = 1;
	}

	if ( empty( $_POST['nfw_options']['php_self']) ) {
		$nfw_options['php_self'] = 0;
	} else {
		$nfw_options['php_self'] = 1;
	}
	if ( empty( $_POST['nfw_options']['php_path_t']) ) {
		$nfw_options['php_path_t'] = 0;
	} else {
		$nfw_options['php_path_t'] = 1;
	}
	if ( empty( $_POST['nfw_options']['php_path_i']) ) {
		$nfw_options['php_path_i'] = 0;
	} else {
		$nfw_options['php_path_i'] = 1;
	}
	// Superglobals override
	if ( empty( $_POST['nfw_options']['php_superglobals']) ) {
		$nfw_options['php_superglobals'] = 0;
	} else {
		$nfw_options['php_superglobals'] = 1;
	}

	// We change those policies only if we're in Full WAF mode
	if (! defined( 'NFW_WPWAF' ) ) {
		$nfw_options['wp_dir'] = ''; $tmp = '';
		if ( isset( $_POST['nfw_options']['wp_admin']) ) {
			$tmp .= '/wp-admin/(?:css|images|includes|js)/|';
		}
		if ( isset( $_POST['nfw_options']['wp_inc']) ) {
			$tmp .= '/wp-includes/(?!ms-files\.php)(?:(?:css|images|js(?!/tinymce/wp-tinymce\.php)|theme-compat)/|[^/]+\.php)|';
		}
		if ( isset( $_POST['nfw_options']['wp_upl']) ) {
			$tmp .= '/' . basename(WP_CONTENT_DIR) .'/(?:uploads|blogs\.dir)/|';
		}
		if ( isset( $_POST['nfw_options']['wp_cache']) ) {
			$tmp .= '/cache/|';
		}
		if ( $tmp ) {
			$nfw_options['wp_dir'] = rtrim( $tmp, '|' );
		}
	}

	if (! isset( $_POST['nfw_options']['disallow_creation']) ) {
		$nfw_options['disallow_creation'] = 0;
	} else {
		$nfw_options['disallow_creation'] = 1;
	}
	if (! isset( $_POST['nfw_options']['disallow_deletion']) ) {
		$nfw_options['disallow_deletion'] = 0;
	} else {
		$nfw_options['disallow_deletion'] = 1;
	}
	if (! isset( $_POST['nfw_options']['disallow_settings']) ) {
		$nfw_options['disallow_settings'] = 0;
	} else {
		$nfw_options['disallow_settings'] = 1;
	}
	if (! isset( $_POST['nfw_options']['disallow_privesc']) ) {
		$nfw_options['disallow_privesc'] = 0;
	} else {
		$nfw_options['disallow_privesc'] = 1;
	}
	if (! isset( $_POST['nfw_options']['disallow_privesc_mu']) ) {
		$nfw_options['disallow_privesc_mu'] = 0;
	} else {
		$nfw_options['disallow_privesc_mu'] = 1;
	}
	if (! isset( $_POST['nfw_options']['disallow_publish']) ) {
		$nfw_options['disallow_publish'] = 0;
	} else {
		$nfw_options['disallow_publish'] = 1;
	}

	if (! isset( $_POST['nfw_options']['enum_archives']) ) {
		$nfw_options['enum_archives'] = 0;
	} else {
		$nfw_options['enum_archives'] = 1;
	}
	if (! isset( $_POST['nfw_options']['enum_sitemap']) ) {
		$nfw_options['enum_sitemap'] = 0;
	} else {
		$nfw_options['enum_sitemap'] = 1;
	}
	if (! isset( $_POST['nfw_options']['enum_login']) ) {
		$nfw_options['enum_login'] = 0;
	} else {
		$nfw_options['enum_login'] = 1;
	}
	if (! isset( $_POST['nfw_options']['admin_ajax']) ) {
		$nfw_options['admin_ajax'] = 0;
	} else {
		$nfw_options['admin_ajax'] = 1;
	}
	if (! isset( $_POST['nfw_options']['enum_restapi']) ) {
		$nfw_options['enum_restapi'] = 0;
	} else {
		$nfw_options['enum_restapi'] = 1;
	}
	if (! isset( $_POST['nfw_options']['enum_feed']) ) {
		$nfw_options['enum_feed'] = 0;
	} else {
		$nfw_options['enum_feed'] = 1;
	}
	if (! isset( $_POST['nfw_options']['no_restapi']) ) {
		$nfw_options['no_restapi'] = 0;
	} else {
		$nfw_options['no_restapi'] = 1;
	}
	if (! isset( $_POST['nfw_options']['restapi_loggedin']) || $nfw_options['no_restapi'] == 0 ) {
		$nfw_options['restapi_loggedin'] = 0;
	} else {
		$nfw_options['restapi_loggedin'] = 1;
	}
	if (! isset( $_POST['nfw_options']['no_appswd']) ) {
		$nfw_options['no_appswd'] = 0;
	} else {
		$nfw_options['no_appswd'] = 1;
	}

	if ( empty( $_POST['nfw_options']['no_xmlrpc']) ) {
		$nfw_options['no_xmlrpc'] = 0;
	} else {
		$nfw_options['no_xmlrpc'] = 1;
		$_POST['nfw_options']['no_xmlrpc_multi'] = 0;
		$_POST['nfw_options']['no_xmlrpc_pingback'] = 0;
	}
	if ( empty( $_POST['nfw_options']['no_xmlrpc_multi']) ) {
		$nfw_options['no_xmlrpc_multi'] = 0;
	} else {
		$nfw_options['no_xmlrpc_multi'] = 1;
	}
	if ( empty( $_POST['nfw_options']['no_xmlrpc_pingback']) ) {
		$nfw_options['no_xmlrpc_pingback'] = 0;
	} else {
		$nfw_options['no_xmlrpc_pingback'] = 1;
	}

	if ( empty( $_POST['nfw_options']['no_post_themes']) ) {
		$nfw_options['no_post_themes'] = 0;
	} else {
		$nfw_options['no_post_themes'] = '/'. basename(WP_CONTENT_DIR) .'/themes/';
	}

	if ( empty( $_POST['nfw_options']['force_ssl']) ) {
		$nfw_options['force_ssl'] = 0;
	} else {
		$nfw_options['force_ssl'] = 1;
	}

	if ( empty( $_POST['nfw_options']['disallow_edit']) ) {
		$nfw_options['disallow_edit'] = 0;
	} else {
		$nfw_options['disallow_edit'] = 1;
	}

	if ( empty( $_POST['nfw_options']['disable_error_handler']) ) {
		$nfw_options['disable_error_handler'] = 0;
	} else {
		$nfw_options['disable_error_handler'] = 1;
	}

	if ( empty( $_POST['nfw_options']['disallow_mods']) ) {
		$nfw_options['disallow_mods'] = 0;
	} else {
		$nfw_options['disallow_mods'] = 1;
	}


	if ( empty( $_POST['nfw_options']['wl_admin']) ) {
		$nfw_options['wl_admin'] = 0;
		if ( isset( $_SESSION['nfw_goodguy']) ) {
			unset( $_SESSION['nfw_goodguy']);
		}
	} else {
		if ( $_POST['nfw_options']['wl_admin'] == 2 ) {
			$nfw_options['wl_admin'] = 2;
		} else {
			$nfw_options['wl_admin'] = 1;
		}
		$_SESSION['nfw_goodguy'] = $nfw_options['wl_admin'];
	}


	if ( empty( $_POST['nfw_rules']['block_null_byte']) ) {
		$nfw_rules[NFW_NULL_BYTE]['ena'] = 0;
	} else {
		$nfw_rules[NFW_NULL_BYTE]['ena'] = 1;
	}
	if ( empty( $_POST['nfw_rules']['block_bots']) ) {
		$nfw_rules[NFW_SCAN_BOTS]['ena'] = 0;
	} else {
		$nfw_rules[NFW_SCAN_BOTS]['ena'] = 1;
	}
	if ( empty( $_POST['nfw_rules']['block_ctrl_chars']) ) {
		$nfw_rules[NFW_ASCII_CTRL]['ena'] = 0;
	} else {
		$nfw_rules[NFW_ASCII_CTRL]['ena'] = 1;
	}


	if ( empty( $_POST['nfw_rules']['block_doc_root']) ) {
		$nfw_rules[NFW_DOC_ROOT]['ena'] = 0;
	} else {

		if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) > 5 ) {
			$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', $_SERVER['DOCUMENT_ROOT'] );
			$nfw_rules[NFW_DOC_ROOT]['ena']	= 1;
		} elseif ( strlen( getenv( 'DOCUMENT_ROOT' ) ) > 5 ) {
			$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', getenv( 'DOCUMENT_ROOT' ) );
			$nfw_rules[NFW_DOC_ROOT]['ena']	= 1;
		} else {
			$nfw_rules[NFW_DOC_ROOT]['ena']	= 0;
		}
	}


	if ( empty( $_POST['nfw_rules']['php_wrappers']) ) {
		$nfw_rules[NFW_WRAPPERS]['ena'] = 0;
	} else {
		$nfw_rules[NFW_WRAPPERS]['ena'] = 1;
	}


	$nfw_objects = '';
	if (! empty( $_POST['nfw_rules']['php_objects_get'] ) ) {
		$nfw_objects .= "GET|";
	}
	if (! empty( $_POST['nfw_rules']['php_objects_post'] ) ) {
		$nfw_objects .= "POST|";
	}
	if (! empty( $_POST['nfw_rules']['php_objects_cookie'] ) ) {
		$nfw_objects .= "COOKIE|";
	}
	if (! empty( $_POST['nfw_rules']['php_objects_http_user_agent'] ) ) {
		$nfw_objects .= "SERVER:HTTP_USER_AGENT|";
	}
	if (! empty( $_POST['nfw_rules']['php_objects_http_referer'] ) ) {
		$nfw_objects .= "SERVER:HTTP_REFERER|";
	}
	if (! empty( $nfw_objects ) ) {
		$nfw_objects = rtrim( $nfw_objects, '|' );
		$nfw_rules[NFW_OBJECTS]['ena'] = 1;
	} else {
		// Disable rule:
		$nfw_rules[NFW_OBJECTS]['ena'] = 0;
	}
	$nfw_rules[NFW_OBJECTS]['cha'][1]['whe'] = $nfw_objects;


	if ( empty( $_POST['nfw_rules']['no_localhost_ip']) ) {
		$nfw_rules[NFW_LOOPBACK]['ena'] = 0;
	} else {
		$nfw_rules[NFW_LOOPBACK]['ena'] = 1;
	}

	nfw_update_option( 'nfw_options', $nfw_options );
	nfw_update_option( 'nfw_rules', $nfw_rules );

}

// ---------------------------------------------------------------------
// Restore default firewall policies.

function nf_sub_policies_default() {

	nf_not_allowed( 'block', __LINE__ );

	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_rules = nfw_get_option( 'nfw_rules' );

	$nfw_options['scan_protocol']		= 3;
	$nfw_options['uploads']				= 1;
	$nfw_options['sanitise_fn']		= 0;
	$nfw_options['substitute'] 		= 'X';
	$nfw_options['get_scan']			= 1;
	$nfw_options['get_sanitise']		= 0;
	$nfw_options['post_scan']			= 1;
	$nfw_options['post_sanitise']		= 0;
	$nfw_options['request_sanitise'] = 0;
	if ( function_exists('header_register_callback') && function_exists('headers_list') && function_exists('header_remove') ) {
		$nfw_options['response_headers'] = '0003000000';
		$nfw_options['referrer_policy_enabled'] = 0;
		// We unset it, so that a default sample line will be displayed:
		unset( $nfw_options['csp_backend_data'] );
		$nfw_options['csp_frontend_data'] = '';
	}
	$nfw_options['custom_headers']	= '';
	$nfw_options['cookies_scan']		= 1;
	$nfw_options['cookies_sanitise']	= 0;
	$nfw_options['ua_scan']				= 1;
	$nfw_options['ua_sanitise']		= 1;
	$nfw_options['referer_scan']		= 0;
	$nfw_options['referer_sanitise']	= 1;
	$nfw_options['referer_post']		= 0;
	$nfw_options['no_host_ip']			= 0;
	$nfw_options['allow_local_ip']	= 1;  // 1 == no !
	$nfw_options['php_superglobals']	= 1;
	$nfw_options['php_errors']			= 1;
	$nfw_options['php_self']			= 1;
	$nfw_options['php_path_t']			= 1;
	$nfw_options['php_path_i']			= 1;
	$nfw_options['wp_dir'] 				= '/wp-admin/(?:css|images|includes|js)/|' .
		'/wp-includes/(?!ms-files\.php)(?:(?:css|images|js(?!/tinymce/wp-tinymce\.php)|theme-compat)/|[^/]+\.php)|' .
		'/'. basename(WP_CONTENT_DIR) .'/(?:uploads|blogs\.dir)/';
	$nfw_options['disallow_creation']= 0;
	$nfw_options['disallow_deletion']= 0;
	$nfw_options['disallow_settings']= 1;
	$nfw_options['disallow_privesc']	= 1;
	$nfw_options['disallow_privesc_mu']	= 0;
	$nfw_options['disallow_publish']	= 0;
	$nfw_options['enum_archives']		= 0;
	$nfw_options['enum_sitemap']		= 0;
	$nfw_options['enum_login']			= 0;
	$nfw_options['admin_ajax']			= 0;
	$nfw_options['enum_restapi']		= 0;
	$nfw_options['enum_feed']			= 0;
	$nfw_options['no_restapi']			= 0;
	$nfw_options['restapi_loggedin']	= 0;
	$nfw_options['no_appswd']			= 0;
	$nfw_options['no_xmlrpc']			= 0;
	$nfw_options['no_xmlrpc_multi']	= 0;
	$nfw_options['no_xmlrpc_pingback']= 0;
	$nfw_options['no_post_themes']	= 0;
	$nfw_options['force_ssl'] 			= 0;
	$nfw_options['disallow_edit'] 	= 0;
	$nfw_options['disable_error_handler']	= 0;
	$nfw_options['disallow_mods'] 	= 0;
	$nfw_options['post_b64']			= 1;
	$nfw_options['wl_admin']			= 1;
	$_SESSION['nfw_goodguy'] 			= true;

	$nfw_rules[NFW_SCAN_BOTS]['ena']	= 1;
	$nfw_rules[NFW_LOOPBACK]['ena']	= 1;
	$nfw_rules[NFW_WRAPPERS]['ena']	= 1;

	$nfw_rules[NFW_OBJECTS]['ena'] = 1;
	$nfw_rules[NFW_OBJECTS]['cha'][1]['whe'] = 'GET|POST|SERVER:HTTP_USER_AGENT|SERVER:HTTP_REFERER';

	// Create but disable the rule by default
	if ( strlen( $_SERVER['DOCUMENT_ROOT'] ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', $_SERVER['DOCUMENT_ROOT'] );
	} elseif ( strlen( getenv( 'DOCUMENT_ROOT' ) ) > 5 ) {
		$nfw_rules[NFW_DOC_ROOT]['cha'][1]['wha'] = str_replace( '/', '/[./]*', getenv( 'DOCUMENT_ROOT' ) );
	}
	$nfw_rules[NFW_DOC_ROOT]['ena']  = 0;


	$nfw_rules[NFW_NULL_BYTE]['ena']  = 1;
	$nfw_rules[NFW_ASCII_CTRL]['ena'] = 0;

	nfw_update_option( 'nfw_options', $nfw_options);
	nfw_update_option( 'nfw_rules', $nfw_rules);

}

// ---------------------------------------------------------------------
// EOF
