<?php
/**
 * @package org.openpsa.calendar
 * @author Nemein Oy, http://www.nemein.com/
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wants this class present and QB etc use this, so keep logic here
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_event_member_dba extends __org_openpsa_calendar_event_member_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->eid)
        {
            $event = new org_openpsa_calendar_event_dba($this->eid);
            return $event;
        }
        else
        {
            return $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
        }
    }

    /**
     * Wrapped so we can hook notifications
     */
    function create($notify=true, $event=false)
    {
        $ret = parent::create();
        if (   $ret
            && $notify)
        {
            $this->notify('add', $event);
        }
        return $ret;
    }

    /**
     * Wrapped so we can hook notifications
     */
    function update($notify=true, $event=false)
    {
        if ($notify)
        {
            $this->notify('update', $event);
        }
        return parent::update();
    }

    /**
     * Wrapped so we can hook notifications and also because current core doesn't support deletes
     */
    function delete($notify=true, $event=false)
    {
        if ($notify)
        {
            $this->notify('remove', $event);
        }
        return parent::delete();
    }

    /**
     * The subclasses need to override this method
     */
    function notify($repeat_handler='this', $event=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden in a subclass', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }

    /**
     * return a given person object from cache or DB
     */
    function &get_person_obj_cache($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //If id is 0 or empty abort
        if (!$id)
        {
            debug_pop();
            return false;
        }

        //Get cached person object if present if not get from DB and cache
        if (!array_key_exists('persons_cache', $GLOBALS['midcom_component_data']['org.openpsa.calendar']))
        {
            $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'] = array();
        }
        if (!array_key_exists($id, $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache']))
        {
            $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'][$id] = new org_openpsa_contacts_person($id);
        }
        $person =& $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'][$id];

        debug_pop();
        return $person;
    }

    /**
     * Returns the person this member points to if that person can be used for notifications
     */
    function &get_person_obj()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $person =& $this->get_person_obj_cache($this->uid);

        //We need to have an email which to send to so if no email no point
        if (empty($person->email))
        {
            debug_add('person #'. $person->id . 'has no email address, aborting');
            debug_pop();
            $x = false;
            return $x;
        }

        debug_pop();
        return $person;
    }

    /**
     * Returns the event this eventmember points to
     */
    function get_event_obj()
    {
        $event = new org_openpsa_calendar_event_dba($this->eid);
        return $event;
    }

    function _on_loaded()
    {
        // Make sure we have correct class
        switch($this->orgOpenpsaObtype)
        {
            /* Nowadays in different class
            case ORG_OPENPSA_OBTYPE_EVENTRESOURCE:
                $x =& $this;
                $x = new org_openpsa_calendar_event_resource_dba($this->id);
                break;
            */
            default:
            case ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT:
                $x =& $this;
                $x = new org_openpsa_calendar_event_participant_dba($this->id);
                break;
        }
        return true;
    }

    /**
     * statically called method to find amount (seconds) of free
     * time for person between start and end
     *
     */
    function find_free_times($amount, $person, $start, $end)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        static $event_cache = array();
        $slots = array();
        if (!is_object($person))
        {
            $person = new org_openpsa_contacts_person($person);
        }
        // Get current events for person
        $qb = org_openpsa_calendar_event_participant_dba::new_query_builder();
        $qb->begin_group('OR');
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT);
            $qb->add_constraint('orgOpenpsaObtype', '=', 0);
        $qb->end_group();
        $qb->add_constraint('uid', '=', $person->id);
        // All events that somehow overlap the given time.
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('eid.start', '>=', $start);
                $qb->add_constraint('eid.start', '<=', $end);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('eid.end', '<=', $end);
                $qb->add_constraint('eid.end', '>=', $start);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('eid.start', '<=', $start);
                $qb->add_constraint('eid.end', '>=', $end);
            $qb->end_group();
        $qb->end_group();
        // Order if we're in 1.8
        /*
        $qb->add_order('eid.start', 'ASC');
        $qb->add_order('eid.end', 'ASC');
        */
        $eventmembers = $qb->execute();
        if (!is_array($eventmembers))
        {
            // QB error
            continue;
        }
        $events_by_date = array();
        foreach($eventmembers as $eventmember)
        {
            if (!array_key_exists($eventmember->eid, $event_cache))
            {
                $event_cache[$eventmember->eid] = new org_openpsa_calendar_event_dba($eventmember->eid);
            }
            $event =& $event_cache[$eventmember->eid];
            if (!$event)
            {
                continue;
            }
            $ymd = date('Ymd', $event->start);
            if (array_key_exists($ymd, $events_by_date))
            {
                $events_by_date[$ymd] = array();
            }
            $events_by_date[$ymd][] = $event;
        }
        // Make sure each date between start and end has at least a dummy event
        $stamp = mktime(0, 0, 1, date('m', $start), date('d', $start), date('Y', $start));
        while ($stamp <= $end)
        {
            $ymd = date('Ymd', $stamp);
            debug_add("making sure date {$ymd} has at least one event");
            $stamp = mktime(0, 0, 1, date('m', $stamp), date('d', $stamp)+1, date('Y', $stamp));
            if (array_key_exists($ymd, $events_by_date))
            {
                continue;
            }
            debug_add('none found, adding a dummy one');
            $dummy = new org_openpsa_calendar_event_dba();
            $dummy->start = $stamp;
            $dummy->end = $stamp+1;
            $events_by_date[$ymd] = array($dummy);
        }
        foreach ($events_by_date as $ymd => $events)
        {
            preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ymd, $ymd_matches);
            $date_stamp = mktime(0, 0, 1, (int)$ymd_matches[2], (int)$ymd_matches[3], (int)$ymd_matches[1]);
            // TODO: get from persons data based on events weekday
            // PONDER: What to do with persons that do not have this data defined ??
            /*
            $weekday =
            */
            $workday_starts = 8;
            $workday_ends = 16;
            if (   empty($workday_starts)
                || empty($workday_ends)
                )
            {
                // No work on that day
                continue;
            }

            $workday_starts_ts = mktime($workday_starts, 0, 0, (int)$ymd_matches[2], (int)$ymd_matches[3], (int)$ymd_matches[1]);
            $workday_ends_ts = mktime($workday_ends, 0, 0, (int)$ymd_matches[2], (int)$ymd_matches[3], (int)$ymd_matches[1]);
            $last_end_time = false;
            $last_event = false;
            foreach ($events as $event_key => $event)
            {
                if ($event->end <= $workday_starts_ts)
                {
                    // We need not to consider this event, it ends before we start working
                    unset($events[$event_key]);
                    continue;
                }
                if ($event->start >= $workday_ends_ts)
                {
                    // We need not to consider this event, it starts after we stop working
                    unset($events[$event_key]);
                    continue;
                }
                debug_add("checking event #{$event->id} ({$event->title})");
                if ($last_end_time === false)
                {
                    if ($event->start > $workday_starts_ts)
                    {
                        // First event of the day starts after we have started working, use work start time as last end time.
                        $last_end_time = $workday_starts_ts;
                    }
                    else
                    {
                        // Make the first event of the day the last end time and skip rest of the checks
                        $last_end_time = $event->end;
                        // PHP5-TODO: Must be copy by value
                        $last_event = $event;
                        continue;
                    }
                }
                $diff = $event->start - $last_end_time;
                if ($diff >= $amount)
                {
                    // slot found
                    $slot = array
                    (
                        'start' => $last_end_time,
                        'end' => $event->start,
                        // PHP5-TODO: These must be copy-by-value
                        'previous' => $last_event,
                        'next' => $event,
                    );
                    // PHP5-TODO: This must be copy-by-value
                    $slots[] = $slot;
                }
                $last_end_time = $event->end;
                $last_event = $event;
            }
            // End of day slot
            if ($last_end_time === false)
            {
                $last_end_time = $workday_starts_ts;
            }
            if (   $last_end_time < $workday_ends_ts
                && (($workday_ends_ts- $last_end_time) >= $amount))
            {
                $slot = array
                (
                    'start' => $last_end_time,
                    'end' => $workday_ends_ts,
                    // PHP5-TODO: These must be copy-by-value
                    'previous' => $last_event,
                    'next' => false,
                );
                // PHP5-TODO: This must be copy-by-value
                $slots[] = $slot;
            }
        }

        debug_pop();
        return $slots;
    }
}

?>