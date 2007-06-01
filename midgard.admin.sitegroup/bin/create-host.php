<?php
/**
 * Created on Mar 4, 2006
 * @author tarjei huse
 * @package midgard.admin.sitegroup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
ini_set('include_path','..:'.ini_get('include_path'));

error_reporting(E_ALL);

require_once('Console/Getargs.php');
require_once(dirname(__FILE__) . '/../creation/base.php');
require_once(dirname(__FILE__) . '/../creation/host.php');

$file =dirname($_SERVER['SCRIPT_NAME']) . '/../../../../midcom/helper/hostconfig.php';

if ( !file_exists( $file ) ) 
{
    $file = dirname($_SERVER['SCRIPT_NAME']) . '/../../../../midcom/helper/hostconfig.php';

    if ( !file_exists( $file ) ) 
    {
        $file = dirname( __FILE__ ) . "/../../midcom.core/midcom/helper/hostconfig.php";
        if ( !file_exists( $file ) ) 
        {
            echo ( "Cannot find hostconfig.php file\n" );
            return -1;
        }
    }
}
require_once( $file );

require_once(dirname(__FILE__) . '/../creation/config/config.php');

$default_hostname = @system('hostname -f');
if (empty($default_hostname))
{
    $default_hostname = 'localhost';
}

$opts_config = array(); 
$opts_config['configuration'] = array
(
    'short' => 'c',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Name of the midgard configuration file.',
    'default' => 'midgard',
);
$opts_config['user'] = array
(
    'short' => 'u',
    'max'   => 1,
    'min'   => 1,
    'desc'  => 'Username to log in with',
);
$opts_config['password'] = array
(
    'short' => 'p',
    'max'   => 1,
    'min'   => 1,
    'desc'  => 'Password to log in with',   
);
$opts_config['verbose'] = array
(
    'short' => 'v',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Be verbose',
    'default' => true,
);
$opts_config['sitegroup_id'] = array
(
    'short' => 's',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'The id of the sitegroup the host should be in',
    'default' => 0,
);
$opts_config['hostname'] = array
(
    'short' => 'host',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Hostname',
    'default' => $default_hostname,
);
$opts_config['host_prefix'] = array
(
    'short' => 'pr',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Host prefix',
    'default' => '/',
);            
$opts_config['topic_midcom'] = array
(
    'short' => 'm',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'The midcom to set the root page to',
    'default' => 'net.nehmer.static',
);
$opts_config['topic_name'] = array
(
    'short' => 'tn',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Name used for the website',
    'default' => 'Midgardian',
);
$opts_config['midcom_path'] = array
(
    'short' => 'mp',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'The path to the midcom directory',
    'default' => 'midcom/lib',
);
$opts_config['extend_style'] = array
(
    'short' => 'st',
    'max'   => 1,
    'min'   => 0,
    'desc'  => 'Style template to use for the site',
    'default' => 'none',
);

/*  */                
$args = Console_Getargs::factory($opts_config);
$header = "Usage: " .basename(__FILE__)." [options]\n\n" ;
if (PEAR::isError($args)) 
{
    if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER) 
    {
        echo Console_Getargs::getHelp($opts_config, $header /*, $args->getMessage()*/)."\n";
    }
    else if ($args->getCode() === CONSOLE_GETARGS_HELP) 
    {
        
        echo Console_Getargs::getHelp($opts_config, $header)."\n";
    }

    exit;
}

$config = new midgard_admin_sitegroup_creation_config_host();
$vals = get_object_vars($config);
//print_r($vals);
foreach ($opts_config as $key => $value ) 
{
    if (array_key_exists($key, $vals))
    {
        if ($args->isDefined($key)) 
        {
            echo "Setting $key to " . $args->getValue($key) . "\n";
            $config->$key = $args->getValue($key);
        } 
        elseif ( array_key_exists('default', $value) ) 
        {
            echo "Setting $key to default value " . $value['default'] . "\n";
            $config->$key = $value['default'];
        }
    } 
    if (!array_key_exists('default', $value) && !$args->isDefined($key)) 
    {
        echo Console_Getargs::getHelp($opts_config, $header/*, $args->getMessage()*/)."\n";
        echo "You are missing   the $key (-{$value['short']}) argument. \n";
        exit;
    }
}

$configfile = $args->isDefined('configuration') ? $args->getValue('configuration') : 'midgard';
echo "Starting midgard with config file: " . $configfile. "\n";
mgd_config_init($configfile);

$config->set_password($args->getValue('password'));
$config->set_username($args->getValue('user'));

if (! mgd_auth_midgard($args->getValue('user'), $args->getValue('password'))) 
{
    echo "Could not log in. Exiting \n";
    exit;
}



$runner = midgard_admin_sitegroup_creation_host::factory($config);
if (!$runner->verbose)  
{
    echo "Running in non verbose mode\n";
}

if (   $runner->validate() 
    && $runner->run()) 
{
    echo "Host created with id : " . $runner->host->id;    
} 
else 
{
    echo "\nCreation failed, error ".mgd_errstr().", see above messages\n";
}
return;
