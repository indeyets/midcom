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

}
?>