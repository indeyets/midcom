<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class midcom_helper_imagepopup_viewer  extends midcom_baseclasses_components_request {

    function midcom_helper_imagepopup_viewer() 
    {
        parent::midcom_baseclasses_components_request($topic, $config);
               
        // Match /<topic guid>/<object guid>/<schema> 
        $this->_request_switch['list_object'] = Array (
            'handler' => Array('midcom_helper_imagepopup_handler_list', 'list'),
            //'fixed_args' => Array(),
            'variable_args' => 3,
        );

        // Match /folder/<topic guid>/<object guid>/<schema>
        $this->_request_switch['list_topic'] = Array (
            'handler' => Array('midcom_helper_imagepopup_handler_list', 'list'),
            'fixed_args' => Array('folder'),
            'variable_args' => 3,            
        );        
    }  

}
?>