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

    function net_nemein_repeathandler(&$master_event)
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
            return mgd_get_event();
        }
        elseif (mgd_is_guid($guid))
        {
            return mgd_get_object_by_guid($guid);
        }
        else
        {
            return mgd_get_event($guid);
        }
    }

    /**
     * Delete all old repeating instances of the event
     */
    function delete_stored_repeats($guid)
    {
        // Find all events sharing the master event
        $query = new MidgardQueryBuilder('NewMidgardParameter');
        $query->add_constraint('tablename', '=', $this->master_event->__table__);
        $query->add_constraint('domain', '=', 'net.nemein.repeathandler');
        $query->add_constraint('name', '=', 'master_guid');
        $query->add_constraint('value', '=', $guid);
        $res = @$query->execute();
        if (   is_array($res)
            && count($res) > 0)
        {
            foreach ($res as $parameter)
            {
                $event = $this->_get_object($parameter->oid);

                $instance_matched = false;

                // Delete the event
                midcom_helper_purge_object($event->guid());
            }
        }
    }

    /**
     * Create new event object from an instance array
     * @param Array $instance Repeating instance returned by calculation
     * @return string GUID of the created instance
     */
    function create_event_from_instance($instance, $previous_guid = '')
    {
        $event = $this->_get_object();
        $event->start = $instance['start'];
        $event->end = $instance['end'];
        $event->up = $this->master_event->up;
        $stat = $event->create();

        if ($stat)
        {
            $event = $this->_get_object($stat);

            // Store order in the repeating situation
            $event->parameter('net.nemein.repeathandler', 'master_guid', $this->master_event->guid());

            if ($previous_guid)
            {
                $event->parameter('net.nemein.repeathandler', 'previous_guid', $previous_guid);
                $previous_event = $this->_get_object($previous_guid);
            }
            else
            {
                // This is the first repeating instance
                $previous_guid = $this->master_event->guid();
                $event->parameter('net.nemein.repeathandler', 'previous_guid', $previous_guid);
                $previous_event = &$this->master_event;
            }
            $previous_event->parameter('net.nemein.repeathandler', 'next_guid', $event->guid());

            /* TODO: NemeinCal compatibility, remove
            $event->repeat_prev = $previous_guid;
            $event->update();

            $previous_event->repeat_next = $event->guid();
            $previous_event->update();
            $event = $this->_get_object($event->guid());
            */

            // Copy properties
            $this->_copy_event_properties(&$event);

            // Return GUID for usage in next event
            $event = $this->_get_object($event->guid());
            //print_r($event);
            return $event->guid();
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
                'title' => '',                'creator' => 1,                'description' => '',                'type' => 0,                'busy' => 0,
            );
        }

        $skip_properties = array(
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

        // Copy parameters
        $query = new MidgardQueryBuilder('NewMidgardParameter');
        $query->add_constraint('tablename', '=', $this->master_event->__table__);
        $query->add_constraint('oid', '=', $this->master_event->id);
        $res = @$query->execute();
        if (   is_array($res)
            && count($res) > 0)
        {
            foreach ($res as $parameter)
            {
                if ($parameter->domain != 'net.nemein.repeathandler')
                {
                    // Don't copy parameters containing repeat information
                    $target->parameter($parameter->domain, $parameter->name, $parameter->value);
                }
            }
        }

        // Update the target object
        return $target->update();
    }

    /**
     * Prepare an event for deletion
     */
    function prepare_deletion()
    {
        // Check if we're in a repeating entry
        $next_event_guid = $this->master_event->parameter('net.nemein.repeathandler', 'next_guid');
        $previous_event_guid = $this->master_event->parameter('net.nemein.repeathandler', 'previous_guid');

        if (   $next_event_guid
            && $previous_event_guid)
        {
            // Connect next and previous event together
            $next_event = mgd_get_object_by_guid($next_event_guid);
            $previous_event = mgd_get_object_by_guid($previous_event_guid);
            $next_event->parameter('net.nemein.repeathandler', 'previous_guid', $previous_event_guid);
            $previous_event->parameter('net.nemein.repeathandler', 'next_guid', $next_event_guid);
        }
        elseif ($next_event_guid)
        {
            // Remove "previous event" connection
            $next_event = mgd_get_object_by_guid($next_event_guid);
            $next_event->parameter('net.nemein.repeathandler', 'previous_guid', '');
        }
        elseif ($previous_event_guid)
        {
            // Remove "previous event" connection
            $previous_event = mgd_get_object_by_guid($previous_event_guid);
            $previous_event->parameter('net.nemein.repeathandler', 'next_guid', '');
        }
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
        $event = mgd_get_event($event_id);

        $extra_params = @unserialize($event->extra);
        if (!is_array($extra_params))
        {
            return false;
        }

        foreach ($extra_params as $key => $value)
        {
            if ($key == 'repeat_prev')
            {
                $event->parameter('net.nemein.repeathandler', 'previous_guid', $value);
            }

            if ($key == 'repeat_next')
            {
                $event->parameter('net.nemein.repeathandler', 'next_guid', $value);
            }

            if ($key == 'location')
            {
                $event->parameter('midcom.helper.datamanager', 'data_location', $value);
            }
        }

        return true;
    }
}
?>