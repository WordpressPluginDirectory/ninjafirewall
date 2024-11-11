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
 +---------------------------------------------------------------------+ 2023-05-14
*/

if (! defined('NFW_ENGINE_VERSION') ) {
	die('Forbidden');
}

// Block immediately if user is not allowed :
nf_not_allowed('block', __LINE__ );

if (! defined('NF_DISABLED') ) {
	is_nfw_enabled();
}
if ( NF_DISABLED ) {
	$err_msg = esc_html__('Error: NinjaFirewall must be enabled and working '.
					'in order to use this feature.', 'ninjafirewall');
}
if ( empty( NinjaFirewall_session::read('nfw_goodguy') ) ) {
	$err_msg = esc_html__('Warning: You must be whitelisted in order to use Live Log: click on '.
		'the Firewall Policies menu and ensure that the "Add the Administrator to the whitelist" '.
		'option is enabled.', 'ninjafirewall');
}
if (! empty( $err_msg ) ) {
	?>
	<div class="wrap">
	<h1><img style="vertical-align:top;" src="<?php
		echo esc_url( plugins_url('/images/ninjafirewall_32.png',
			dirname( __FILE__ ) ) )
		?>">&nbsp;<?php esc_html_e('Live Log', 'ninjafirewall') ?></h1>

	<br />
	<div class="error notice is-dismissible"><p><?php echo $err_msg ?></p></div>
	</div>
	<?php
	return;
}

// Create an empty log et set the required session
@file_put_contents(
	NFW_LOG_DIR .'/nfwlog/cache/livelog.php',
	'<?php exit; ?>',
	LOCK_EX
);

NinjaFirewall_session::write( ['nfw_livelog' => 1 ] );

if (! isset($_COOKIE['nfwscroll']) || ! empty($_COOKIE['nfwscroll']) ) {
	// Default, if not set
	$nfwscroll = 1;
} else {
	$nfwscroll = 0;
}
if ( empty($_COOKIE['nfwlwrap']) ) {
	// Default, if not set
	$nfwlwrap = 0;
} else {
	$nfwlwrap = 1;
}
if ( isset( $_COOKIE['nfwintval']) &&
	preg_match('/^(5|10|20|45)000$/', $_COOKIE['nfwintval'] ) ) {

	$nfwintval = (int) $_COOKIE['nfwintval'];
} else {
	$nfwintval = 10000;
}
if ( NFW_IS_HTTPS == true ) {
	$nfwsite = site_url('/index.php', 'https');
} else {
	$nfwsite = site_url('/index.php');
}
?>
<script>
	var liveinterval = <?php echo $nfwintval ?>;
	var scroll = <?php echo $nfwscroll ?>;
	var lwrap = <?php echo $nfwlwrap ?>;
	var site_url = '<?php echo esc_js( $nfwsite ) ?>';
</script>

<?php
if ( isset( $_POST['lf'] ) ) {
	$res = nf_sub_liveloge_save();
	if ( $res ) {
		echo '<div class="error notice is-dismissible"><p>'. $res .'</p></div>';
	} else {
		echo '<div class="updated notice is-dismissible"><p>'.
		esc_html__('Your changes have been saved.', 'ninjafirewall') .'</p></div>';
	}
}
$nfw_options = nfw_get_option('nfw_options');

