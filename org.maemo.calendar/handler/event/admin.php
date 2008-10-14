<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * events edit/delete event handler
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_event_admin extends midcom_baseclasses_components_handler
{
    var $_calendar_type;

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
     * @var org_openpsa_calendar_event_dba
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
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();

        $session =& new midcom_service_session('org.maemo.calendar');
        if ($session->exists('active_type'))
        {
            $this->_calendar_type = $session->get('active_type');
        }
        else
        {
            $this->_calendar_type = $this->_config->get('default_view');
        }
        unset($session);
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
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
        $this->_controller->schemaname = 'default';
        $this->_controller->set_storage($this->_event, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for event {$this->_event->id}.");
            // This will exit.
        }
    }

    function _timezone_hack($override_start=false,$override_end=false)
    {
        $active_timezone = org_maemo_calendar_common::active_timezone();
        $utc_timezone = timezone_open("UTC");

        if (   empty($_POST)
            && is_object($this->_event)
            && !$override_start)
        {
            debug_add("Alter the start/end times with timezone");
            $event_start = $this->_event->start;
            debug_add("this->_event->start before timezone change: " . $this->_event->start);
            $event_end = $this->_event->end;
            $event_start = date_create("@$event_start",$utc_timezone);
            $event_end = date_create("@$event_end",$utc_timezone);
            $start_offset = $active_timezone->getOffset($event_start);
            debug_add("offset {$start_offset} (in hours): " . ($start_offset/(60*60)));
            $end_offset = $active_timezone->getOffset($event_end);
            $event_start->setTimezone($active_timezone);
            $event_end->setTimezone($active_timezone);
            $this->_event->start = $event_start->format("U") + $start_offset;
            $this->_event->end = $event_end->format("U") + $end_offset;
            debug_add("this->_event->start after timezone change: " . $this->_event->start);
        }
        else
        {
            debug_add("Make sure the start/end times are saved with UTC timezone");

            $start = null;
            $end = null;

            if (   isset($_POST['start'])
                && isset($_POST['end']))
            {
                $start = $_POST['start'];
                $end = $_POST['end'];
            }

            if (   $override_start
                && $override_end)
            {
                $start = $override_start;
                $end = $override_end;
            }

            $event_start = strtotime($start);
            debug_add("event_start before timezone change: " . $event_start . " (" . date("H:i:s",$event_start) . ")");
            $event_end = strtotime($end);
            $event_start_dt = date_create("@$event_start", $utc_timezone);
            $event_end_dt = date_create("@$event_end", $utc_timezone);
            $start_tz_name = $event_start_dt->getTimeZone()->getName();
            debug_add("start_tz_name: ".$start_tz_name);
            $event_start_dt->setTimezone($active_timezone);
            $start_tz_name = $event_start_dt->getTimeZone()->getName();
            debug_add("start_tz_name after set: ".$start_tz_name);
            $event_end_dt->setTimezone($active_timezone);
            $start_offset = $event_start_dt->format('Z');//$active_timezone->getOffset($event_start);
            debug_add("offset {$start_offset} (in hours): " . ($start_offset/(60*60)));
            $end_offset = $event_end_dt->format('Z');//$active_timezone->getOffset($event_end);

            if ($start_offset > 0)
            {
                $event_start = $event_start - $start_offset;
                $event_end = $event_end - $end_offset;
            }
            else
            {
                $event_start = $event_start + $start_offset;
                $event_end = $event_end + $end_offset;
            }

            debug_add("event_start after timezone change: " . $event_start . " (" . date("H:i:s",$event_start) . ")");

            $_POST['start_ts'] = $event_start;

            $_POST['start'] = date("Y-m-d H:i:s", $event_start);
            $_POST['end'] = date("Y-m-d H:i:s", $event_end);
        }
    }

    function _participant_hack()
    {
        if (! is_object($this->_event))
        {
            return false;
        }

        debug_print_r('Event participants', $this->_event->participants);

        // if (isset($_POST['participants']))
        // {
            $participants = array();
            foreach ($this->_event->participants as $participant => $included)
            {
                $participants[] = $participant;
            }
            $this->_event->participants = serialize($participants);

            debug_print_r('Event participants after serialize', $this->_event->participants);
        // }
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
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($handler_id == 'ajax-event-edit')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_event = new org_maemo_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:update');

        /*
         * TODO: Get rid of this ugly hack, by changing few things on event schema, etc
        */
        $this->_participant_hack();

        $this->_timezone_hack();

        $this->_load_controller();

        // TODO: Check for resourcing conflict

        switch ($this->_controller->process_form())
        {
            case 'save':
                debug_print_r('Event participants in save', $this->_event->participants);

                // Reindex the event
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_events_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                //$_MIDCOM->relocate("event/{$this->_event->guid}/");
                $_MIDCOM->relocate("view/{$_POST['start_ts']}/{$this->_calendar_type}");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->bind_view_to_object($this->_event, $this->_request_data['controller']->datamanager->schema->name);

        debug_pop();

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_move($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($handler_id == 'ajax-event-move')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_event = new org_maemo_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:update');

        /*
         * TODO: Get rid of this ugly hack, by changing few things on event schema, etc
        */
        $this->_participant_hack();

        $this->_timezone_hack();

        $event_length = $this->_event->end - $this->_event->start;
        $this->_event->start = $args[1] + 1;
        $this->_event->end = $event_length + $this->_event->start;

        $this->_load_controller();

        // TODO: Check for resourcing conflict
        switch ($this->_controller->process_form())
        {
            case 'save':
                if ($handler_id == 'ajax-event-move')
                {
                    $session =& new midcom_service_session('org.maemo.calendarpanel');
                    if ($session->exists('shelf_contents'))
                    {
                        $contents = json_decode($session->get('shelf_contents'));
                        $new_contents = array();
                        foreach ($contents as $item)
                        {
                            if ($item->guid != $this->_event->guid)
                            {
                                $new_contents[] = $item;
                            }
                        }
                        $session->set('shelf_contents',json_encode($new_contents));
                    }
                }

            case 'cancel':
                //$_MIDCOM->relocate("event/{$this->_event->guid}/");
                $_MIDCOM->relocate("view/{$_POST['start_ts']}/{$this->_calendar_type}");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->bind_view_to_object($this->_event, $this->_request_data['controller']->datamanager->schema->name);

        debug_pop();

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
        if ($handler_id == 'ajax-event-edit')
        {
            midcom_show_style('event-edit-ajax');
        }
        else
        {
            midcom_show_style('event-edit');
        }
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_move($handler_id, &$data)
    {
        if ($handler_id == 'ajax-event-move')
        {
            midcom_show_style('event-edit-ajax');
        }
        else
        {
            midcom_show_style('event-edit');
        }
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
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($handler_id == 'ajax-event-delete')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_event = new org_maemo_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }

        $this->_event->require_do('midgard:delete');

        $this->_load_controller();

        if (array_key_exists('org_maemo_calendar_event_deleteok', $_REQUEST))
        {
            $data['deleted'] = $this->_event->guid;

            // Deletion confirmed.
            if (! $this->_event->delete())
            {
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete event {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            if ($handler_id != 'ajax-event-delete')
            {
                // Delete ok, relocating to latest view.
                debug_pop();
                $_MIDCOM->relocate("");
                // This will exit.
            }
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->bind_view_to_object($this->_event, $this->_request_data['controller']->datamanager->schema->name);

        if ($handler_id != 'ajax-event-delete')
        {
            $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title}");
            $this->_update_breadcrumb_line($handler_id);
        }

        debug_pop();

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
        if ($handler_id == 'ajax-event-delete')
        {
            midcom_show_style('event-delete-ajax');
        }
    }
}

?>