<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: new.php,v 1.4 2006/07/06 15:49:49 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * New task handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_new extends midcom_baseclasses_components_handler
{
    /**
     * The Controller of the task used for creating
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
     * The schema to use for the new task.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    /**
     * The defaults to use for the new task.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();


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
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_task_dm2'));
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
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * This is what Datamanager calls to actually create a person
     */
    function & dm2_create_callback(&$controller)
    {
        $task = new org_openpsa_projects_task();

        if (   array_key_exists('project', $this->_request_data)
            && !empty($this->_request_data['project']))
        {
            // Add the task to the project
            $task->up = (int) $this->_request_data['project']->id;

            // Populate some default data from parent as needed
            $task->orgOpenpsaAccesstype = $this->_request_data['project']->orgOpenpsaAccesstype;
            $task->orgOpenpsaOwnerWg = $this->_request_data['project']->orgOpenpsaOwnerWg;

        }

        if (! $task->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $task);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a new task under project #{$this->_request_data['project']->id}, cannot continue. Error: " . mgd_errstr());
            // This will exit.
        }

        $this->_request_data['task'] = new org_openpsa_projects_task($task->id);
        $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_request_data['task'], 'org.openpsa.projects');
        debug_add("org_openpsa_relatedto_handler returned \n===\n" . sprint_r($rel_ret) . "===\n");

        return $task;
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

        if (count($args) > 0)
        {
            // Get the related object
            switch ($args[0])
            {
                case "project":
                    // This task is to be connected to a project
                    $this->_request_data['project'] = new org_openpsa_projects_project($args[1]);
                    if (!$this->_request_data['project'])
                    {
                        return false;
                    }
                    $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['project']);

                    // Copy resources and contacts from project
                    $this->_request_data['project']->get_members();
                    $this->_defaults['resources'] = $this->_request_data['project']->resources;
                    $this->_defaults['contacts'] = $this->_request_data['project']->contacts;

                    break;

                default:
                    return false;
            }
        }
        else
        {
            $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_projects_task');
        }

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

                /* this for some reason dies
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/edit/");
                */
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                // This will exit.

            case 'cancel':
                if (array_key_exists('project', $this->_request_data))
                {
                    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    $_MIDCOM->relocate("{$prefix}project/{$this->_request_data['project']->guid}/");
                }
                else
                {
                    $_MIDCOM->relocate('');
                }
                // This will exit.
        }

        if (array_key_exists('project', $this->_request_data))
        {
            $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('new task to project %s'), $this->_request_data['project']->title));
        }
        else
        {
            $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('new task'));
        }

        $this->_update_breadcrumb_line();

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     */
    private function _update_breadcrumb_line()
    {
        $tmp = Array();

        if (array_key_exists('project', $this->_request_data))
        {

            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "project/" . $this->_request_data['project']->guid . "/",
                MIDCOM_NAV_NAME => $this->_request_data['project']->title,
            );
        }
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_request_data['l10n']->get('new task'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;
        midcom_show_style("show-task-new");
    }
}
?>