if ( defined('NFW_TEXTAREA_HEIGHT') ) {
	$th = (int) NFW_TEXTAREA_HEIGHT;
} else {
	$th = '450';
}
?>
<form name="liveform">
	<table class="form-table">
		<tr>
			<td style="width:100%;text-align:center;">
				<progress id="nfw-progress" value="1" max="<?php echo ($nfwintval/1000) ?>" class="nfw-progress" style="display:none"></progress>
				<br />
				<textarea name="txtlog" id="idtxtlog" class="large-text code" style="height:<?php echo $th; ?>px;" wrap="off" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php echo esc_textarea( __('Live Log lets you watch your blog traffic in real time. To enable it, click on the button below.', 'ninjafirewall') ) ?></textarea>
				<br />
				<div style="float:left;width:40%;padding-top:10px;" class="nfw-right">
					<?php nfw_toggle_switch('danger', 'nfw_options[wf_case]', esc_attr__('Enabled', 'ninjafirewall'), esc_attr__('Disabled', 'ninjafirewall'), 'large', 0, false, 'onclick="nfwjs_livelog()"', 'livelog-switch', 'right') ?>
				</div>
				<div style="float:right;width:60%;text-align:left;padding-top:10px;" class="nfw-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php esc_html_e('Refresh rate:', 'ninjafirewall') ?>
					<select name="liveint" id="liveint" onchange="nfwjs_change_int(this.value);">
						<option value="5000"<?php  selected( $nfwintval, 5000 ) ?>><?php esc_html_e('5 seconds', 'ninjafirewall') ?></option>
						<option value="10000"<?php selected( $nfwintval, 10000 ) ?>><?php esc_html_e('10 seconds', 'ninjafirewall') ?></option>
						<option value="20000"<?php selected( $nfwintval, 20000 ) ?>><?php esc_html_e('20 seconds', 'ninjafirewall') ?></option>
						<option value="45000"<?php selected( $nfwintval, 45000 ) ?>><?php esc_html_e('45 seconds', 'ninjafirewall') ?></option>
					</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="button" class="button-secondary" name="livecls" value="<?php esc_html_e('Clear screen', 'ninjafirewall') ?>" onClick="nfwjs_cls()" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input type="checkbox" name="livescroll" id="livescroll" value="1" onchange="nfwjs_is_scroll()" <?php checked($nfwscroll, 1)?> /><?php esc_html_e('Autoscrolling', 'ninjafirewall') ?></label>
					&nbsp;&nbsp;&nbsp;
					<label><input type="checkbox" name="linewrapping" id="linewrapping" value="1" onchange="nfwjs_linewrapping()" <?php checked($nfwlwrap, 1)?> /><?php esc_html_e('Line wrapping', 'ninjafirewall') ?></label>
				</div>
			</td>
		</tr>
	</table>
	<p class="description alignright"><?php esc_html_e('Live Log will not display whitelisted users and brute-force attacks.', 'ninjafirewall') ?></p>
</form>
<?php

if ( empty( $nfw_options['liveformat'] ) ) {
	$lf = 0;
	$liveformat = '';
} else {
	$lf = 1;
	$liveformat = htmlspecialchars( $nfw_options['liveformat'] );
}

