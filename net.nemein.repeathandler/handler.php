<?php
/**
 * Calendar repeating event handler
 *
 * @package net.nemein.repeathandler
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id$
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar repeating event handler
 *
 * @package net.nemein.repeathandler
 */
class net_nemein_repeathandler extends midcom_baseclasses_components_purecode
{
    /**
     * Name of the event class to process
     * @var string
     */
    var $_classname = '';

    /**
     * Master event object
     */
    var $master_event = null;

    function __construct(&$master_event)
    {
        $this->master_event = &$master_event;

        // Class name is used for creating new instances
        $this->_classname = get_class($master_event);
    }

    /**
     * Wrapper for MidCOM DBA or Classic API
     * Override in implementation as needed
     */
    function _get_object($guid = null)
    {
        if (is_null($guid))
        {
            return new org_openpsa_calendar_event();
        }

        return new org_openpsa_calendar_event($guid);
    }

    /**
     * Delete all old repeating instances of the event
     *
     * @return void
     * @param string $guid  GUID of the master event
     */
    function delete_stored_repeats($guid)
    {
        if (version_compare(mgd_version(), '1.8', '>='))
        {
            $qb = org_openpsa_calendar_event::new_query_builder();
            $qb->add_constraint('parameter.domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('parameter.name', '=', 'master_guid');
            $qb->add_constraint('parameter.value', '=', $guid);

            $results = $qb->execute_unchecked();
        }
        else
        {
            $qb = new midgard_query_builder('midgard_parameter');
            $qb->add_constraint('domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('name', '=', 'master_guid');
            $qb->add_constraint('value', '=', $guid);
            $qb->add_constraint('tablename', '=', 'event');

            $results = array ();

            foreach (@$qb->execute() as $parameter)
            {
                $results[] = new org_openpsa_calendar_event($parameter->oid);
            }
        }

        foreach ($results as $event)
        {
            // Don't purge the master event object
            if ($event->guid === $guid)
            {
                continue;
            }

            midcom_helper_purge_object($event->guid);
        }
    }

    /**
     * Create new event object from an instance array
     * @param Array $instance Repeating instance returned by calculation
     * @return string GUID of the created instance
     */
    function create_event_from_instance($instance, $previous_guid = '')
    {
        $event = new org_openpsa_calendar_event();

        $event->start = $instance['start'];
        $event->end = $instance['end'];
        $event->up = $this->master_event->up;
        $event->title = $this->master_event->title;
        $event->description = $this->master_event->description;
        $event->type = $this->master_event->type;

        if ($event->create())
        {
            // Store order in the repeating situation
            $event->set_parameter('net.nemein.repeathandler', 'master_guid', $this->master_event->guid);

            if ($previous_guid)
            {
                $event->set_parameter('net.nemein.repeathandler', 'previous_guid', $previous_guid);
                $previous_event = $this->_get_object($previous_guid);
            }
            else
            {
                // This is the first repeating instance
                $previous_guid = $this->master_event->guid;
                $event->set_parameter('net.nemein.repeathandler', 'previous_guid', $previous_guid);
                $previous_event = &$this->master_event;
            }

            $previous_event->parameter('net.nemein.repeathandler', 'next_guid', $event->guid);

            /* TODO: NemeinCal compatibility, remove
            $event->repeat_prev = $previous_guid;
            $event->update();

            $previous_event->repeat_next = $event->guid;
            $previous_event->update();
            $event = $this->_get_object($event->guid);
            */

            // Copy properties
            $this->_copy_event_properties(&$event);

            // Return GUID for usage in next event
            $event = $this->_get_object($event->guid);
            //print_r($event);
            return $event->guid;
        }
        else
        {
            return false;
        }
    }

    /**
     * Copy properties of the master event to a repeating instance
     * @param object $target Object to copy properties to
     * @return boolean
     */
    function _copy_event_properties(&$target)
    {
        $vars = get_class_vars($this->_classname);

        if (count($vars) == 0)
        {
            // Legacy object vars are not available on PHP level
            $vars = Array(
                'title' => '',
                'creator' => 1,
                'description' => '',
                'type' => 0,
                'busy' => 0,
            );
        }

        $skip_properties = array
        (
            'start',
            'end',
            'guid',
            'id',
            /* TODO: Legacy NemeinCalendar properties, remove
            'repeat_next',
            'repeat_prev',
            'repeat_rule',
            'vCal_GUID',
            'vCal_variables',
            */
        );

        // Copy regular properties
        foreach ($vars as $property => $default_value)
        {
            // TODO: Check that no unwanted properties get copied
            if (!in_array($property, $skip_properties))
            {
                // echo "Setting {$property} to {$this->master_event->$property}<br />\n";
                $target->$property = $this->master_event->$property;
            }
        }

        foreach ($this->master_event->list_parameters() as $domain => $array)
        {
            if ($domain !== 'net.nemein.repeathandler')
            {
                continue;
            }

            foreach ($array as $name => $value)
            {
                $target->set_parameter($domain, $name, $value);
            }
        }

        // Update the target object
        return $target->update();
    }

    /**
     * Prepare an event for deletion
     *
     * @return boolean
     */
    function prepare_deletion()
    {
        // Check if we're in a repeating entry
        $next_event_guid = $this->master_event->get_parameter('net.nemein.repeathandler', 'next_guid');
        $previous_event_guid = $this->master_event->get_parameter('net.nemein.repeathandler', 'previous_guid');

        if (   $next_event_guid
            && $previous_event_guid)
        {
            // Connect next and previous event together
            $next_event = new org_openpsa_calendar_event($next_event_guid);
            $previous_event = new org_openpsa_calendar_event($previous_event_guid);
            $next_event->set_parameter('net.nemein.repeathandler', 'previous_guid', $previous_event_guid);
            $previous_event->set_parameter('net.nemein.repeathandler', 'next_guid', $next_event_guid);
            return true;
        }

        if ($next_event_guid)
        {
            // Remove "previous event" connection
            $next_event = new org_openpsa_calendar_event($next_event_guid);
            $next_event->set_parameter('net.nemein.repeathandler', 'previous_guid', '');
            return true;
        }

        if ($previous_event_guid)
        {
            // Remove "previous event" connection
            $previous_event = new org_openpsa_calendar_event($previous_event_guid);
            $previous_event->set_parameter('net.nemein.repeathandler', 'next_guid', '');
            return true;
        }

        return false;
    }

    /**
     * Convert a legacy NemeinCalendar 1.x event object to the new format
     *
     * Old NemeinCalendar serialized lots of attributes as an associative array to the extra field
     * The keys included:
     * location, repeat_rule, repeat_prev, repeat_next, vCal_GUID, vCal_variables, task_resources, tentative,
     * sendNotify, vCal_parameters, vCal_external_attendees, cached_guid
     *
     * We currently use only the repeat_next and repeat_prev of these
     *
     * @param object $event NemeinCalendar_event object
     */
    function convert_legacy_event($event_id)
    {
        $event = new org_openpsa_calendar_event($event_id);

        $extra_params = @unserialize($event->extra);
        if (!is_array($extra_params))
        {
            return false;
        }

        foreach ($extra_params as $key => $value)
        {
            if ($key === 'repeat_prev')
            {
                $event->set_parameter('net.nemein.repeathandler', 'previous_guid', $value);
            }

            if ($key === 'repeat_next')
            {
                $event->set_parameter('net.nemein.repeathandler', 'next_guid', $value);
            }

            if ($key === 'location')
            {
                $event->set_parameter('midcom.helper.datamanager', 'data_location', $value);
            }
        }

        return true;
    }

    /**
     * Get the repeat rules from an event
     *
     * @access public
     * @static
     */
    function get_repeat_rules($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $event = new org_openpsa_calendar_event($guid);

        if (   !$event
            || !$event->guid)
        {
            return false;
        }

        // Initialize the rules set
        $rules = array
        (
            'from' => null,
            'to' => null,
            'type' => null,
            'interval' => 1,
            'num' => 1,
            'end_type' => null,
            'days' => array (),
        );

        // Get the repeat rule type
        // Available options are 'daily', 'weekly', 'weekly_by_day', 'monthly_by_dom'
        $type = $event->get_parameter('net.nemein.repeathandler', 'rule.type');

        if ($event->get_parameter('net.nemein.repeathandler', 'rule.interval'))
        {
            $rules['interval'] = $event->get_parameter('net.nemein.repeathandler', 'rule.interval');
        }

        $master_guid = $event->get_parameter('net.nemein.repeathandler', 'master_guid');

        if (!$master_guid)
        {
            debug_add('GUID of the master event not set!', MIDCOM_LOG_ERROR);
            debug_pop();
            return $rules;
        }

        $master = new org_openpsa_calendar_event($master_guid);

        if (!$type)
        {
            debug_add('No rules found, return an empty set');
            debug_pop();
            return array ();
        }

        $rules['type'] = $type;

        if (   !$master
            || !$master->guid)
        {
            debug_add('Master event probably deleted, cannot continue!', MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not get the master event for the repeat rule');
            // This will exit
        }

        $rules['from'] = date('Y-m-d', net_nemein_repeathandler::get_repeat_start($master_guid));
        $rules['to'] = date('Y-m-d', net_nemein_repeathandler::get_repeat_end($master_guid));

        if ($type === 'weekly_by_day')
        {
            $rules['days'] = unserialize($master->get_parameter('net.nemein.repeathandler', 'rule.days'));
        }

        if ($master->get_parameter('net.nemein.repeathandler', 'rule.num'))
        {
            $rules['end_type'] = 'num';
        }

        if (version_compare(mgd_version(), '1.8', '>='))
        {
            $qb = org_openpsa_calendar_event::new_query_builder();
            $qb->add_constraint('parameter.domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('parameter.name', '=', 'master_guid');
            $qb->add_constraint('parameter.value', '=', $master_guid);
            $rules['num'] = $qb->count();
        }
        else
        {
            $qb = new midgard_query_builder('midgard_parameter');
            $qb->add_constraint('domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('name', '=', 'master_guid');
            $qb->add_constraint('value', '=', $master_guid);
            $qb->add_constraint('tablename', '=', 'event');
            $rules['num'] = $qb->count();
        }

        return $rules;
    }

    /**
     * Get repeat rule start time
     *
     * @access public
     * @static
     */
    function get_repeat_start($master_guid)
    {
        if (version_compare(mgd_version(), '1.8', '>='))
        {
            $start = 0;
            $qb = org_openpsa_calendar_event::new_query_builder();
            $qb->add_constraint('parameter.domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('parameter.name', '=', 'master_guid');
            $qb->add_constraint('parameter.value', '=', $master_guid);
            $qb->add_order('start');
            $qb->set_limit(1);

            foreach ($qb->execute_unchecked() as $event)
            {
                $start = $event->start;
            }
        }
        else
        {
            $qb = new midgard_query_builder('midgard_parameter');
            $qb->add_constraint('domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('name', '=', 'master_guid');
            $qb->add_constraint('value', '=', $master_guid);
            $qb->add_constraint('tablename', '=', 'event');

            $results = array ();

            foreach (@$qb->execute() as $parameter)
            {
                $event = new org_openpsa_calendar_event($parameter->oid);

                if (   !isset($start)
                    || $start > $event->start)
                {
                    $start = $event->start;
                }
            }
        }

        return $start;
    }

    /**
     * Get repeat rule end time
     *
     * @access public
     * @static
     */
    function get_repeat_end($master_guid)
    {
        if (version_compare(mgd_version(), '1.8', '>='))
        {
            $qb = org_openpsa_calendar_event::new_query_builder();
            $qb->add_constraint('parameter.domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('parameter.name', '=', 'master_guid');
            $qb->add_constraint('parameter.value', '=', $master_guid);
            $qb->add_order('end', 'DESC');
            $qb->set_limit(1);

            $end = 0;

            foreach ($qb->execute_unchecked() as $event)
            {
                $end = $event->end;
            }
        }
        else
        {
            $qb = new midgard_query_builder('midgard_parameter');
            $qb->add_constraint('domain', '=', 'net.nemein.repeathandler');
            $qb->add_constraint('name', '=', 'master_guid');
            $qb->add_constraint('value', '=', $master_guid);
            $qb->add_constraint('tablename', '=', 'event');

            $results = array ();

            foreach (@$qb->execute() as $parameter)
            {
                $event = new org_openpsa_calendar_event($parameter->oid);

                if (   !isset($end)
                    || $end < $event->end)
                {
                    $end = $event->end;
                }
            }
        }

        return $end;
    }
}
?>