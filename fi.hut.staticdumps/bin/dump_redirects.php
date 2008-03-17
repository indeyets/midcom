#!/usr/bin/php
<?php
ini_set('error_reporting', E_ALL);

$wget_options = "-erobots=off -q -m -nH";
$rsync_options = '-a';
$http_timeout = 300; // seconds = 5minutes

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

if (!test_command('rsync --version'))
{
    better_die('rsync not executable in path');
}
if (!test_command('wget --version'))
{
    better_die('wget not executable in path');
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
        // This way only works with the nonmidcom.php initialized client
        $client->_config->options['http_timeout'] = $http_timeout;
        $retries = 5;
        do
        {
            echo "Fetching, {$retries} tries left\n";
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
                    echo "executing: {$mkdir_cmd}\n";
                    system($mkdir_cmd);
                }
                echo "Writing {$file_path}\n";
                file_put_contents($file_path, $file_content);
                unset($file_path, $file_content);
            }
        }
    }

}

?>