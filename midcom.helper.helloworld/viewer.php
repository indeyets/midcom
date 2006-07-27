<?php

/**
 * @package midcom.helper.helloworld
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Hello World site interface class.
 * 
 * Use this as an example to write new components.
 * 
 * ...
 * 
 * @package midcom.helper.helloworld
 */
class midcom_helper_helloworld_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch.
     */
    function midcom_helper_helloworld_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->_request_switch[] = Array 
        ( 
            /* These two are the default values anyway, so we can skip them. */
            // 'fixed_arguments' => null,
            // 'variable_arguments' => 0,
            'handler' => 'welcome'
        );
        
    }
    
    
    /**
     * Welcome page handler, does nothing, as we only have to display a hello world
     * style element in _show_welcome.
     * 
     * It is executed during the code-init phase of the Midgard request, so stuff like
     * HTTP redirects are still possible here.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed $data The local request data. 
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        return true;
    }
    
    /**
     * Search form show handler, displays the search form, including
     * some hints about how to write queries. 
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data. 
     */
    function _show_welcome($handler_id, &$data)
    {
        midcom_show_style('welcome');
    }
    
}

?>