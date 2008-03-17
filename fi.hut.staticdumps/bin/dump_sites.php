#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);

$wget_options = "-erobots=off -q -m -nH";
$rsync_options = '-a';
$http_timeout = 300; // seconds = 5minutes
$lockfile_path = '/var/run';
$lockfile_prefix = 'fi_hut_staticdumps_';

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

if (!function_exists('file_put_contents'))
{
    function file_put_contents($filename, &$data)
    {
        $fp = fopen($filename, 'w');
        if (!$fp)
        {
            return false;
        }
        if (!fwrite($fp, $data))
        {
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!test_command('rsync --version'))
{
    better_die('rsync not executable in path');
}
if (!test_command('wget --version'))
{
    better_die('wget not executable in path');
}
if (!test_command('find --version'))
{
    better_die('find not executable in path');
}
require_once('midcom/lib/org/openpsa/httplib/nonmidcom.php');

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
$process_site = -1; // all
if (   isset($argv[2])
    && !empty($argv[2]))
{
    $process_site = (int)$argv[2];
}
$pid = posix_getpid();
$all_ok = true;
eval('$sites_config = array(' . file_get_contents($conffile) . ');');
if (   $process_site > 0)
{
    if (!isset($sites_config[$process_site]))
    {
        better_die("Key {$process_site} doesn't exists in $conffile");
    }
    // Overwrite the sites_config array to contain only the site to be processed
    $site_config = $sites_config[$process_site];
    $sites_config = array($site_config);
}
foreach ($sites_config as $k => $site_config)
{
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

    // Site locking so we can dump many sites in parallel
    $lockfile = "{$lockfile_path}/{$lockfile_prefix}" . md5(serialize($site_config)) . '.pid';
    //echo "DEBUG: pid={$pid}, lockfile={$lockfile}\n";
    if (file_exists($lockfile))
    {
        // File exists, check if the process_id within is valid
        $check_pid = (int)trim(file_get_contents($lockfile));
        $parent_pid = posix_getsid($check_pid);
        if (   !empty($parent_pid)
            && is_numeric($parent_pid))
        {
            //echo "DEBUG: {$lockfile} exists and containts valid PID {$check_pid}, skipping this site\n";
            continue;
        }
        else
        {
            // Lockfile contains invalid PID
            echo "\nWARN: {$lockfile} exists but contains INVALID PID {$check_pid}, previous run crashed ?\n";
            echo "      Removing the stale lockfile and continuing\n";
            unlink($lockfile);
        }
    }
    file_put_contents($lockfile, $pid);

    /**
     * Run pre-dump script, continue to next site if returns nonzero code
     */
    $pre_dump_ret = 0;
    if (   isset($site_config['pre_dump_script'])
        && !empty($site_config['pre_dump_script']))
    {
        $pre_dump_cmd = $site_config['pre_dump_script'] . ' ' . escapeshellarg($site_config['url']);
        system($pre_dump_cmd, $pre_dump_ret);
    }
    if ($pre_dump_ret !== 0)
    {
        echo "command: {$pre_dump_cmd} exited with status {$pre_dump_ret}\n";
        $all_ok = false;
        unlink($lockfile);
        continue;
    }
    
    /**
     * wget dump
     */
    $wget_cmd = 'cd '. escapeshellarg($site_config['dump_path']) . " && wget {$wget_options}";
    if (isset($site_config['wget_extra_options']))
    {
        $wget_cmd = "{$wget_cmd} " . trim($site_config['wget_extra_options']);
    }
    if (   isset($site_config['username'])
        && isset($site_config['password']))
    {
        $wget_cmd = "{$wget_cmd} --post-data 'username=" . rawurlencode($site_config['username']) . "&password=" . rawurlencode($site_config['password']) . "&midcom_services_auth_frontend_form_submit=true'";
    }
    $wget_cmd = "{$wget_cmd} " . escapeshellarg($site_config['url']);
    $wget_ret = 0;
    //echo "executing: {$wget_cmd}\n";
    system($wget_cmd, $wget_ret);
    if ($wget_ret !== 0)
    {
        echo "command: {$wget_cmd} exited with status {$wget_ret}\n";
        $all_ok = false;
    }

    /**
     * Rename files with GET parameters in the name
     *
     * So we can actually serve them, see documentation/USAGE 
     * on how to configure mod_rewrite
     *
     * alternatively try if -E helps you
     */
    if (   strpos($wget_cmd, '-E') === false
        && strpos($wget_cmd, '--html-extension') === false)
    {
        $cmd = "find {$site_config['dump_path']} -name '*\?*'";
        $output = array();
        $ret = 0;
        exec($cmd, $output, $ret);
        // TODO: check return status
        if (   $ret === 0
            && !empty($output))
        {
            foreach($output as $filepath)
            {
                list($filepart, $querypart) = explode('?', $filepath);
                $newpath = dirname($filepart) . "/{$querypart}_" . basename($filepart);
                $mv_cmd = "mv -f '{$filepath}' '{$newpath}'";
                $mv_ret = 0;
                //echo "executing: {$mv_cmd}\n";
                system($mv_cmd, $mv_ret);
                if ($mv_ret !== 0)
                {
                    echo "command: {$mv_cmd} exited with status {$mv_ret}\n";
                    $all_ok = false;
                }
            }
        }
    }

    /**
     * protected paths
     */
    if (   isset($site_config['protected_htaccess_file'])
        && isset($site_config['username'])
        && isset($site_config['password']))
    {
        if (isset($site_config['protected_htaccess_suffix']))
        {
            $suffix = $site_config['protected_htaccess_suffix'];
        }
        else
        {
            $suffix = '';
        }
        $client = new org_openpsa_httplib();
        $client->_config->options['http_timeout'] = $http_timeout;
        // Quick and Dirty way to do retries (the result might be empty and still be valid...)
        $retries = 5;
        do
        {
            $result = $client->get("{$site_config['url']}midcom-exec-fi.hut.staticdumps/protected_folders.php", null, $site_config['username'], $site_config['password']);
        }
        while (   empty($result)
               && $retries--);
        if (!empty($result))
        {
            $paths = explode("\n", $result);
            foreach ($paths as $path)
            {
                $path = trim($path);
                if (empty($path))
                {
                    continue;
                }
                if (!is_dir("{$site_config['dump_path']}/{$path}"))
                {
                    // Silently ignore, this path may have been --excluded by wget_extra_options
                    continue;
                }
                $ln_cmd = "ln -f -s {$site_config['protected_htaccess_file']} {$site_config['dump_path']}/{$path}.htaccess{$suffix}";
                $ln_ret = 0;
                //echo "executing: {$ln_cmd}\n";
                system($ln_cmd, $ln_ret);
                if ($ln_ret !== 0)
                {
                    echo "command: {$ln_cmd} exited with status {$ln_ret}\n";
                    $all_ok = false;
                }
            }
        }
    }

    /** 
     * Redirection folders
     */
    if (   array_key_exists('redirect_htaccess_suffix', $site_config) //might be null, still needs to be considered...
        && isset($site_config['username'])
        && isset($site_config['password']))
    {
        if (isset($site_config['redirect_htaccess_suffix']))
        {
            $suffix = $site_config['redirect_htaccess_suffix'];
        }
        else
        {
            $suffix = '';
        }
        $client = new org_openpsa_httplib();
        $client->_config->options['http_timeout'] = $http_timeout;
        // Quick and Dirty way to do retries (the result might be empty and still be valid...)
        $retries = 5;
        do
        {
            $result = $client->get("{$site_config['url']}midcom-exec-fi.hut.staticdumps/redirect_folders.php", null, $site_config['username'], $site_config['password']);
        }
        while (   empty($result)
               && $retries--);
        if (!empty($result))
        {
            require_once('HTTP/Request.php');
            $paths = explode("\n", $result);
            foreach ($paths as $path)
            {
                $path = trim($path);
                if (empty($path))
                {
                    continue;
                }
                $path_url = "{$site_config['url']}/{$path}";
                $client =& new HTTP_Request($path_url);
                $client->setMethod(HTTP_REQUEST_METHOD_HEAD);
                $client->addHeader('User-Agent', org_openpsa_httplib::_user_agent());
                $client->setBasicAuth($site_config['username'], $site_config['password']);
                $response = $client->sendRequest();
                if (PEAR::isError($response))
                {
                    continue;
                }
                $headers = $client->getResponseHeader();
                if (   !isset($headers['location'])
                    || empty($headers['location']))
                {
                    // Could not get valid redirection info
                    continue;
                }
                // Clean double-slashes from the location (except for proto:// -part)
                $headers['location'] = trim(preg_replace('%(?<!:)/{2,}%', '/', $headers['location']));
                // Remove $site_config['url'] from beginning of location ??
                $regex = '%^' . str_replace('.', '\.', $site_config['url']) . '%';
                $redirect_to = preg_replace($regex, '/', $headers['location']);
                $redirect_to = str_replace($site_config['url'], '/', $headers['location']);
                $file_content = <<<EOD
RewriteEngine On
#This would work in global config file
#RewriteRule ^/{$path}$ {$redirect_to} [R]
#We use this in directory local one
RewriteRule ^$ {$redirect_to} [R]
EOD;
                $file_path = "{$site_config['dump_path']}/{$path}.htaccess{$suffix}";
                if (!file_exists(dirname($file_path)))
                {
                    $mkdir_cmd = 'mkdir -p ' . dirname($file_path);
                    $mkdir_ret = 0;
                    //echo "executing: {$mkdir_cmd}\n";
                    system($mkdir_cmd, $mkdir_ret);
                    if ($mkdir_ret !== 0)
                    {
                        echo "command: {$mkdir_cmd} exited with status {$mkdir_ret}\n";
                        $all_ok = false;
                    }
                }
                //echo "Writing {$file_path}\n";
                if (!file_put_contents($file_path, $file_content))
                {
                    echo "Failed to write file {$file_path}\n";
                    $all_ok = false;
                }
                unset($file_path, $file_content);
            }
        }
    }

    /**
     * Documentroot sync
     */
    if (isset($site_config['documentroot']))
    {
        if (isset($site_config['rsync_extra_options']))
        {
            $rsync_cmd = "rsync {$rsync_options} " . trim($site_config['rsync_extra_options']) . " {$site_config['documentroot']}/ {$site_config['dump_path']}/";
        }
        else
        {
            $rsync_cmd = "rsync {$rsync_options} {$site_config['documentroot']}/* {$site_config['dump_path']}/";
        }
        $rsync_ret = 0;
        //echo "executing: {$rsync_cmd}\n";
        system($rsync_cmd, $rsync_ret);
        if ($rsync_ret !== 0)
        {
            echo "command: {$rsync_cmd} exited with status {$rsync_ret}\n";
            $all_ok = false;
        }
    }

    /**
     * Run post-dump script
     */
    $post_dump_ret = 0;
    if (   isset($site_config['post_dump_script'])
        && !empty($site_config['post_dump_script']))
    {
        $all_ok_int = (int)$all_ok;
        $post_dump_cmd = $site_config['post_dump_script'] . ' ' . escapeshellarg($site_config['url']) . " {$all_ok_int} {$wget_ret} {$rsync_ret}";
        system($post_dump_cmd, $post_dump_ret);
    }
    if ($post_dump_ret !== 0)
    {
        echo "command: {$post_dump_cmd} exited with status {$post_dump_ret}\n";
        $all_ok = false;
    }
    unlink($lockfile);
}

if (!$all_ok)
{
    better_die('Some operation failed, see above');
}

?>