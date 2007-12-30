<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration management handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_event extends midcom_baseclasses_components_handler
{
    /**
     * The events to register for
     *
     * @var array
     * @access private
     */
    var $_event = null;

    /**
     * The DM2 datamanager used to do view operations on events.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The DM2 controller used to do edit operations on events.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The root event (taken from the request data area)
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_root_event = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Compute a few URLs
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $this->_request_data['view_url'] = "{$prefix}event/view/{$this->_event->guid}.html";

        if ($this->_event->can_do('net.nemein.registrations:manage'))
        {
            $this->_request_data['list_registrations_url'] = "{$prefix}event/list_registrations/{$this->_event->guid}.html";
            $this->_request_data['export_csv_url'] = "{$prefix}event/export/csv/{$this->_event->guid}/{$this->_event->guid}.csv";
        }
        else
        {
            $this->_request_data['list_registrations_url'] = null;
            $this->_request_data['export_csv_url'] = null;
        }

        if ($this->_event->can_do('midgard:update'))
        {
            $this->_request_data['edit_url'] = "{$prefix}event/edit/{$this->_event->guid}.html";
            if ($this->_event->is_open())
            {
                $this->_request_data['close_url'] = "{$prefix}event/close/{$this->_event->guid}.html";
                $this->_request_data['open_url'] = null;
            }
            else
            {
                $this->_request_data['open_url'] = "{$prefix}event/open/{$this->_event->guid}.html";
                $this->_request_data['close_url'] = null;
            }
        }
        else
        {
            $this->_request_data['edit_url'] = null;
            $this->_request_data['open_url'] = null;
            $this->_request_data['close_url'] = null;
        }

        if ($this->_event->can_do('midgard:delete'))
        {
            $this->_request_data['delete_url'] = "{$prefix}event/delete/{$this->_event->guid}.html";
        }
        else
        {
            $this->_request_data['delete_url'] = null;
        }

        $this->_request_data['registration_allowed'] = $this->_event->can_do('midgard:create');
        $this->_request_data['registration_open'] = $this->_event->is_open();
        $this->_request_data['register_url'] = $this->_event->get_registration_link();
        if ($this->_event->is_registered())
        {
            $registration = $this->_event->get_registration();
            $this->_request_data['registration_url'] = "{$prefix}registration/view/{$registration->guid}.html";
        }
        else
        {
            $this->_request_data['registration_url'] = null;
        }
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_registrations_handler_event()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_root_event =& $this->_request_data['root_event'];
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    function _populate_toolbar(&$data)
    {
        if ($data['list_registrations_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['list_registrations_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('list registrations'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
        }
        if ($data['export_csv_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['export_csv_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('csv export'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/save.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
        }
        if ($data['edit_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['edit_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
        }

        if ($data['open_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['open_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('open the even for registration now'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
        }
        if ($data['close_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['close_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('close the event for registration now'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
        }

        if ($data['delete_url'])
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => $data['delete_url'],
                MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));
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
            MIDCOM_NAV_URL => "event/view/{$this->_event->guid}.html",
            MIDCOM_NAV_NAME => $this->_event->title,
        );

        switch ($handler_id)
        {
            case 'event-edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "event/edit/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;

            case 'event-delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "event/delete/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;

            case 'event-list_registrations':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "event/list_registrations/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('registrations'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows an event, no permissions required.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }

        $this->_datamanager =& $this->_event->get_datamanager();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = $this->_event->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        $this->_populate_toolbar($data);

        return true;
    }

    /**
     * Lists the registrations of a particular event, manage permissions required.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('event-view');
    }

    /**
     * Shows an event, no permissions required.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_event->require_do('midgard:update');

        $this->_controller =& $this->_event->create_simple_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_registrations_event::index($this->_controller->datamanager, $indexer, $this->_topic);

                // *** FALL THROUGH ***

            case 'cancel':
                // If we have a save or cancel event, we relocate back to the view.
                $_MIDCOM->relocate("event/view/{$this->_event->guid}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = $this->_event->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        $this->_populate_toolbar($data);

        return true;
    }

    /**
     * Lists the registrations of a particular event, manage permissions required.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('event-edit');
    }


    /**
     * Lists the registrations of a particular event.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list_registrations($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_event->require_do('net.nemein.registrations:manage');

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = sprintf($this->_l10n->get('list registrations of %s'), $this->_event->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * Lists the registrations of a particular event.
     */
    function _show_list_registrations($handler_id, &$data)
    {
        $data['registrations'] = $this->_event->get_registrations();

        midcom_show_style('event-list-registrations-start');
        if ($data['registrations'])
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach (array_keys($data['registrations']) as $id)
            {
                $data['registration'] =& $data['registrations'][$id];
                $data['registrar'] = $data['registration']->get_registrar();
                $data['registration_url'] = "{$prefix}registration/view/{$data['registration']->guid}.html";
                $data['approved'] = $data['registration']->is_approved();
                midcom_show_style('event-list-registrations-item');
            }
        }
        else
        {
            midcom_show_style('event-list-registration-nonefound');
        }
        midcom_show_style('event-list-registrations-end');
    }

    /**
     * Shows an event, no permissions required.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_event->require_do('midgard:delete');

        $this->_datamanager =& $this->_event->get_datamanager();

        // Processing
        if (array_key_exists('net_nemein_registrations_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_event->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete registration {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_event->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nemein_registrations_deletecancel', $_REQUEST))
        {
            // Delete cancelled, relocating to view.
            $_MIDCOM->relocate("event/view/{$this->_event->guid}.html");
            // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = $this->_event->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * Lists the registrations of a particular event, manage permissions required.
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('event-delete');
    }

    /**
     * CSV export handler, no permissions required, does not invoke show method,
     * will exit immediately.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_export_csv($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_event->require_do('net.nemein.registrations:manage');

        $registrar_schema = $this->_config->get('registrar_schema');
        $aq_schema = $this->_event->get_additional_questions_schema();

        // Change the output mode accordingly, enforce UTF-8.
        header('Content-Type: application/csv;charset=UTF-8');
        $_MIDCOM->cache->content->enable_live_mode();

        // Prepare required classes and values
        $dm = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        require_once(MIDCOM_ROOT . '/midcom/helper/datamanager2/csv.php');
        $csv = new midcom_helper_datamanager2_csv($dm);
        if ($this->_config->get('csv_separator'))
        {
            $csv->separator = $this->_config->get('csv_separator');
        }
        if ($this->_config->get('csv_newline'))
        {
            $csv->newline = $this->_config->get('csv_newline');
        }
        $newline_length = - strlen($csv->newline);

        // Print Header
        $dm->set_schema($registrar_schema);
        echo substr($csv->get_header_line(), 0, $newline_length);
        echo $csv->separator;
        $dm->set_schema($aq_schema);
        echo $csv->get_header_line();

        // Print data
        $registrations = $this->_event->get_registrations();

        debug_push_class(__CLASS__, __FUNCTION__);
        if ($registrations)
        {
            foreach ($registrations as $registration)
            {
                // Skip unapproved registrations (can't be done on the QB level yet)
                if (! $registration->is_approved())
                {
                    debug_add("Skipping registration ID {$registration->id}: Unapproved");
                    continue;
                }

                $registrar = $registration->get_registrar();

                if (! $registrar)
                {
                    // Skip invalid records.
                    debug_add("Skipping registration ID {$registration->id}: Cannot get registrar");
                    continue;
                }

                $dm->set_schema($registrar_schema);
                echo substr($csv->get_line($registrar), 0, $newline_length);
                echo $csv->separator;
                $dm->set_schema($aq_schema);
                echo $csv->get_line($registration);
            }
        }
        debug_pop();

        $_MIDCOM->finish();
        exit();
    }

    /**
     * Opens an event for registration and relocates to view mode. Already opened events
     * are ignored silently, closed events are reopened. Any set close date will not be
     * touched, of course.
     *
     * @see net_nemein_registrations_event::open_registration()
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_open($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:update');

        if (! $this->_event->is_open())
        {
            $this->_event->open_registration();
        }

        $_MIDCOM->relocate("event/view/{$this->_event->guid}.html");
    }


    /**
     * Closes an event for registration and relocates to view mode. Already closed events
     * are ignored silently.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_close($handler_id, $args, &$data)
    {
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The event {$args[0]} could not be found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:update');

        if ($this->_event->is_open())
        {
            $this->_event->close_registration();
        }

        $_MIDCOM->relocate("event/view/{$this->_event->guid}.html");
    }

}

?>