#!/usr/bin/php
<?php
error_reporting(E_ALL);
require_once('normalize_whitespace_normalizer.php');
if ($argc < 4)
{
    $name = basename($argv[0]);
    echo "\nUsage: {$name} <configfile> <username> <password>\n";
    echo "  For example:\n";
    echo "  {$name} midgard 'sgadmin+sgname' 'adminpasswd' \n\n";
    exit(1);
}
$conffile =& $argv[1];
$username =& $argv[2];
$password =& $argv[3];
if (!mgd_config_init($conffile))
{
    echo "\nInitialization failed\n\n";
    exit(1);
}
mgd_auth_midgard($username, $password);
if (!$_MIDGARD['user'])
{
    echo "\nAuthentication failed\n\n";
    exit(1);
}
if (!$_MIDGARD['sitegroup'] === 0)
{
    echo "\nSG0 usage not supported\n\n";
    exit(1);
}



$normalizer = new midcom_support_wsnormalizer();

?>