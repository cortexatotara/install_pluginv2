<?php

// make sure we are running from the cli
if (php_sapi_name() != "cli") {
    die();
}

function parmUsage(){
    echo " Usage: php install_plugin.php TotaraFolder PluginName [SudoUser]\n";
}

function errHandle($errNo, $errStr, $errFile, $errLine) {
    echo "\n\nERROR - $errStr in $errFile on line $errLine\n";
    die();
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

function rchown($path, $group, $user, $mode)
{
    $dir = rtrim($path, '/');
    if ($items = glob($dir . '/*')) 
    {
        foreach ($items as $item) 
        {
            if (is_dir($item)) 
            {
                (!empty($mode)) ? chmod($item, $mode) : '';
                (!empty($user)) ? chown($item, $user) : '';
                (!empty($group)) ? chgrp($item, $group) : '';
                rchown($item, $group, $user, $mode);
            } 
            else 
            {
                (!empty($mode)) ? chmod($item, $mode) : '';
                (!empty($user)) ? chown($item, $user) : '';
                (!empty($group)) ? chgrp($item, $group) : '';
            }
        }
    }
    (!empty($mode)) ? chmod($path, $mode) : '';
    (!empty($user)) ? chown($path, $user) : '';
    (!empty($group)) ? chgrp($path, $group) : '';
}

set_error_handler('errHandle');

// parse args from the command line
$sudoUser='totaratxp';
if ( $argc == 4){
    $sudoUser=$argv[3];
}
else if ( $argc != 3){
    echo "Incorrect number of parameters.\n";
    parmUsage();
    die();
}

$totaraFolder=$argv[1];
$pluginName=$argv[2];

$tempFolder="/tmp";
if ( !is_dir($tempFolder) ){
    echo "Temp folder '{$tempFolder}' does not exist.\n";
    parmUsage();
    die();
}

if ( !is_dir($totaraFolder) ){
    echo "Folder '{$totaraFolder}' does not exist.\n";
    parmUsage();
    die();
}

if ( !file_exists("{$totaraFolder}/config.php")){
    echo "File 'config.php not found in the folder '{$totaraFolder}'.\n";
    parmUsage();
    die();
}

if ( empty($pluginName)){
    echo "Plugin name must be specified.\n";
    parmUsage();
    die();
}

echo "\n\nInstalling plugin '{$pluginName}'\n";

$pluginFolder="{$tempFolder}/{$pluginName}";

if (is_dir($pluginFolder) ){
    //echo "Deleting previous folder '{$pluginFolder}'\n";
    rrmdir($pluginFolder);
}

$gitUrl = "git@github.com:cortexatotara/{$pluginName}.git";
      
echo "Cloning plugin '{$pluginName}' from: {$gitUrl}\n";
$command = "sudo -u {$sudoUser} git clone {$gitUrl} {$pluginFolder} 2>&1";
exec($command, $output, $returnCode);

if ($returnCode !== 0) {
    echo "Cloning of plugin '{$pluginName}' failed.\n";
    echo "Output: " . implode("\n", $output) . "\n";
    die;
}

$pluginSourceFolder=$pluginFolder;
$pluginLocationFile="{$pluginFolder}/install_location.php";

if ( !file_exists($pluginLocationFile) ){
    $oldVF=$pluginLocationFile;
    $pluginLocationFile="{$tempFolder}/{$pluginName}/{$pluginName}/install_location.php";
    //echo "Unable to find install_location.php in folder '{$oldVF}' trying '{$pluginLocationFile}'.\n";
    if ( !file_exists($pluginLocationFile) ){
        echo "Unable to find file '{$pluginLocationFile}'.\n";
        die();   
    }
    else {
        $pluginFolder="{$tempFolder}/{$pluginName}/{$pluginName}";
        echo "Using plugin folder '{$pluginFolder}'.\n";
        echo "Using location file '{$pluginLocationFile}'.\n";
    }
}

$location=new stdClass();
include($pluginLocationFile);

if (empty($location->pluginName) ){
    echo "Unable to find variable '{$location->pluginName}' in file '{$pluginLocationFile}'.\n";
        die();   
}

if (empty($location->parentFolder) ){
    echo "Unable to find variable '$location->parentFolder' in file '{$pluginLocationFile}'.\n";
        die();   
}

if ( str_contains($totaraFolder,'totaratxp')){
    $installParent="{$totaraFolder}/server/{$location->parentFolder}";
}
else{
    $installParent="{$totaraFolder}/{$location->parentFolder}";
}

if (!is_dir($installParent) ){
    echo "\n\nThe instalation parent folder '{$installParent}' does not exist.\n";
    die();
}

$installPath="{$installParent}/{$location->pluginName}";

if (is_dir($installPath) ){
    echo "\n\nInstalling plugin '{$pluginName}' to folder '{$installPath}'.\n *** An existing version will be replaced. ***\n";
}
else{
    echo "\n\nInstalling plugin '{$pluginName}' to folder '{$installPath}'.\n";
}

echo "Are you sure you want to continue? (y/n) \n";
$input = fgetc(STDIN);

if ( $input == 'y') {

    if (is_dir($installPath) ){
       rrmdir($installPath);
    }

    rename($pluginFolder,$installPath);

    rchown($installPath, 'www-data', 'www-data', null);

    // Remove .git directory from installed plugin
    $gitDir = "{$installPath}/.git";
    if (is_dir($gitDir) ){
        rrmdir($gitDir);
    }

    echo "\nPlugin '{$pluginName}' successfully installed.\n\n";
}
else{
    echo "\nInstall aborted plugin '{$pluginName}' NOT installed.\n\n";
}

