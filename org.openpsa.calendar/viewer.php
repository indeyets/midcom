<?php

/**
 * @package org.openpsa.calendar
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.46 2006/06/08 16:24:37 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.calendar site interface class.
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_viewer extends midcom_baseclasses_components_request
{

    var $_datamanager;
    var $_selected_time = null;
    var $_dm_createfailed_event = null;

    /**
     * Constructor.
     *
     * @todo OpenPSA Calendar handles its URL space how?
     */
    function org_openpsa_calendar_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->_selected_time = time();

        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($this->_config->get('schemadb'));
        eval("\$schemadb = Array ( {$schemadb_contents} );");

        // Initialize the datamanager with the schema
        $this->_datamanager = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanager) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        if (   !isset($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'])
            || !is_object($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            // Match /
            $this->_request_switch[] = array(
                'handler' => 'notinitialized'
            );
        }
        else
        {
            // Match /month/<date>
            $this->_request_switch[] = array(
                'fixed_args' => 'month',
                'variable_args' => 1,
                'handler' => 'month'
            );
            // Match /month/
            $this->_request_switch[] = array(
                'fixed_args' => 'month',
                'handler' => 'month'
            );
            // Match /week/<date>
            $this->_request_switch[] = array(
                'fixed_args' => 'week',
                'variable_args' => 1,
                'handler' => 'week'
            );
            // Match /week/
            $this->_request_switch[] = array(
                'fixed_args' => 'week',
                'handler' => 'week'
            );
            // Match /day/<date>
            $this->_request_switch[] = array(
                'fixed_args' => 'day',
                'variable_args' => 1,
                'handler' => 'day'
            );
            // Match /day/
            $this->_request_switch[] = array(
                'fixed_args' => 'day',
                'handler' => 'day'
            );
            // Match /event/new/<person_guid>/<timestamp>
            $this->_request_switch[] = array(
                'fixed_args' => array('event', 'new'),
                'variable_args' => 2,
                'handler' => 'event_new'
            );
            // Match /event/new/<person_guid>
            $this->_request_switch[] = array(
                'fixed_args' => array('event', 'new'),
                'variable_args' => 1,
                'handler' => 'event_new'
            );
            // Match /event/new
            $this->_request_switch[] = array(
                'fixed_args' => array('event', 'new'),
                'handler' => 'event_new'
            );
            // Match /event/raw/<guid>
            $this->_request_switch['event_view_raw'] = array(
                'fixed_args' => array('event', 'raw'),
                'variable_args' => 1,
                'handler' => 'event'
            );
            // Match /event/<guid>/<action>
            $this->_request_switch[] = array(
                'fixed_args' => 'event',
                'variable_args' => 2,
                'handler' => 'event_action'
            );
            // Match /event/<guid>
            $this->_request_switch['event_view'] = array(
                'fixed_args' => 'event',
                'variable_args' => 1,
                'handler' => 'event'
            );
            //Match /debug
            $this->_request_switch[] = array(
                'fixed_args' => 'debug',
                'handler' => 'debug'
            );
            // Match /
            $this->_request_switch[] = array(
                'handler' => 'frontpage'
            );

            // Match /filters
            $this->_request_switch['filters_edit'] = array(
                'fixed_args' => Array('filters'),
                'handler' => Array('org_openpsa_calendar_handler_filters', 'edit'),
            );

            // Match /agenda/day/<timestamp>
            $this->_request_switch['agenda_day'] = array(
                'fixed_args' => Array('agenda', 'day'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_calendar_handler_agenda', 'day'),
            );

            // Match /ical/events/<username>
            $this->_request_switch['ical_user_feed'] = array(
                'fixed_args' => Array('ical', 'events'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_calendar_handler_ical', 'user_events'),
            );

            // Match /ical/busy/<username>
            $this->_request_switch['ical_user_busy'] = array(
                'fixed_args' => Array('ical', 'busy'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_calendar_handler_ical', 'user_busy'),
            );


            // Match /config/
            $this->_request_switch['config'] = Array
            (
                'handler' => Array('midcom_core_handler_configdm', 'configdm'),
                'schemadb' => 'file:/org/openpsa/calendar/config/schemadb_config.inc',
                'schema' => 'config',
                'fixed_args' => Array('config'),
            );

            //Add common relatedto request switches
            org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.calendar');
            //If you need any custom switches add them here
        }

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $this->_request_data['view'] = 'default';
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.core/ui-elements.css",
            )
        );

    }

    function _handler_notinitialized($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_notinitialized($handler_id, &$data)
    {
        midcom_show_style('show-not-initialized');
    }


    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create an event
        $result = array (
            "storage" => null,
            "success" => false,
        );

        $event = new org_openpsa_calendar_event();
        //Pre-populate some data to the event to allow consistency checks to work (I *really* hate the way DM handles creation)
        if (array_key_exists('midcom_helper_datamanager_field_participants', $_POST))
        {
            $event->participants = $_POST['midcom_helper_datamanager_field_participants'];
        }
        if (array_key_exists('midcom_helper_datamanager_field_busy', $_POST))
        {
            $event->busy = $_POST['midcom_helper_datamanager_field_busy'];
        }
        if (array_key_exists('midcom_helper_datamanager_field_start', $_POST))
        {
            $event->start = strtotime($_POST['midcom_helper_datamanager_field_start']);
        }
        if (array_key_exists('midcom_helper_datamanager_field_end', $_POST))
        {
            $event->end = strtotime($_POST['midcom_helper_datamanager_field_end']);
        }
        $event->send_notify = false;

        $stat = $event->create();
        if ($stat)
        {
            $this->_request_data['event'] = new org_openpsa_calendar_event($event->id);
            $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_request_data['event'], 'org.openpsa.calendar');
            debug_add("org_openpsa_relatedto_handler returned \n===\n" . print_r($rel_ret) . "===\n");
            $this->_request_data['event']->notify_force_add = true;
            $result["storage"] =& $this->_request_data['event'];
            $result["success"] = true;
            //return $result;
        }
        else
        {
            $this->_dm_createfailed_event = $event;
            $result = null;
        }
        return $result;
    }

    function _get_datestring($from = false)
    {
        if (!$from)
        {
            $from = $this->_selected_time;
        }
        $datestring = date('Y-m-d', $from);
        return $datestring;
    }

    function _populate_toolbar($today_path = null)
    {
        // "New event" should always be in toolbar
        $nap = new midcom_helper_nav();
        $this_node = $nap->get_node($nap->get_current_node());
        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "#",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create event'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_OPTIONS  => Array(
                        'rel' => 'directlink',
                        'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($this_node),
                    ),
                )
            );
        }


        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }

        $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/jscript-calendar';
        $_MIDCOM->add_jsfile("{$prefix}/calendar.js");

        // Select correct locale
        $i18n =& $_MIDCOM->get_service("i18n");
        $language = $i18n->get_current_language();
        switch ($language)
        {
            // TODO: Add more languages here when corresponding locale files exist
            case "fi":
                $_MIDCOM->add_jsfile("{$prefix}/calendar-fi.js");
                break;
            case "en":
            default:
                $_MIDCOM->add_jsfile("{$prefix}/calendar-en.js");
                break;
        }

        $_MIDCOM->add_jsfile("{$prefix}/calendar-setup.js");

        $dateopts = date('Y', $this->_selected_time).', ';
        $dateopts .= date('n', $this->_selected_time) - 1;
        $dateopts .= ', '.date('j', $this->_selected_time);

        $_MIDCOM->add_jscript('
var openPsaShowMonthSelectorCalendarShown = false;
var openPsaShowMonthSelectorCalendarInitialized = false;
function openPsaShowMonthSelectorHandler(calendar)
{
    if (calendar.dateClicked)
    {
        var y = calendar.date.getFullYear();
        var m = new String(calendar.date.getMonth() + 1);
        if (m.length == 1)
        {
            m = "0"+m;
        }
        var d = calendar.date.getDate();
        // Relocate to correct view
        // TODO: It would be safer to use full URL
        window.location = y + "-" + m + "-" + d + ".html";
    }
}

function openPsaShowMonthSelectorCalendar()
{
    Calendar.setup(
        {
            flat        : "org_openpsa_calendar_calendarwidget",
            flatCallback: openPsaShowMonthSelectorHandler,
            firstDay    : 1,
            date        : new Date('.$dateopts.')
        }
    );
}

function openPsaShowMonthSelector()
{
    calendarArea = document.getElementById("org_openpsa_calendar_calendarwidget");
    if (openPsaShowMonthSelectorCalendarShown)
    {
        calendarArea.style.display = "none";
        openPsaShowMonthSelectorCalendarShown = false;
    }
    else
    {
        calendarArea.style.display = "block";
        if (!openPsaShowMonthSelectorCalendarInitialized)
        {
            openPsaShowMonthSelectorCalendar();
            openPsaShowMonthSelectorCalendarInitialized = true;
        }
        openPsaShowMonthSelectorCalendarShown = true;
    }
}
        ');

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "#",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('go to'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/web-calendar.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_OPTIONS  => Array(
                    'rel' => 'directlink',
                    'onClick' => "javascript:openPsaShowMonthSelector();",
                ),
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "{$today_path}/" . $this->_get_datestring(time()) . ".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('today'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/web-calendar.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "filters.html?org_openpsa_calendar_returnurl={$_MIDGARD['uri']}",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('choose calendars'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

    }

    function _populate_calendar_resource($resource, $from, $to)
    {
        $resource_array = array(
            'name' => $resource->firstname.' '.$resource->lastname,
        );
        if ($resource->id == $_MIDGARD['user'])
        {
            $resource_array['name'] = $this->_request_data['l10n']->get('me');
            $resource_array['css_class'] = 'blue';
        }

        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_eventmember');

        // Find all events that occur during [$from, $end]
        $qb->begin_group("OR");
            // The event begins during [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.start", ">=", $from);
                $qb->add_constraint("eid.start", "<=", $to);
            $qb->end_group();
            // The event begins before and ends after [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.start", "<=", $from);
                $qb->add_constraint("eid.end", ">=", $to);
            $qb->end_group();
            // The event ends during [$from, $to]
            $qb->begin_group("AND");
                $qb->add_constraint("eid.end", ">=", $from);
                $qb->add_constraint("eid.end", "<=", $to);
            $qb->end_group();
        $qb->end_group();

        $qb->add_constraint('eid.up', '=', (int)$GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id);
        $qb->add_constraint('uid', '=', (int)$resource->id);

        $memberships = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if ($memberships)
        {
            foreach ($memberships as $membership)
            {
                $event = new org_openpsa_calendar_event($membership->eid);

                // Customize label
                $label_field = $this->_config->get('event_label');
                if (!$label_field)
                {
                    $label_field = 'title';
                }
                $label = $event->$label_field;
                if ($label_field == 'creator')
                {
                    $user = $_MIDCOM->auth->get_user($event->creator);
                    $label = $user->name;
                }

                $resource_array['reservations'][$event->id] = array(
                    'name' => $label,
                    'location' => $event->location,
                    'start' => $event->start,
                    'end' => $event->end,
                    'private' => false,
                );

                if ($event->orgOpenpsaAccesstype == ORG_OPENPSA_ACCESSTYPE_PRIVATE)
                {
                    $resource_array['reservations'][$event->id]['css_class'] = ' private';
                    $resource_array['reservations'][$event->id]['private'] = true;
                }
            }
        }

        return $resource_array;
    }

    function _populate_calendar_contacts($from, $to)
    {
        $shown_persons = array();

        $user = $_MIDCOM->auth->user->get_storage();
        if (   $this->_config->get('always_show_self')
            || $user->parameter('org_openpsa_calendar_show', $user->guid))
        {
            // Populate the user himself first, but only if they can create events
            $this->_request_data['calendar']->_resources[$user->guid] = $this->_populate_calendar_resource($user, $from, $to);
        }
        $shown_persons[$user->id] = true;

        $subscribed_contacts = $user->list_parameters('org_openpsa_calendar_show');
        foreach ($subscribed_contacts as $guid => $subscribed)
        {
            $person = new midcom_db_person($guid);
            $this->_request_data['calendar']->_resources[$person->guid] = $this->_populate_calendar_resource($person, $from, $to);
            $shown_persons[$person->id] = true;
        }

        if ($this->_config->get('always_show_group'))
        {
            // Add this group to display as well
            $additional_group = & $_MIDCOM->auth->get_group($this->_config->get('always_show_group'));
            if ($additional_group)
            {
                $members = $additional_group->list_members();
                foreach ($members as $person)
                {
                    if (array_key_exists($person->id, $shown_persons))
                    {
                        continue;
                    }
                    $person_object = $person->get_storage();
                    $this->_request_data['calendar']->_resources[$person_object->guid] = $this->_populate_calendar_resource($person_object, $from, $to);
                    $shown_persons[$person->id] = true;
                }
            }
        }
    }

    function _load_event($guid)
    {
        $event = new org_openpsa_calendar_event($guid);
        if (!is_object($event))
        {
            return false;
        }
        return $event;
    }

    function _handler_month($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (count($args) == 1)
        {
            // Go to the chosen week instead of current one
            // TODO: Check format as YYYY-MM-DD via regexp
            $requested_time = @strtotime($args[0]);
            if ($requested_time)
            {
                $this->_selected_time = $requested_time;
            }
            else
            {
                // We couldn't generate a date
                return false;
            }
        }

        $this->_populate_toolbar('month');
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "week/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('week view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "day/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('day view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        // Instantiate calendar widget
        $this->_request_data['calendar'] = new org_openpsa_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
        $this->_request_data['calendar']->type = ORG_OPENPSA_CALENDARWIDGET_MONTH;
        $this->_request_data['calendar']->cell_height = 100;
        $this->_request_data['calendar']->column_width = 60;

        $previous_month = date('Y-m-d', $this->_request_data['calendar']->get_month_start() - 100);
        $next_month = date('Y-m-d', $this->_request_data['calendar']->get_month_end() + 100);
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'month/' . $previous_month . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('previous'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/up.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'month/' . $next_month . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('next'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/down.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        // Clicking a free slot should bring up "new event" dialogue
        $nap = new midcom_helper_nav();
        $this_node = $nap->get_node($nap->get_current_node());

        $this->_request_data['calendar']->reservation_div_options = array(
            'onClick' => org_openpsa_calendar_interface::calendar_editevent_js('__GUID__', $this_node),
        );
        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            $this->_request_data['calendar']->free_div_options = array(
                'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($this_node, '__START__', '__RESOURCE__'),
            );
        }

        // Populate contacts
        $this->_populate_calendar_contacts($this->_request_data['calendar']->get_month_start(), $this->_request_data['calendar']->get_month_end());

        return true;
    }

    function _show_month($handler_id, &$data)
    {
        $this->_request_data['selected_time'] = $this->_selected_time;
        midcom_show_style("show-month");
    }

    function _handler_week($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (count($args) == 1)
        {
            // Go to the chosen week instead of current one
            // TODO: Check format as YYYY-MM-DD via regexp
            $requested_time = @strtotime($args[0]);
            if ($requested_time)
            {
                $this->_selected_time = $requested_time;
            }
            else
            {
                // We couldn't generate a date
                return false;
            }
        }

        $this->_populate_toolbar('week');
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "month/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('month view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "day/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('day view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        // Instantiate calendar widget
        $this->_request_data['calendar'] = new org_openpsa_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
        // Slots are 2 hours long
        $this->_request_data['calendar']->calendar_slot_length = $this->_config->get('week_slot_length') * 60;
        $this->_request_data['calendar']->start_hour = $this->_config->get('day_start_time');
        $this->_request_data['calendar']->end_hour = $this->_config->get('day_end_time');

        // Clicking a free slot should bring up "new event" dialogue
        $nap = new midcom_helper_nav();
        $this_node = $nap->get_node($nap->get_current_node());
        $this->_request_data['calendar']->reservation_div_options = array(
            'onClick' => org_openpsa_calendar_interface::calendar_editevent_js('__GUID__', $this_node),
        );
        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            $this->_request_data['calendar']->free_div_options = array(
                'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($this_node, '__START__', '__RESOURCE__'),
            );
        }
        //$this->_request_data['calendar']->column_width = 30;

        // Populate contacts
        $this->_populate_calendar_contacts($this->_request_data['calendar']->get_week_start(), $this->_request_data['calendar']->get_week_end());

        $previous_week = date('Y-m-d', $this->_request_data['calendar']->get_week_start() - 100);
        $next_week = date('Y-m-d', $this->_request_data['calendar']->get_week_end() + 100);
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'week/' . $previous_week . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('previous'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/up.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'week/' . $next_week . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('next'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/down.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        return true;
    }

    function _show_week($handler_id, &$data)
    {
        $this->_request_data['selected_time'] = $this->_selected_time;
        midcom_show_style("show-week");
    }

    function _handler_day($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (count($args) == 1)
        {
            // Go to the chosen week instead of current one
            // TODO: Check format as YYYY-MM-DD via regexp
            $requested_time = @strtotime($args[0]);
            if ($requested_time)
            {
                $this->_selected_time = $requested_time;
            }
            else
            {
                // We couldn't generate a date
                return false;
            }
        }

        $this->_populate_toolbar('day');
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "month/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('month view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "week/".$this->_get_datestring().".html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('week view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        // Instantiate calendar widget
        $this->_request_data['calendar'] = new org_openpsa_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
        $this->_request_data['calendar']->type = ORG_OPENPSA_CALENDARWIDGET_DAY;

        // Slots are 2 hours long
        $this->_request_data['calendar']->calendar_slot_length = $this->_config->get('day_slot_length') * 60;
        $this->_request_data['calendar']->start_hour = $this->_config->get('day_start_time');
        $this->_request_data['calendar']->end_hour = $this->_config->get('day_end_time');
        $this->_request_data['calendar']->column_width = 60;

        $previous_day = date('Y-m-d', $this->_request_data['calendar']->get_day_start() - 100);
        $next_day = date('Y-m-d', $this->_request_data['calendar']->get_day_end() + 100);
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'day/' . $previous_day . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('previous'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/up.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'day/' . $next_day . '.html',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('next'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/down.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        // Clicking a free slot should bring up "new event" dialogue
        $nap = new midcom_helper_nav();
        $this_node = $nap->get_node($nap->get_current_node());

        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
            $this->_request_data['calendar']->reservation_div_options = array(
                'onClick' => org_openpsa_calendar_interface::calendar_editevent_js('__GUID__', $this_node),
            );
        }
        $this->_request_data['calendar']->free_div_options = array(
            'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($this_node, '__START__', '__RESOURCE__'),
        );

        // Populate contacts
        $this->_populate_calendar_contacts($this->_request_data['calendar']->get_day_start(), $this->_request_data['calendar']->get_day_end());

        return true;
    }

    function _show_day($handler_id, &$data)
    {
        $this->_request_data['selected_time'] = $this->_selected_time;
        midcom_show_style("show-day");
    }

    //For playing with stuff
    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }

    function _handler_event_new_toolbar()
    {
        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
    }

    function _handler_event_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']);
        debug_push_class(__CLASS__, __FUNCTION__);

        // We're using a popup here
        $_MIDCOM->skip_page_style = true;

        if (count($args) > 0)
        {
            // Hack participant list
            $this->_datamanager->_layoutdb['default']['fields']['participants']['default'] = array();
            $current_user = $_MIDCOM->auth->user->get_storage();
            if (is_object($current_user))
            {
                $this->_datamanager->_layoutdb['default']['fields']['participants']['default'][$current_user->id] = true;
            }

            $resource = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
            if (!is_object($resource))
            {
                $msg = new org_openpsa_helpers_uimessages();
                $msg->add_message(sprintf($this->_request_data['l10n']->get('resource "%s" could not be resolved'), $args[0]), 'warning');
            }
            else
            {
                //Handle various types of resources given
                switch(get_class($resource))
                {
                    case 'midcom_baseclasses_database_person':
                        $this->_datamanager->_layoutdb['default']['fields']['participants']['default'][$resource->id] = true;
                        break;
                    case 'midcom_baseclasses_database_group':
                        $qb =  $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_member');
                        $qb->add_constraint('gid', '=', $resource->id);
                        $grp_members = $_MIDCOM->dbfactory->exec_query_builder($qb);
                        if (   is_array($grp_members)
                            && count($grp_members)>0)
                        {
                            foreach ($grp_members as $member)
                            {
                                if ($member->uid == $current_user->id)
                                {
                                    continue;
                                }
                                $this->_datamanager->_layoutdb['default']['fields']['participants']['default'][$member->uid] = false;
                            }
                        }
                        break;
                    case 'midcom_org_openpsa_salesproject':
                        $qb =  $_MIDCOM->dbfactory->new_query_builder('org_openpsa_sales_salesproject_member');
                        $qb->add_constraint('salesproject', '=', $resource->id);
                        $sp_members = $_MIDCOM->dbfactory->exec_query_builder($qb);
                        if (   is_array($sp_members)
                            && count($sp_members)>0)
                        {
                            foreach ($sp_members as $member)
                            {
                                if ($member->person == $current_user->id)
                                {
                                    continue;
                                }
                                $this->_datamanager->_layoutdb['default']['fields']['participants']['default'][$member->person] = true;
                            }
                        }
                        break;
                    default:
                        /* Now that due to relatedtos we have automatically generated links from whatever objects do not bother the user with this
                        $msg = new org_openpsa_helpers_uimessages();
                        $msg->add_message(sprintf($this->_request_data['l10n']->get('class "%s" has no resource handler'), get_class($resource)), 'warning');
                        */
                        debug_add(sprintf('class "%s" has no resource handler', get_class($resource)), MIDCOM_LOG_WARN);
                        break;
                }
            }

        }
        if (count($args) == 2)
        {
            $this->_selected_time = $args[1];
        }

        if ($this->_selected_time > 0)
        {
            // Hack time fields
            $this->_datamanager->_layoutdb['default']['fields']['start']['default'] = $this->_selected_time;
            $this->_datamanager->_layoutdb['default']['fields']['end']['default'] = $this->_selected_time + 3600;
        }

        if (!$this->_datamanager->init_creation_mode('default',$this,"_creation_dm_callback"))
        {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'default'.");
            // This will exit
        }


        switch ($this->_datamanager->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;

            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                //$this->_request_data['event']->parameter("midcom.helper.datamanager","layout","default");

                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);

                // Relocate to event view
                debug_pop();
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "event/" . $this->_request_data["event"]->id. '/?reload=1');
                //this will exit
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                //$this->_request_data['metadata']->parameter("midcom.helper.datamanager","layout","default");

                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);

                // Relocate to event view
                debug_pop();
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "event/" . $this->_request_data["event"]->id. '/?reload=1');
                //this will exit
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                // Close the popup
                $_MIDCOM->add_jsonload('window.close();');
                debug_pop();
                break;

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                //debug_add("failed hooked, datamanager\n===\n" .  sprint_r($this->_datamanager) . "===\n");
                if (   is_object($this->_dm_createfailed_event)
                    && is_array($this->_dm_createfailed_event->busy_em))
                {
                    debug_add('resource conflict hooked, handling it');
                    $this->_event_resourceconflict_messages(&$this->_dm_createfailed_event);
                    $this->_handler_event_new_toolbar();
                    debug_pop();
                    return true;
                }
                else
                {
                    debug_add('The DM failed critically, see above.');
                    $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_pop();
                    return false;
                }
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }
        $this->_handler_event_new_toolbar();
        debug_pop();
        return true;
    }

    function _event_resourceconflict_messages(&$conflict_event)
    {
        $messenger = new org_openpsa_helpers_uimessages();
        reset($conflict_event->busy_em);
        foreach ($conflict_event->busy_em as $pid => $events)
        {
            $person = new org_openpsa_contacts_person($pid);
            if (   !is_object($person)
                || !$person->id)
            {
                continue;
            }
            debug_add("{$person->name} is busy, adding DM errors");
            reset($events);
            foreach ($events as $eguid)
            {
                //We might need sudo to get the event
                $_MIDCOM->auth->request_sudo();
                $event = new org_openpsa_calendar_event($eguid);
                $_MIDCOM->auth->drop_sudo();
                if (   !is_object($event)
                    || !$event->id)
                {
                    continue;
                }
                //Then on_loaded checks again
                $event->_on_loaded();
                debug_add("{$person->name} is busy in event {$event->title}, appending error\n===\n".sprintf('%s is busy in event "%s" (%s)', $person->name, $event->title, $event->format_timeframe())."\n===\n");
                //TODO: Localize
                $messenger->addMessage(sprintf($this->_request_data['l10n']->get('%s is busy in event \'%s\' (%s)'), $person->name, $event->title, $event->format_timeframe()), 'error');
            }
        }
    }

    function _show_event_new($handler_id, &$data)
    {
        if (   array_key_exists('view', $this->_request_data)
            && $this->_request_data['view'] === 'conflict_handler')
        {
            $this->_request_data['popup_title'] = 'resource conflict';
            midcom_show_style("show-popup-header");
            $this->_request_data['event_dm'] =& $this->_datamanager;
            midcom_show_style("show-event-conflict");
            midcom_show_style("show-popup-footer");
        }
        else
        {
            // Set title to popup
            $this->_request_data['popup_title'] = $this->_request_data['l10n']->get('create event');
            // Show popup
            midcom_show_style("show-popup-header");
            $this->_request_data['event_dm'] =& $this->_datamanager;
            midcom_show_style("show-event-new");
            midcom_show_style("show-popup-footer");
        }
    }

    function _handler_event_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_request_data['view'] = $args[1];

        // Check if we get the event
        if (!$this->_handler_event($handler_id, $args, &$data))
        {
            debug_pop();
            return false;
        }

        // Check if the action is a valid one
        switch ($args[1])
        {
            case "delete":
                $_MIDCOM->auth->require_do('midgard:delete', $this->_request_data['event']);

                $this->_request_data['delete_succeeded'] = false;
                if (array_key_exists('org_openpsa_calendar_deleteok', $_POST))
                {
                    $this->_request_data['delete_succeeded'] = $this->_request_data['event']->delete();
                    if ($this->_request_data['delete_succeeded'])
                    {
                        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.calendar'), $this->_request_data['l10n']->get('event deleted'), 'ok');
                        // Close the popup and refresh main calendar
                        $_MIDCOM->add_jsonload('window.opener.location.reload();window.close();');
                    } else {
                        // Failure, give a message
                        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.calendar'), $this->_request_data['l10n']->get("failed to delete event, reason ") . mgd_errstr(), 'error');
                    }
                    // Update the index
                    $indexer =& $_MIDCOM->get_service('indexer');
                    $indexer->delete($this->_request_data['event']->guid);
                }
                else
                {
                    $this->_view_toolbar->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => 'event/'.$this->_request_data['event']->id.'/',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("cancel"),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                        )
                    );
                }
                debug_pop();
                return true;
            case "edit":
                //debug_add("got POST\n===\n" .  sprint_r($_POST) . "===\n");
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['event']);

                switch ($this->_datamanager->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "default";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        debug_pop();
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        $indexer =& $_MIDCOM->get_service('indexer');
                        $indexer->index($this->_datamanager);

                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "event/" . $this->_request_data["event"]->id. '/?reload=1');
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "event/" . $this->_request_data["event"]->id. '/?reload=1');
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        if (is_array( $this->_request_data['event']->busy_em))
                        {
                            debug_add('resource conflict hooked, handling it');
                            // Add toolbar items
                            org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                            $this->_event_resourceconflict_messages(&$this->_request_data['event']);
                            debug_pop();
                            return true;
                        }
                        else
                        {
                            //Some other error, raise hell.
                            $this->errstr = "Datamanager failed: " . $GLOBALS["midcom_errstr"];
                            $this->errcode = MIDCOM_ERRCRIT;
                            debug_pop();
                            return false;
                        }
                }
                debug_pop();
                return true;
            default:
                debug_pop();
                return false;
        }
        debug_pop();
    }

    function _show_event_action($handler_id, &$data)
    {
        switch ($this->_request_data['view'])
        {
            case 'edit':
                // Set title to popup
                $this->_request_data['popup_title'] = sprintf($this->_request_data['l10n']->get('edit %s'), $this->_request_data['event']->title);

                // Show popup
                midcom_show_style("show-popup-header");
                $this->_request_data['event_dm'] =& $this->_datamanager;
                midcom_show_style("show-event-edit");
                midcom_show_style("show-popup-footer");
                break;
            case 'delete':
                // Set title to popup
                if ($this->_request_data['delete_succeeded'])
                {
                    $this->_request_data['popup_title'] = sprintf($this->_request_data['l10n']->get('event %s deleted'), $this->_request_data['event']->title);
                }
                else
                {
                    $this->_request_data['popup_title'] = $this->_request_data['l10n']->get('delete event');
                }

                // Show popup
                midcom_show_style("show-popup-header");
                $this->_request_data['event_dm'] =& $this->_datamanager;
                midcom_show_style("show-event-delete");
                midcom_show_style("show-popup-footer");
                break;
            case 'conflict_handler':
                $this->_request_data['popup_title'] = 'resource conflict';
                midcom_show_style("show-popup-header");
                $this->_request_data['event_dm'] =& $this->_datamanager;
                midcom_show_style("show-event-conflict");
                midcom_show_style("show-popup-footer");
            break;
        }
    }


    function _handler_event($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // We're using a popup here
        $_MIDCOM->skip_page_style = true;

        // Get the requested document metadata object
        $this->_request_data['event'] = $this->_load_event($args[0]);
        if (!$this->_request_data['event'])
        {
            return false;
        }

        // Muck schema on private events
        if (!$this->_request_data['event']->can_do('org.openpsa.calendar:read'))
        {
            foreach ($this->_datamanager->_layoutdb as $schemaname => $schema)
            {
                foreach ($this->_datamanager->_layoutdb[$schemaname]['fields'] as $fieldname => $field)
                {
                    switch ($fieldname)
                    {
                        case 'title':
                        case 'start':
                        case 'end':
                            break;
                        default:
                            $this->_datamanager->_layoutdb[$schemaname]['fields'][$fieldname]['hidden'] = true;
                    }
                }
            }
        }

        // Load the document to datamanager
        if (!$this->_datamanager->init($this->_request_data['event']))
        {
            return false;
        }

        // Reload parent if needed
        if (array_key_exists('reload',$_GET))
        {
            $_MIDCOM->add_jsonload('window.opener.location.reload();');
        }

        // Add toolbar items
        if ($this->_request_data['view'] == 'default')
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'event/'.$this->_request_data['event']->id.'/edit.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['event']),
                )
            );
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'event/'.$this->_request_data['event']->id.'/delete.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("delete"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:delete', $this->_request_data['event']),
                )
            );
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'javascript:window.print()',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("print"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_OPTIONS  => Array(
                        'rel' => 'directlink',
                    ),
                )
            );

            $user = $_MIDCOM->auth->user->get_storage();
            $relatedto_button_settings = Array(
                'wikinote'      => array(
                    'node'  => false,
                    'wikiword'  => str_replace('/', '-', sprintf($this->_request_data['l10n']->get($this->_config->get('wiki_title_skeleton')), $this->_request_data['event']->title, strftime('%x'), $user->name)),
                ),
            );
            org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_view_toolbar, $this->_request_data['event'], $this->_component, $relatedto_button_settings);
        }
        return true;
    }

    function _show_event($handler_id, &$data)
    {
        if ($handler_id == 'event_view')
        {
            // Set title to popup
            $this->_request_data['popup_title'] = sprintf($this->_request_data['l10n']->get('event %s'), $this->_request_data['event']->title);

            // Show popup
            midcom_show_style('show-popup-header');
            $this->_request_data['event_dm'] =& $this->_datamanager;
            midcom_show_style('show-event');
            midcom_show_style('show-popup-footer');
        }
        else
        {
            midcom_show_style('show-event-raw');
        }
    }

    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        switch($this->_config->get('start_view'))
        {
            case 'day':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'day/' . date('Y-m-d', $this->_selected_time) . '.html');
                // This will exit()
            break;
            case 'month':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'month/' . date('Y-m-d', $this->_selected_time) . '.html');
                // This will exit()
                break;
            default:
            case 'week':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'week/' . date('Y-m-d', $this->_selected_time) . '.html');
                // This will exit()
                break;
        }
    }

}
?>