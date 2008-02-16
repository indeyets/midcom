#!/usr/bin/php
<?php
//Usage
$args = $_SERVER["argv"];

if ($_SERVER["argc"] < 3 || $_SERVER["argc"] >4)
{
    echo "{$args[0]} injects component's manifest file into cache so that cache invalidation
 is not required for new components. Save time on slow machines\n\n";
    echo "Usage: \n\n";
    echo "{$args[0]} <hostname> <component> [mgdconfigfile]\n\n";
    exit;
}

require('midcom/lib/midcom/config/midcom_config.php');
require('midcom/lib/midcom/services/cache.php');
require('midcom/lib/midcom/debug.php');
require('midcom/lib/midcom/services/cache/module/phpscripts.php');
define('MIDCOM_XDEBUG', 0);
define('MIDCOM_ROOT','/usr/share/pear/midcom/lib/');

$values_to_skip = array
(
    'midcom_path'           => true,
    'configuration_version' => true,
    'midcom_helper_datamanager2_save' => true,
);
$template = '$GLOBALS[\'midcom_config\'][\'{__NAME__}\'] = \'{__VALUE__}\';';

$hostname = $_SERVER["argv"][1];
$component = $_SERVER["argv"][2];
$config = ($args[3])?$args[3]:"midgard";

// MGD init
mgd_config_init($config);

// Get host
$qb = new midgard_query_builder('midgard_host');
$qb->add_constraint('name','=',$hostname);
$rst = $qb->execute();
$host = $rst[0];
$page = new midgard_page($host->root);

// Get site configuration
$params = $page->listparameters('midgard');

if ($params)
{
    $configuration_keys_handled = array();
    while ($params->fetch())
    {
            if (   !array_key_exists($params->name, $values_to_skip)
                && !array_key_exists($params->name, $configuration_keys_handled)
                && $params->value != '')
            {
                $txt = $template;
                $txt = str_replace('{__NAME__}', $params->name, $txt);
                $txt = str_replace('{__VALUE__}', $params->value, $txt);
                eval($txt);
            }
            // Safeguard against duplicate values
            $configuration_keys_handled[$params->name] = true;
    }
}

//Initialize cache
$cache = new midcom_services_cache();
$cache->initialize();
$cache_identifier = $cache->phpscripts->create_identifier('midcom.componentloader', 'manifests');

$cache_filename = "{$cache->phpscripts->_cache_dir}{$cache_identifier}.php";
$cache_code = trim(ereg_replace("^<\?php(.*)\?>","\\1",file_get_contents($cache_filename)));
$directory = str_replace(".","/",$component);

$filename = MIDCOM_ROOT."{$directory}/config/manifest.inc";
if (file_exists($filename))
{
    echo "Injecting component {$component} into manifests cache\n\n";

    if (strstr($cache_code,$filename))
    {
        echo "Component {$component} already in manifests cache\n\n";
        exit;
    }

    $manifest_data = file_get_contents($filename);
    $cache_code .= "\n\$_MIDCOM->componentloader->load_manifest(
                    new midcom_core_manifest(    
                    '{$filename}', array({$manifest_data})));\n";
    
    if (! $cache->phpscripts->add($cache_identifier, $cache_code, true))
    {
       echo "Cache injection failed\n\n";
    }
    else
    {
       echo "Cache injection successful\n\n";
    }
}
else
{
    echo "Manifest {$filename} for {$component} doesn't exist\n\n";
}

?>