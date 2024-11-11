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

// Daily report cronjob?
if ( defined('NFREPORTDO') ) {
	nf_daily_report();
	return;
}

// Block immediately if user is not allowed :
nf_not_allowed( 'block', __LINE__ );

$nfw_options = nfw_get_option( 'nfw_options' );

echo '<div class="wrap">
	<h1><img style="vertical-align:top;width:33px;height:33px;" src="'. plugins_url( '/ninjafirewall/images/ninjafirewall_32.png' ) .'">&nbsp;' . __('Event Notifications', 'ninjafirewall') . '</h1>';

// Saved ?
if ( isset( $_POST['nfw_options']) ) {
	if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'events_save') ) {
		wp_nonce_ays('events_save');
	}
	nf_sub_event_save();
	echo '<div class="updated notice is-dismissible"><p>' . esc_html__('Your changes have been saved.', 'ninjafirewall') . '</p></div>';
	$nfw_options = nfw_get_option( 'nfw_options' );
}

if (! isset( $nfw_options['a_0'] ) ) {
	$nfw_options['a_0'] = 1;
}
?><br />
	<form method="post" name="nfwalerts">
	<?php wp_nonce_field('events_save', 'nfwnonce', 0); ?>
	<h3><?php _e('WordPress admin dashboard', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever', 'ninjafirewall') ?></th>
			<td>
			<p><label><input type="radio" name="nfw_options[a_0]" value="1"<?php checked( $nfw_options['a_0'], 1) ?>>&nbsp;<?php _e('An administrator logs in (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="radio" name="nfw_options[a_0]" value="2"<?php checked( $nfw_options['a_0'], 2) ?>>&nbsp;<?php _e('Someone - user, admin, editor, etc - logs in', 'ninjafirewall') ?></label></p>
			<p><label><input type="radio" name="nfw_options[a_0]" value="0"<?php checked( $nfw_options['a_0'], 0) ?>>&nbsp;<?php _e('No, thanks (not recommended)', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />

	<h3><?php _e('Plugins', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever someone', 'ninjafirewall') ?></th>
			<td>
			<p><label><input type="checkbox" name="nfw_options[a_11]" value="1"<?php checked( $nfw_options['a_11'], 1) ?>>&nbsp;<?php _e('Uploads a plugin (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_12]" value="1"<?php checked( $nfw_options['a_12'], 1) ?>>&nbsp;<?php _e('Installs a plugin (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_13]" value="1"<?php checked( $nfw_options['a_13'], 1) ?>>&nbsp;<?php _e('Activates a plugin', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_14]" value="1"<?php checked( $nfw_options['a_14'], 1) ?>>&nbsp;<?php _e('Updates a plugin', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_15]" value="1"<?php checked( $nfw_options['a_15'], 1) ?>>&nbsp;<?php _e('Deactivates a plugin (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_16]" value="1"<?php checked( $nfw_options['a_16'], 1) ?>>&nbsp;<?php _e('Deletes a plugin', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />

	<h3><?php _e('Themes', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever someone', 'ninjafirewall') ?></th>
			<td>
			<p><label><input type="checkbox" name="nfw_options[a_21]" value="1"<?php checked( $nfw_options['a_21'], 1) ?>>&nbsp;<?php _e('Uploads a theme (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_22]" value="1"<?php checked( $nfw_options['a_22'], 1) ?>>&nbsp;<?php _e('Installs a theme (default)', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_23]" value="1"<?php checked( $nfw_options['a_23'], 1) ?>>&nbsp;<?php _e('Activates a theme', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_24]" value="1"<?php checked( $nfw_options['a_24'], 1) ?>>&nbsp;<?php _e('Deletes a theme', 'ninjafirewall') ?></label></p>
			<p><label><input type="checkbox" name="nfw_options[a_25]" value="1"<?php checked( $nfw_options['a_25'], 1) ?>>&nbsp;<?php _e('Updates a theme', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />

	<h3><?php _e('Core', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever someone', 'ninjafirewall') ?></th>
			<td>
			<p><label><input type="checkbox" name="nfw_options[a_31]" value="1"<?php checked( $nfw_options['a_31'], 1) ?>>&nbsp;<?php _e('Updates WordPress (default)', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />

	<?php
	if (! isset( $nfw_options['secupdates'] ) ) {
		$nfw_options['secupdates'] = 1;
	}
	?>
	<h3><?php _e('Security updates', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[secupdates]" value="1"<?php checked( $nfw_options['secupdates'], 1) ?>>&nbsp;<?php _e('An important security update is available for a plugin, theme or WordPress (default)', 'ninjafirewall') ?></label></p>
				<p><span class="description"><a href="https://blog.nintechnet.com/how-to-get-informed-about-the-latest-security-updates-in-your-wordpress-plugins-and-themes/" target="_blank"><?php esc_html_e('Consult our blog for more info.', 'ninjafirewall') ?></a>
			</td>
		</tr>
	</table>

	<br />

	<?php
	if (! isset( $nfw_options['a_51']) ) {
		$nfw_options['a_51'] = 1;
	}
	if (! isset( $nfw_options['a_52']) ) {
		$nfw_options['a_52'] = 1;
	}
	?>
	<h3><?php _e('Administrator account', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me an alert whenever', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="checkbox" name="nfw_options[a_51]" value="1"<?php checked( $nfw_options['a_51'], 1) ?>>&nbsp;<?php _e('An administrator account is created, modified or deleted in the database (default)', 'ninjafirewall') ?></label></p>
			</td>
		</tr>
	</table>

	<br />

	<h3><?php _e('Daily report', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Send me a daily activity report', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[a_52]', __('Yes', 'ninjafirewall'), __('No', 'ninjafirewall'), 'small', $nfw_options['a_52'] ) ?>
			</td>
		</tr>
	</table>

	<br />

	<h3><?php _e('Log', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Write all events to the firewall log', 'ninjafirewall') ?></th>
			<td>
				<?php nfw_toggle_switch( 'info', 'nfw_options[a_41]', __('Yes', 'ninjafirewall'), __('No', 'ninjafirewall'), 'small', $nfw_options['a_41'] ) ?>
			</td>
		</tr>
	</table>

	<br />

	<?php
	if (! isset( $nfw_options['a_61']) ) {
		$nfw_options['a_61'] = 1;
	}
	?>
	<h3><?php _e('PHP backtrace', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Attach a PHP backtrace to important notifications', 'ninjafirewall') ?></th>
			<td>
			<select name="nfw_options[a_61]">
				<option value="off"<?php selected( $nfw_options['a_61'], -1) ?>><?php _e('Disable backtrace', 'ninjafirewall') ?></option>
				<option value="0"<?php selected( $nfw_options['a_61'], 0) ?>><?php _e('Low verbosity', 'ninjafirewall') ?></option>
				<option value="1"<?php selected( $nfw_options['a_61'], 1) ?>><?php _e('Medium verbosity (default)', 'ninjafirewall') ?></option>
				<option value="2"<?php selected( $nfw_options['a_61'], 2) ?>><?php _e('High verbosity', 'ninjafirewall') ?></option>
			</select>
			<p><span class="description"><a href="https://blog.nintechnet.com/ninjafirewall-wp-edition-adds-php-backtrace-to-email-notifications/" target="_blank"><?php esc_html_e('Consult our blog for more info.', 'ninjafirewall') ?></a>
			</td>
		</tr>
	</table>

	<br />

<?php
if (! is_multisite() ) {
?>
	<h3><?php _e('Contact email', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Alerts should be sent to', 'ninjafirewall') ?></th>
			<td>
			<input class="regular-text" type="text" name="nfw_options[alert_email]" size="45" maxlength="250" value="<?php
			if ( empty( $nfw_options['alert_email'])) {
				echo htmlspecialchars( get_option('admin_email') );
			} else {
				echo htmlspecialchars( $nfw_options['alert_email'] );
			}
			?>">
			<br /><span class="description"><?php _e('Multiple recipients must be comma-separated (e.g., <code>joe@example.org,alice@example.org</code>).', 'ninjafirewall') ?></span>
			<input type="hidden" name="nfw_options[alert_sa_only]" value="2">
			</td>
		</tr>
	</table>

<?php
} else {
	// Select which admin(s) will recevied alerts in multi-site mode :
	if (! isset( $nfw_options['alert_sa_only'] ) ) {
		$nfw_options['alert_sa_only'] = 1;
	}
	if ($nfw_options['alert_sa_only'] == 3) {
		$tmp_email = htmlspecialchars( $nfw_options['alert_email'] );
	} else {
		$tmp_email = '';
	}
?>
	<h3><?php _e('Contact email', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php _e('Alerts should be sent to', 'ninjafirewall') ?></th>
			<td>
			<p><label><input type="radio" name="nfw_options[alert_sa_only]" value="1"<?php checked( $nfw_options['alert_sa_only'], 1 ) ?> onclick="ac_radio_toogle(0,'alert_multirec');" />&nbsp;<?php _e('Only to me, the Super Admin', 'ninjafirewall') ?>, <?php echo '<code>'. htmlspecialchars(get_option('admin_email')) . '</code>'; ?> (<?php _e('default', 'ninjafirewall') ?>)</label></p>
			<p><label><input type="radio" name="nfw_options[alert_sa_only]" value="2"<?php checked( $nfw_options['alert_sa_only'], 2) ?> onclick="ac_radio_toogle(0,'alert_multirec');" />&nbsp;<?php _e('To the administrator of the site where originated the alert', 'ninjafirewall') ?></label></p>
			<p><label><input type="radio" name="nfw_options[alert_sa_only]" value="3"<?php checked( $nfw_options['alert_sa_only'], 3) ?> onclick="ac_radio_toogle(1,'alert_multirec');" />&nbsp;<?php _e('Other(s):', 'ninjafirewall') ?> </label><input class="regular-text" type="text" name="nfw_options[alert_multirec]" size="45" maxlength="250" value="<?php echo $tmp_email ?>" <?php disabled($tmp_email, '') ?>></p>
			<span class="description"><?php _e('Multiple recipients must be comma-separated (e.g., <code>joe@example.org,alice@example.org</code>).', 'ninjafirewall') ?></span>
			<input type="hidden" name="nfw_options[alert_email]" value="<?php echo htmlspecialchars(get_option('admin_email')); ?>">
			</td>
		</tr>
	</table>
<?php
}
?>

	<br />
	<br />
	<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Event Notifications', 'ninjafirewall') ?>" />

	</form>

</div>
<?php

// ---------------------------------------------------------------------

function nf_sub_event_save() {

	// Save Event Notifications :

	// Block immediately if user is not allowed :
	nf_not_allowed( 'block', __LINE__ );

	$nfw_options = nfw_get_option( 'nfw_options' );

	if (! preg_match('/^[012]$/', $_POST['nfw_options']['a_0']) ) {
		$nfw_options['a_0'] = 1;
	} else {
		$nfw_options['a_0'] = $_POST['nfw_options']['a_0'];
	}

	if (! preg_match('/^[123]$/', $_POST['nfw_options']['alert_sa_only']) ) {
		$nfw_options['alert_sa_only'] = 1;
	} else {
		$nfw_options['alert_sa_only'] = $_POST['nfw_options']['alert_sa_only'];
	}

	if ( empty( $_POST['nfw_options']['a_11']) ) {
		$nfw_options['a_11'] = 0;
	} else {
		$nfw_options['a_11'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_12']) ) {
		$nfw_options['a_12'] = 0;
	} else {
		$nfw_options['a_12'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_13']) ) {
		$nfw_options['a_13'] = 0;
	} else {
		$nfw_options['a_13'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_14']) ) {
		$nfw_options['a_14'] = 0;
	} else {
		$nfw_options['a_14'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_15']) ) {
		$nfw_options['a_15'] = 0;
	} else {
		$nfw_options['a_15'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_16']) ) {
		$nfw_options['a_16'] = 0;
	} else {
		$nfw_options['a_16'] = 1;
	}

	if ( empty( $_POST['nfw_options']['a_21']) ) {
		$nfw_options['a_21'] = 0;
	} else {
		$nfw_options['a_21'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_22']) ) {
		$nfw_options['a_22'] = 0;
	} else {
		$nfw_options['a_22'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_23']) ) {
		$nfw_options['a_23'] = 0;
	} else {
		$nfw_options['a_23'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_24']) ) {
		$nfw_options['a_24'] = 0;
	} else {
		$nfw_options['a_24'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_25']) ) {
		$nfw_options['a_25'] = 0;
	} else {
		$nfw_options['a_25'] = 1;
	}

	if ( empty( $_POST['nfw_options']['a_31']) ) {
		$nfw_options['a_31'] = 0;
	} else {
		$nfw_options['a_31'] = 1;
	}

	if ( empty( $_POST['nfw_options']['a_41']) ) {
		$nfw_options['a_41'] = 0;
	} else {
		$nfw_options['a_41'] = 1;
	}

	if ( empty( $_POST['nfw_options']['secupdates']) ) {
		$nfw_options['secupdates'] = 0;
	} else {
		$nfw_options['secupdates'] = 1;
	}

	if ( empty( $_POST['nfw_options']['a_51']) ) {
		$nfw_options['a_51'] = 0;
	} else {
		$nfw_options['a_51'] = 1;
	}
	if ( empty( $_POST['nfw_options']['a_52']) ) {
		$nfw_options['a_52'] = 0;
	} else {
		$nfw_options['a_52'] = 1;
	}

	if ( empty( $_POST['nfw_options']['a_61']) ) {
		$nfw_options['a_61'] = 0;

	} elseif ( $_POST['nfw_options']['a_61'] == 1 ) {
		$nfw_options['a_61'] = 1;

	} elseif ( $_POST['nfw_options']['a_61'] == 2 ) {
		$nfw_options['a_61'] = 2;

	} else {
		$nfw_options['a_61'] = -1;
	}

	// Multiple recipients (WPMU only) ?
	if (! empty( $_POST['nfw_options']['alert_multirec']) ) {
		$_POST['nfw_options']['alert_email'] = $_POST['nfw_options']['alert_multirec'];
	}

	if (! empty( $_POST['nfw_options']['alert_email']) ) {
		$nfw_options['alert_email'] = '';
		$tmp_email = explode(',', $_POST['nfw_options']['alert_email'] );
		foreach ($tmp_email as $notif_email) {
			$nfw_options['alert_email'] .= sanitize_email($notif_email) . ', ';
		}
		$nfw_options['alert_email'] = trim($nfw_options['alert_email'], ', ' );
	}
	if ( empty( $nfw_options['alert_email'] ) ) {
		$nfw_options['alert_email'] = get_option('admin_email');
	}

	// Update options
	nfw_update_option( 'nfw_options', $nfw_options );

	// Recreate cronjobs if needed
	nfw_create_scheduled_tasks('nfdailyreport');

}

// ---------------------------------------------------------------------

function nf_daily_report() {

	// Send a daily report to the admin(s)
	$nfw_options = nfw_get_option('nfw_options');

	// We check if it is enabled here just in case something went
	// wrong with the task scheduler
	if ( empty( $nfw_options['a_52']) ) {
		return;
	}

	// Make sure we didn't send it already (if WP-Cron is ran twice by mistake)
	if ( is_multisite() ) {
		$nf_transient = get_site_transient('nfw_dailyreport');
	} else {
		$nf_transient = get_transient('nfw_dailyreport');
	}

	if ( $nf_transient == false || $nf_transient < time() ) {
		if ( is_multisite() ) {
			set_site_transient('nfw_dailyreport', time() + 3600, 3600 );
		} else {
			set_transient('nfw_dailyreport', time() + 3600, 3600 );
		}

		$logstats = [];
		$logstats = nf_daily_report_log();

		nf_daily_report_email( $logstats );
	}

}
// ---------------------------------------------------------------------
function nf_daily_report_log() {

	if ( date('j') == 1 ) {
		$cur_month_log = date('Y-m', strtotime( date('Y-m') .' -1 month') );
	} else {
		$cur_month_log = date('Y-m');
	}
	$previous_day	= strtotime( date('Y-m-d 00:00:01', strtotime('-1 day') ) );
	$logstats		= [ 0 => 0, 1 => 0, 2 => 0, 3 => 0, 5 => 0 ];

	$files = NinjaFirewall_helpers::nfw_glob(
		NFW_LOG_DIR .'/nfwlog', 'firewall_'. $cur_month_log, true, true
	);

	// Parse each log
	foreach( $files as $file ) {

		// Stat the file: if it's older than 24 hours, we skip it
		$log_stat = stat( $file );
		if ( $log_stat['mtime'] < $previous_day ) {
			continue;
		}

		$log_lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		foreach ( $log_lines as $line ) {
			if ( preg_match(
				'/^\[(\d{10})\]\s+\[.+?\]\s+\[.+?\]\s+\[#\d{7}\]\s+\[\d+\]\s+\[([1235])\]\s+\[/',
				$line, $match )
			) {
				// Fetch last 24 hours only
				if ( $match[1] > $previous_day && $match[1] < $previous_day + 86400 ) {
					++$logstats[ $match[2] ];
					if ( strpos( $line, 'Brute-force attack detected') !== FALSE ) {
						++$logstats[0];
					}
				}
			}
		}
	}
	return $logstats;
}

// ---------------------------------------------------------------------

function nf_daily_report_email( $logstats ) {

	$subject = __('[NinjaFirewall] Daily Activity Report', 'ninjafirewall');
	if ( is_multisite() ) {
		$url = network_home_url('/');
	} else {
		$url = home_url('/');
	}
	if ( preg_match( '`^https?://(.+)/`', $url, $match ) ) {
		$subject .= " for {$match[1]}";
	}

	$message = "\n". sprintf( __('Daily activity report for: %s', 'ninjafirewall'), $url) . "\n";
	$message .= __('Date Range Processed: Yesterday', 'ninjafirewall') .", ". ucfirst( date('F j, Y',strtotime("-1 days")) ) ."\n\n";

	$message.= __('Blocked threats:', 'ninjafirewall') .' '.
		($logstats[1] + $logstats[2] + $logstats[3]) .
		' ('. __('critical:', 'ninjafirewall') .' '. $logstats[3] .', '.
		__('high:', 'ninjafirewall') .' '. $logstats[2] .', '.
		__('medium:', 'ninjafirewall') .' '. $logstats[1] . ")\n";

	$message.= __('Blocked brute-force attacks:', 'ninjafirewall') .' '. $logstats[0] ."\n\n";
	$message.= __('This notification can be turned off from NinjaFirewall "Event Notifications" page.', 'ninjafirewall') ."\n\n";
	$message .= NF_PG_SIGNATURE ."\n\n";
	$message .= NF_PG_MORESEC;

	nfw_mail( $subject, $message, 'unsubscribe' );
}

// ---------------------------------------------------------------------
// EOF
