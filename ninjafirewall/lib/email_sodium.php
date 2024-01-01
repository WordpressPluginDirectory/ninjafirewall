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
 +---------------------------------------------------------------------+ 2022-11-24
*/

if (! defined('NFW_ENGINE_VERSION') ) {
	die('Forbidden');
}

// ---------------------------------------------------------------------
// Check whether Sodium is available (WordPress >=5.2 or PHP >= 7.2.0).

function nfw_check_sodium() {

	$sodium = 0;

	if ( function_exists('sodium_crypto_generichash') ) {
		$sodium = 'php';
	} elseif ( file_exists( ABSPATH . WPINC . '/sodium_compat/autoload.php') ) {
		$sodium = 'wordpress';
	}
	return $sodium;
}
// ---------------------------------------------------------------------
// Generate an encrypted link for notification emails.

function nfw_sodium_encrypt( $email, $expire, $which_sodium ) {

	if ( $which_sodium == 'php') {
		// PHP native functions
		$nonce		= nfw_sodium_nonce();
		$key			= sodium_crypto_generichash( AUTH_KEY, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
		$ciphertext	= sodium_crypto_secretbox( "$email::$expire", $nonce, $key);
		$link			= sodium_bin2hex( $ciphertext );

	} else {
		// WP sodium libraries
		require ABSPATH . WPINC .'/sodium_compat/autoload.php';
		$nonce		= nfw_sodium_nonce();
		$key			= \Sodium\crypto_generichash( AUTH_KEY, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
		$ciphertext	= \Sodium\crypto_secretbox( "$email::$expire", $nonce, $key);
		$link			= \Sodium\bin2hex( $ciphertext );
	}
	return $link;
}

// ---------------------------------------------------------------------
// Get or generate a nonce depending on the PHP version.

function nfw_sodium_nonce() {

	$nfw_options = nfw_get_option('nfw_options');

	// Make sure we have a nonce, or create one and save it:
	if ( empty( $nfw_options['sodium_nonce'] ) ) {
		if ( function_exists('random_bytes') ) {
			// PHP >=7
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		} else {
			// PHP <7
			$nonce = openssl_random_pseudo_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		}
		// Save it
		$nfw_options['sodium_nonce'] = bin2hex( $nonce );
		nfw_update_option('nfw_options', $nfw_options );

	} else {
		$nonce = hex2bin( $nfw_options['sodium_nonce'] );
	}

	return $nonce;
}
// ---------------------------------------------------------------------
// Verify encrypted signature.

function nfw_sodium_decrypt( $hex ) {

	// Make sure we have Sodium
	$which_sodium = nfw_check_sodium();
	if ( empty( $which_sodium ) ) {
		return;
	}
	// Hexadecimal input only
	if (! preg_match('/^(?:[0-9a-f]{2})+$/', $hex ) ) {
		return;
	}
	$ciphertext = hex2bin( $hex );

	// Retrieve our nonce
	$nonce = nfw_sodium_nonce();

	if ( $which_sodium == 'php') {
		// PHP native functions
		$key			= sodium_crypto_generichash( AUTH_KEY, null, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
		$decrypted	= sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );

	} else {
		// WP sodium libraries
		require ABSPATH . WPINC .'/sodium_compat/autoload.php';
		$key			= \Sodium\crypto_generichash( AUTH_KEY, null, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
		$decrypted	= \Sodium\crypto_secretbox_open( $ciphertext, $nonce, $key );
	}

	if ( $decrypted === false ) {
		nfw_removal_error();
	}

	$data = explode('::', $decrypted );
	if ( empty( $data[0] ) || empty( $data[1] ) ) {
		nfw_removal_error();
	}

	// Verify expiry date
	$now = time();
	if ( $data[1] < $now ) {
		// Link has expired
		wp_die(
			esc_html__('The link you followed has expired.', 'ninjafirewall'),
			esc_html__('Error', 'ninjafirewall'),
			200
		);
	}

	// Confirm deletion?
	if ( empty( $_REQUEST['nfw_confirm'] ) ) {
		nfw_removal_confimation( $_GET['nfw_stop_notification'] );
		exit;
	}

	$new_list = '';
	$found = 0;
	$nfw_options	= nfw_get_option('nfw_options');
	$recipients		= explode(',', $nfw_options['alert_email'] );
	foreach( $recipients as $recipient ) {
		$recipient = trim( $recipient );
		if ( $recipient == $data[0] ) {
			// Remove that email from the list
			$found = 1;
			continue;
		}
		$new_list .= "$recipient, ";
	}

	if ( $found ) {
		// Update options
		$nfw_options['alert_email'] = trim( $new_list, ', ');
		if ( empty( $nfw_options['alert_email'] ) ) {
			$nfw_options['alert_email'] = get_option('admin_email');
		}
		nfw_update_option('nfw_options', $nfw_options );

		$subject = __('Email removal confirmation', 'ninjafirewall');
		nfw_log2( 'WordPress: ' . $subject, "User: {$data[0]}", 6, 0);
		$subject = "[NinjaFirewall] $subject";
		$message = __('Your email address was removed from the "Event Notifications" option.', 'ninjafirewall') . "\n\n";
		$message.= __('Blog:', 'ninjafirewall') .' '. home_url('/') . "\n";
		$message.= __('Email address:', 'ninjafirewall') .' '. "{$data[0]}\n";
		$message.= __('User IP:', 'ninjafirewall') .' '. NFW_REMOTE_ADDR . "\n";
		$message.= __('Date:', 'ninjafirewall') .' '. date_i18n('F j, Y @ H:i:s T') . "\n\n";
		wp_mail( $data[0], $subject, $message );
	}

}
// --------------------------------------------------------------------- 2023-07-26
// Fatal error.

function nfw_removal_error() {

	wp_die(
		esc_html__('Error, your resquest cannot be processed.', 'ninjafirewall'),
		esc_html__('Error', 'ninjafirewall'),
		200
	);
}

// --------------------------------------------------------------------- 2023-07-26
// Email removal confirmation.

function nfw_removal_confimation( $hex ) {

	$home_url		= esc_url( home_url('/') );
	$removal_url	= esc_url( home_url("/?nfw_stop_notification=$hex&nfw_confirm=1") );
	wp_die(
		esc_html__('If you want to remove your email address from the Event Notifications option, click '.
			'the button below. If the operation is successful, a confirmation email will be sent to you.',
			'ninjafirewall'
		).
		'<p>
		<button class="button button-large button-active" style="min-width:100px;" onclick=\'location.'.
		'href="'. $removal_url .'"\'>'. esc_html__('Yes', 'ninjafirewall' ) .'</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="button button-large button-active" style="min-width:100px;" onclick=\'location.'.
		'href="'. $home_url .'"\'>'. esc_html__('No', 'ninjafirewall' ) .'</button>
		</p>',
		esc_html__('Email removal confirmation', 'ninjafirewall'),
		200
	);
}

// ---------------------------------------------------------------------
// EOL
