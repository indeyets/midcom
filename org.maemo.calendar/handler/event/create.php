<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_event_create  extends midcom_baseclasses_components_handler
{
    /**
     * The events to register for
     *
     * @var array
     * @access private
     */
    var $_event = null;

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
     * Currently active controller instance.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The defaults to use for the new resource.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['controller'] =& $this->_controller;

        $this->_request_data['full_schema_in_use'] = true;
        if (! isset($_REQUEST['full_schema'])
            || !$_REQUEST['full_schema'])
        {
            $this->_request_data['full_schema_in_use'] = false;
        }
    }

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_event_create()
    {
        parent::midcom_baseclasses_components_handler();

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_root_event =& $this->_request_data['root_event'];

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
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];

        $this->_defaults['start'] = $this->_request_data['selected_day'];
//mktime(date('H', $this->_request_data['selected_day']), date('i', $this->_request_data['selected_day']), 0, date('m', $this->_request_data['selected_day']), date('d', $this->_request_data['selected_day']), date('Y', $this->_request_data['selected_day']));

        $this->_defaults['end'] = $this->_defaults['start'] + 3600;

        //$user_tags = org_maemo_calendar_common::fetch_available_user_tags();

        // Insert user's default tag
        //$_MIDCOM->componentloader->load_graceful('net.nemein.tag');
        //$this->_defaults['tags'] = net_nemein_tag_handler::string2tag_array($user_tags[0]['id']);
        //$this->_defaults['tags'] = $user_tags[0]['id'];

        // Populate the participants
        if ($_MIDCOM->auth->user)
        {
            //$this->_defaults['participants'] = '|'.$_MIDGARD['user'].'|';
            // $this->_defaults['participants'] = array
            // (
            //     $_MIDGARD['user'],
            // );
            $this->_defaults['participants'] = serialize( array
            (
                $_MIDGARD['user'],
            ) );
            // $this->_defaults['participants'] = array
            // (
            //     $_MIDGARD['user'] = true,
            // );
        }

        $session =& new midcom_service_session();
        if ($session->exists('failed_POST_data'))
        {
            $this->_defaults = $session->get('failed_POST_data');
            $session->remove('failed_POST_data');
        }
        unset($session);

        $this->_request_data['defaults'] = $this->_defaults;

    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;

        $this->_controller->schemaname = 'default';
        if (   $handler_id == 'ajax-event-create'
            && !$this->use_full_schema)
        {
            $this->_controller->schemaname = 'ajax';
        }

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
    function &dm2_create_callback(&$controller)
    {
        $this->_event = new org_maemo_calendar_event();
        $this->_event->up = $this->_request_data['root_event_id'];

        // Populate the participants
        $participants = array();
        if (   !is_array($_POST['participants'])
            || empty($_POST['participants']))
        {
            $_POST['participants'] = array( $_MIDGARD['user'] );
        }
        debug_print_r('_POST[participants]: ',$_POST['participants']);
        foreach ($_POST['participants'] as $participant_id)
        {
            $participants[] = $participant_id;
        }

        $this->_event->participants = serialize($participants);

        debug_print_r('this->_event->participants before create: ',$this->_event->participants);

        if (array_key_exists('start', $_POST))
        {
            $this->_event->start = strtotime($_POST['start']);
            debug_add("new start time: ".date("Y-m-d H:i:s", $this->_event->start));
        }
        if (array_key_exists('end', $_POST))
        {
            $this->_event->end = strtotime($_POST['end']);
            debug_add("new end time: ".date("Y-m-d H:i:s", $this->_event->end));
        }
        if (array_key_exists('title', $_POST))
        {
            $this->_event->title = $_POST['title'];
        }

        // TODO: Add support for tentatives?
        $this->_event->busy = false;

        if (! $this->_event->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_event);
            debug_pop();
            if (   is_array($this->_event->busy_em)
                || !empty($this->_event->busy_em)
                || is_array($this->_event->busy_er)
                || !empty($this->_event->busy_er))
            {
                // Raise UImessage
                $_MIDCOM->uimessages->add('Title', $this->_l10n->get('failed to create a new event due to resourcing conflict'), 'error');
                // Store form values
                $post_data = array();
                foreach ($this->_controller->datamanager->schema->field_order as $fieldname)
                {
                    if (!isset($_POST[$fieldname]))
                    {
                        continue;
                    }
                    $post_data[$fieldname] = $_POST[$fieldname];
                }
                $session =& new midcom_service_session();
                $session->set('failed_POST_data', $post_data);
                // Return user to create view
                $_MIDCOM->relocate("event/create/{$this->_event->start}");
                // This will exit
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }

        debug_print_r('this->_event->participants after create: ',$this->_event->participants);

        return $this->_event;
    }

    function _timezone_hack()
    {
        $active_timezone = org_maemo_calendar_common::active_timezone();
        $utc_timezone = timezone_open("UTC");

        if (   isset($_POST['start'])
            && isset($_POST['end']))
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

            $_POST['start_ts'] = $event_start;
            $_POST['start'] = date("Y-m-d H:i:s", $event_start);
            $_POST['end'] = date("Y-m-d H:i:s", $event_end);

            $this->_event->start = $event_start;
            $this->_event->end = $event_end;
            debug_add("new start time: ".date("Y-m-d H:i:s", $this->_event->start));
            debug_add("new end time: ".date("Y-m-d H:i:s", $this->_event->end));
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['root_event']);

        $this->use_full_schema = true;
        if ($handler_id == 'ajax-event-create')
        {
            $_MIDCOM->skip_page_style = true;

            if (! isset($_REQUEST['full_schema'])
                || !$_REQUEST['full_schema'])
            {
                $this->use_full_schema = false;
            }
        }

        $this->_request_data['selected_day'] = time();
        $requested_time = $args[0];//@strtotime($args[0]);
        if ($requested_time)
        {
            $this->_request_data['selected_day'] = $requested_time;
        }

        debug_add("requested time: {$this->_request_data['selected_day']}");

        $this->_timezone_hack();

        $this->_load_controller($handler_id);
        $this->_prepare_request_data();

        switch ($this->_controller->process_form())
        {
            case 'save':
                if (   $handler_id == 'ajax-event-create'
                    && !$this->use_full_schema)
                {
                    // Change to default schema on the fly
                    $this->_event->set_parameter('midcom.helper.datamanager2', 'schema_name', 'default');
                }
                // this will exit.

            case 'cancel':
                $_MIDCOM->relocate("view/{$_POST['start_ts']}/{$this->_calendar_type}");
                // This will exit.
        }

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        if ($handler_id == 'ajax-event-create')
        {
            midcom_show_style('event-create-ajax');
        }
        else
        {
            midcom_show_style('event-create');
        }
    }

}

?>