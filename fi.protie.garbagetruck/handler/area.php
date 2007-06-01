<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Area related handlers
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_area extends midcom_baseclasses_components_handler
{
    /**
     * Object containing the selected area
     * 
     * @access private
     * @var fi_protie_garbagetruck_area_db
     */
    var $_area = null;
    
    /**
     * Object containing the selected route
     * 
     * @access private
     * @var fi_protie_garbagetruck_route_db
     */
    var $_route = null;
    
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
     * DM2 schema instance for areas
     * 
     * @access private
     */
    var $_schemadb = null;
    
    /**
     * DM2 schema instance for routes
     * 
     * @access private
     */
    var $_schemadb_route = null;
    
    /**
     * Show the style depending on the action requested
     * 
     * @access private
     * @var string
     */
    var $_show_style = '';
    
    /**
     * Simple constructor, which calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_handler_area()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the schemadbs connected to areas
     * 
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb_area'];
        $this->_schemadb_route =& $this->_request_data['schemadb_route'];
    }
    
    /**
     * Simple collector, which creates the reference between the original instances and
     * request data for outputting the information.
     * 
     * @access private
     */
    function _populate_request_data()
    {
        $this->_request_data['area'] =& $this->_area;
        $this->_request_data['route'] =& $this->_route;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Check for area dependencies
     * 
     * @access private
     */
    function _dependencies()
    {
        $qb = fi_protie_garbagetruck_route_dba::new_query_builder();
        $qb->add_constraint('area', '=', $this->_area->id);
        
        if ($qb->count() === 0)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Handler for area listing
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
                    MIDCOM_TOOLBAR_URL => 'area/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create area'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                )
            );
        }
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'log/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create a log entry'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                )
            );
        }
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'vehicle/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create vehicle'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
                )
            );
        }
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'person/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_AREA;
        
        return true;
    }
    
    /**
     * Show area listing.
     * 
     * @access private
     */
    function _show_list($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('areas');
        $this->_request_data['page_type'] = 'area';
        
        $qb = fi_protie_garbagetruck_area_dba::new_query_builder();
        $qb->add_order('name');
        $results =@ $qb->execute();
        
        midcom_show_style('area_list_header');
        
        foreach ($results as $area)
        {
            $this->_area =& $area;
            $this->_load_datamanager();
            $this->_populate_request_data();
            
            // Get the list of routes for the requested area
            $qb_routes = fi_protie_garbagetruck_route_dba::new_query_builder();
            $qb_routes->add_constraint('area', '=', $this->_area->id);
            
            $this->_request_data['routes'] = @$qb_routes->execute_unchecked();
        
            midcom_show_style('area_list_item');
        }
        
        midcom_show_style('area_list_footer');
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
            || !$this->_datamanager->autoset_storage($this->_area))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for team {$this->_area->name}");
            // This will exit
        }
    }
    
    /**
     * Load the datamanager for the requested route object.
     * 
     * @access private
     */
    function _load_route_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb_route);
        
        if (   !$this->_datamanager
            || !$this->_datamanager->autoset_storage($this->_route))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for route {$this->_route->name}");
            // This will exit
        }
    }
    
    /**
     * Loads the DM2 creation controller for the requested area
     * 
     * @access private
     */
    function _load_create_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'area';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * Loads the DM2 editing controller for the requested area
     * 
     * @access private
     */
    function _load_edit_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'area';
        $this->_controller->set_storage($this->_area);
        
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
        if (!$this->_area->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_area);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new area, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        return $this->_area;
    }
    
    /**
     * Load the DM2 instance for creation of a new area object
     * 
     * @access private
     * @return bool Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Initialize an instance of fi_protie_garbagetruck_area_db for DM2 controller
        $this->_area = new fi_protie_garbagetruck_area_dba();
        
        $this->_load_create_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("area/{$this->_area->guid}/");
                break;
            
            case 'cancel':
                $_MIDCOM->relocate('');
                break;
        }
        
        $this->_topic->require_do('midgard:create');
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_AREA;
        
        return true;
    }
    
    /**
     * Show creation form
     * 
     * @access private
     */
    function _show_create($handler_id, &$data)
    {
        $this->_area = new fi_protie_garbagetruck_area_dba();
        
        $this->_request_data['page_title'] = $this->_l10n->get('create a new area');
        $this->_populate_request_data();
        
        midcom_show_style('create_form');
    }
    
    /**
     * Loads the area and its datamanager
     * 
     * @access private
     * @param String GUID of the requested area
     */
    function _get_area($guid)
    {
        if (!mgd_is_guid($guid))
        {
            return false;
        }
        
        $this->_area = new fi_protie_garbagetruck_area_dba();
        if (!$this->_area->get_by_guid($guid))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the integrity of the requested area.
     * 
     * Matches /area/<area guid>/
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, &$data)
    {
        if (!$this->_get_area($args[0]))
        {
            return false;
        }
        
        if ($this->_topic->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "area/{$this->_area->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit area'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "route/create/{$this->_area->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('create a route for area %s'), $this->_area->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
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
                    MIDCOM_TOOLBAR_URL => "delete/area/{$this->_area->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete area %s'), $this->_area->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_AREA;
        
        return true;
    }
    
    /**
     * Show the area details
     * 
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $this->_load_datamanager();
        $this->_populate_request_data();
        
        midcom_show_style('show_area');
        
        $qb = fi_protie_garbagetruck_route_dba::new_query_builder();
        $qb->add_constraint('area', '=', $this->_area->id);
        
        $qb->add_order('name');
        
        if ($qb->count() === 0)
        {
            return;
        }
        
        $results =@ $qb->execute_unchecked();
        
        $this->_request_data['page_title'] = sprintf($this->_l10n->get('routes of area %s'), $this->_area->name);
        $this->_request_data['page_type'] = 'arealist';
        
        midcom_show_style('route_list_header');
        foreach ($results as $route)
        {
            $this->_route =& $route;
            $this->_load_route_datamanager();
            $this->_populate_request_data();
            
            // Get the list of routes for the requested area
            $qb_routes = fi_protie_garbagetruck_route_dba::new_query_builder();
            $qb_routes->add_constraint('area', '=', $this->_area->id);
            
            $this->_request_data['routes'] = @$qb_routes->execute_unchecked();
        
            midcom_show_style('route_list_item');
        }
        
        midcom_show_style('route_list_footer');
    }
    
    /**
     * A virtual handler for editing, which is called by method _handler_action.
     * 
     * @access private
     */
    function _action_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        
        $this->_load_edit_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("area/{$this->_area->guid}/");
                // This will exit
                break;
                
            case 'cancel':
                $_MIDCOM->relocate("area/{$this->_area->guid}/");
                // This will exit
                break;
                
        }
        
        if (   $this->_topic->can_do('midgard:delete')
            && !$this->_dependencies())
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/area/{$this->_area->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete area %s'), $this->_area->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "area/{$this->_area->guid}",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('back to area %s'), $this->_area->name),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );
        
        return true;
    }
    
    /**
     * Handler for actions for the requested area. Loads DM2 instances.
     * 
     * @access private
     */
    function _handler_action($handler_id, $args, &$data)
    {
        if (!$this->_get_area($args[0]))
        {
            return false;
        }
        
        switch ($args[1])
        {
            case 'edit':
                $this->_request_data['page_title'] = sprintf($this->_l10n->get('edit area %s'), $this->_area->name);
                $this->_show_style = 'edit_form';
                return $this->_action_edit($handler_id, $args, &$data);
                break;
            
            case 'delete':
                $_MIDCOM->relocate("delete/area/{$this->_area->guid}/");
                // This will exit
            
            default:
                return false;
                break;
        }
    }
    
    /**
     * Show the editing form for the area
     * 
     * @access private
     */
    function _show_action($handler_id, &$data)
    {
        $this->_request_data['page_type'] = 'area';
        
        $this->_populate_request_data();
        
        midcom_show_style($this->_show_style);
    }
}
?>