#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);
/**
 * Helper script to do SVN add/delete +commit for dumped site
 *
 * Supposes following hierarchy
 *
 * ./checkouts/sitename
 * ./dumps/sitename
 *
 * where dump_path of the site is ./dumps/sitename and ./checkouts/sitename
 * is an existing checkout where current can commit non-interactively
 */
// TODO: How to make configurable ?
$file_exclude_patterns = array('%\.svn%', '%\.orig$%', '%\.sedbackup[0-9]*$%');

// Normalize dump_path (no consecutive slashes, no trailing slash)
$dump_path = preg_replace(array('%/{2,}%','%/$%'), array('/', ''), $argv[5]);
$svn_path = preg_replace("%dumps/(.*?)$%", "checkouts/\\1", $dump_path);
$commit = ((int)$argv[3] === 0) && ((int)$argv[4] === 0);
$site_url = $argv[1];
if (!$commit)
{
    // wget or rsync returned failure (all_ok does not interest us in this case...)
    exit(1);
}
if (!file_exists($dump_path))
{
    echo "\nERROR: {$dump_path} does not exist\n";
    exit(1);
}
if (!file_exists($svn_path))
{
    echo "\nERROR: {$svn_path} does not exist\n";
    exit(1);
}

/**
 * recursively lists files in directory
 */
function list_path_files_recursive($path)
{
    $path = preg_replace('%/{2,}|/$%', '', $path);
    $ret = array();
    $dp = opendir($path);
    if (!$dp)
    {
        return false;
    }
    
    while (($file_name = readdir($dp)) !== false)
    {
        if (   $file_name == '.'
            || $file_name == '..')
        {
            continue;
        }
        $file_path = "{$path}/{$file_name}";
        if (   is_dir($file_path)
            && !is_link($file_path))
        {
            $ret[] = $file_path;
            $ret = array_merge($ret, list_path_files_recursive($file_path));
            continue;
        }
        $ret[] = $file_path;
    }
    closedir($dp);

    sort($ret);
    return $ret;
}

$dump_files = list_path_files_recursive($dump_path);
$dump_comparable = array();
foreach ($dump_files as $k => $v)
{
    foreach($file_exclude_patterns as $pattern)
    {
        if (preg_match($pattern, $v))
        {
            continue 2;
        }
    }
    $dump_comparable[$k] = str_replace($dump_path, '', $v);
}

$svn_up_cmd = "cd {$svn_path} && svn up";
$svn_up_output = array();
$svn_up_ret = 0;
exec($svn_up_cmd, $svn_up_output, $svn_up_ret);
if ($svn_up_ret !== 0)
{
    echo "\nERROR: Could not update checkout\n";
    exit(1);
}

$svn_files = list_path_files_recursive($svn_path);
/*
echo "DEBUG: svn_files\n";
print_r($svn_files);
*/
$svn_comparable = array();
foreach ($svn_files as $k => $v)
{
    foreach($file_exclude_patterns as $pattern)
    {
        if (preg_match($pattern, $v))
        {
            continue 2;
        }
    }
    $svn_comparable[$k] = str_replace($svn_path, '', $v);
}

/*
echo "DEBUG: dump_comparable and svn_comparable\n";
print_r($dump_comparable);
print_r($svn_comparable);
*/

$update_files = array_intersect($dump_comparable, $svn_comparable);
$add_files = array_intersect($dump_comparable, array_diff($dump_comparable, $svn_comparable));
$remove_files = array_intersect($svn_comparable, array_diff($svn_comparable, $dump_comparable));

/*
echo "DEBUG: update_files\n";
print_r($update_files);
echo "DEBUG: add_files\n";
print_r($add_files);
echo "DEBUG: remove_files\n";
print_r($remove_files);
*/

/**
 * Sort array of filenames to alphabetical tree order
 */
function by_tree($a_part, $b_part)
{
    global $dump_path;
    $a_path = $dump_path . $a_part;
    $b_path = $dump_path . $b_part;
    $a_components = explode('/', $a_part);
    $b_components = explode('/', $b_part);
    $a_score = count($a_components);
    $b_score = count($b_components);
    if (is_dir($a_path))
    {
        --$a_score;
    }
    if (is_dir($b_path))
    {
        --$b_score;
    }
    if ($a_score === $b_score)
    {
        // Equal scores, sort by strnatcmp
        return strnatcmp($a_part, $b_part);
    }
    // Standard gt/lt response
    if ($a_score > $b_score)
    {
        return 1;
    }
    if ($a_score < $b_score)
    {
        return -1;
    }
}

