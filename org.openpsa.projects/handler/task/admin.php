<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Projects edit/delete task handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_admin extends midcom_baseclasses_components_handler
{
    /**
     * The task to operate on
     *
     * @var org_openpsa_projects_task
     * @access private
     */
    var $_task = null;

    /**
     * The Datamanager of the task to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the task used for editing
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
     * Schema to use for task display
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['task'] =& $this->_task;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "task/edit/{$this->_task->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_task->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "task/delete/{$this->_task->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_task->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        switch ($handler_id)
        {
            case 'task_edit':
                $this->_view_toolbar->disable_item("task/edit/{$this->_task->guid}/");
                break;
            case 'task_delete':
                $this->_view_toolbar->disable_item("task/delete/{$this->_task->guid}/");
                break;
        }
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/midcom.helper.datamanager2/legacy.css",
            )
        );
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_task'));
    }

    /**
     * Internal helper, loads the datamanager for the current task. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_task))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for task {$this->_task->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current task. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_task, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for task {$this->_task->id}.");
            // This will exit.
        }
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = $breadcrumb = org_openpsa_projects_viewer::update_breadcrumb_line($this->_request_data['task']);

        switch ($handler_id)
        {
            case 'task_edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "task/edit/{$this->_task->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'task_delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "task/delete/{$this->_task->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Displays a task edit view.
     *
     * Note, that the task for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation task
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_task = new org_openpsa_projects_task($args[0]);
        if (! $this->_task)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The task {$args[0]} was not found.");
            // This will exit.
        }
        $this->_task->require_do('midgard:update');

	$this->_task->get_members();

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the task
                //$indexer =& $_MIDCOM->get_service('indexer');
                //org_openpsa_projects_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("task/{$this->_task->guid}/");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_task->title}");
        $_MIDCOM->bind_view_to_object($this->_task, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded task.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('show-task-edit');
    }

    /**
     * Displays a task delete confirmation view.
     *
     * Note, that the task for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation task
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_task = new org_openpsa_projects_task($args[0]);
        if (! $this->_task)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The task {$args[0]} was not found.");
            // This will exit.
        }
        $this->_task->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('org_openpsa_projects_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_task->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete task {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_task->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_openpsa_projects_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("task/{$this->_task->guid}/");
            // This will exit()
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_task->title}");
        $_MIDCOM->bind_view_to_object($this->_task, $this->_datamanager->schema->description);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded task.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['task_view'] = $this->_datamanager->get_content_html();

        midcom_show_style('show-task-delete');
    }
}

?>