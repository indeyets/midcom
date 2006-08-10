<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: edit.php,v 1.6 2006/07/17 14:57:13 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * salesproject edit/view handler
 * 
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_edit extends midcom_baseclasses_components_handler
{
    var $_datamanagers;
    var $_schemadb_deliverable;
    
    /**
     * Array of Datamanager 2 controllers for deliverable display and management
     *
     * @var array
     * @access private
     */
    var $_controllers = array();

    function org_openpsa_sales_handler_edit()
    {
        parent::midcom_baseclasses_components_handler();
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            debug_pop();
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit. 	 
        }
        debug_pop();
    }

    
    function _load_salesproject($identifier)
    {
        if (!isset($this->_datamanagers['salesproject']))
        {
            $this->_initialize_datamanager('salesproject', $this->_config->get('schemadb_salesproject'));
        }
        $salesproject = new org_openpsa_sales_salesproject($identifier);
        
        if (!is_object($salesproject))
        {
            return false;
        }
        
        //Fill the customer field to DM
        debug_add("schema before \n===\n" . sprint_r($this->_datamanagers['salesproject']->_layoutdb['default']) . "===\n");
        org_openpsa_helpers_schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget', 'select', 'default', false);
        //Indidentally this helper works for salesprojects as well (same property names and logic)
        org_openpsa_helpers_schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget_select_choices', org_openpsa_helpers_task_groups($salesproject), 'default', false);
        debug_add("schema after \n===\n" . sprint_r($this->_datamanagers['salesproject']->_layoutdb['default']) . "===\n");
        
        // Load the project to datamanager
        if (!$this->_datamanagers['salesproject']->init($salesproject))
        {
            return false;
        }
        
        return $salesproject;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );
        $salesproject = new org_openpsa_sales_salesproject();        
        $stat = $salesproject->create();
        if ($stat)
        {
            $this->_request_data['salesproject'] = new org_openpsa_sales_salesproject($salesproject->id);
            //Debugging
            $result["storage"] =& $this->_request_data['salesproject'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

/*
    function _handler_xxx($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_xxx($handler_id, &$data)
    {
    }
*/
    
    function _handler_view_salesproject($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['salesproject'] = $this->_load_salesproject($args[0]);
        if (!$this->_request_data['salesproject'])
        {
            return false;
        }
        
        $_MIDCOM->set_pagetitle($this->_request_data['salesproject']->title);

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "salesproject/edit/{$this->_request_data['salesproject']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['salesproject']),
            )
        );
        
        $this->_view_toolbar->bind_to($this->_request_data['salesproject']);
        
        // List deliverables
        $this->_schemadb_deliverable = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_deliverable'));
        $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
        $deliverable_qb->add_constraint('salesproject', '=', $this->_request_data['salesproject']->id);
        $deliverable_qb->add_constraint('up', '=', 0);
        $deliverable_qb->add_order('created', 'DESC');
        $deliverables = $deliverable_qb->execute();
        foreach ($deliverables as $deliverable)
        {
            $this->_controllers[$deliverable->id] =& midcom_helper_datamanager2_controller::create('ajax');
            // TODO: Modify schema's "price per unit" to readonly if the product has components
            $this->_controllers[$deliverable->id]->schemadb =& $this->_schemadb_deliverable;
            $this->_controllers[$deliverable->id]->set_storage($deliverable);
            $this->_controllers[$deliverable->id]->process_ajax();
            $this->_request_data['deliverables'][$deliverable->guid] = $this->_controllers[$deliverable->id]->get_content_html();
            $this->_request_data['deliverables_objects'][$deliverable->guid] = $deliverable;
        }

        $relatedto_button_settings = org_openpsa_relatedto_handler::common_toolbar_buttons_defaults();
        $relatedto_button_settings['wikinote']['wikiword'] = sprintf($this->_request_data['l10n']->get($this->_config->get('new_wikinote_wikiword_format')), $this->_request_data['salesproject']->title, date('Y-m-d H:i'));
        //TODO: make wiki node configurable
        //TODO: make documents node configurable
        org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_node_toolbar, $this->_request_data['salesproject'], $this->_component, $relatedto_button_settings);

        return true;
    }

    function _show_view_salesproject($handler_id, &$data)
    {
        $this->_request_data['salesproject_dm']  = $this->_datamanagers['salesproject'];
        midcom_show_style('show-salesproject');
    }

    function _handler_edit_salesproject($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['salesproject'] = $this->_load_salesproject($args[0]);
        $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['salesproject']);
    
        switch ($this->_datamanagers['salesproject']->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                return true;
                // This will break;
                
            case MIDCOM_DATAMGR_SAVED:                
            case MIDCOM_DATAMGR_CANCELLED:
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "salesproject/" . $this->_request_data['salesproject']->guid);
                // This will exit()
        
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
                // This will break;
        }

        $this->_view_toolbar->bind_to($this->_request_data['salesproject']);

        return true;
    }

    function _show_edit_salesproject($handler_id, &$data)
    {
        $this->_request_data['salesproject_dm']  = $this->_datamanagers['salesproject'];
        midcom_show_style('show-salesproject-edit');
    }

    function _handler_new_salesproject($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_sales_salesproject');

        if (!isset($this->_datamanagers['salesproject']))
        {
            $this->_initialize_datamanager('salesproject', $this->_config->get('schemadb_salesproject'));
        }

        if (!$this->_datamanagers['salesproject']->init_creation_mode('newsalesproject',$this,'_creation_dm_callback'))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanger in creation mode for schema 'newsalesproject'.");
            // This will exit   
        }

        switch ($this->_datamanagers['salesproject']->process_form())
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                break;
            
            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['salesproject']->parameter('midcom.helper.datamanager','layout','default');
                // TODO: index
                                
                // Relocate to main view
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);          
                $GLOBALS['midcom']->relocate($prefix."salesproject/edit/".$this->_request_data['salesproject']->guid.".html");
                break;
            
            case MIDCOM_DATAMGR_SAVED:                    
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['salesproject']->parameter('midcom.helper.datamanager','layout','default');
                // TODO: index
                                
                // Relocate to main view
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);          
                $GLOBALS['midcom']->relocate($prefix."salesproject/edit/".$this->_request_data['salesproject']->guid.".html");
                break;
            
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $GLOBALS['midcom']->relocate('');
                // This will exit
            
            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
            
        }
        
        return true;
    }

    function _show_new_salesproject($handler_id, &$data)
    {
        $this->_request_data['salesproject_dm']  = $this->_datamanagers['salesproject'];
        midcom_show_style('show-salesproject-new');
    }

}

?>
