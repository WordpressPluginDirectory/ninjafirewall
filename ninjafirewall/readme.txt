=== NinjaFirewall (WP Edition) - Advanced Security Plugin and Firewall ===
Contributors: nintechnet, bruandet
Tags: security, firewall, malware, virus, protection
Requires at least: 4.9
Tested up to: 6.7
Stable tag: 4.7
Requires PHP: 7.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A true Web Application Firewall to protect and secure WordPress.

== Description ==

= A true Web Application Firewall =

NinjaFirewall (WP Edition) is a true Web Application Firewall. Although it can be installed and configured just like a plugin, it is a stand-alone firewall that stands in front of WordPress.

It allows any blog administrator to benefit from very advanced and powerful security features that usually aren't available at the WordPress level, but only in security applications such as the Apache [ModSecurity](http://www.modsecurity.org/ "") module or the PHP [Suhosin](http://suhosin.org/ "") extension.

> NinjaFirewall requires at least PHP 7.1, MySQLi extension and is only compatible with Unix-like OS (Linux, BSD). It is **not compatible with Microsoft Windows**.

NinjaFirewall can hook, scan, sanitise or reject any HTTP/HTTPS request sent to a PHP script before it reaches WordPress or any of its plugins. All scripts located inside the blog installation directories and sub-directories will be protected, including those that aren't part of the WordPress package. Even encoded PHP scripts, hackers shell scripts and backdoors will be filtered by NinjaFirewall.

= Powerful filtering engine =

