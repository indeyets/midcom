<?php
/**
 * @package net.nemein.repeathandler
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id$
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar repeating event handler
 */
class net_nemein_repeathandler_calculator extends midcom_baseclasses_components_purecode
{
    /**
     * Repeating rules
     * @var Array
     */
    var $rule = Array();
    
    /**
     * Master event object
     */
    var $master_event = null;
    
    function net_nemein_repeathandler_calculator(&$master_event, $repeat_rule)
    {
        $this->master_event = &$master_event;
        $this->rule = $repeat_rule;
    }
    
    function event2instance($event)
    {
        $instance = array
        (
            'guid'  => $event->guid,
            'start' => $event->start,
            'end'   => $event->end,
        );
        return $instance;
    }

    /**
     * Calculate the days where this repeat rule would place instances
     *
     * Instances are in array format sorted by date:
     * YYYY-MM-DD = array
     * (
     *   'start' => timestamp,
     *   'end'   => timestamp,
     * );
     */
    function calculate_instances()
    {
        $instances = array();
        
        $instances_calculated = false;
        
        // Start calculating from the master event
        $instances[date('Y-m-d', $this->master_event->start)] = $this->event2instance($this->master_event);
        $previous_instance = $instances[date('Y-m-d', $this->master_event->start)];
        
        $i = 0;
        
        while (!$instances_calculated)
        {
            if (   array_key_exists('num', $this->rule)
                && !is_null($this->rule['num'])
                && count ($instances) >= $this->rule['num'])
            {
                // We've now generated enough instances to fulfill the rule.
                // Stop calculating here.
                $instances_calculated = true;
                break;
            }
            
            // Figure out which calculator method to use based on repeat rule type
            switch ($this->rule['type'])
            {
                case 'daily':
                    $next_instance = $this->_get_next_instance_for_days($previous_instance);
                    break;
                    
                case 'weekly':
                    $next_instance = $this->_calculate_instances_for_days($previous_instance, 7);
                    break;
                    
                case 'weekly_by_day':                    
                    if (   !array_key_exists('days', $this->rule)
                        || !is_array($this->rule['days'])
                        || count($this->rule['days']) < 1)
                    {
                        // No days have been defined for this repeating handler
                        // Therefore, there are no instances except the first one either
                        return $instances;
                    }
                    $next_instance = $this->_get_next_instance_for_weekdays($previous_instance);
                    break;
                    
                case 'monthly_by_dom':
                    $next_instance = $this->_get_next_instance_for_date_in_month($previous_instance);
                    break;
                    
                default:
                    // Unknown repeat type
                    return $instances;
            }
            
            if (   array_key_exists('to', $this->rule)
                && !is_null($this->rule['to'])
                && $next_instance['end'] > $this->rule['to'])
            {
                // We're now generated enough instances to fulfill the rule.
                // Don't add this instance to the array.                
                $instances_calculated = true;
                break;
            }
            
            $instances[date('Y-m-d', $next_instance['start'])] = $next_instance;
            $previous_instance = $next_instance;
            
            $i++;
        }
        
        return $instances;        
    }
    
    /**
     * Calculate next instance for daily events and weekly events that occur only on one day in the week
     * @param Array $previous_instance The previous occurrence of the event
     * @param integer $default_interval Default interval for the events. 7 for weekly repeating.
     * @return Array Calculated next instance
     */
    function _get_next_instance_for_days($previous_instance, $default_interval = 1)
    {
        $interval = $this->rule['interval'] * $default_interval;
        
        // Use mktime instad of summing timestamps together to support DST changes
        return array
        (
            'start' => mktime(date('H', $previous_instance['start']), date('i', $previous_instance['start']), date('s', $previous_instance['start']), date('n', $previous_instance['start']), date('j', $previous_instance['start']) + $interval, date('Y', $previous_instance['start'])),
            'end'   => mktime(date('H', $previous_instance['end']), date('i', $previous_instance['end']), date('s', $previous_instance['end']), date('n', $previous_instance['end']), date('j', $previous_instance['end']) + $interval, date('Y', $previous_instance['end'])),
        );
    }
    
