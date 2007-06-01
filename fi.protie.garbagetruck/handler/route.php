<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Route related handlers
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_route extends midcom_baseclasses_components_handler
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
     * Simple constructor, which calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_handler_route()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the schemadbs connected to routes
     * 
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb_route'];
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
     * Check for route dependencies
     * 
     * @access private
     */
    function _dependencies()
    {
        $qb = fi_protie_garbagetruck_log_dba::new_query_builder();
        $qb->add_constraint('route', '=', $this->_route->id);
        
        if ($qb->count() === 0)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Loads the requested area by GUID
     * 
     * @access private
     * @param String GUID of the requested area
     */
    function _load_area($guid)
    {
        if (!mgd_is_guid($guid))
        {
            return false;
        }
        
        $this->_area = new fi_protie_garbagetruck_area_dba();
        
        if (!$this->_area->get_by_guid($guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load fi_protie_garbagetruck_area_dba with GUID '.$guid);
            // This will exit
        }
        
        return true;
    }
    
    /**
     * Handler for route listing
     *
     * @access private
     */
    function _handler_list($handler_id, $args, &$data)
    {
        if (array_key_exists(0, $args))
        {
            if (!mgd_is_guid($args[0]))
            {
                return false;
            }
            
            $this->_load_area($args[0]);
        }
        
        if (   $this->_topic->can_do('midgard:create')
            && $this->_area)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'route/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create route'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE;
        
        return true;
    }
    
    /**
     * Show route listing.
     * 
     * @access private
     */
    function _show_list($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('routes');
        $this->_request_data['page_type'] = 'route';
        
        $qb = fi_protie_garbagetruck_route_dba::new_query_builder();
        $qb->add_order('name');
        
        if ($this->_area)
        {
            $qb->add_constraint('area', '=', $this->_area->id);
        }
        
        $results =@ $qb->execute();
        
        midcom_show_style('route_list_header');
        
        foreach ($results as $route)
        {
            $this->_route =& $route;
            $this->_load_datamanager();
            $this->_populate_request_data();
            midcom_show_style('route_list_item');
        }
        
        midcom_show_style('route_list_footer');
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
            || !$this->_datamanager->autoset_storage($this->_route))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for team {$this->_route->name}");
            // This will exit
        }
    }
    
    /**
     * Loads the DM2 creation controller for the requested route
     * 
     * @access private
     */
    function _load_create_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'route';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * Loads the DM2 editing controller for the requested route
     * 
     * @access private
     */
    function _load_edit_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'route';
        $this->_controller->set_storage($this->_route);
        
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
        $this->_route->area = $this->_area->id;
        
        if (!$this->_route->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_route);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new route, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        return $this->_route;
    }
    
    /**
     * Load the DM2 instance for creation of a new route object
     * 
     * @access private
     * @return bool Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Loads the area or returns false on failure
        $this->_load_area($args[0]);
        
        // Initialize an instance of fi_protie_garbagetruck_route_dba for DM2 controller
        $this->_route = new fi_protie_garbagetruck_route_dba();
        
        $this->_topic->require_do('midgard:create');
        $this->_load_create_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("route/{$this->_route->guid}/");
                break;
            
            case 'cancel':
                $_MIDCOM->relocate('');
                break;
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE;
        
        return true;
    }
    
    /**
     * Show creation form
     * 
     * @access private
     */
    function _show_create($handler_id, &$data)
    {
        $this->_route = new fi_protie_garbagetruck_route_dba();
        
        $this->_request_data['page_title'] = sprintf($this->_l10n->get('create a new route for area %s'), $this->_area->name);
        $this->_populate_request_data();
        
        midcom_show_style('create_form');
    }
    
    /**
     * Loads the route and its datamanager
     * 
     * @access private
     * @param String GUID of the requested route
     */
    function _get_route($guid)
    {
        if (!mgd_is_guid($guid))
        {
            return false;
        }
        
        $this->_route = new fi_protie_garbagetruck_route_dba();
        if (!$this->_route->get_by_guid($guid))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the integrity of the requested route.
     * 
     * Matches /route/<route guid>/
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, &$data)
    {
        if (!$this->_get_route($args[0]))
        {
            return false;
        }
        
        $this->_area = new fi_protie_garbagetruck_area_dba($this->_route->area);
        
        if ($this->_topic->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "route/{$this->_route->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit route'),
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
                    MIDCOM_TOOLBAR_URL => "log/create/{$this->_route->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('create a log for route %s'), $this->_route->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
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
                    MIDCOM_TOOLBAR_URL => "delete/route/{$this->_route->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete route %s'), $this->_route->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "area/{$this->_area->guid}/",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('back to area %s'), $this->_area->name),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/back.png',
            )
        );
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE;
        
        return true;
    }
    
    /**
     * Show the route details
     * 
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $this->_load_datamanager();
        $this->_populate_request_data();
        
        midcom_show_style('show_route');
    }
    
    /**
     * Handler for editing the requested route. Loads DM2 instances.
     * 
     * @access private
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        if (!$this->_get_route($args[0]))
        {
            return false;
        }
        
        $this->_topic->require_do('midgard:update');
        
        $this->_load_edit_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("route/{$this->_route->guid}/");
                // This will exit
                break;
                
            case 'cancel':
                $_MIDCOM->relocate("route/{$this->_route->guid}/");
                // This will exit
                break;
                
        }
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "route/{$this->_route->guid}",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('back to route %s'), $this->_route->name),
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
                    MIDCOM_TOOLBAR_URL => "delete/route/{$this->_route->guid}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('delete route %s'), $this->_route->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE;
        
        return true;
    }
    
    /**
     * Show the editing form for the route
     * 
     * @access private
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['page_title'] = sprintf($this->_l10n->get('edit route %s'), $this->_route->name);
        $this->_request_data['page_type'] = 'route';
        
        $this->_populate_request_data();
        
        midcom_show_style('edit_form');
    } 
}
?>