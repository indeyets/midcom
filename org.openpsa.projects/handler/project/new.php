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
    /**
     * The Controller of the project used for creating
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new project.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    private function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_project'));
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    private function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * This is what Datamanager calls to actually create a project
     */
    function & dm2_create_callback(&$controller)
    {

        $project = new org_openpsa_projects_project();

        if (! $project->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $project);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a new project, cannot continue. Error: " . mgd_errstr());
            // This will exit.
        }

	$this->_request_data['project'] = new org_openpsa_projects_project($project->id);

        return $project;
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


        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix . "project/" . $this->_request_data['project']->guid."/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get("create project"));

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;
        midcom_show_style("show-project-new");
    }
}
?>