    /**
     * Calculate next instance for events occurring once per month
     * @param Array $previous_instance The previous occurrence of the event
     * @return Array Calculated next instance
     */
    function _get_next_instance_for_date_in_month($previous_instance)
    {       
        return array
        (
            'start' => mktime(date('H', $previous_instance['start']), date('i', $previous_instance['start']), date('s', $previous_instance['start']), date('n', $previous_instance['start']) + $this->rule['interval'], date('j', $previous_instance['start']), date('Y', $previous_instance['start'])),
            'end'   => mktime(date('H', $previous_instance['end']), date('i', $previous_instance['end']), date('s', $previous_instance['end']), date('n', $previous_instance['end']) + $this->rule['interval'], date('j', $previous_instance['end']), date('Y', $previous_instance['end'])),
        );
    }
    
    /**
     * Calculate next instance for events occurring on multiple weekdays
     * @param Array $previous_instance The previous occurrence of the event
     * @return Array Calculated next instance
     */
    function _get_next_instance_for_weekdays($previous_instance)
    {
        // Get the weekday of the previous instance
        $previous_instance_weekday = date('w', $previous_instance['start']);
        
        // Figure out if the next instance is on the same week as the previous instance
        $jump_week = 0;

        // Go through the week days
        $days_processed = 0;
        $previous_weekday = $previous_instance_weekday;
        while ($days_processed < 7)
        {
            if ($previous_weekday == 6)
            {
                // Saturday, jump to sunday
                $weekday = 0;
            }
            elseif ($previous_weekday == 0)
            {
                // Sunday, switch week
                $weekday = 1;
                $jump_week++;
            }
            else
            {
                $weekday = $previous_weekday + 1;
            }           
            
            if (   array_key_exists($weekday, $this->rule['days'])
                && $this->rule['days'][$weekday] == true)
            {
                // This is a day where we should have an instance
                break;     
            }
        
            $previous_weekday = $weekday;
            $days_processed++;
        }
        
        if ($weekday == 0)
        {
            // Place sundays at end of week
            $weekday = 7;
        }
        $day_change = $weekday - $previous_instance_weekday + ($jump_week * 7);
        
        if (   $this->rule['interval'] > 1
            && $jump_week > 0)
        {
            // Apply intervals only on week changes
            $day_change = $day_change + (($this->rule['interval'] - 1) * 7);
        }
        
        // Use mktime instad of summing timestamps together to support DST changes
        return array
        (
            'start' => mktime(date('H', $previous_instance['start']), date('i', $previous_instance['start']), date('s', $previous_instance['start']), date('n', $previous_instance['start']), date('j', $previous_instance['start']) + $day_change, date('Y', $previous_instance['start'])),
            'end'   => mktime(date('H', $previous_instance['end']), date('i', $previous_instance['end']), date('s', $previous_instance['end']), date('n', $previous_instance['end']), date('j', $previous_instance['end']) + $day_change, date('Y', $previous_instance['end'])),
        );
    }
        
    /**
     * Get start timestamp of the selected week
     * @param integer $timestamp Timestamp to use
     * @return integer Timestamp showing first second of the week
     */    
    function get_week_start($timestamp)
    {
        return mktime(0, 0, 0, $this->month, date('d',$timestamp) - strftime('%u', $timestamp) + 1, $this->year);
    }
    
    /**
     * Get end timestamp of the selected week.
     * @param integer $timestamp Timestamp to get the week end for
     * @return integer Timestamp showing last second of the week
     */    
    function get_week_end($timestamp)
    {
        return mktime(23, 59, 59, $this->month, strftime('%d', $this->get_week_start($timestamp)) + 6, $this->year);
    }
}
?>