usort($remove_files, 'by_tree');
$remove_files = array_reverse($remove_files);
/*
echo "DEBUG: remove_files (after sort)\n";
print_r($remove_files);
*/
foreach ($remove_files as $partial_path)
{
    if (empty($partial_path))
    {
        continue;
    }
    $filepath = $svn_path . $partial_path;
    if (!file_exists($filepath))
    {
        // extra safety
        continue;
    }
    $svn_del_cmd =  'svn delete --force ' . escapeshellarg($filepath);
    $svn_del_output = array();
    $svn_del_ret = 0;
    exec($svn_del_cmd, $svn_del_output, $svn_del_ret);
    if ($svn_del_ret !== 0)
    {
        // Error, what to do
        echo "ERROR: {$svn_del_cmd} exited with code {$svn_del_ret}, output:\n";
        print_r($svn_del_output);
        $commit = false;
    }
}

usort($add_files, 'by_tree');
/*
echo "DEBUG: add_files (after sort)\n";
print_r($add_files);
*/
foreach ($add_files as $partial_path)
{
    if (empty($partial_path))
    {
        continue;
    }
    $dump_filepath = $dump_path . $partial_path;
    $svn_filepath = $svn_path . $partial_path;
    if (!file_exists($dump_filepath))
    {
        // extra safety
        continue;
    }
    if (file_exists($svn_filepath))
    {
        // extra safety
        $update_files[] = $partial_path;
        continue;
    }
    if (   is_dir($dump_filepath)
        && !is_link($dump_filepath))
    {
        $svn_add_cmd =  'mkdir -p ' . escapeshellarg($svn_filepath) . ' && svn add ' . escapeshellarg($svn_filepath);
    }
    else
    {
        $svn_add_cmd =  'cp -fP ' . escapeshellarg($dump_filepath) . ' ' . escapeshellarg($svn_filepath) . ' && svn add ' . escapeshellarg($svn_filepath);
    }
    $svn_add_output = array();
    $svn_add_ret = 0;
    exec($svn_add_cmd, $svn_add_output, $svn_add_ret);
    if ($svn_add_ret !== 0)
    {
        // Error, what to do
        echo "ERROR: {$svn_add_cmd} exited with code {$svn_add_ret}, output:\n";
        print_r($svn_add_output);
        $commit = false;
    }
}

foreach ($update_files as $partial_path)
{
    if (empty($partial_path))
    {
        continue;
    }
    $dump_filepath = $dump_path . $partial_path;
    $svn_filepath = $svn_path . $partial_path;
    if (!file_exists($dump_filepath))
    {
        // extra safety
        continue;
    }
    if (   is_dir($dump_filepath)
        && !is_link($dump_filepath))
    {
        continue;
    }
    //$cp_cmd =  'cp -fP ' . escapeshellarg($dump_filepath) . ' ' . escapeshellarg($svn_filepath);
    $cp_cmd =  'cp -fP ' . escapeshellarg($dump_filepath) . ' ' . escapeshellarg($svn_filepath);
    $cp_output = array();
    $cp_ret = 0;
    exec($cp_cmd, $cp_output, $cp_ret);
    if ($cp_ret !== 0)
    {
        // Error, what to do
        echo "ERROR: {$cp_cmd} exited with code {$cp_ret}, output:\n";
        print_r($cp_output);
        $commit = false;
    }
}

if (!$commit)
{
    echo "ERROR detected, rolling back checkout\n";
    $svn_up_cmd = 'cd ' . escapeshellarg($svn_path) . ' && rm -rf * && svn up && svn revert --recursive .';
    $svn_up_output = array();
    $svn_up_ret = 0;
    exec($svn_up_cmd, $svn_up_output, $svn_up_ret);
    if ($svn_up_ret !== 0)
    {
        echo "ERROR: {$svn_up_cmd} exited with code {$svn_up_ret}, output:\n";
        print_r($svn_up_output);
    }
    exit(1);
}

/*
echo "DEBUG: svn diff {$svn_path}\n";
$svn_diff_cmd = "cd {$svn_path} && svn diff";
system($svn_diff_cmd);
*/

$commit_message = "Automatic commit of {$site_url} with " . basename($argv[0]);
$svn_commit_cmd = 'cd ' . escapeshellarg($svn_path) . ' && svn commit -m ' . escapeshellarg($commit_message);
$svn_commit_output = array();
$svn_commit_ret = 0;
exec($svn_commit_cmd, $svn_commit_output, $svn_commit_ret);
if ($svn_commit_ret !== 0)
{
    echo "ERROR: {$svn_commit_cmd} exited with code {$svn_commit_ret}, output:\n";
    print_r($svn_commit_output);
    exit(1);
}


exit(0);
?>