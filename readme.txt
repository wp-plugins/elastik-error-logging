=== Elastik Error Logging ===
Contributors: jarofgreen
Tags: php, error, logging, ticket
Requires at least: 3.0.4
Tested up to: 3.1.0
Stable tag: 0.1

Logs errors with full information to an Elastik Ticket System. Errors can be tracked and emails can be sent to devs from there.

== Description ==

This plugin collects errors from WordPress sites and sends them to Elastik, an Open Source ticket system available from http://elastik.sourceforge.net/ 

Elastik will collect PHP errors with full information, merge duplicate errors, create tickets so staff can track errors and email staff. 

== Installation ==

1. Set up an instance of Elastik ( http://elastik.sourceforge.net/ ) with the ErrorReportingService module.
2. In Elastik, create a project and create a new Error Reporting Site (See Elastik documentation on the ErrorReportingService module for more information on this).
3. Upload the folder 'ElastikErrorLogging' and all it's contents to the `/wp-content/plugins/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Go to "Elastik Error Logging" and copy the settings from the site you created earlier.

That's it! From your WordPress site you can ping the server. You can then check the ping was recorded in Elastik to check your configuration is correct.

== Frequently Asked Questions ==

= For Fatal run-time errors (E_ERROR), the stack trace does not seem to be complete? =

No. Due to how these are caught, we can only provide you with minimal information. Sorry.

= What is the performance like? =

Every time an error in encountered, data is collected and a HTTP request is sent to Elastik to log that error. The HTTP request may take time - how long depends on how slow the Elastik server is. So do bear in mind that errors should be fixed as soon as possible.

= Can I get anyone to host Elastik for me while I test this? =

You may be able to get an account at http://demo.elastik.jarofgreen.co.uk/mod.DemoSignUp/ or contact the plugin authors and we may be able to help. We cannot guarantee the uptime or data on our testing server.

= Can I get anyone to host Elastik for me? (I can pay) =

Contact http://www.mapix.com/ - they are the original developers of both Elastik and this plugin.

= Can I get anyone to host Elastik for me? (I can't pay) =

We do not know of anyone.

= I'm writing WordPress code and want my errors to be caught by this plugin! =

Simply throw an exception and if you don't catch it we will! http://php.net/manual/en/language.exceptions.php

= What versions of PHP does this run on? =

PHP 5.2 and above.

== Screenshots ==

1. The plugin settings you can configure.
2. An error report in Elastik (Elastik Version 0.3.1). This is an actual error from WordPress 3.0.4.

== Changelog ==

= 0.1 =
* First Version

= 0.2 =
* Bug fix for PHP 5.2

== Upgrade Notice ==

= 0.2 =
Bug fix for PHP 5.2.
