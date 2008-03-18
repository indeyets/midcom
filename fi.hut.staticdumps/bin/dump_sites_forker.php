#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);

function better_die($msg)
{
    // Why oh why does not die() return with nonzero exit status ??
    echo trim($msg) . "\n";
    exit(1);
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
$all_ok = true;
foreach ($sites_config as $k => $site_config)
{
    $pid = pcntl_fork();
    switch ($pid)
    {
        case -1:
            better_die("Could not fork child process");
            // this exits
        case 0:
            // in parent, spawn next child
            continue 2;
        default:
            // in child process
            // Start the standard dump script with specific config key
            $dump_script = dirname($argv[0]) . '/dump_sites.php';
            $dump_cmd = "{$dump_script} {$conffile} {$k}";
            $dump_cmd_ret = 0;
            /**
             * PONDER: use pnctl_exec in stead ??
             */
            system($dump_cmd, $dump_cmd_ret);
            if ($dump_cmd_ret !== 0)
            {
                echo "command: {$dump_cmd} exited with status {$dump_cmd_ret}\n";
                $all_ok = false;
                exit(1);
            }
            exit(0);
    }
}

/*
$status = null;
while (($child_pid = pcntl_waitpid(0, $status)) != -1)
{
    $exitcode = pcntl_wexitstatus($status);
    echo "Child {$child_pid} exited with code {$exitcode}\n";
}
*/
$status = null;
while (pcntl_waitpid(0, $status) != -1)
{
    // Wait for children to complete
}


if (!$all_ok)
{
    better_die('Some operation failed, see above');
}

?>