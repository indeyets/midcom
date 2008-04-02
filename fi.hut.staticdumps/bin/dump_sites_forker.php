#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);
// if max_children is automatically resolved to number lower than this, set it to this value
$min_children = 2; 
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

// Determine max children to spawn at one time
$max_children = false;
if (   isset($argv[2])
    && !empty($argv[2])
    && is_numeric($argv[2]))
{
    // Amount given on CLI, use that
    $max_children = (int)$argv[2];
    $min_children = $max_children;
}
if (empty($max_children))
{
    // Try to be smart
    $proc_count = trim(`/bin/grep 'processor' /proc/cpuinfo | /usr/bin/wc -l`);
    if (is_numeric($proc_count))
    {
        $max_children = (int)$proc_count;
    }
}
// If automatic value is too low, use the min_children value
if ($max_children < $min_children)
{
    $max_children = $min_children;
}

/**
 * Waits for a child to exit and checks the exit code
 *
 * @param boolean $all_ok reference to the global all_ok status
 * @return boolean indicating if any children remain
 */
function wait_child_check_exit(&$all_ok)
{
    $child_status = null;
    //echo "DEBUG: calling pcntl_wait(\$child_status)\n";
    $child_pid = pcntl_wait($child_status);
    //echo "DEBUG: \$child_pid={$child_pid}, \$child_status={$child_status}\n";
    if ($child_pid == -1)
    {
        // No children remaining
        return false;
    }
    $child_exit_code = pcntl_wexitstatus($child_status);
    if ($child_exit_code !== 0)
    {
        $all_ok = false;
    }
    //echo "DEBUG: \$child_status={$child_status}, \$child_exit_code={$child_exit_code}, \$all_ok={$all_ok}\n";
    return true;
}

eval('$sites_config = array(' . file_get_contents($conffile) . ');');
$all_ok = true;
$active_children = 0;
foreach ($sites_config as $k => $site_config)
{
    ++$active_children;
    //echo "DEBUG: \$active_children={$active_children}, \$max_children={$max_children}\n";
    if ($active_children > $max_children)
    {
        // Wait for one to complete
        //echo "DEBUG: Calling wait_child_check_exit... ";
        wait_child_check_exit($all_ok);
        //echo "DONE\n";
        --$active_children;
    }

    $pid = pcntl_fork();
    switch ($pid)
    {
        case -1:
            better_die("Could not fork child process");
            // this exits
        case 0:
            /**
             * WEIRDNESS: According to documentation this should be the parent, not the child process.
             * However it seems that we must do our child jobs here or pcntl_wait* -functions return immediatily.
             * These are processed correctly in the background though, see also comment in the default catch
             */
            // Start the standard dump script with specific config key
            $dump_script = dirname($argv[0]) . '/dump_sites.php';
            $dump_cmd = $dump_script;
            $dump_args = array($conffile, $k);
            /*
            echo "DEBUG: calling pcntl_exec({$dump_cmd}, \$dump_args, \$_ENV\n";
            echo "       \$dump_args\n";
            print_r($dump_args);
            */
            pcntl_exec($dump_cmd, $dump_args, $_ENV);
            // this will exit (in fact it does much more, see documentation)
        default:
            /**
             * WEIRDNESS: According to documentation this should be the child process.
             * However it seems that whatever we do here will be processed in the background
             * but pcnlt_wait* -functions do not wait for it to complete.
             */
            // What to do ?
            break;
    }
}

$i = 1;
//echo "DEBUG: Looping wait_child_check_exit(), i={$i}\n";
while (wait_child_check_exit($all_ok))
{
    ++$i;
    //echo "DEBUG: Looping wait_child_check_exit(), i={$i}\n";
    // Waiting for remaining children to exit
}
//echo "DEBUG: Loop complete\n";

if (!$all_ok)
{
    better_die('Some operation failed, see above');
}

?>