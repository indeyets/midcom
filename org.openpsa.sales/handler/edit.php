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

    function __construct()
    {
        parent::__construct();
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        $_MIDCOM->load_library('midcom.helper.datamanager');

        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb_snippet);

        if (!$this->_datamanagers[$type])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
    }


    function _load_salesproject($identifier)
    {
        if (!isset($this->_datamanagers['salesproject']))
        {
            $this->_initialize_datamanager('salesproject', $this->_config->get('schemadb_salesproject'));
        }
        $salesproject = new org_openpsa_sales_salesproject_dba($identifier);

        if (!is_object($salesproject))
        {
            return false;
        }

        //Fill the customer field to DM
        debug_add("schema before \n===\n" . org_openpsa_helpers::sprint_r($this->_datamanagers['salesproject']->_layoutdb['default']) . "===\n");
        org_openpsa_helpers::schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget', 'select', 'default', false);
        //Indidentally this helper works for salesprojects as well (same property names and logic)
        org_openpsa_helpers::schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget_select_choices', org_openpsa_helpers_list::task_groups($salesproject), 'default', false);
        debug_add("schema after \n===\n" . org_openpsa_helpers::sprint_r($this->_datamanagers['salesproject']->_layoutdb['default']) . "===\n");

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
        $salesproject = new org_openpsa_sales_salesproject_dba();
        $stat = $salesproject->create();
        if ($stat)
        {
            $this->_request_data['salesproject'] = new org_openpsa_sales_salesproject_dba($salesproject->id);
            //Debugging
            $result["storage"] =& $this->_request_data['salesproject'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['salesproject'] = $this->_load_salesproject($args[0]);
        $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['salesproject']);

        switch ($this->_datamanagers['salesproject']->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                // Add toolbar items
                org_openpsa_helpers::dm_savecancel($this->_view_toolbar, $this);
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

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['salesproject_dm']  = $this->_datamanagers['salesproject'];
        midcom_show_style('show-salesproject-edit');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_sales_salesproject_dba');

        if (!isset($this->_datamanagers['salesproject']))
        {
            $this->_initialize_datamanager('salesproject', $this->_config->get('schemadb_salesproject'));
        }

        org_openpsa_helpers::schema_modifier($this->_datamanagers['salesproject'], 'code', 'default', org_openpsa_sales_salesproject_dba::generate_salesproject_number(), 'newsalesproject', false);

        if (!$this->_datamanagers['salesproject']->init_creation_mode('newsalesproject',$this,'_creation_dm_callback'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newsalesproject'.");
            // This will exit
        }

        switch ($this->_datamanagers['salesproject']->process_form())
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers::dm_savecancel($this->_view_toolbar, $this);
                break;

            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['salesproject']->parameter('midcom.helper.datamanager','layout','default');
                // TODO: index

                // Relocate to main view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix."salesproject/edit/" . $this->_request_data['salesproject']->guid . "/");
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['salesproject']->parameter('midcom.helper.datamanager','layout','default');
                // TODO: index

                // Relocate to main view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix."salesproject/edit/" . $this->_request_data['salesproject']->guid . "/");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
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

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['salesproject_dm']  = $this->_datamanagers['salesproject'];
        midcom_show_style('show-salesproject-new');
    }

}

?>