if ( empty( $nfw_options['liveport'] ) ||
	! preg_match('/^[1-2]$/', $nfw_options['liveport'] ) ) {

	$liveport = 0;
} else {
	$liveport = $nfw_options['liveport'];
}
if ( empty( $nfw_options['livetz'] ) ||
	preg_match('/[^\w\/]/', $nfw_options['livetz'] ) ) {

	$livetz = 'UTC';
} else {
	$livetz = $nfw_options['livetz'];
}
if ( empty( $nfw_options['liverules'] ) ||
	! preg_match('/^[0-2]$/', $nfw_options['liverules'] ) ) {

	$liverules = 0;
	$lr_disabled = 'disabled="disabled" ';
} else {
	$liverules = $nfw_options['liverules'];
	$lr_disabled = '';
}
if ( empty( $nfw_options['liverulespath'] ) ) {
	$liverulespath = '';
} else{
	$liverulespath = $nfw_options['liverulespath'];
}
?>
<br />
<form method="post">
	<h3><?php esc_html_e('Live Log Options', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Inclusion and exclusion filters (REQUEST_URI)', 'ninjafirewall') ?></th>
			<td>
				<select name="liverules" onchange="nfwjs_lv_select(this.value);">
					<option value="0"<?php selected($liverules, 0) ?>><?php esc_html_e('None', 'ninjafirewall') ?></option>
					<option value="1"<?php selected($liverules, 1) ?>><?php esc_html_e('Must include', 'ninjafirewall') ?></option>
					<option value="2"<?php selected($liverules, 2) ?>><?php esc_html_e('Must not include', 'ninjafirewall') ?></option>
				</select>&nbsp;
				<input <?php echo $lr_disabled; ?>type="text" id="lr-disabled" class="regular-text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="liverulespath" value="<?php echo esc_attr( $liverulespath ) ?>" placeholder="<?php esc_attr_e('e.g.,', 'ninjafirewall') ?> /blog <?php esc_attr_e('or', 'ninjafirewall') ?> admin.php <?php esc_attr_e('or', 'ninjafirewall') ?> index.php,/blog" />
				<br />
				<p class="description"><?php esc_html_e('Full or partial case-sensitive REQUEST_URI string. Multiple values must be comma-separated.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Format', 'ninjafirewall') ?></th>
			<td>
				<p><label><input type="radio" name="lf" value="0"<?php checked($lf, 0) ?> onclick="document.getElementById('liveformat').disabled=true"><code>[%time] %name %client &quot;%method %uri&quot; &quot;%referrer&quot; &quot;%ua&quot; &quot;%forward&quot; &quot;%host&quot;</code></label></p>
				<p><label><input type="radio" name="lf" value="1"<?php checked($lf, 1) ?> onclick="document.getElementById('liveformat').disabled=false;document.getElementById('liveformat').focus()"><?php esc_html_e('Custom', 'ninjafirewall') ?> </label><input id="liveformat" type="text" class="regular-text" name="liveformat" value="<?php echo $liveformat ?>"<?php disabled($lf, 0) ?> autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" /></p>
				<p class="description"><?php esc_html_e('See contextual help for available log format.', 'ninjafirewall') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Display', 'ninjafirewall') ?></th>
			<td>
				<select name="liveport">
					<option value="0"<?php selected( $liveport, 0 ) ?>><?php esc_html_e('HTTP and HTTPS traffic (default)', 'ninjafirewall') ?></option>
					<option value="1"<?php selected( $liveport, 1 ) ?>><?php esc_html_e('HTTP traffic only', 'ninjafirewall') ?></option>
					<option value="2"<?php selected( $liveport, 2 ) ?>><?php esc_html_e('HTTPS traffic only', 'ninjafirewall') ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Timezone', 'ninjafirewall') ?></th>
			<td>
				<select name="livetz">
				<?php
				$timezone_choice = nfw_timezone_choice();
				foreach ($timezone_choice as $tz_place) {
					echo '<option value ="' . esc_attr( $tz_place ) . '"';
					if ($livetz == $tz_place) { echo ' selected'; }
					echo '>'. esc_html( $tz_place ) .'</option>';
				}
				?>
				</select>
			</td>
		</tr>
	</table>
	<p><input type="submit" class="button-primary" value="<?php esc_html_e('Save Live Log Options', 'ninjafirewall') ?>" /></p>
	<?php wp_nonce_field('livelog_save', 'nfwnonce', 0); ?>
	<input type="hidden" name="tab" value="livelog" />
</form>
<?php

// ---------------------------------------------------------------------
function nf_sub_liveloge_save() {

	if ( empty($_POST['nfwnonce']) ||
		! wp_verify_nonce($_POST['nfwnonce'], 'livelog_save') ) {

		wp_nonce_ays('livelog_save');
	}

	$nfw_options = nfw_get_option('nfw_options');

	if ( empty( $_POST['liverules'] ) ||
		! preg_match('/^[0-2]$/', $_POST['liverules']) ) {

		$nfw_options['liverules'] = 0;
	} else {
		$nfw_options['liverules'] = $_POST['liverules'];
	}

	$nfw_options['liverulespath'] = '';
	if (! empty( $_POST['liverulespath'] ) ) {
		$liverulespath = trim( $_POST['liverulespath'], " \t\n\r\0\x0B,");
		$nfw_options['liverulespath'] = preg_replace('/\s*,\s*/', ',', $liverulespath );
	}
	if ( empty( $nfw_options['liverulespath'] ) ) {
		$nfw_options['liverules'] = 0;
	}

	if ( empty($_POST['lf']) ) {
		$nfw_options['liveformat'] = '';
	} else {
		if (! empty($_POST['liveformat']) ) {
			$tmp = stripslashes($_POST['liveformat']);
			// Remove unwanted characters :
			$nfw_options['liveformat'] = preg_replace('`[^a-z%[\]"\x20]`', '', $tmp);
		}
		if (empty($_POST['liveformat']) ) {
			return esc_html__('Error: please enter the custom log format.', 'ninjafirewall');
		}
	}

	if ( empty($_POST['liveport']) || ! preg_match('/^[1-2]$/', $_POST['liveport']) ) {
		$nfw_options['liveport'] = 0;
	} else {
		$nfw_options['liveport'] = $_POST['liveport'];
	}

	if ( empty($_POST['livetz']) || preg_match('/[^\w\/]/', $_POST['livetz']) ) {
		$nfw_options['livetz'] = 0;
	} else {
		$nfw_options['livetz'] = $_POST['livetz'];
	}

	$nfw_options = nfw_update_option('nfw_options', $nfw_options);
}

// ---------------------------------------------------------------------

function nfw_timezone_choice() {
	return array('UTC', 'Africa/Abidjan', 'Africa/Accra', 'Africa/Addis_Ababa', 'Africa/Algiers', 'Africa/Asmara', 'Africa/Asmera', 'Africa/Bamako', 'Africa/Bangui', 'Africa/Banjul', 'Africa/Bissau', 'Africa/Blantyre', 'Africa/Brazzaville', 'Africa/Bujumbura', 'Africa/Cairo', 'Africa/Casablanca', 'Africa/Ceuta', 'Africa/Conakry', 'Africa/Dakar', 'Africa/Dar_es_Salaam', 'Africa/Djibouti', 'Africa/Douala', 'Africa/El_Aaiun', 'Africa/Freetown', 'Africa/Gaborone', 'Africa/Harare', 'Africa/Johannesburg', 'Africa/Kampala', 'Africa/Khartoum', 'Africa/Kigali', 'Africa/Kinshasa', 'Africa/Lagos', 'Africa/Libreville', 'Africa/Lome', 'Africa/Luanda', 'Africa/Lubumbashi', 'Africa/Lusaka', 'Africa/Malabo', 'Africa/Maputo', 'Africa/Maseru', 'Africa/Mbabane', 'Africa/Mogadishu', 'Africa/Monrovia', 'Africa/Nairobi', 'Africa/Ndjamena', 'Africa/Niamey', 'Africa/Nouakchott', 'Africa/Ouagadougou', 'Africa/Porto-Novo', 'Africa/Sao_Tome', 'Africa/Timbuktu', 'Africa/Tripoli', 'Africa/Tunis', 'Africa/Windhoek', 'America/Adak', 'America/Anchorage', 'America/Anguilla', 'America/Antigua', 'America/Araguaina', 'America/Argentina/Buenos_Aires', 'America/Argentina/Catamarca', 'America/Argentina/ComodRivadavia', 'America/Argentina/Cordoba', 'America/Argentina/Jujuy', 'America/Argentina/La_Rioja', 'America/Argentina/Mendoza', 'America/Argentina/Rio_Gallegos', 'America/Argentina/Salta', 'America/Argentina/San_Juan', 'America/Argentina/San_Luis', 'America/Argentina/Tucuman', 'America/Argentina/Ushuaia', 'America/Aruba', 'America/Asuncion', 'America/Atikokan', 'America/Atka', 'America/Bahia', 'America/Barbados', 'America/Belem', 'America/Belize', 'America/Blanc-Sablon', 'America/Boa_Vista', 'America/Bogota', 'America/Boise', 'America/Buenos_Aires', 'America/Cambridge_Bay', 'America/Campo_Grande', 'America/Cancun', 'America/Caracas', 'America/Catamarca', 'America/Cayenne', 'America/Cayman', 'America/Chicago', 'America/Chihuahua', 'America/Coral_Harbour', 'America/Cordoba', 'America/Costa_Rica', 'America/Cuiaba', 'America/Curacao', 'America/Danmarkshavn', 'America/Dawson', 'America/Dawson_Creek', 'America/Denver', 'America/Detroit', 'America/Dominica', 'America/Edmonton', 'America/Eirunepe', 'America/El_Salvador', 'America/Ensenada', 'America/Fort_Wayne', 'America/Fortaleza', 'America/Glace_Bay', 'America/Godthab', 'America/Goose_Bay', 'America/Grand_Turk', 'America/Grenada', 'America/Guadeloupe', 'America/Guatemala', 'America/Guayaquil', 'America/Guyana', 'America/Halifax', 'America/Havana', 'America/Hermosillo', 'America/Indiana/Indianapolis', 'America/Indiana/Knox', 'America/Indiana/Marengo', 'America/Indiana/Petersburg', 'America/Indiana/Tell_City', 'America/Indiana/Vevay', 'America/Indiana/Vincennes', 'America/Indiana/Winamac', 'America/Indianapolis', 'America/Inuvik', 'America/Iqaluit', 'America/Jamaica', 'America/Jujuy', 'America/Juneau', 'America/Kentucky/Louisville', 'America/Kentucky/Monticello', 'America/Knox_IN', 'America/La_Paz', 'America/Lima', 'America/Los_Angeles', 'America/Louisville', 'America/Maceio', 'America/Managua', 'America/Manaus', 'America/Marigot', 'America/Martinique', 'America/Matamoros', 'America/Mazatlan', 'America/Mendoza', 'America/Menominee', 'America/Merida', 'America/Mexico_City', 'America/Miquelon', 'America/Moncton', 'America/Monterrey', 'America/Montevideo', 'America/Montreal', 'America/Montserrat', 'America/Nassau', 'America/New_York', 'America/Nipigon', 'America/Nome', 'America/Noronha', 'America/North_Dakota/Center', 'America/North_Dakota/New_Salem', 'America/Ojinaga', 'America/Panama', 'America/Pangnirtung', 'America/Paramaribo', 'America/Phoenix', 'America/Port-au-Prince', 'America/Port_of_Spain', 'America/Porto_Acre', 'America/Porto_Velho', 'America/Puerto_Rico', 'America/Rainy_River', 'America/Rankin_Inlet', 'America/Recife', 'America/Regina', 'America/Resolute', 'America/Rio_Branco', 'America/Rosario', 'America/Santa_Isabel', 'America/Santarem', 'America/Santiago', 'America/Santo_Domingo', 'America/Sao_Paulo', 'America/Scoresbysund', 'America/Shiprock', 'America/St_Barthelemy', 'America/St_Johns', 'America/St_Kitts', 'America/St_Lucia', 'America/St_Thomas', 'America/St_Vincent', 'America/Swift_Current', 'America/Tegucigalpa', 'America/Thule', 'America/Thunder_Bay', 'America/Tijuana', 'America/Toronto', 'America/Tortola', 'America/Vancouver', 'America/Virgin', 'America/Whitehorse', 'America/Winnipeg', 'America/Yakutat', 'America/Yellowknife', 'Arctic/Longyearbyen', 'Asia/Aden', 'Asia/Almaty', 'Asia/Amman', 'Asia/Anadyr', 'Asia/Aqtau', 'Asia/Aqtobe', 'Asia/Ashgabat', 'Asia/Ashkhabad', 'Asia/Baghdad', 'Asia/Bahrain', 'Asia/Baku', 'Asia/Bangkok', 'Asia/Beirut', 'Asia/Bishkek', 'Asia/Brunei', 'Asia/Calcutta', 'Asia/Choibalsan', 'Asia/Chongqing', 'Asia/Chungking', 'Asia/Colombo', 'Asia/Dacca', 'Asia/Damascus', 'Asia/Dhaka', 'Asia/Dili', 'Asia/Dubai', 'Asia/Dushanbe', 'Asia/Gaza', 'Asia/Harbin', 'Asia/Ho_Chi_Minh', 'Asia/Hong_Kong', 'Asia/Hovd', 'Asia/Irkutsk', 'Asia/Istanbul', 'Asia/Jakarta', 'Asia/Jayapura', 'Asia/Jerusalem', 'Asia/Kabul', 'Asia/Kamchatka', 'Asia/Karachi', 'Asia/Kashgar', 'Asia/Kathmandu', 'Asia/Katmandu', 'Asia/Kolkata', 'Asia/Krasnoyarsk', 'Asia/Kuala_Lumpur', 'Asia/Kuching', 'Asia/Kuwait', 'Asia/Macao', 'Asia/Macau', 'Asia/Magadan', 'Asia/Makassar', 'Asia/Manila', 'Asia/Muscat', 'Asia/Nicosia', 'Asia/Novokuznetsk', 'Asia/Novosibirsk', 'Asia/Omsk', 'Asia/Oral', 'Asia/Phnom_Penh', 'Asia/Pontianak', 'Asia/Pyongyang', 'Asia/Qatar', 'Asia/Qyzylorda', 'Asia/Rangoon', 'Asia/Riyadh', 'Asia/Saigon', 'Asia/Sakhalin', 'Asia/Samarkand', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Asia/Taipei', 'Asia/Tashkent', 'Asia/Tbilisi', 'Asia/Tehran', 'Asia/Tel_Aviv', 'Asia/Thimbu', 'Asia/Thimphu', 'Asia/Tokyo', 'Asia/Ujung_Pandang', 'Asia/Ulaanbaatar', 'Asia/Ulan_Bator', 'Asia/Urumqi', 'Asia/Vientiane', 'Asia/Vladivostok', 'Asia/Yakutsk', 'Asia/Yekaterinburg', 'Asia/Yerevan', 'Atlantic/Azores', 'Atlantic/Bermuda', 'Atlantic/Canary', 'Atlantic/Cape_Verde', 'Atlantic/Faeroe', 'Atlantic/Faroe', 'Atlantic/Jan_Mayen', 'Atlantic/Madeira', 'Atlantic/Reykjavik', 'Atlantic/South_Georgia', 'Atlantic/St_Helena', 'Atlantic/Stanley', 'Australia/ACT', 'Australia/Adelaide', 'Australia/Brisbane', 'Australia/Broken_Hill', 'Australia/Canberra', 'Australia/Currie', 'Australia/Darwin', 'Australia/Eucla', 'Australia/Hobart', 'Australia/LHI', 'Australia/Lindeman', 'Australia/Lord_Howe', 'Australia/Melbourne', 'Australia/NSW', 'Australia/North', 'Australia/Perth', 'Australia/Queensland', 'Australia/South', 'Australia/Sydney', 'Australia/Tasmania', 'Australia/Victoria', 'Australia/West', 'Australia/Yancowinna', 'Europe/Amsterdam', 'Europe/Andorra', 'Europe/Athens', 'Europe/Belfast', 'Europe/Belgrade', 'Europe/Berlin', 'Europe/Bratislava', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest', 'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar', 'Europe/Guernsey', 'Europe/Helsinki', 'Europe/Isle_of_Man', 'Europe/Istanbul', 'Europe/Jersey', 'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/Ljubljana', 'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid', 'Europe/Malta', 'Europe/Mariehamn', 'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Nicosia', 'Europe/Oslo', 'Europe/Paris', 'Europe/Podgorica', 'Europe/Prague', 'Europe/Riga', 'Europe/Rome', 'Europe/Samara', 'Europe/San_Marino', 'Europe/Sarajevo', 'Europe/Simferopol', 'Europe/Skopje', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane', 'Europe/Tiraspol', 'Europe/Uzhgorod', 'Europe/Vaduz', 'Europe/Vatican', 'Europe/Vienna', 'Europe/Vilnius', 'Europe/Volgograd', 'Europe/Warsaw', 'Europe/Zagreb', 'Europe/Zaporozhye', 'Europe/Zurich', 'Indian/Antananarivo', 'Indian/Chagos', 'Indian/Christmas', 'Indian/Cocos', 'Indian/Comoro', 'Indian/Kerguelen', 'Indian/Mahe', 'Indian/Maldives', 'Indian/Mauritius', 'Indian/Mayotte', 'Indian/Reunion', 'Pacific/Apia', 'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Easter', 'Pacific/Efate', 'Pacific/Enderbury', 'Pacific/Fakaofo', 'Pacific/Fiji', 'Pacific/Funafuti', 'Pacific/Galapagos', 'Pacific/Gambier', 'Pacific/Guadalcanal', 'Pacific/Guam', 'Pacific/Honolulu', 'Pacific/Johnston', 'Pacific/Kiritimati', 'Pacific/Kosrae', 'Pacific/Kwajalein', 'Pacific/Majuro', 'Pacific/Marquesas', 'Pacific/Midway', 'Pacific/Nauru', 'Pacific/Niue', 'Pacific/Norfolk', 'Pacific/Noumea', 'Pacific/Pago_Pago', 'Pacific/Palau', 'Pacific/Pitcairn', 'Pacific/Ponape', 'Pacific/Port_Moresby', 'Pacific/Rarotonga', 'Pacific/Saipan', 'Pacific/Samoa', 'Pacific/Tahiti', 'Pacific/Tarawa', 'Pacific/Tongatapu', 'Pacific/Truk', 'Pacific/Wake', 'Pacific/Wallis', 'Pacific/Yap');
}

// ---------------------------------------------------------------------
// EOF
