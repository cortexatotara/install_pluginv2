# Plugin Installer

This is not a plugin! It is a PHP cli program for installing plugins into a Totara server.

You must have root privledges to run this program.

To be installable the plugin must have a install_location.php file in the plugin's root folder.

install_plugin.php should be installed at /usr/local/totara although it can be installed anywhere.


## Usage:
		php install_plugin.php <path to destination Totara folder> <plugin name> <SSHuser>

		e.g. $ php install_plugin.php /var/www/test.totata.co.uk ctxdirectlogin


## Example install_location.php file that every plugin should contain:
		<?php

		// make sure we are running from the cli
		if (php_sapi_name() != "cli") {
		    echo "Install location must be run from the command line.";
		    die();
		}

		$location->pluginName = 'ctxdirectlogin'; // The name of the plugin.
		$location->parentFolder = 'local'; // The folder within Totara where the plugin will be installed.