NinjaFirewall includes the most powerful filtering engine available in a WordPress plugin. Its most important feature is its ability to normalize and transform data from incoming HTTP requests which allows it to detect Web Application Firewall evasion techniques and obfuscation tactics used by hackers, as well as to support and decode a large set of encodings. See our blog for a full description: [An introduction to NinjaFirewall filtering engine](https://blog.nintechnet.com/introduction-to-ninjafirewall-filtering-engine/ "").

= Fastest and most efficient brute-force attack protection for WordPress =

By processing incoming HTTP requests before your blog and any of its plugins, NinjaFirewall is the only plugin for WordPress able to protect it against very large brute-force attacks, including distributed attacks coming from several thousands of different IPs.

See our benchmarks and stress-tests: [Brute-force attack detection plugins comparison](https://blog.nintechnet.com/wordpress-brute-force-attack-detection-plugins-comparison-2015/ "")

The protection applies to the `wp-login.php` script but can be extended to the `xmlrpc.php` one. The incident can also be written to the server `AUTH` log, which can be useful to the system administrator for monitoring purposes or banning IPs at the server level (e.g., Fail2ban).

= Real-time detection =

**File Guard** real-time detection is a totally unique feature provided by NinjaFirewall: it can detect, in real-time, any access to a PHP file that was recently modified or created, and alert you about this. If a hacker uploaded a shell script to your site (or injected a backdoor into an already existing file) and tried to directly access that file using his browser or a script, NinjaFirewall would hook the HTTP request and immediately detect that the file was recently modified or created. It would send you an alert with all details (script name, IP, request, date and time).

= File integrity monitoring  =

**File Check** lets you perform file integrity monitoring by scanning your website hourly, twicedaily or daily. Any modification made to a file will be detected: file content, file permissions, file ownership, timestamp as well as file creation and deletion.

= Watch your website traffic in real time =

**Live Log** lets you watch your website traffic in real time. It displays connections in a format similar to the one used by the `tail -f` Unix command. Because it communicates directly with the firewall, i.e., without loading WordPress, **Live Log** is fast, lightweight and it will not affect your server load, even if you set its refresh rate to the lowest value.

= Event Notifications =

NinjaFirewall can alert you by email on specific events triggered within your blog. Some of those alerts are enabled by default and it is highly recommended to keep them enabled. It is not unusual for a hacker, after breaking into your WordPress admin console, to install or just to upload a backdoored plugin or theme in order to take full control of your website. NinjaFirewall can also [attach a PHP backtrace](https://blog.nintechnet.com/ninjafirewall-wp-edition-adds-php-backtrace-to-email-notifications/ "NinjaFirewall adds PHP backtrace to email notifications") to important notifications.

Monitored events:

* Administrator login.
* Modification of any administrator account in the database.
* Plugins upload, installation, (de)activation, update, deletion.
* Themes upload, installation, activation, deletion.
* WordPress update.
* Pending security update in your plugins and themes.

= Stay protected against the latest WordPress security vulnerabilities =

To get the most efficient protection, NinjaFirewall can automatically update its security rules daily, twice daily or even hourly. Each time a new vulnerability is found in WordPress or one of its plugins/themes, a new set of security rules will be made available to protect your blog immediately.

= Strong Privacy =

Unlike a Cloud Web Application Firewall, or Cloud WAF, NinjaFirewall works and filters the traffic on your own server and infrastructure. That means that your sensitive data (contact form messages, customers credit card number, login credentials etc) remains on your server and is not routed through a third-party company's servers, which could pose unnecessary risks (e.g., decryption of your HTTPS traffic in order to inspect it, employees accessing your data or logs in plain text, theft of private information, man-in-the-middle attack etc).

Your website can run NinjaFirewall and be **compliant with the General Data Protection Regulation (GDPR)**. [See our blog for more details](https://blog.nintechnet.com/ninjafirewall-general-data-protection-regulation-compliance/ "GDPR Compliance").

= IPv6 compatibility =

IPv6 compatibility is a mandatory feature for a security plugin: if it supports only IPv4, hackers can easily bypass the plugin by using an IPv6. NinjaFirewall natively supports IPv4 and IPv6 protocols, for both public and private addresses.

= Multi-site support =

NinjaFirewall is multi-site compatible. It will protect all sites from your network and its configuration interface will be accessible only to the Super Admin from the network main site.

= Possibility to prepend your own PHP code to the firewall =

You can prepend your own PHP code to the firewall with the help of an [optional distributed configuration file](https://nintechnet.com/ninjafirewall/wp-edition/help/?htninja). It will be processed before WordPress and all its plugins are loaded. This is a very powerful feature, and there is almost no limit to what you can do: add your own security rules, manipulate HTTP requests, variables etc.

= Low Footprint Firewall =

NinjaFirewall is very fast, optimised, compact, and requires very low system resource.
See for yourself: download and install the [Code Profiler](https://wordpress.org/plugins/code-profiler/ "") plugin and compare NinjaFirewall's performance with other security plugins.

= Non-Intrusive User Interface =

NinjaFirewall looks and feels like a built-in WordPress feature. It does not contain intrusive banners, warnings or flashy colors. It uses the WordPress simple and clean interface and is also smartphone-friendly.

= Contextual Help =

Each NinjaFirewall menu page has a contextual help screen with useful information about how to use and configure it.
If you need help, click on the *Help* menu tab located in the upper right corner of each page in your admin panel.

= Need more security ? =

Check out our new supercharged edition: [NinjaFirewall WP+ Edition](https://nintechnet.com/ninjafirewall/wp-edition/ "NinjaFirewall WP+ Edition")

* Unix shared memory use for inter-process communication and blazing fast performances.
* IP-based Access Control.
* Role-based Access Control.
* Country-based Access Control via geolocation.
* URL-based Access Control.
* Bot-based Access Control.
* [Centralized Logging](https://blog.nintechnet.com/centralized-logging-with-ninjafirewall/ "Centralized Logging").
* Antispam for comment and user regisration forms.
* Rate limiting option to block aggressive bots, crawlers, web scrapers and HTTP attacks.
* Response body filter to scan the output of the HTML page right before it is sent to your visitors browser.
* Better File uploads management.
* Better logs management.
* [Syslog logging](https://blog.nintechnet.com/syslog-logging-with-ninjafirewall/ "Syslog logging").

[Learn more](https://nintechnet.com/ninjafirewall/wp-edition/ "") about the WP+ Edition unique features. [Compare](https://nintechnet.com/ninjafirewall/wp-edition/?comparison "") the WP and WP+ Editions.


= Requirements =

* WordPress 4.9+
* Admin/Superadmin with `manage_options` + `unfiltered_html capabilities`.
* PHP 7.1+
* MySQL or MariaDB with MySQLi extension
* Apache / Nginx / LiteSpeed / Openlitespeed compatible
* Unix-like operating systems only (Linux, BSD etc). NinjaFirewall is **NOT** compatible with Microsoft Windows.

== Frequently Asked Questions ==

= Why is NinjaFirewall different from other security plugins for WordPress ? =

NinjaFirewall stands between the attacker and WordPress. It can filter requests before they reach your blog and any of its plugins. This is how it works :

`Visitor -> HTTP server -> PHP -> NinjaFirewall #1 -> WordPress -> NinjaFirewall #2 -> Plugins & Themes -> WordPress exit -> NinjaFirewall #3`

And this is how all WordPress plugins work :

`Visitor > HTTP server > PHP > WordPress > Plugins -> WordPress exit`


Unlike other security plugins, it will protect all PHP scripts, including those that aren't part of the WordPress package.

= How powerful is NinjaFirewall? =
NinjaFirewall includes a very powerful filtering engine which can detect Web Application Firewall evasion techniques and obfuscation tactics used by hackers, as well as support and decode a large set of encodings. See our blog for a full description: [An introduction to NinjaFirewall 3.0 filtering engine](https://blog.nintechnet.com/introduction-to-ninjafirewall-filtering-engine/ "").

= Do I need root privileges to install NinjaFirewall ? =

NinjaFirewall does not require any root privilege and is fully compatible with shared hosting accounts. You can install it from your WordPress admin console, just like a regular plugin.


= Does it work with Nginx ? =

NinjaFirewall works with Nginx and others Unix-based HTTP servers (Apache, LiteSpeed etc). Its installer will detect it.

= Do I need to alter my PHP scripts ? =

You do not need to make any modifications to your scripts. NinjaFirewall hooks all requests before they reach your scripts. It will even work with encoded scripts (ionCube, ZendGuard, SourceGuardian etc).

= I moved my wp-config.php file to another directory. Will it work with NinjaFirewall ? =

NinjaFirewall will look for the wp-config.php script in the current folder or, if it cannot find it, in the parent folder.

= Will NinjaFirewall detect the correct IP of my visitors if I am behind a CDN service like Cloudflare ? =

You can use an optional configuration file to tell NinjaFirewall which IP to use. Please [follow these steps](https://nintechnet.com/ninjafirewall/wp-edition/help/?htninja "").

= Will it slow down my site ? =

Your visitors will not notice any difference with or without NinjaFirewall. From WordPress administration console, you can click "NinjaFirewall > Status" menu to see the benchmarks and statistics (the fastest, slowest and average time per request). NinjaFirewall is very fast, optimised, compact, requires very low system resources and [outperforms all other security plugins](https://blog.nintechnet.com/wordpress-brute-force-attack-detection-plugins-comparison/ "").
By blocking dangerous requests and bots before WordPress is loaded, it will save bandwidth and reduce server load.

= Is there any Microsoft Windows version ? =

NinjaFirewall works on Unix-like servers only. There is no Microsoft Windows version and we do not expect to release any.


== Installation ==

1. Upload `ninjafirewall` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'NinjaFirewall' menu.

== Screenshots ==

1. Overview page.
2. Statistics and benchmarks page.
3. Options page.
4. Policies pages 1/3: NinjaFirewall has a large list of powerful and unique policies that you can tweak accordingly to your needs.
5. Policies pages 2/3: NinjaFirewall has a large list of powerful and unique policies that you can tweak accordingly to your needs.
6. Policies pages 3/3: NinjaFirewall has a large list of powerful and unique policies that you can tweak accordingly to your needs.
7. File Guard: this is a totally unique feature, because it can detect, in real-time, any access to a PHP file that was recently modified or created, and alert you about this.
8. File Check: lets you perform file integrity monitoring upon request or on a specific interval (hourly, twicedaily, daily).
9. Event notifications can alert you by email on specific events triggered within your blog.
10. Login page protection: the fastest and most efficient brute-force attack protection for WordPress.
11. Firewall Log.
12. Live Log: lets you watch your website traffic in real time. It is fast, light and it does not affect your server load.
13. Rules Editor.
14. Security rules updates.
15. Contextual help.
16. Dashboard widget.

Security Plugin for WordPress.
Plugin de Seguridad de WordPress.
Plugin de Sécurité pour WordPress.
WordPress Sicherheit Plugin.

== Changelog ==

Need more security? Take the time to explore our supercharged Premium edition: [NinjaFirewall WP+ Edition](https://nintechnet.com/ninjafirewall/wp-edition/?comparison)

= 4.7 =

* This new version introduces NinjaFirewall sessions, an alternative to PHP sessions. They are an hybrid of PHP sessions and object caching, without session blocking. If you want to switch between PHP sessions and NinjaFirewall sessions, go to "NinjaFirewall > Dashboard" and follow the instructions.
* Fixed a "Undefined constant NFW_RULES" fatal error when migrating NinjaFirewall to another host.
* We have a new API (updates, security rules etc): api.nintechnet.com. Make sure to whitelist this subdomain if you are filtering outgoing connections.
* Updated Charts.js.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.6.1 =

* WP+ Edition (Premium): You can now enter your license key from WP CLI. Type "wp ninjafirewall license" and enter your license at the prompt.
* Fixed an issue with bulk user deletion: when multiple users were deleted at once, only the first one was written to the firewall log.
* Fixed an issue with the login protection: after disabling it and logging out, NinjaFirewall was still displaying a notice on the login page.
* Fixed a potential PHP fatal error: Attempt to modify property "no_update" on bool.
* Replaced all calls to the PHP glob() function with DirectoryIterator() to make file search compatible with remote files.
* Fixed an issue where some scheduled tasks were executed too often on multisite installations.
* WP+ Edition (Premium): Updated GeoIP databases.
* Updated Charts.js.
* Many additional small fixes and adjustments.

= 4.5.11 =

* Updated Charts.js.
* WP+ Edition (Premium): updated PayPal IPN and Automattic IP addresses.
* WP+ Edition (Premium): Updated GeoIP databases.
* Small fixes and adjustments.

= 4.5.10 =

* Added compatibility with blogs that don't have a database prefix.
* In the "Custom HTTP headers" section, NinjaFirewall will automatically convert header names to lowercase.
* Fixed a potential "Timezone ID is invalid" PHP notice when viewing the log.
* Updated Charts.js library.
* WP+ Edition (Premium): Updated GeoIP databases.
* Small fixes and adjustments.

= 4.5.9 =

* Added a new policy to protect against user accounts deletion. It can be found in the "Firewall Policies > WordPress > Permissions" section.
* Fixed an issue with the firewall log where the time and date could be using the wrong timezone.
* Fixed a PHP deprecated notice in the sodium_crypto_generichash function.
* WP+ Edition (Premium): Fixed a bug in the firewall where some uploaded images could be wrongly blocked.
* Updated Charts.js library.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5.8 =

* Added a "Line wrapping" checkbox in the "Live Log" page: it can be used to wrap or unwrap the lines in the textarea field.
* Updated Charts.js library.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5.7 =

* You can now select to block access to the REST API only if the user is not authenticated. See "Firewall Policies > WordPress REST API > Allow logged-in users to access the API".
* Fixed an accessibility issue with the toggle switches used in NinjaFirewall's settings. They were not compatible with screen readers.
* Added a new constant that can be used to change the frequency used by the firewall to monitor the database: `NFW_DBCHECK_INTERVAL`. It can be added to the wp-config.php or .htninja script. For instance, a 300-second interval: `define('NFW_DBCHECK_INTERVAL', 300);`. The lowest possible value, which is also the default, is 60 seconds.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5.6 =

* WP+ Edition (Premium): Updated GeoIP databases.
* Updated Charts.js library.
* Small fixes and adjustments.

= 4.5.5 =

* NinjaFirewall will always rely on the timezone that was set by WordPress and PHP, and will no longer attempt to set it.
* Updated Charts.js library.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5.4 =

* Fixed a potential "syntax error" on sites running PHP <=7.2.
* Fixed a bug where quotes in "Custom HTTP headers" values were escaped with slashes.
* Updated Charts.js library.
* WP+ Edition (Premium): Updated GeoIP databases.
* Small fixes and adjustments.

= 4.5.2 =

* Fixed several deprecated messages on websites running PHP 8.1.
* Updated Charts.js library.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5.1 =

* Fixed a PHP "Cannot use object of type WP_Error as array" error.
* Activating/deactivating NinjaFirewall from WP CLI doesn't require the `--user` parameter anymore.
* On websites running PHP 7.3 or above, NinjaFirewall will use the hrtime() function instead of microtime() for its metrics, because it is more reliable as it is not based on the internal system clock.
* WP+ Edition (Premium): Fixed a bug with right-to-left (RTL) WordPress sites where the checkboxes below the log were all messed up.
* The detection of base64-encoded injection has been slightly tweaked to lower the risk of false positives.
* WP+ Edition (Premium): The Bot Access Control input now accepts the following 6 additional characters: `( ) , ; ' "`.
* The "Monthly Statistics" graph and tooltip colours were improved.
* Updated Charts.js library.
* Small fixes and adjustments.
* WP+ Edition (Premium): Updated GeoIP databases.

= 4.5 =

* Added the possibility to enter custom HTTP response headers. See "Firewall Policies > Advanced Policies > HTTP response headers > Custom HTTP headers".
* Added the possibility to view the server's HTTP response headers. Click on the "Firewall Policies > Advanced Policies > HTTP response headers > HTTP headers test" button.
* Added a warning if WordPress is running inside a Docker image and the user wants to upgrade NinjaFirewall to Full WAF mode.
* Fixed a PHP "Undefined array key pluginzip" warning when reinstalling a plugin from a ZIP archive.
* WP+ Edition (Premium): The Access Control URI whitelist and blacklist now support permalinks.
* Fixed an issue where the daily report could be sent multiple times on some multisite installations.
* Fixed deprecated readonly() function message on WordPress 5.9.
* Fixed an issue where the firewall would wrongly send a WordPress update notification.
* WP+ Edition (Premium): Updated Stripes webhook notifications IP addresses in the Access Control section.
* Updated Charts.js library.
* WP+ Edition (Premium): Updated GeoIP databases.
* Many small fixes and adjustments.

