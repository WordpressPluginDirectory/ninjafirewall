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
 +---------------------------------------------------------------------+
*/

if (! defined( 'NFW_ENGINE_VERSION' ) ) { die( 'Forbidden' ); }

// ---------------------------------------------------------------------
// Create scheduled tasks.

function nfw_create_scheduled_tasks( $task = 0  ) {

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Return if NF was disabled from the "Options" page
	if ( empty( $nfw_options['enabled'] ) ) {
		return;
	}


	// File Check (optional)
	if (! $task || $task == 'nfscanevent' ) {
		if ( wp_next_scheduled('nfscanevent') ) {
			wp_clear_scheduled_hook('nfscanevent');
		}
		if (! empty($nfw_options['sched_scan']) ) {
			if ($nfw_options['sched_scan'] == 1) {
				$schedtype = 'hourly';
			} elseif ($nfw_options['sched_scan'] == 2) {
				$schedtype = 'twicedaily';
			} else {
				$schedtype = 'daily';
			}
			wp_schedule_event( time() + 120, $schedtype, 'nfscanevent');
		}
	}


	// Garbage Collector (always on)
	if ( wp_next_scheduled( 'nfwgccron' ) ) {
		wp_clear_scheduled_hook( 'nfwgccron' );
	}
	wp_schedule_event( time() + 30, 'hourly', 'nfwgccron' );


	// Security rules update (optional)
	if (! $task || $task == 'nfsecupdates' ) {
		if ( wp_next_scheduled('nfsecupdates') ) {
			wp_clear_scheduled_hook('nfsecupdates');
		}
		if (! empty($nfw_options['enable_updates']) ) {
			if ($nfw_options['sched_updates'] == 1) {
				$schedtype = 'hourly';
			} elseif ($nfw_options['sched_updates'] == 2) {
				$schedtype = 'twicedaily';
			} else {
				$schedtype = 'daily';
			}
			wp_schedule_event( time() + 60, $schedtype, 'nfsecupdates');
		}
	}


	// Daily report (optional)
	if (! $task || $task == 'nfdailyreport' ) {
		if ( wp_next_scheduled('nfdailyreport') ) {
			wp_clear_scheduled_hook('nfdailyreport');
		}
		if (! empty( $nfw_options['a_52'] ) ) {
			wp_schedule_event( strtotime( date('Y-m-d 00:00:05', strtotime('+1 day')) ), 'daily', 'nfdailyreport');
		}
	}

}
// ---------------------------------------------------------------------
// Verify scheduled tasks, reactivate them if needed and write
// the incident to the error log.

function nfw_verify_scheduled_tasks() {

	if ( defined('NFW_DONTVERIFYCRON') && NFW_DONTVERIFYCRON == true ) {
		return;
	}

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Return if NF was disabled from the "Options" page
	if ( empty( $nfw_options['enabled'] ) ) {
		return;
	}

	$now = time();

	// File Check (optional)
	if (! empty($nfw_options['sched_scan']) && ! wp_next_scheduled('nfscanevent') ) {
		if ($nfw_options['sched_scan'] == 1) {
			$schedtype = 'hourly';
		} elseif ($nfw_options['sched_scan'] == 2) {
			$schedtype = 'twicedaily';
		} else {
			$schedtype = 'daily';
		}
		wp_schedule_event( $now + 120, $schedtype, 'nfscanevent');
		nfw_log_error(
			sprintf( __('Scheduled task has stopped, restarting it (%s)', 'ninjafirewall'), 'nfscanevent' )
		);
	}

	// Garbage Collector (always on)
	if (! wp_next_scheduled('nfwgccron') ) {
		wp_schedule_event( $now + 30, 'hourly', 'nfwgccron' );
		nfw_log_error(
			sprintf( __('Scheduled task has stopped, restarting it (%s)', 'ninjafirewall'), 'nfwgccron' )
		);
	}

	// Security rules update (optional)
	if (! empty($nfw_options['enable_updates']) && ! wp_next_scheduled('nfsecupdates') ) {
		if ($nfw_options['sched_updates'] == 1) {
			$schedtype = 'hourly';
		} elseif ($nfw_options['sched_updates'] == 2) {
			$schedtype = 'twicedaily';
		} else {
			$schedtype = 'daily';
		}
		wp_schedule_event( $now + 60, $schedtype, 'nfsecupdates');
		nfw_log_error(
			sprintf( __('Scheduled task has stopped, restarting it (%s)', 'ninjafirewall'), 'nfsecupdates' )
		);
	}

	// Daily report (optional)
	if (! empty( $nfw_options['a_52'] ) && ! wp_next_scheduled('nfdailyreport') ) {
		wp_schedule_event( strtotime( date('Y-m-d 00:00:05', strtotime('+1 day')) ), 'daily', 'nfdailyreport');
		nfw_log_error(
			sprintf( __('Scheduled task has stopped, restarting it (%s)', 'ninjafirewall'), 'nfdailyreport' )
		);
	}
}
// ---------------------------------------------------------------------
// Delete scheduled tasks.

function nfw_delete_scheduled_tasks() {

	if ( wp_next_scheduled('nfscanevent') ) {
		wp_clear_scheduled_hook('nfscanevent');
	}
	if ( wp_next_scheduled( 'nfwgccron' ) ) {
		wp_clear_scheduled_hook( 'nfwgccron' );
	}
	if ( wp_next_scheduled('nfsecupdates') ) {
		wp_clear_scheduled_hook('nfsecupdates');
	}
	if ( wp_next_scheduled('nfdailyreport') ) {
		wp_clear_scheduled_hook('nfdailyreport');
	}

}
// ---------------------------------------------------------------------
// EOF