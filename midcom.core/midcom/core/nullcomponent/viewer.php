<?php
/**
 * @package midcom.core.nullcomponent
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package midcom.core.nullcomponent
 */
class midcom_core_nullcomponent_viewer extends midcom_baseclasses_components_request
{
    function midcom_core_nullcomponent_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('midcom_core_nullcomponent_handler_index', 'index'),
        );
    }
}

?>
