#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);

$wget_options = "-erobots=off -q -m -nH";
$rsync_options = '-a';

function better_die($msg)
{
    // Why oh why does not die() return with nonzero exit status ??
    echo trim($msg) . "\n";
    exit(1);
}

function test_command($cmd)
{
    $output = array();
    $ret = 0;
    exec($cmd, $output, $ret);
    if ($ret !== 0)
    {
        return false;
    }
    return true;
}
if (!test_command('find --version'))
{
    better_die('find not executable in path');
}

if (   !isset($argv[1])
    || empty($argv[1]))
{
    better_die("First argument must be path to config file\n");
}
$conffile = $argv[1];
if (   !is_readable($conffile)
    && strpos('/', $conffile) !== false)
{
    // Try adding current path
    $conffile = dirname($argv[0]) . "/{$argv[1]}";
}
if (!is_readable($conffile))
{
    better_die("File {$conffile} not readable\n");
}

eval('$sites_config = array(' . file_get_contents($conffile) . ');');
foreach ($sites_config as $k => $site_config)
{
    // TODO: site locking so we can do multiple dumps in parallel
    if (!isset($site_config['url']))
    {
        better_die("'url' not set for site {$k}");
    }
    if (!isset($site_config['dump_path']))
    {
        better_die("'dump_path' not set for site {$k}");
    }
    if (!is_writable($site_config['dump_path']))
    {
        better_die("{$site_config['dump_path']} is not writable");
    }
    

    /**
     * Rename files with GET parameters in the name
     *
     * So we can actually serve them, see documentation/USAGE 
     * on how to configure mod_rewrite
     */
    $cmd = "find {$site_config['dump_path']} -name '*\?*'";
    $output = array();
    $ret = 0;
    exec($cmd, $output, $ret);
    if (   $ret === 0
        && !empty($output))
    {
        foreach($output as $filepath)
        {
            list($filepart, $querypart) = explode('?', $filepath);
            $newpath = dirname($filepart) . "/{$querypart}_" . basename($filepart);
            $mv_cmd = "mv -f '{$filepath}' '{$newpath}'";
            echo "executing: {$mv_cmd}\n";
            system($mv_cmd);
        }
    }

}

?>