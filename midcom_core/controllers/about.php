<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM "About Midgard CMS" controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_about
{
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function get_about($args)
    {
        $_MIDCOM->authorization->require_user();

        $this->data['versions'] = array
        (
            'midcom'  => $_MIDCOM->componentloader->manifests['midcom_core']['version'],
            'midgard' => mgd_version(),
            'php'     => phpversion(),
        );
        
        $this->data['components'] = array();
        foreach ($_MIDCOM->componentloader->manifests as $component => $manifest)
        {
            if ($component == 'midcom_core')
            {
                continue;
            }
            
            $this->data['components'][$component] = array
            (
                'name'    => $manifest['component'],
                'version' => $manifest['version'],
            );
        }
        
        $this->data['authors'] = $_MIDCOM->componentloader->authors;
        ksort($this->data['authors']);
    }

    public function get_database($args)
    {
        $_MIDCOM->authorization->require_admin();

        $this->data['installed_types'] = array();
        foreach ($_MIDGARD['schema']['types'] as $classname => $null)
        {
            $this->data['installed_types'][] = $classname;
        }
    }
    
    public function post_database($args)
    {
        $_MIDCOM->authorization->require_admin();

        if (isset($_POST['update']))
        {
            //Disable limits
            // TODO: Could this be done more safely somehow
            @ini_set('memory_limit', -1);
            @ini_set('max_execution_time', 0);

            midgard_connection::set_loglevel('debug');
            // Generate tables
            if (!midgard_config::create_midgard_tables())
            {
                throw new Exception("Could not create Midgard class tables");
            }
            // And update as necessary
            foreach ($_MIDGARD['schema']['types'] as $type => $val)
            {
                if (substr($type, 0, 2) == '__')
                {
                    continue;
                }
        
                if (midgard_config::class_table_exists($type))
                {
                    $_MIDCOM->log('midcom_core_controllers_about::post_database', "Updating database table for type {$type}", 'debug');
                    if (!midgard_config::update_class_table($type))
                    {
                        throw new Exception('Could not update ' . $type . ' tables in test database');
                    }
                    continue;
                }
                $_MIDCOM->log('midcom_core_controllers_about::post_database', "Creating database table for type {$type}", 'debug');
                if (!midgard_config::create_class_table($type))
                {
                    throw new Exception('Could not create ' . $type . ' tables in test database');
                }
            }
            midgard_connection::set_loglevel($_MIDCOM->configuration->get('log_level'));
        }
        
        $this->get_database($args);
    }
}
?>