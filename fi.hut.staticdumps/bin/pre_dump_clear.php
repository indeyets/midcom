#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);
/**
 * Helper script to make sure dump_path exists and is clean for us to use
 */
// Normalize dump_path (no consecutive slashes, no trailing slash)
$dump_path = preg_replace(array('%/{2,}%','%/$%'), array('/', ''), $argv[2]);

if (!file_exists($dump_path))
{
    system('mkdir -p ' . escapeshellarg($dump_path));
}
else
{
    system('rm -rf ' . escapeshellarg("{$dump_path}/*"));
}

if (!file_exists($dump_path))
{
    echo "\nERROR: {$dump_path} does not exist\n";
    exit(1);
}

exit(0);
?>