<?php
/**
 * @package ${component}
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller
 *
 * @package ${component}
 */
class ${component}_controllers_index
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_index($route_id, &$data, $args)
    {        
        $data['name'] = "${component}";
    }
}
?>