<?php
/**
 * @package org.openpsa.expenses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product database create hour_report handler
 *
 * @package org.openpsa.expenses
 */
class org_openpsa_expenses_handler_hours_admin extends midcom_baseclasses_components_handler
{
    /**
     * The hour report
     *
     * @var org_openpsa_projects_hour_report_dba
     * @access private
     */
    var $_hour_report = null;

    /**
     * The Controller of the article used for editing
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
     * The schema to use for the new article.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

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
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_hours'));
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_create_controller()
    {
        $this->_defaults['task'] = $this->_request_data['task'];
        $this->_defaults['person'] = $_MIDGARD['user'];
        $this->_defaults['date'] = time();
        
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
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_hour_report = new org_openpsa_projects_hour_report_dba();
        $this->_hour_report->hour_reportGroup = $this->_request_data['task'];

        if (! $this->_hour_report->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_hour_report);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a new hour_report under hour_report group #{$this->_request_data['task']}, cannot continue. Error: " . mgd_errstr());
            // This will exit.
        }

        return $this->_hour_report;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_load_schemadb();
        $data['selected_schema'] = $args[0];
        if (!array_key_exists($data['selected_schema'], $this->_schemadb))
        {
            return false;
        }
        $this->_schema =& $data['selected_schema'];

        if (count($args) > 1)
        {
            $data['task'] = (int) $args[1];

            $parent = new org_openpsa_projects_task_dba($data['task']);
            if (!$parent)
            {
                return false;
            }
            $parent->require_do('midgard:create');
        }
        else
        {
            $_MIDCOM->auth->require_valid_user();
            $data['task'] = 0;
        }

        $this->_load_create_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("hours/edit/{$this->_hour_report->guid}/");
                // This will exit.

            case 'cancel':
                if ($data['task'] == 0)
                {
                    $_MIDCOM->relocate('');
                }
                else
                {
                    // TODO: Look up projects node
                    //$_MIDCOM->relocate("{$data['task']}/");
                    $_MIDCOM->relocate('');
                }
                // This will exit.
        }

        $this->_prepare_request_data();

        if ($this->_hour_report)
        {
            $_MIDCOM->set_26_request_metadata($this->_hour_report->metadata->revised, $this->_hour_report->guid);
        }

        $data['view_title'] = sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$this->_schema]->description);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        return true;
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('hours_create');
    }

    /**
     * Looks up an hour_report to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_hour_report = new org_openpsa_projects_hour_report_dba($args[0]);
        if (!$this->_hour_report)
        {
            return false;
        }
        
        $this->_load_schemadb();
        $this->_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_hour_report);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for hour_report {$this->_hour_report->id}.");
            // This will exit.
        }

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_wiki_viewer::index($this->_request_data['controller']->datamanager, $indexer, $this->_topic);
                // *** FALL-THROUGH ***
            case 'cancel':
                $task = new org_openpsa_projects_task_dba($this->_hour_report->task);
                $_MIDCOM->relocate("hours/task/" . $task->guid . "/");
                // This will exit.
        }


        $this->_prepare_request_data();
        // Add toolbar items
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "hours/delete/{$this->_hour_report->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',

            )
        );
        $this->_view_toolbar->bind_to($this->_hour_report);

        $_MIDCOM->set_26_request_metadata($this->_hour_report->metadata->revised, $this->_hour_report->guid);
        //$_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_hour_report->title}");

        $this->_update_breadcrumb_line();

        return true;
    }


    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     */
    private function _update_breadcrumb_line()
    {
        $task = new org_openpsa_projects_task_dba($this->_hour_report->task);
        $tmp = Array();

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "hours/task/" . $task->guid,
            MIDCOM_NAV_NAME => $task->get_label(),
        );

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Shows the loaded hour_report.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('hours_edit');
    }
    
    /**
     * The delete handler.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_hour_report = new org_openpsa_projects_hour_report_dba($args[0]);
        if (!$this->_hour_report)
        {
            return false;
        }

        $this->_hour_report->require_do('midgard:delete');

        if (array_key_exists('org_openpsa_expenses_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_hour_report->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete hour report {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_openpsa_expenses_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate();
            // This will exit()
        }

        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_hour_report);

        $_MIDCOM->set_26_request_metadata($this->_hour_report->metadata->revised, $this->_hour_report->guid);
      
        return true;
    }

    /**
     * Shows the delete hour_report form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('hours_delete');
    }
}
?>