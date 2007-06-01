<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Vehicle related handlers
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_vehicle extends midcom_baseclasses_components_handler
{
    /**
     * Object containing the selected vehicle
     * 
     * @access private
     * @var fi_protie_garbagetruck_vehicle_db
     */
    var $_vehicle = null;
    
    /**
     * DM2 instance
     * 
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * DM2 controller instance
     * 
     * @access private
     */
    var $_controller = null;
    
    
    /**
     * Simple constructor, which calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_handler_vehicle()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the schemadbs connected to vehicles
     * 
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb_vehicle'];
    }
    
    /**
     * Check for area dependencies
     * 
     * @access private
     */
    function _dependencies()
    {
        $qb = fi_protie_garbagetruck_log_dba::new_query_builder();
        $qb->add_constraint('vehicle', '=', $this->_vehicle->id);
        
        if ($qb->count() === 0)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Simple collector, which creates the reference between the original instances and
     * request data for outputting the information.
     * 
     * @access private
     */
    function _populate_request_data()
    {
        $this->_request_data['vehicle'] =& $this->_vehicle;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Handler for vehicle listing
     *
     * @access private
     */
    function _handler_list($handler_id, $args, &$data)
    {
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'vehicle/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create vehicle'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE;
        
        return true;
    }
    
    /**
     * Show vehicle listing.
     * 
     * @access private
     */
    function _show_list($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('vehicles');
        $this->_request_data['page_type'] = 'vehicle';
        
        $qb = fi_protie_garbagetruck_vehicle_dba::new_query_builder();
        $qb->add_order('name');
        $results =@ $qb->execute();
        
        midcom_show_style('vehicle_list_header');
        
        foreach ($results as $vehicle)
        {
            $this->_vehicle =& $vehicle;
            $this->_load_datamanager();
            $this->_populate_request_data();
            midcom_show_style('vehicle_list_item');
        }
        
        midcom_show_style('vehicle_list_footer');
    }
    
    /**
     * Load the datamanager for the requested object.
     * 
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        
        if (   !$this->_datamanager
            || !$this->_datamanager->autoset_storage($this->_vehicle))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for team {$this->_vehicle->name}");
            // This will exit
        }
    }
    
    /**
     * Loads the DM2 creation controller for the requested vehicle
     * 
     * @access private
     */
    function _load_create_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'vehicle';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * Loads the DM2 editing controller for the requested vehicle
     * 
     * @access private
     */
    function _load_edit_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'vehicle';
        $this->_controller->set_storage($this->_vehicle);
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }
    
    /**
     * DM2 creation callback
     */
    function &dm2_create_callback(&$controller)
    {
        if (!$this->_vehicle->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_vehicle);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new vehicle, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        return $this->_vehicle;
    }
    
    /**
     * Load the DM2 instance for creation of a new vehicle object
     * 
     * @access private
     * @return bool Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Initialize an instance of fi_protie_garbagetruck_vehicle_db for DM2 controller
        $this->_vehicle = new fi_protie_garbagetruck_vehicle_dba();
        
        $this->_load_create_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("vehicle/{$this->_vehicle->guid}/");
                // This will exit
                break;
            
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit
                break;
        }
        
        $this->_topic->require_do('midgard:create');
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE;
        
        return true;
    }
    
    /**
     * Show creation form
     * 
     * @access private
     */
    function _show_create($handler_id, &$data)
    {
        $this->_vehicle = new fi_protie_garbagetruck_vehicle_dba();
        
        $this->_request_data['page_title'] = $this->_l10n->get('create a new vehicle');
        $this->_populate_request_data();
        
        midcom_show_style('create_form');
    }
    
    /**
     * Loads the vehicle and its datamanager
     * 
     * @access private
     * @param String GUID of the requested vehicle
     */
    function _get_vehicle($guid)
    {
        if (!mgd_is_guid($guid))
        {
            return false;
        }
        
        $this->_vehicle = new fi_protie_garbagetruck_vehicle_dba();
        if (!$this->_vehicle->get_by_guid($guid))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the integrity of the requested vehicle.
     * 
     * Matches /vehicle/<vehicle guid>/
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, &$data)
    {
        if (!$this->_get_vehicle($args[0]))
        {
            return false;
        }
        
        if ($this->_topic->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "vehicle/{$this->_vehicle->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit vehicle'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }
        
        if (   $this->_topic->can_do('midgard:delete')
            && !$this->_dependencies())
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/vehicle/{$this->_vehicle->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete vehicle %s'), $this->_vehicle->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE;
        
        return true;
    }
    
    /**
     * Show the vehicle details
     * 
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $this->_load_datamanager();
        $this->_populate_request_data();
        
        midcom_show_style('show_vehicle');
    }
    
    /**
     * Handler for editing the requested vehicle. Loads DM2 instances.
     * 
     * @access private
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        if (!$this->_get_vehicle($args[0]))
        {
            return false;
        }
        
        $this->_topic->require_do('midgard:update');
        
        $this->_load_edit_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("vehicle/{$this->_vehicle->guid}/");
                // This will exit
                break;
                
            case 'cancel':
                $_MIDCOM->relocate("vehicle/{$this->_vehicle->guid}/");
                // This will exit
                break;
                
        }
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "vehicle/{$this->_vehicle->guid}",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('back to vehicle %s'), $this->_vehicle->name),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );
        
        if (   $this->_topic->can_do('midgard:delete')
            && !$this->_dependencies())
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/vehicle/{$this->_vehicle->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete vehicle %s'), $this->_vehicle->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE;
        
        return true;
    }
    
    /**
     * Show the editing form for the vehicle
     * 
     * @access private
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['page_title'] = sprintf($this->_l10n->get('edit vehicle "%s"'), $this->_vehicle->name);
        $this->_request_data['page_type'] = 'vehicle';
        
        $this->_populate_request_data();
        
        midcom_show_style('edit_form');
    } 
}
?>