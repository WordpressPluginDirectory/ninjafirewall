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
 +---------------------------------------------------------------------+ i18n++ / sa / 2
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// Display a one-time notice after two weeks of use:
$nfw_options = nfw_get_option( 'nfw_options' );
nfw_rate_notice( $nfw_options );

$slow = 0; $tot_bench = 0; $speed = 0; $fast = 1000;

// Which monthly log should we read ?
if ( empty( $_GET['statx'] ) || ! preg_match('/^\d{4}-\d{2}$/D', $_GET['statx'] ) ) {
	$statx = date('Y-m');
} else {
	$statx = $_GET['statx'];
}
// Make sure the stat file exists:
$stat_file = NFW_LOG_DIR . "/nfwlog/stats_{$statx}.php";
// Parse it:
if ( file_exists( $stat_file ) ) {
	$nfw_stat = file_get_contents( $stat_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	$nfw_stat = str_replace( '<?php exit; ?>', '', $nfw_stat );
} else {
	$nfw_stat = '0:0:0:0:0:0:0:0:0:0';
	goto NO_STATS;
}
// Look for the corresponding firewall log:
$log_file = NFW_LOG_DIR . "/nfwlog/firewall_{$statx}.php";
if ( file_exists( $log_file ) ) {
	$fh = @fopen( $log_file, 'r', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	// Fetch processing times to output benchmarks:
	while (! feof( $fh ) ) {
		$line = fgets( $fh );
		if ( preg_match( '/^\[.+?\]\s+\[(.+?)\]/', $line, $match ) ) {
			if ( $match[1] == 0 ) { continue; }
			if ( $match[1] > $slow ) {
				$slow = $match[1];
			}
			if ( $match[1] < $fast ) {
				$fast = $match[1];
			}
			$speed += $match[1];
			++$tot_bench;
		}
	}
	fclose( $fh );
}

NO_STATS:
list( $tmp, $medium, $high, $critical ) = explode( ':', $nfw_stat );
$medium = (int) $medium;
$high = (int) $high;
$critical = (int) $critical;
$total = $critical + $high + $medium;
$c = $critical; $h = $high; $m = $medium;
if ( $total == 1 ) { $fast = $slow; }

if (! $total ) {
	echo '<div class="nfw-notice nfw-notice-orange"><p>' . esc_html__('You do not have any stats for the selected month yet.', 'ninjafirewall') . '</p></div>';
	$fast = 0;
} else {
	$coef = 100 / $total;
	$critical = round( $critical * $coef, 2 );
	$high = round( $high * $coef, 2 );
	$medium = round( $medium * $coef, 2 );
	// Avoid divide error :
	if ($tot_bench) {
		$speed = round( $speed / $tot_bench, 4 );
	} else {
		$fast = 0;
	}
}

?><h3><?php esc_html_e('Monthly Statistics', 'ninjafirewall') ?></h3>
	<table class="form-table nfw-table">
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Select a month', 'ninjafirewall') ?></th>
			<td style="vertical-align: middle;"><?php echo summary_stats_combo( $statx ) ?></td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Blocked threats', 'ninjafirewall') ?></th>
			<td><?php echo $total ?></td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Threats level', 'ninjafirewall') ?></th>
			<td><canvas id="nfw_stats"></canvas></td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><h3><?php esc_html_e('Benchmarks', 'ninjafirewall') ?> <span class="ninjafirewall-tip" data-tip="<?php esc_attr_e('In the Premium version of NinjaFirewall and on compatible servers, you can use Unix shared memory to speed up the processing of each HTTP request by the firewall.', 'ninjafirewall' ) ?>"></span></h3></th>
			<td>&nbsp;</td><td>&nbsp;</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Average time per request', 'ninjafirewall') ?></th>
			<td><?php echo $speed ?> <?php esc_html_e('seconds', 'ninjafirewall') ?></td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Fastest request', 'ninjafirewall') ?></th>
			<td><?php echo round( $fast, 4) ?> <?php esc_html_e('seconds', 'ninjafirewall') ?></td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('Slowest request', 'ninjafirewall') ?></th>
			<td><?php echo round( $slow, 4) ?> <?php esc_html_e('seconds', 'ninjafirewall') ?></td>
		</tr>
	</table>

<script type="text/javascript">
window.onload = function() {
	var ctx = document.getElementById('nfw_stats').getContext('2d');
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: [
				"<?php echo esc_attr__('Critical', 'ninjafirewall') .': '. $critical ?>%",
				"<?php echo esc_attr__('High', 'ninjafirewall') .': '. $high ?>%",
				"<?php echo esc_attr__('Medium', 'ninjafirewall') .': '. $medium ?>%"
			],
			datasets: [{
				label: '<?php esc_attr_e('Blocked threats', 'ninjafirewall') ?>',
				data: [<?php echo "{$c}, {$h}, {$m}" ?>],
				backgroundColor: ['rgba(201, 48, 44, .8)', 'rgba(236, 151, 31, .8)', 'rgba(236, 232, 31, .8)'],
				hoverBackgroundColor: ['rgba(201, 48, 44, 1)', 'rgba(236, 151, 31, 1)', 'rgba(236, 232, 31, 1)'],
				borderColor: ['#8C2C2A', '#c9302c', '#ec971f'],
				borderWidth: 1
			}]
		},
		options: {
			indexAxis: 'y',
			scales: {
				y: {
					beginAtZero: true
				}
			},
			responsive: true,
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					borderWidth: 1,
					borderColor: '#666',
					displayColors: false,
					backgroundColor: '#F5F5B5',
					titleColor:'#666',
					padding: 8,
					footerColor: '#666',
					callbacks: {
						labelTextColor: function(context) {
							return '#543453';
						}
					}
				}
			}
		}
	});
}
</script>
<?php

// ---------------------------------------------------------------------
function summary_stats_combo( $statx ) {

	// Find all stat files:
	$avail_logs = array();
	if ( is_dir( NFW_LOG_DIR . '/nfwlog/' ) ) {
		if ( $dh = opendir( NFW_LOG_DIR . '/nfwlog/' ) ) {
			while ( ( $file = readdir( $dh ) ) !== false ) {
				if (preg_match( '/^stats_(\d{4})-(\d\d)\.php$/', $file, $match ) ) {
					$month = ucfirst( date_i18n('F', mktime(0, 0, 0, $match[2], 1, 2000) ) );
					$avail_logs["{$match[1]}-{$match[2]}" ] = "{$month} {$match[1]}";
				}
			}
			closedir( $dh );
		}
	}
	krsort( $avail_logs );

	$ret = '<form>
			<select class="input" name="statx" onChange="return nfwjs_stat_redir(this.value);">
				<option value="">' . esc_html__('Select monthly stats to view...', 'ninjafirewall') . '</option>';
   foreach ( $avail_logs as $file => $text ) {
      $ret .= '<option value="'. $file .'"';
      if ($file === $statx ) {
         $ret .= ' selected';
      }
      $ret .= ">{$text}</option>";
   }
   $ret .= '</select>
		</form>';
	return $ret;
}

// ---------------------------------------------------------------------
// EOF
