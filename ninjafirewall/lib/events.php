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
 +---------------------------------------------------------------------+ i18n+ / sa
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

function nfw_sys_events() {

	$nfw_options = nfw_get_option( 'nfw_options' );

	$script			= $_SERVER['SCRIPT_NAME'];
	$label_name		= __('Name:', 'ninjafirewall');
	$label_plugin	= __('Plugin', 'ninjafirewall');
	$label_theme	= __('Theme', 'ninjafirewall');
	$label_version	= __('Version:', 'ninjafirewall');
	$alert_action	= 0;

	// themes.php
	if ( strpos( $script, '/themes.php' ) !== FALSE ) {

		if ( current_user_can( 'switch_themes' ) && isset( $_GET['action'] ) ) {

			if ( $_GET['action'] == 'activate' && ! empty( $nfw_options['a_23'] ) ) {
				$theme			= wp_get_theme( $_GET['stylesheet'] );
				$alert_action	= sprintf( '%s %s', $label_theme, __('activated', 'ninjafirewall') );
				$alert_item		= sprintf( '%s %s', $label_name, $theme );

			} elseif ( $_GET['action'] == 'delete' && current_user_can( 'delete_themes' ) && ! empty( $nfw_options['a_24'] ) ) {
				$theme			= wp_get_theme( $_GET['stylesheet'] );
				$alert_action	= sprintf( '%s %s', $label_theme, __('deleted', 'ninjafirewall') );
				$alert_item		= sprintf( '%s %s', $label_name, $theme );
			}
		}

	// plugins.php
	} elseif ( current_user_can( 'activate_plugins' ) && strpos( $script, '/plugins.php' ) !== FALSE ) {

		if ( isset( $_REQUEST['action2'] ) ) {
			if (! isset( $_REQUEST['action'] ) || $_REQUEST['action'] == '-1' ) {
				$_REQUEST['action'] = $_REQUEST['action2'];
			}
			$_REQUEST['action2'] = '-1';
		}
		if (! isset( $_REQUEST['action'] ) ) { return; }

		if ( isset( $_REQUEST['plugin'] ) ) {
			$plugin = $_REQUEST['plugin'];
		} else {
			$plugin = '';
		}
		if ( isset( $_POST['checked'] ) ) {
			$plugin_list = nfw_implode( ", ", $_POST['checked'] );
		} else {
			$plugin_list = '';
		}

		if ( $_REQUEST['action'] == 'activate' && ! empty( $nfw_options['a_13'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('activated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin );

		} elseif ( $_REQUEST['action'] == 'activate-selected' && ! empty( $nfw_options['a_13'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('activated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin_list );

		} elseif ( $_REQUEST['action'] == 'update-selected' && ! empty( $nfw_options['a_14'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin_list );

		} elseif ( $_REQUEST['action'] == 'deactivate'  && current_user_can( 'deactivate_plugin' ) && ! empty( $nfw_options['a_15'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('deactivated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin );

		} elseif ( $_REQUEST['action'] == 'deactivate-selected'  && current_user_can( 'deactivate_plugin' ) && ! empty( $nfw_options['a_15'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('deactivated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin_list );

		} elseif ( $_REQUEST['action'] == 'delete-selected'  && current_user_can( 'delete_plugins' ) && ! empty( $nfw_options['a_16'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('deleted', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin_list );
		}

	// update-core.php (only used for WP updates)
	} elseif ( strpos($_SERVER['SCRIPT_NAME'], '/update-core.php' ) !== FALSE ) {

		if (! isset( $_GET['action'] ) || empty( $_POST['upgrade'] ) ) { return; }

		if ( $_GET['action'] == 'do-core-upgrade' && current_user_can( 'update_core' ) && ! empty( $nfw_options['a_31'] ) ) {
			$alert_action	= sprintf( '%s %s', 'WordPress', __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_version, @$_POST['version'] );
		}

	// update.php
	} elseif ( strpos($_SERVER['SCRIPT_NAME'], '/update.php' ) !== FALSE ) {

		if (! isset( $_GET['action'] )  ) { return; }

		if ( $_GET['action'] == 'update-selected' && current_user_can( 'update_plugins' ) && ! empty( $nfw_options['a_14'] ) ) {
			if ( isset( $_GET['plugins'] ) ) {
				$plugin		= $_GET['plugins'];
			} elseif ( isset( $_POST['checked'] ) ) {
				$plugin		= nfw_implode( ", ", $_POST['checked'] );
			}
			$alert_action	= sprintf( '%s %s', $label_plugin, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $plugin );

		} elseif ( $_GET['action'] == 'upgrade-plugin' && current_user_can( 'update_plugins' ) && ! empty( $nfw_options['a_14'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_REQUEST['plugin'] );

		} elseif ( $_GET['action'] == 'activate-plugin' && current_user_can( 'update_plugins' ) && ! empty( $nfw_options['a_13'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('activated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_REQUEST['plugin'] );

		} elseif ( $_GET['action'] == 'install-plugin' && current_user_can( 'install_plugins' ) && ! empty( $nfw_options['a_12'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('installed', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_REQUEST['plugin'] );

		} elseif ( $_GET['action'] == 'upload-plugin' && current_user_can( 'upload_plugins' ) && ! empty( $nfw_options['a_11'] ) ) {
			if ( isset( $_FILES['pluginzip']['name'] ) ) {
				$alert_action	= sprintf( '%s %s', $label_plugin, __('uploaded', 'ninjafirewall') );
				$alert_item		= sprintf( '%s %s', $label_name, $_FILES['pluginzip']['name'] );
			}

		} elseif ( $_GET['action'] == 'upgrade-theme' && current_user_can( 'update_themes' ) && ! empty( $nfw_options['a_25'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_REQUEST['theme'] );

		} elseif ( $_GET['action'] == 'update-selected-themes' && current_user_can( 'update_themes' ) && ! empty( $nfw_options['a_25'] ) ) {
			if ( isset( $_GET['themes'] ) ) {
				$theme		= nfw_implode( ", ", $_GET['themes'] );
			} elseif ( isset( $_POST['checked'] ) ) {
				$theme		= nfw_implode( ", ", $_POST['checked'] );
			}
			$alert_action	= sprintf( '%s %s', $label_theme, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, $theme );

		} elseif ( $_GET['action'] == 'install-theme' && current_user_can( 'install_themes' ) && ! empty( $nfw_options['a_22'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('installed', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_REQUEST['theme'] );

		} elseif ( $_GET['action'] == 'upload-theme' && current_user_can( 'upload_themes' ) && ! empty( $nfw_options['a_21'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('uploaded', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_FILES['themezip']['name'] );
		}

	// AJAX actions
	} elseif ( strpos($_SERVER['SCRIPT_NAME'], '/admin-ajax.php' ) !== FALSE ) {

		if (! isset( $_REQUEST['action'] ) ) { return; }

		if ( $_REQUEST['action'] == 'install-theme' && current_user_can( 'install_themes' ) && ! empty( $nfw_options['a_22'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('installed', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['slug'] );

		} elseif ( $_REQUEST['action'] == 'update-theme' && current_user_can( 'update_themes' ) && ! empty( $nfw_options['a_25'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['slug'] );

		} elseif ( $_REQUEST['action'] == 'delete-theme' && current_user_can( 'delete_themes' ) && ! empty( $nfw_options['a_24'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_theme, __('deleted', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['slug'] );

		} elseif ( $_REQUEST['action'] == 'install-plugin' && current_user_can( 'install_plugins' ) && ! empty( $nfw_options['a_12'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('installed', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['slug'] );

		} elseif ( $_REQUEST['action'] == 'update-plugin' && current_user_can( 'update_plugins' ) && ! empty( $nfw_options['a_14'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('updated', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['plugin'] );

		} elseif ( $_REQUEST['action'] == 'delete-plugin' && current_user_can( 'delete_plugins' ) && ! empty( $nfw_options['a_16'] ) ) {
			$alert_action	= sprintf( '%s %s', $label_plugin, __('deleted', 'ninjafirewall') );
			$alert_item		= sprintf( '%s %s', $label_name, @$_POST['plugin'] );
		}
	}

	if (! empty( $alert_action ) ) {

		global $current_user;
		$current_user = wp_get_current_user();

		$subject = sprintf( __('[NinjaFirewall] Alert: %s', 'ninjafirewall'), $alert_action );
		if ( is_multisite() ) {
			$url = __('-Blog:', 'ninjafirewall') .' '. network_home_url('/') ."\n\n";
		} else {
			$url = __('-Blog:', 'ninjafirewall') .' '. home_url('/') ."\n\n";
		}

		$message = __('NinjaFirewall has detected the following activity on your account:', 'ninjafirewall') . "\n\n".
			"-$alert_action\n-$alert_item\n\n" .
			__('-User:', 'ninjafirewall') .' '. $current_user->user_login .' ('. $current_user->roles[0] .")\n" .
			__('-IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n" .
			__('-Date:', 'ninjafirewall') .' '. ucfirst( date_i18n('F j, Y @ H:i:s O') ) ."\n" .
			$url .
			NF_PG_SIGNATURE ."\n\n". NF_PG_MORESEC;

		nfw_mail( $subject, $message, 'unsubscribe' );

		if (! empty($nfw_options['a_41']) ) {
			nfw_log2(
				$alert_action .' by '. $current_user->user_login,
				$alert_item,
				6,
				0
			);
		}
	}
}

add_action('admin_init', 'nfw_sys_events', 999999);

/* ------------------------------------------------------------------ */
// Make sure we have an array to prevent E_ERROR.

function nfw_implode( $separator, $input ) {

	if ( is_array( $input ) ) {
	  return implode( ', ', $input );
  } else {
	  return $input;
  }

}
/* ------------------------------------------------------------------ */
// EOF
