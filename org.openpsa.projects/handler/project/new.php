<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: new.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * New project handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_project_new extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $this->_datamanagers = array
        (
            'project' => new midcom_helper_datamanager($this->_config->get('schemadb_project'))
        );
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $project = new org_openpsa_projects_project();
        $stat = $project->create();
        if ($stat)
        {
            $this->_request_data['project'] = new org_openpsa_projects_project($project->id);
            //Debugging
            $result["storage"] =& $this->_request_data['project'];
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
    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_projects_project');

        if (!$this->_datamanagers['project']->init_creation_mode("newproject",$this,"_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newproject'.");
            // This will exit
        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get("create project"));

        switch ($this->_datamanagers['project']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                break;

            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['project']->parameter("midcom.helper.datamanager","layout","default");
                // TODO: index

                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix."project/".$this->_request_data['project']->guid."/");
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['project']->parameter("midcom.helper.datamanager","layout","default");
                // TODO: index

                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix."project/".$this->_request_data['project']->guid."/");
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

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['project_dm']  = $this->_datamanagers['project'];
        midcom_show_style("show-project-new");
    }
}
?>