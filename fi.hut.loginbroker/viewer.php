<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**$
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
            'handler' => Array('fi_hut_loginbroker_handler_index', 'index'),
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        return true;
    }

    function load_callback_class($classname)
    {
        if (class_exists($classname))
        {
            return true;
        }
        $include_path = 'midcom/lib/' . str_replace('_', '/', $classname) . '.php';
        include_once($include_path);
        if (class_exists($classname))
        {
            // TODO: log PHP error if possible
            return true;
        }
        return false;
    }

}

?>