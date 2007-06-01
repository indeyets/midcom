<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * General delete handler class for any object used in fi.protie.garmagetruck:
 * 
 * - fi_protie_garbagetruck_log
 * - fi_protie_garbagetruck_area
 * - fi_protie_garbagetruck_route
 * - fi_protie_garbagetruck_vehicle
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_delete extends midcom_baseclasses_components_handler
{
    /**
     * DM2 instance for deleting
     * 
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * Object requested for deleting
     * 
     * @access private
     */
    var $_object = null;
    
    /**
     * Simple constructor, which calls for the parent method
     * 
     * @access public
     */
    function fi_protie_garbagetruck_handler_delete()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Indicate the link between internal objects and request data
     * 
     * @access private
     */
    function _populate_request_data()
    {
        $this->_request_data['object'] =& $this->_object;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['page_title'] = $this->_l10n->get('confirm delete');
    }
    
    /**
     * Loads the DM2 instance
     * 
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        
        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for {$this->_request_data['type']} object (GUID: {$this->_object->guid}).");
            // This will exit.
        }
    }
    
    /**
     * Get the schemadb for the requested content object type
     * 
     * @access private
     * @param String describing the requested type
     * @return boolean Indicating success
     */
    function _get_schemadb($type)
    {
        if (!array_key_exists('schemadb_'.$type, $this->_request_data))
        {
            return false;
        }
        
        $this->_schemadb =& $this->_request_data['schemadb_'.$type];
        return true;
    }
    
    /**
     * Checks the object requested for deleting if it has dependencies.
     * 
     * @access private
     * @return bool True if the resource has dependencies
     * @param $type String Describing the requested type
     */
    function _dependencies($type)
    {
        switch ($type)
        {
            // A log will never have dependencies
            case 'log':
                return false;
                break;
            
            case 'area':
                $qb = fi_protie_garbagetruck_route_dba::new_query_builder();
                $qb->add_constraint('area', '=', $this->_object->id);
                break;
            
            case 'route':
            case 'vehicle':
                $qb = fi_protie_garbagetruck_log_dba::new_query_builder();
                $qb->add_constraint($type, '=', $this->_object->id);
                break;
            
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not check the dependencies for an unknown object type!');
        }
        
        if ($qb->count() === 0)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the request for deleting an object
     * 
     * @access private
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:delete');
        $this->_request_data['type'] =$args[0];
        
        // Check the GUID validity
        if (!mgd_is_guid($args[1]))
        {
            return false;
        }
        
        // Check if it's possible to get the schemadb for the requested object type
        if ($this->_get_schemadb($args[0]) === false)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not get the requested schemadb for the type '{$args[0]}'.");
            // This will exit
        }
        
        // Get the object class according to the type requested
        switch ($args[0])
        {
            case 'area':
                $this->_object = new fi_protie_garbagetruck_area_dba();
                break;
            case 'route':
                $this->_object = new fi_protie_garbagetruck_route_dba();
                break;
            case 'log':
                $this->_object = new fi_protie_garbagetruck_log_dba();
                break;
            case 'vehicle':
                $this->_object = new fi_protie_garbagetruck_vehicle_dba();
                break;
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Unknown object type requested for deletion.');
        }
        
        // Check if it is possible to get the object by requested GUID
        if (!$this->_object->get_by_guid($args[1]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not get the object requested for deletion.');
            // This will exit
        }
        
        // Load the datamanager instance
        $this->_load_datamanager();
        
        // Submit clicked
        if (array_key_exists('f_confirm', $_GET))
        {
            if (!$this->_object->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete the {$args[0]} (GUID: {$args[1]}), last Midgard error was: " . mgd_errstr());
                // This will exit.
            }
            
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            
            $_MIDCOM->add_meta_head
            (
                array
                (
                    'http-equiv' => 'refresh',
                    'content'    => '5; url='.$prefix,
                )
            );
        }
        
        // Cancel clicked
        if (array_key_exists('f_cancel', $_GET))
        {
            // Relocate to the original object
            $_MIDCOM->relocate("{$args[0]}/{$args[1]}/");
        }
        
        return true;
    }
    
    /**
     * Show the object before deleting
     * 
     * @access private
     */
    function _show_delete($handler_id, &$data)
    {
        $this->_populate_request_data();
        
        if ($this->_dependencies($this->_request_data['type']))
        {
            $this->_request_data['page_title'] = sprintf($this->_l10n->get('%s %s has dependencies'), $this->_l10n->get($this->_request_data['type']), $this->_object->name);
            midcom_show_style('dependencies');
            return;
        }
        
        if (array_key_exists('f_confirm', $_GET))
        {
            midcom_show_style('delete_ok');
        }
        else
        {
            midcom_show_style('delete_form');
        }
    }
}
?>
