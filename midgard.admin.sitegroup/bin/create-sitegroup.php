<?php
/**
 * Created on Mar 4, 2006
 * @author tarjei huse
 * @package midgard.admin.sitegroup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

/**
 * @ignore
 */
ini_set('include_path','..:'.ini_get('include_path'));

require_once 'Console/Getargs.php';
require_once dirname(__FILE__).'/../creation/base.php';
require_once dirname(__FILE__).'/../creation/sitegroup.php';

require_once dirname(__FILE__).'/../creation/config/config.php';

error_reporting(E_ALL);

    $opts_config =array(); 
    $opts_config['configuration'] = array (
                'short' => 'c',
                'max'   => 1,
                'min'   => 1,
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
    $opts_config['sitegroup_name'] = array (
                'short' => 's',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Name of the new sitegroup',
                );
    $opts_config['admingroup_name'] = array (
                'short' => 'agn',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Name of the admin group',
                'default' => 'Administrators',
                );
    $opts_config['admin_name'] = array (
                'short' => 'an',
                'max'   => 1,
                'min'   => 0,
                'desc'  => 'Name of the adminuser',
                'default' => 'admin',
                );
    $opts_config['admin_password'] = array (
                'short' => 'ap',
                'max'   => 1,
                'min'   => 1,
                'desc'  => 'Password for the admin user',
                );

    /*  */                
    $args = Console_Getargs::factory($opts_config);
    if (PEAR::isError($args)) 
    {
        $header = "Usage: " .basename($_SERVER['SCRIPT_NAME'])." [options]\n\n" ;
        if ($args->getCode() === CONSOLE_GETARGS_ERROR_USER) 
        {
            echo Console_Getargs::getHelp($opts_config, $header, $args->getMessage())."\n";
        }
        else if ($args->getCode() === CONSOLE_GETARGS_HELP) 
        {
            echo Console_Getargs::getHelp($opts_config, $header)."\n";
        }
    
        exit;
    }
    
    $config = new midgard_admin_sitegroup_creation_config_sitegroup();
    $vals = get_object_vars($config);
    //print_r($vals);
    foreach ($opts_config as $key => $value ) 
    {
        if (array_key_exists($key, $vals))
        {
            if ($args->isDefined($key)) 
            {
                echo "setting $key to " . $args->getValue($key) . "\n";
                $config->$key = $args->getValue($key);
            } elseif ( array_key_exists('default', $value) ) 
            {
                echo "setting $key to default value " . $value['default'] . "\n";
                $config->$key = $value['default'];
            }
        } 
        if (!array_key_exists('default', $value) && !$args->isDefined($key)) 
        {
            echo "You are missing   the $key (-{$value['short']}) argument. \n";
            exit;
        }
    }
    
    echo "Starting midgard with config file: " . $args->getValue('configuration') . "\n";
    if (version_compare(mgd_version(), '1.9.0alpha', '>='))
    {
        $midgard = new midgard_connection();
        $midgard->open($args->getValue('configuration'));
    }
    else
    {
        mgd_config_init($args->getValue('configuration'));
    }
    
    $config->set_password($args->getValue('password'));
    $config->set_username($args->getValue('user'));
    
    if (! mgd_auth_midgard($args->getValue('user'),$args->getValue('password'),false) ) 
    {
        echo "Could not log in. Exiting \n";
        exit;
    }

    
    
    $runner = new midgard_admin_sitegroup_creation_sitegroup($config);
    if (!$runner->verbose)  {
        echo "Running in non verbose mode\n";
    
    }
    
    $runner->config->admingroup_name = $runner->config->sitegroup_name . " " . $runner->config->admingroup_name; 
    
    if ($runner->validate() && $runner->run()) 
    {
        echo "Sitegroup created with id : " . $runner->get_id();    
    } else {
        echo "Creation failed, se above messages";
        
    }
    return;