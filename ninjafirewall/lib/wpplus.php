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

// Block immediately if user is not allowed :
nf_not_allowed( 'block', __LINE__ );

?>
<div class="wrap about-wrap full-width-layout">

	<h1>
		<?php _e('Need more security?', 'ninjafirewall') ?>
	</h1>

	<p class="about-text">
		<?php
		printf( __('Take the time to explore NinjaFirewall %s, a supercharged Edition of our Web Application Firewall. It adds many new exciting features and blazing fast performances to make it the fastest and most powerful security plugin for WordPress, no less!', 'ninjafirewall'), '<font color="#21759B">WP+</font> Edition' ) ?>
	</p>

	<div style="position:absolute;top:0;right:0;display:inline-block;">
		<img src="<?php echo plugins_url() ?>/ninjafirewall/images/ninjafirewall_100.png" />
	</div>

	<hr />

	<h1>
		<?php _e('New Features', 'ninjafirewall') ?>
	</h1>

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('Access Control is a powerful set of directives that can be used to allow or restrict access to your blog, depending on the <strong>User Role</strong>, <strong>IP</strong>, <strong>Geolocation</strong>, <strong>Requested URL</strong>, <strong>User-agent</strong> visitors behavior (<strong>Rate Limiting</strong>) and <strong>User Input</strong>. Those directives will be processed before the Firewall Policies and NinjaFirewall\'s built-in security rules.', 'ninjafirewall') ?>
			<p><?php _e('Its main configuration allows you to whitelist WordPress users depending on their roles, to select the source IP (useful if your site is using a CDN or behind a reverse-proxy/load balancer), and the HTTP methods all directives should apply to.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-geolocation.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-geolocation.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Geolocation Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('Geolocation can be used to block visitors from specific countries. It can apply to the whole blog or only to specific folders or scripts (e.g., /wp-login.php, /xmlrpc.php etc). If you have a theme or a plugin that needs to know your visitors location, you can even ask NinjaFirewall to append the country code to the PHP headers.', 'ninjafirewall') ?></p>
		</div>

	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('IP Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('The IP Access Control allows you to permanently allow or block an IP, a whole range of IP addresses <strong>and even AS numbers</strong> (Autonomous System number). IPv4 and IPv6 are fully supported by NinjaFirewall.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-ip.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-ip.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<p><?php _e('The IP Access Control can slow down aggressive bots, crawlers, web scrapers or even small HTTP attacks with its <strong>Rate-Limiting</strong> feature.', 'ninjafirewall') .' ' ?>

			<?php _e('Because it can block attackers <strong>before WordPress and all its plugins are loaded</strong> and can handle a lot of HTTP requests per second, NinjaFirewall will save precious bandwidth and reduce your server load.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/rate-limiting.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/rate-limiting.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-url.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-url.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('URL Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('URL Access Control lets you permanently allow/block any access to one or more PHP scripts.', 'ninjafirewall') ?></p>
		</div>

	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Bot Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('Bot Access Control allows you block bots, scanners and various annoying crawlers.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-bot.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-bot.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-input.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/access-control-input.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('User Input Access Control', 'ninjafirewall') ?></h3>
			<p><?php _e('User Input Access Control allows you to to ignore or block some specific user input.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3>Web Filter</h3>
			<p><?php _e('The Web Filter can monitor the response body, i.e., the output of the HTML page, and search it for some specific keywords, which can be useful to identify errors, hacked content and data leakage issues in the response body sent to your visitors.', 'ninjafirewall') .' ' ?>
			<?php _e('In the case of a positive detection, NinjaFirewall will not block the response body but will send you an alert by email. It can even attach the whole HTML source of the page for your review.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/web-filter.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/web-filter.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/anti-spam.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/anti-spam.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<h3><?php _e('Antispam', 'ninjafirewall') ?></h3>
			<p><?php _e('The Antispam can protect your blog comment and registration forms against spam. The protection is totally transparent to your visitors and does not require any interaction: no CAPTCHA, no math puzzles or trivia questions. Extremely easy to activate, but powerful enough to make spam bots life as miserable as possible.', 'ninjafirewall') ?></p>
			<p class="description"><?php _e('NinjaFirewall antispam feature works only with WordPress built-in comment and registration forms.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Centralized Logging', 'ninjafirewall') ?></h3>
			<p><?php printf( __('Centralized Logging lets you remotely access the firewall log of all your NinjaFirewall protected websites from one single installation, using the <a href="%s">Centralized Logging</a> feature. You do not need any longer to log in to individual servers to analyse your log data.', 'ninjafirewall'), 'https://blog.nintechnet.com/centralized-logging-with-ninjafirewall/') ?>
			<br />
			<?php _e('There is no limit to the number of websites you can connect to, and they can be running any edition of NinjaFirewall: WP, <font color="#21759B">WP+</font>, Pro or <font color="red">Pro+</font>.', 'ninjafirewall') ?>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/centralized-logging.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/centralized-logging.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<h1>
		<?php _e('Improved features', 'ninjafirewall') ?>
	</h1>

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<h3><?php _e('File uploads', 'ninjafirewall') ?></h3>
			<p><?php _e('You can allow uploads while rejecting potentially dangerous files, <strong>even if they are compressed inside a ZIP archive</strong>: scripts (PHP, CGI, Ruby, Python, bash/shell), C/C++ source code, binaries (MZ/PE/NE and ELF formats), system files (.htaccess, .htpasswd and PHP INI) and SVG files containing Javascript/XML events. You can easily limit the size of each uploaded file too, without having to modify your PHP configuration.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/file-uploads.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/file-uploads.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/firewall-log.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/firewall-log.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Firewall Log', 'ninjafirewall') ?></h3>
			<p>
			<?php _e('The log menu has been revamped too. You can disable the firewall log, delete the current one, enable its rotation based on the size of the file and, if any, view each rotated log separately. Quick filtering options are easily accessible from checkboxes and the log can be exported as a TSV (tab-separated values) text file. You can also easily add any IP to your Access Control whitelist or blacklist.', 'ninjafirewall') ?><br />
			<?php _e('It is also possible to redirect all incidents and events to the Syslog server:', 'ninjafirewall') ?> <a href="https://blog.nintechnet.com/syslog-logging-with-ninjafirewall/">Syslog logging with NinjaFirewall</a>.</p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php _e('Rules Update', 'ninjafirewall') ?></h3>
			<p>
			<?php _e('You can check for security rules updates <b>as often as every 15 minutes</b>, versus one hour for the free WP Edition. Don\'t leave your blog at risk!', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/rules-update.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/rules-update.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<a href="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/shared-memory.png" class="thickbox"><img src="<?php echo plugins_url() ?>/ninjafirewall/images/screenshots/shared-memory.png" class="wpplus" title="<?php _e('Click to enlarge image.', 'ninjafirewall') ?>" /></a>
			<p class="description aligncenter"><?php _e('Click to enlarge image.', 'ninjafirewall') ?></p>
			<p class="description"><?php _e('This feature requires that PHP was compiled with the <code>--enable-shmop</code> parameter and that NinjaFirewall is running in "Full WAF" mode.', 'ninjafirewall') ?></p>
		</div>
		<div class="column">
			<h3><?php _e('Shared Memory use', 'ninjafirewall') ?></h3>
			<p>
			<?php printf( __('Although NinjaFirewall is already <a href="%s">much faster than other WordPress plugins</a>, the <b><font color="#21759B">WP+</font> Edition</b> brings its performance to a whole new level by using Unix shared memory in order to speed things up even more.', 'ninjafirewall'), 'https://blog.nintechnet.com/wordpress-brute-force-attack-detection-plugins-comparison-2015/') ?> <?php _e('This allows easier and faster inter-process communication between the firewall and the plugin part of NinjaFirewall and, because its data and configuration are stored in shared memory segments, the firewall does not need to connect to the database any longer.', 'ninjafirewall') ?> <?php _e('This dramatically increases the processing speed (there is nothing faster than RAM), prevents blocking I/O and MySQL slow queries. On a very busy server like a multi-site network, the firewall processing speed will increase from 25% to 30%. It can be enabled from the "Firewall Options" menu.', 'ninjafirewall') ?></p>
		</div>

	</div>

	<hr />

	<h3><b><a href="https://nintechnet.com/ninjafirewall/wp-edition/"><?php _e('Learn more</a> about the <font color="#21759B">WP+</font> Edition unique features.', 'ninjafirewall') ?></b></h3>
	<h3><b><a href="https://nintechnet.com/ninjafirewall/wp-edition/?comparison"><?php _e('Compare</a> the WP and <font color="#21759B">WP+</font> Editions.', 'ninjafirewall') ?></b></h3>

	<hr />

</div>

<?php
// ---------------------------------------------------------------------
// EOF
