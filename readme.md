# Plugin Installer

This is a PHP cli program for installing plugins into a Totara server.

You must have root privledges to run this program.

To be installable the plugin must have a install_location.php file in the plugin's root folder.

install_pluginv2.php should be installed at /usr/local/totara although it can be installed anywhere.

By default the code expects there to be a user named totaratxp that has SSH credentials that have been added to
Cortexa's private repository. If there is no totaratxp user on the system any user can be used once it has been granted access to the github repo.

## Usage: 
		php install_pluginv2.php <path to destination Totara folder> <plugin name> <SSHuser>

		e.g. $ php install_pluginv2.php /var/www/test.totata.co.uk myplugin totaratxp


## Example install_location.php file that every plugin should contain:
		<?php

		// make sure we are running from the cli
		if (php_sapi_name() != "cli") {
		    echo "Install location must be run from the command line.";
		    die();
		}

		$location->pluginName = 'mypluginname'; // The name of the plugin.
		$location->parentFolder = 'local'; // The folder within Totara where the plugin will be installed.


