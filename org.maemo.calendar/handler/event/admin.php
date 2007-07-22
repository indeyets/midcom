<?php
/**
 * @package net.nemein.events
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * events edit/delete event handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package net.nemein.events
 */
class org_maemo_calendar_handler_event_admin extends midcom_baseclasses_components_handler
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
    function org_maemo_calendar_handler_event_admin()
    {
        parent::midcom_baseclasses_components_handler();
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
            case 'edit-event':
                $this->_view_toolbar->disable_item("edit/{$this->_event->guid}.html");
                break;
            case 'delete-event':
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
        $this->_schemadb =& $this->_request_data['schemadb'];
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
        $this->_controller->schemaname = 'default';
        $this->_controller->set_storage($this->_event, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for event {$this->_event->id}.");
            // This will exit.
        }
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        // $tmp[] = Array
        // (
        //     MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
        //     MIDCOM_NAV_NAME => $this->_resource->title,
        // );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "event/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => "{$this->_event->title} " . strftime('%x', $this->_event->start),
        );
        
        switch ($handler_id)
        {
            case 'edit-event':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "edit/{$this->_event->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'delete-event':
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
     * If create privileges apply, we relocate to the index creation event,
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($handler_id == 'ajax-event-edit')
        {
            $_MIDCOM->skip_page_style = true;
        }
        
        $active_timezone = org_maemo_calendar_common::active_timezone();
        // date_default_timezone_set(timezone_name_get($active_timezone));
        $utc_timezone = timezone_open("UTC");
        
        $this->_event = new org_maemo_calendar_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} was not found.");
            // This will exit.
        }
        
        $this->_event->require_do('midgard:update');
        
        debug_print_r('Event participants', $this->_event->participants);
        
        $participants = array();
        foreach ($this->_event->participants as $participant => $included)
        {
            $participants[] = $participant;
        }
        $this->_event->participants = serialize($participants);
        
        debug_print_r('Event participants after serialize', $this->_event->participants);
        
        if (empty($_POST))
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
            
            $event_start = strtotime($_POST['start']);
            debug_add("event_start before timezone change: " . $event_start . " (" . date("H:i:s",$event_start) . ")");
            $event_end = strtotime($_POST['end']);
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

            $_POST['start'] = date("Y-m-d H:i:s", $event_start);
            $_POST['end'] = date("Y-m-d H:i:s", $event_end);
        }
        
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
                $_MIDCOM->relocate("");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title}");
        $_MIDCOM->bind_view_to_object($this->_event, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);
        
        debug_pop();
        
        return true;
    }


    /**
     * Shows the loaded event.
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
     * Displays an event delete confirmation view.
     *
     * Note, that the event for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation event,
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
        
        $this->_load_datamanager();

        if (array_key_exists('net_nemein_events_deleteok', $_REQUEST))
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

        /*
        if (array_key_exists('net_nemein_events_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("event/{$this->_event->guid}/");
            // This will exit()
        }
        */

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title}");
        $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded event.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['view_event'] = $this->_datamanager->get_content_html();
        midcom_show_style('view-event-delete');
    }
}

?>