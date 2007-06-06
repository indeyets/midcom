<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Generic CSV export handler baseclass
 *
 * @package midcom
 */
class midcom_baseclasses_components_handler_dataexport extends midcom_baseclasses_components_handler
{    
    /**
     * The Datamanager of the project to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    var $_objects = array();
    
    function midcom_baseclasses_components_handler_dataexport()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['objects'] =& $this->_objects;
    }
    
    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager($schemadb)
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for schemadb.");
            // This will exit.
        }
    }
    
    function _load_schemadb()
    {
        die("Must be overridden in implementation");
    }

    function _load_data()
    {
        die("Must be overridden in implementation");
    }
    
    function _handler_csv($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
            
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
            
        $this->_load_datamanager($this->_load_schemadb());
        $this->_objects = $this->_load_data($handler_id);
        
        $_MIDCOM->skip_page_style = true;
        
        if (   !isset($args[0])
            || empty($args[0]))
        {
            //We do not have filename in URL, generate one and redirect
            $fname = preg_replace('/[^a-z0-9-]/i', '_', strtolower($this->_topic->extra)) . '_' . date('Y-m-d') . '.csv';
            $_MIDCOM->relocate("{$_MIDGARD['uri']}/{$fname}");
            // This will exit
        }
        
        // TODO: Make configurable
        $_MIDCOM->cache->content->content_type('application/csv');
        
        return true;
    }

    function _show_csv($handler_id, &$data)
    {
        foreach ($this->_objects as $object)
        {
            if (!$this->_datamanager->autoset_storage($object))
            {
                // Object failed to load, skip
                continue;
            }
            
            // TODO: Do the fancy CSV cleaning here
            
            echo implode(',',$this->_datamanager->get_content_csv()) . "\n";
        }
    }
}
?>