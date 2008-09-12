<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Reservations edit/delete event handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_handler_reservation_admin extends midcom_baseclasses_components_handler
{
    /**
     * The resource which we're reserving
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * The event to operate on
     *
     * @var org_openpsa_calendar_event
     * @access private
     */
    var $_event = null;

    /**
     * The Datamanager of the event to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the event used for editing
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
     * Schema to use for event display
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_reservations_handler_reservation_admin()
    {
        parent::__construct();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_event->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_event->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        switch ($handler_id)
        {
            case 'edit_reservation':
                $this->_view_toolbar->disable_item("edit/{$this->_event->guid}.html");
                break;
            case 'delete_reservation':
                $this->_view_toolbar->disable_item("delete/{$this->_event->guid}.html");
                break;
        }
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb_reservation'];
    }

    /**
     * Internal helper, loads the datamanager for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        //$this->_datamanager->schema = $this->_event->type;
        if (!$this->_datamanager->autoset_storage($this->_event))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for event {$this->_event->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_event, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for event {$this->_event->id}.");
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
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "reservation/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => "{$this->_event->title} " . strftime('%x', $this->_event->start),
        );

        switch ($handler_id)
        {
            case 'edit_reservation':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "edit/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'delete_reservation':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "delete/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays an event edit view.
     *
     * Note, that the event for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation event
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_event = new org_openpsa_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:update');

        foreach ($this->_event->resources as $resource => $included)
        {
            $this->_resource = new org_openpsa_calendar_resource_dba($resource);
            break;
        }

        $this->_load_controller();

        // TODO: Check for resourcing conflict

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the event
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_reservations_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("reservation/{$this->_event->guid}/");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title}");
        $_MIDCOM->bind_view_to_object($this->_event, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded event.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('view-reservation-edit');
    }

    /**
     * Displays an event delete confirmation view.
     *
     * Note, that the event for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation event
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_event = new org_openpsa_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:delete');

        $data['dependencies'] = false;

        // If repeats are set with net.nemein.repeathandler, events should
        if (   $this->_event->get_parameter('net.nemein.repeathandler', 'master_guid')
            && $this->_event->guid == $this->_event->get_parameter('net.nemein.repeathandler', 'master_guid'))
        {
            if (version_compare(mgd_version(), '1.8', '>='))
            {
                $qb = org_openpsa_calendar_event::new_query_builder();
                $qb->add_constraint('parameter.domain', '=', 'net.nemein.repeathandler');
                $qb->add_constraint('parameter.name', '=', 'master_guid');
                $qb->add_constraint('parameter.value', '=', $this->_event->guid);

                $results = $qb->execute_unchecked();
                $data['dependant_events'] = $results;
            }
            else
            {
                $qb = new midgard_query_builder('midgard_parameter');
                $qb->add_constraint('domain', '=', 'net.nemein.repeathandler');
                $qb->add_constraint('name', '=', 'master_guid');
                $qb->add_constraint('value', '=', $this->_event->guid);
                $qb->add_constraint('tablename', '=', 'event');

                $results = array ();
                $params = @$qb->execute();
                foreach ($params as $parameter)
                {
                    $results[] = new org_openpsa_calendar_event($parameter->oid);
                }
                $data['dependant_events'] = $results;
            }

            // Dependencies found, block deleting
            if ($qb->count() > 1)
            {
                $data['dependencies'] = true;
            }
        }

        foreach ($this->_event->resources as $resource => $included)
        {
            $this->_resource = new org_openpsa_calendar_resource_dba($resource);
            break;
        }

        $this->_load_datamanager();

        if (array_key_exists('net_nemein_reservations_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_event->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete event {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_event->guid);

            // Delete ok, relocating to resource.
            $_MIDCOM->relocate("view/{$this->_resource->name}/");
            // This will exit.
        }

        if (array_key_exists('net_nemein_reservations_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("reservation/{$this->_event->guid}/");
            // This will exit()
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title}");
        $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded event.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete ($handler_id, &$data)
    {
        $_MIDCOM->load_library('org.openpsa.contactwidget');
        $data['view_reservation'] = $this->_datamanager->get_content_html();
        midcom_show_style('view-reservation-delete');
    }
}

?>