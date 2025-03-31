<?php
/*
 +=====================================================================+
 |    _   _ _        _       _____ _                        _ _        |
 |   | \ | (_)_ __  (_) __ _|  ___(_)_ __ _____      ____ _| | |       |
 |   |  \| | | '_ \ | |/ _` | |_  | | '__/ _ \ \ /\ / / _` | | |       |
 |   | |\  | | | | || | (_| |  _| | | | |  __/\ V  V / (_| | | |       |
 |   |_| \_|_|_| |_|/ |\__,_|_|   |_|_|  \___| \_/\_/ \__,_|_|_|       |
 |                |__/                                                 |
 |  (c) NinTechNet Limited ~ https://nintechnet.com/                   |
 +=====================================================================+
*/

if ( class_exists('NinjaFirewall_mail') ) {
	return;
}

class NinjaFirewall_mail {

	private static $template = [];

	/**
	 * Load the template.
	 */
	private static function initialize( $what, $dir = '') {
		/**
		 * Merge the default and custom (if any) templates.
		 */
		if ( $what == 'firewall') {
			$file = '/mail_template_firewall.php';
			$logdir = $dir;
		} else {
			$file = '/mail_template_plugin.php';
			$logdir = NFW_LOG_DIR .'/nfwlog';
		}

		$custom = [];
		if ( is_file( __DIR__ . $file ) ) {
			$default = require __DIR__ . $file;
		}
		if ( is_file( "$logdir/$file" ) ) {
			$custom = require "$logdir/$file";
		}
		self::$template = array_merge( $default, $custom );
	}


	/**
	 * Send an email using WordPress wp_mail().
	 */
	public static function send( $tpl, $s_values = [], $c_values = [], $headers = '',
											$attachment = [], $unsubscribe = 0 ) {
		/**
		 * Initialize the template.
		 */
		self::initialize('plugin');

		$nfw_options = nfw_get_option('nfw_options');
		/**
		 * Retrieve recipient.
		 */
		if ( is_multisite() && ( $nfw_options['alert_sa_only'] == 2 ) ) {
			$recipient = get_option('admin_email');
			$unsubscribe = 0;
		} else {
			$recipient = $nfw_options['alert_email'];
		}
		if ( empty( $recipient ) ) {
			nfw_log_error( sprintf(
				__('Cannot send notification, no valid email found (%s)', 'nfwplus'),
				'alert_email')
			);
			return;
		}

		$subject = vsprintf( self::$template[$tpl]['subject'], $s_values );
		$message = vsprintf( self::$template[$tpl]['content'], $c_values ) .
					"\n\n". self::$template['signature'];

		/**
		 * In order to use Sodium, we must have WordPress >=5.2 or PHP >= 7.2.0.
		 */
		if (! empty( $unsubscribe ) ) {
			require_once __DIR__ .'/email_sodium.php';
			$unsubscribe = nfw_check_sodium();
		}

		if (! empty( $unsubscribe ) ) {
			/**
			 * Link will be valid for 12 hours.
			 */
			$expire = time() + 60 * 60 * 12;
			$admin_email = get_option('admin_email');
			/**
			 * We need to create a unique unsubscribe link for each recipients.
			 */
			$recipients = explode(',', $recipient );
			foreach( $recipients as $to ) {
				$to = trim( $to );
				/**
				 * If that's the admin email, we can't remove it.
				 */
				if ( $to == $admin_email ) {
					$click = '';

				} else {
					$link		= nfw_sodium_encrypt( $to, $expire, $unsubscribe );
					$uri		= home_url('/') ."?nfw_stop_notification=$link";
					$click	= "\n\n". sprintf(
						/* Translators: unsubscribe link */
						__("If you don't have access to that site any longer, you can remove your email by clicking the following link (valid for 12 hours): %s", 'ninjafirewall'),
						$uri
					);
				}
				$res = wp_mail( $to, $subject, $message . $click, $headers, $attachment );
				if ( $res === false ) {
					nfw_log_error( sprintf(
						/* Translators: 1=subject, 2=recipient */
						__('Cannot send email "%1$s" to recipient "%2$s"', 'ninjafirewall'),
						$to, $subject
					) );
				}
				/**
				 * Delete attachment.
				 */
				if ( $attachment && is_file( $attachment ) ) {
					unlink( $attachment );
				}
				return $res;
			}

		} else {
			$res = wp_mail( $recipient, $subject, $message, $headers, $attachment );
			if ( $res === false ) {
				nfw_log_error( sprintf(
					/* Translators: 1=subject, 2=recipient */
					__('Cannot send email "%1$s" to recipient "%2$s"', 'ninjafirewall'),
					$recipient, $subject
				) );
			}
			/**
			 * Delete attachment.
			 */
			if ( $attachment && is_file( $attachment ) ) {
				unlink( $attachment );
			}
			return $res;
		}
	}


	/**
	 * Send an email using PHP mail().
	 * Used by the firewall part that loads before WordPress.
	 */
	public static function PHPsend( $to, $tpl, $s_values = [], $c_values = [],
							$logdir = '', $headers = '', $attachment = '', $attachment_name = '') {

		if (! function_exists('mail') ) {
			/**
			 * There's nothing we can do here.
			 */
			return false;
		}

		/**
		 * Initialize the template.
		 */
		self::initialize('firewall', $logdir);

		$subject = vsprintf( self::$template[$tpl]['subject'], $s_values );
		$message = vsprintf( self::$template[$tpl]['content'], $c_values ) .
					"\n\n". self::$template['signature'];

		/**
		 * Multipart mime for attachments.
		 */
		if (! empty( $attachment ) ) {
			$random_hash = md5( date('r', time() ) );
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: multipart/mixed; boundary=\"NFWP-mixed-{$random_hash}\"\r\n";

			$body = '--NFWP-mixed-' . $random_hash . "\n" .
			'Content-Type: multipart/alternative; boundary="NFWP-alt-' . $random_hash . '"' . "\n\n" .
			'--NFWP-alt-' . $random_hash . "\n" .
			'Content-Type: text/plain; charset="UTF-8"'. "\n" .
			'Content-Transfer-Encoding: 7bit'. "\n\n" .
			$message ."\n".
			'--NFWP-alt-' . $random_hash . '--'. "\n\n\n" .
			'--NFWP-mixed-' . $random_hash . "\n" .
			'Content-Type: text/plain; name="'. $attachment_name .'"'. "\n" .
			'Content-Transfer-Encoding: base64' . "\n" .
			'Content-Disposition: attachment' . "\n\n" .
			chunk_split( base64_encode( $attachment ) ) . "\n" .
			'--NFWP-mixed-' . $random_hash . '--' . "\n\n";

			return mail( $to, $subject, $body, $headers );
		/**
		 * No attachment.
		 */
		} else {
			$headers .= "Content-Transfer-Encoding: 7bit\r\n";
			$headers .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
			$headers .= "MIME-Version: 1.0\r\n";

			return mail( $to, $subject, $message, $headers );
		}
	}

}

// =====================================================================
// EOF
