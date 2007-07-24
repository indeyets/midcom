<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for org.maemo.calendar
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_index  extends midcom_baseclasses_components_handler 
{

    var $_selected_time = null;
    var $_calendar_type = ORG_MAEMO_CALENDARWIDGET_WEEK;
    
    /**
     * Current users object
     */
    var $current_user = null;
    
    var $layer_data = array();

    var $user_tags = array();
    var $default_tag = array();

    var $_approved_buddies = array();
    var $_all_buddies = array();
    var $_pending_buddy_requests = array();
    
    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_index()
    {
        parent::midcom_baseclasses_components_handler();

        $this->_selected_time = time();
        
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
        $this->current_user = $_MIDCOM->auth->user->get_storage();
        $this->layer_data = array( 'calendars' => array(), 'busy' => array() );
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "org.maemo.calendar";
        
        $this->_calendar_type = $this->_config->get('default_view');
        
        $this->_update_breadcrumb_line($handler_id);
        
        $title = $this->_request_data['l10n']->get('calendar_title');
        $view_string = $this->_request_data['l10n']->get('current view') . ': ' . org_maemo_calendarwidget::get_type_name($this->_calendar_type);
        $_MIDCOM->set_pagetitle(" :: {$title} - {$view_string} ");
        
        $this->_init_calendar();
        $this->_init_panel();

        return true;
    }
    
    function _init_calendar()
    {
        $this->_request_data['maemo_calender'] = new org_maemo_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
        
        //$this->_request_data['maemo_calender']->type = $this->_calendar_type;
        $this->_request_data['maemo_calender']->set_type($this->_calendar_type);
        
        $this->_request_data['maemo_calender']->start_hour = $this->_config->get('day_start_hour');
        $this->_request_data['maemo_calender']->end_hour = $this->_config->get('day_end_hour');
                
        switch ($this->_calendar_type)
        {
            case ORG_MAEMO_CALENDARWIDGET_WEEK:
                $this->_request_data['maemo_calender']->cell_height = $this->_config->get('week_row_height');
                $this->_request_data['maemo_calender']->cell_height_unit = $this->_config->get('week_row_unit');
                $this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('week_slot_length') * 60;
                $script = '$j("div.calendar-timeline-holder")[0].scrollTop = calendar_config["start_hour_x"];' ."\n";
                $script .= '$j("body").attr("class", "week");' ."\n";
                $_MIDCOM->add_jquery_state_script($script);
            break;
            case ORG_MAEMO_CALENDARWIDGET_MONTH:
                $this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('month_slot_length') * 60;
                $this->_request_data['maemo_calender']->column_width = $this->_config->get('month_column_width');
                $script = '$j("body").attr("class", "month");' ."\n";
                $_MIDCOM->add_jquery_state_script($script);
            break;
        }
        
        $this->_fetch_calendars();
        
        $this->_request_data['maemo_calender']->add_calendar_layers($this->layer_data);
        
        // $this->_request_data['maemo_calender']->type = ORG_MAEMO_CALENDARWIDGET_WEEK;
        // $this->_request_data['maemo_calender']->calendar_slot_length = 1800; // 30mins
        // $this->_request_data['maemo_calender']->type = ORG_MAEMO_CALENDARWIDGET_MONTH;
        // $this->_request_data['maemo_calender']->column_width = 14;
        
        $slh = 3600 / $this->_request_data['maemo_calender']->calendar_slot_length;
        $scrollTop = $this->_request_data['maemo_calender']->cell_height * ($this->_request_data['maemo_calender']->start_hour * $slh);
        
        $class_names = array(
            0 => 'undefined',
            ORG_MAEMO_CALENDARWIDGET_YEAR => 'year',
            ORG_MAEMO_CALENDARWIDGET_MONTH => 'month',
            ORG_MAEMO_CALENDARWIDGET_WEEK => 'week',
            ORG_MAEMO_CALENDARWIDGET_DAY => 'day',                              
        );
        
        $script = 'const MIDCOM_STATIC_URL = "' . MIDCOM_STATIC_URL . '";'."\n";
        $script .= 'const APPLICATION_PREFIX = "' . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . '";'."\n";
        $script .= 'var calendar_config = {'."\n";
        $script .= ' type: ' . $this->_request_data['maemo_calender']->type . ',' ."\n";
        $script .= ' start_hour_x: ' . $scrollTop . ',' ."\n";
        $script .= ' timestamp: ' . $this->_selected_time . ','."\n";
        $script .= ' types_classes: [\'' . implode("','",$class_names) . '\']' . "\n";
        $script .= '};'."\n";
        $script .= 'var shelf_contents = Array();'."\n";
        $script .= 'var active_shelf_item = false;'."\n";
        $_MIDCOM->add_jscript($script,"",true);
        
        return true;        
    }
    
    function _init_panel()
    {
        $this->_request_data['panel'] = new org_maemo_calendarpanel();
        
        $calendar_leaf = new org_maemo_calendarpanel_calendar_leaf($this->_request_data['maemo_calender']);
        $buddylist_leaf = new org_maemo_calendarpanel_buddylist_leaf();
        $profile_leaf = new org_maemo_calendarpanel_profile_leaf();
        $shelf_leaf = new org_maemo_calendarpanel_shelf_leaf();
                
        $calendar_leaf->add_calendars(&$this->layer_data['calendars']);
        $buddylist_leaf->add_buddies(&$this->_all_buddies);
        $profile_leaf->set_schemadb(
            midcom_helper_datamanager2_schema::load_database( $this->_config->get('profile_schemadb') ),
            $this->_config->get('profile_schema')
        );
        $profile_leaf->set_person(&$this->current_user);
        $buddylist_leaf->add_penging_buddies(&$this->_pending_buddy_requests);
                
        $this->_request_data['panel']->add_leaf('calendar', &$calendar_leaf);
        $this->_request_data['panel']->add_leaf('buddylist', &$buddylist_leaf);
        $this->_request_data['panel']->add_leaf('profile', &$profile_leaf);
        $this->_request_data['panel']->add_leaf('shelf', &$shelf_leaf);
    }
    
    function _fetch_calendars()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->user_tags = org_maemo_calendar_common::get_users_tags();
        
        $_MIDCOM->componentloader->load_graceful('net.nemein.tag');
        $persons = $this->_get_persons();
        $all_events = $this->_get_users_events($persons, $this->_request_data['maemo_calender']->from_date, $this->_request_data['maemo_calender']->to_date);

        $this->_create_default_calendars();
        
        foreach($all_events as $guid => $event)
        {
            $this->_parse_event($event);
        }
        
        //print_r($this->layer_data);
        
        debug_pop();
    }
    
    function _create_default_calendars()
    {
        $default_calendar_id = $this->current_user->guid;
        $default_calendar_name = $this->current_user->name;

        if (! isset($this->layer_data['calendars'][$default_calendar_id]))
        {
            $this->layer_data['calendars'][$default_calendar_id] = array(
                'events' => array(),
                'tags' => $this->user_tags,
                'owner' => $this->current_user,
                'name' => $default_calendar_name,
                'color' => org_maemo_calendar_common::fetch_user_calendar_color()
            );
        }
        if (!isset($this->layer_data['busy'][$default_calendar_id]))
        {
            $this->layer_data['busy'][$default_calendar_id] = array();
        }        
        
        foreach ($this->_approved_buddies as $person_id => $person)
        {
            $calendar_id = $person->guid;
            $calendar_name = $person->name;
            
            $public_tags = org_maemo_calendar_common::fetch_available_user_tags($person->guid, true);

            $calendar_color = org_maemo_calendar_common::fetch_user_calendar_color($person->guid);
            
            if (! isset($this->layer_data['calendars'][$calendar_id]))
            {
                $this->layer_data['calendars'][$calendar_id] = array(
                    'events' => array(),
                    'tags' => $public_tags,
                    'owner' => $person->guid,
                    'name' => $calendar_name,
                    'color' => $calendar_color
                );
            }
            if (!isset($this->layer_data['busy'][$calendar_id]))
            {
                $this->layer_data['busy'][$calendar_id] = array();
            }                       
        }
    }
    
    function _parse_event(&$event)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for #{$event->id} ({$event->title})");
        
        if (class_exists('net_nemein_tag_handler'))
        {
            $tags = net_nemein_tag_handler::get_object_tags($event);
            debug_print_r('Tags before', $tags);
            
            /*
             * Make sure we put the event to our Calendar if we own or are participant in it.
             */
            if (   array_key_exists($this->current_user->id, $event->participants)
                ) //|| $event->metadata->creator == $this->current_user->guid
            {
                $default_calendar_id = $this->current_user->guid;
                
                if ( empty($tags))
                {
                    $tag_string = $this->user_tags[0]['id'] . ' ';
                    $tag_array = net_nemein_tag_handler::string2tag_array($tag_string);
                    $tag_added = net_nemein_tag_handler::tag_object($event,$tag_array);
                    if (!$tag_added)
                    {
                        debug_add("Failed adding tag '{$this->user_tags[0]['id']}' to event #{$event->id} ({$event->title})");
                    }
                    else
                    {
                        debug_add("Successfully added tag '{$this->user_tags[0]['id']}' to event #{$event->id} ({$event->title})");
                    }                   
                }

                $this->layer_data['calendars'][$default_calendar_id]['events'][] = $event;

                $this->layer_data['busy'][$default_calendar_id][$event->guid] = array( 'start' => $event->start, 'end' => $event->end );
            }
            else
            {               
                foreach ($this->_approved_buddies as $person_id => $person)
                {
                    $calendar_id = $person->guid;
                    
                    if (array_key_exists($person_id, $event->participants))
                    {
                        $this->layer_data['calendars'][$calendar_id]['events'][] = $event;
                        $this->layer_data['busy'][$calendar_id][$event->guid] = array( 'start' => $event->start, 'end' => $event->end );                        
                    }
                }               
            }
            
            $tags = net_nemein_tag_handler::get_object_tags($event);
            debug_print_r('final tags', $tags);
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load required handler 'net_nemein_tag_handler'");           
        }
        
        debug_pop();
    }

    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    function _get_persons()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret = array();
        
        $this->_get_buddies();
        foreach ($this->_approved_buddies as $person_id => $person)
        {
            $ret[] = $person_id;
        }
                
        $ret[] = $this->current_user->id;
        
        $person_count = count($ret);
        debug_add("Persons {$person_count}");       
        
        debug_pop();
        
        return $ret;
    }
    
    function _get_buddies()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        //$this->_dummy_add_buddy('a9215ef4304c11dc85400b8f9328cb19cb19');

        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $this->current_user->guid);
        $qb->add_constraint('isapproved', '=', true);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies_qb = $qb->execute();

        foreach ($buddies_qb as $buddy)
        {
            $person = new midcom_db_person($buddy->buddy);
            if ($person)
            {
                $this->_approved_buddies[$person->id] = $person;
            }
        }
        
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $this->current_user->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies_qb = $qb->execute();

        foreach ($buddies_qb as $buddy)
        {
            $person = new midcom_db_person($buddy->buddy);
            if ($person)
            {
                $this->_all_buddies[$person->id] = $person;
            }
        }
        
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('buddy', '=', $this->current_user->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $qb->add_constraint('isapproved', '=', false);
        $buddies_qb = $qb->execute();

        foreach ($buddies_qb as $buddy)
        {
            $person = new midcom_db_person($buddy->account);
            if ($person)
            {
                $this->_pending_buddy_requests[$person->id] = $person;
            }
        }        
        
        debug_pop();
    }

    function _get_users_events($user_ids, $from, $to)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Querying events on {$from} - {$to}");
        debug_print_r('For users', $user_ids);
        
        $events = array();

        $qb = org_openpsa_calendar_eventmember::new_query_builder();

        // Find all events that occur during [$from, $to]
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

        $qb->add_constraint('eid.up', '=', $this->_request_data['root_event_id']);
        $qb->add_constraint('uid', 'IN', $user_ids);

        $qb->add_order('eid.start', 'ASC');

        mgd_debug_start();
        $memberships = $qb->execute();
        mgd_debug_stop();
        unset($qb);
        if (empty($memberships))
        {
            debug_add('QB returned empty resultset');
            debug_pop();
            return $events;
        }
        $seen_events = array();
        foreach ($memberships as $membership)
        {
            if (isset($seen_events[$membership->eid]))
            {
                debug_add("Ran into already seen event #{$membership->eid}, skipping");
                continue;
            }
            $event = new org_maemo_calendar_event($membership->eid);
            if (!$event)
            {
                debug_add("Could not instantiate event #{$membership->eid}", MIDCOM_LOG_ERROR);
                // TODO: ERROR HANDLING
                continue;
            }
            $seen_events[$membership->eid] = true;
            
            // FILL return array
            $events[$event->guid] = $event;
            debug_add("Added event #{$event->id} to return array as key '{$event->guid}'");
        }
        
        debug_pop();
        
        return $events;
    }
    
    
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
