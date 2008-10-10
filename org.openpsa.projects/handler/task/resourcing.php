<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Projects task resoucing handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_resourcing extends midcom_baseclasses_components_handler
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
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/legacy.css",
            )
        );

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.projects/projectbroker.js");
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.projects/crir.js");
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
            case 'task_resourcing':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "task/resourcing/{$this->_task->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('resourcing'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Display possible available resources
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_resourcing($handler_id, $args, &$data)
    {
        $this->_task = new org_openpsa_projects_task($args[0]);
        if (! $this->_task)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The task {$args[0]} was not found.");
            // This will exit.
        }
        $this->_task->require_do('midgard:create');

        if (   array_key_exists('org_openpsa_projects_prospects', $_POST)
            && $_POST['save'])
        {
            $_MIDCOM->componentloader->load('org.openpsa.calendar');
            $_MIDCOM->componentloader->load('org.openpsa.relatedto');
            foreach ($_POST['org_openpsa_projects_prospects'] as $prospect_guid => $slots)
            {
                $prospect = new org_openpsa_projects_task_resource($prospect_guid);
                if (!$prospect)
                {
                    // Could not fetch  prospect object
                    continue;
                }
                $update_prospect = false;
                foreach ($slots as $data)
                {
                    if (   !array_key_exists('used', $data)
                        || empty($data['used']))
                    {
                        // Slot not selected, skip
                        continue;
                    }
                    $prospect->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PROJECTRESOURCE;
                    $update_prospect = true;
                    // Create event from slot
                    $event = new org_openpsa_calendar_event();
                    $event->start = $data['start'];
                    $event->end = $data['end'];
                    $event->search_relatedtos = false;
                    $event->participants = array($prospect->person => true);
                    $event->title = sprintf($this->_l10n->get('work for task %s'), $this->_task->title);
                    if (!$event->create())
                    {
                        // TODO: error reporting
                        continue;
                    }
                    // create relatedto
                    if (!org_openpsa_relatedto_handler::create_relatedto($event, 'org.openpsa.calendar', $this->_task, 'org.openpsa.projects'))
                    {
                        // TODO: Error reporting, delete event ???
                    }
                }
            }
            if ($update_prospect)
            {
                if (!$prospect->update())
                {
                    // TODO: error handling
                }
            }
            $_MIDCOM->relocate("task/{$this->_task->guid}/");
            // This will exit.
        }
        elseif (   array_key_exists('cancel', $_POST)
                && $_POST['cancel'])
        {
            $_MIDCOM->relocate("task/{$this->_task->guid}/");
            // This will exit.
        }

        $this->_load_datamanager();

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_task->title}");
        $_MIDCOM->bind_view_to_object($this->_task);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded task.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_resourcing($handler_id, &$data)
    {
        $data['task_view'] = $this->_datamanager->get_content_html();

        midcom_show_style('show-task-resourcing');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list_prospects($handler_id, $args, &$data)
    {
        $this->_task = new org_openpsa_projects_task($args[0]);
        if (! $this->_task)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The task {$args[0]} was not found.");
            // This will exit.
        }
        $this->_task->require_do('midgard:create');

        $qb = org_openpsa_projects_task_resource::new_query_builder();
        $qb->add_constraint('task', '=', $this->_task->id);
        $qb->begin_group('OR');
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTPROSPECT);
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTRESOURCE);
        $qb->end_group('OR');
        $qb->add_order('orgOpenpsaObtype');
        $data['prospects'] = $qb->execute();

        $_MIDCOM->skip_page_style = true;

        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list_prospects($handler_id, &$data)
    {
        midcom_show_style('show-prospects-xml');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_prospect_slots($handler_id, $args, &$data)
    {
        $data['prospect'] = new org_openpsa_projects_task_resource($args[0]);
        if (!$data['prospect'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Prospect {$args[0]} was not found.");
            // This will exit.
        }

        $data['person'] = new org_openpsa_contacts_person($data['prospect']->person);
        if (! $data['person'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Person #{$data['prospect']->person} was not found.");
            // This will exit.
        }

        $this->_task = new org_openpsa_projects_task($data['prospect']->task);
        if (! $this->_task)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Task #{$data['prospect']->task} was not found.");
            // This will exit.
        }
        $this->_task->require_do('midgard:create');

        $projectbroker = new org_openpsa_projects_projectbroker();
        $data['slots'] = $projectbroker->resolve_person_timeslots($data['person'], $this->_task);

        $_MIDCOM->skip_page_style = true;

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_prospect_slots($handler_id, &$data)
    {
        midcom_show_style('show-prospect');
    }
}

?>