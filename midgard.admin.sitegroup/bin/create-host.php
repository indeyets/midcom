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
/*
 * this isn't perfect, but a start, I would like to be able to have
 * MIDCOM_ROOT set somewhere else. 
 */

if (strstr(dirname(__FILE__), 'src/midgard.admin.sitegroup'))
{
    // This is a bootstrapped SVN checkout
    define ('MIDCOM_ROOT', dirname(__FILE__). "/../../midcom.core");
}
else
{
    define ('MIDCOM_ROOT' , realpath ("../../../../"));
}

require_once 'Console/Getargs.php';
require_once dirname(__FILE__) .'/../creation/base.php';
require_once dirname(__FILE__) .'/../creation/host.php';
require_once dirname(__FILE__) .'/../hostconfig.php';
require_once dirname(__FILE__) .'/../creation/config/config.php';
require_once MIDCOM_ROOT . '/constants.php';
require_once MIDCOM_ROOT . '/midcom/debug.php';
require_once dirname(__FILE__) .'/../debug.php';



$default_hostname = @system('hostname -f');
if (empty($default_hostname))
{
    $default_hostname = 'localhost';
}

error_reporting(E_ALL);

    $opts_config =array(); 
    $opts_config['configuration'] = array (
                'short' => 'c',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Name of the midgard configuration file.',
                'default' => 'midgard',
            );
    $opts_config['user'] = array (
                'short' => 'u',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Username to log in with',
            );
    $opts_config['password'] = array (
                'short' => 'p',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'password to log in with',
                
            );
    $opts_config['verbose'] = array (
                'short' => 'v',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Be verbose',
                'default' => true,
                
            );
    $opts_config['sitegroup_id'] = array (
                'short' => 's',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'The id of the sitegroup the host should be in',
                'default' => 0,
                );
    $opts_config['hostname'] = array (
                'short' => 'host',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Hostname',
                'default' => $default_hostname,
                );
    $opts_config['style_name'] = array (
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Name of the style that will be created',
                'default' => -1,
                );
    $opts_config['host_prefix'] = array (
                'short' => 'pr',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Host prefix',
                'default' => '/',
                );
                
    $opts_config['topic_midcom'] = array (
                'short' => 'm',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'The midcom to set the root page to',
                'default' => 'midcom.admin.aegir',
                );
    $opts_config['midcom_path'] = array (
                'short' => 'mp',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'The path to the midcom directory',
                'default' => 'midcom/lib',
                );
    $opts_config['extend_style'] = array (
                'short' => 'st',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Style template to use for the site',
                'default' => 'none',
                );

    /*  */                
    $args = Console_Getargs::factory($opts_config);
    $header = "Usage: " .basename($_SERVER['SCRIPT_NAME'])." [options]\n\n" ;
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
                if ( is_array( $args->getValue($key) )) {
                     $config->$key = array_pop($args->getValue($key));
                } else {
                    $config->$key = $args->getValue($key);
                }
            } 
            elseif ( array_key_exists('default', $value) ) 
            {
                echo "Setting $key to default value " . $value['default'] . "\n";
                $config->$key = $value['default'];
                if ($key == 'style_name' ) {
                    $config->$key = $config->get_value('host_name') .$config->get_value('host_prefix') . " style"; 
                }
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
    debug_add( "Starting midgard with config file: " . $configfile. "\n");
    mgd_config_init($configfile);
    
    $config->set_password($args->getValue('password'));
    $config->set_username($args->getValue('user'));
    
    if (! mgd_auth_midgard($args->getValue('user'),$args->getValue('password')) ) 
    {
        echo "Could not log in. Exiting \n";
        exit;
    }

    
    
    $runner = midgard_admin_sitegroup_creation_host::factory($config);
    if (!$runner->verbose)  {
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
