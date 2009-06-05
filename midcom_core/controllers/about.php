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
    
    public function action_about($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();

        $data['versions'] = array
        (
            'midcom'  => $_MIDCOM->componentloader->manifests['midcom_core']['version'],
            'midgard' => mgd_version(),
            'php'     => phpversion(),
        );
        
        $data['components'] = array();
        foreach ($_MIDCOM->componentloader->manifests as $component => $manifest)
        {
            if ($component == 'midcom_core')
            {
                continue;
            }
            
            $data['components'][$component] = array
            (
                'name'    => $manifest['component'],
                'version' => $manifest['version'],
            );
        }
        
        $data['authors'] = $_MIDCOM->componentloader->authors;
        ksort($data['authors']);
    }

    public function action_database($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_admin();

        if (isset($_POST['update']))
        {
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
                    if (!midgard_config::update_class_table($type))
                    {
                        throw new Exception('Could not update ' . $type . ' tables in test database');
                    }
                    continue;
                }
    
                if (!midgard_config::create_class_table($type))
                {
                    throw new Exception('Could not create ' . $type . ' tables in test database');
                }
            }
        }
        $data['installed_types'] = array();
        foreach ($_MIDGARD['schema']['types'] as $classname => $null)
        {
            $data['installed_types'][] = $classname;
        }
    }
}
?>