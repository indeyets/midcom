<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: event.php,v 1.1.2.2 2005/11/07 18:57:43 bergius Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event abstraction class
 * TODO: Port to OpenPsa2 Calendar classes as soon as they handle repeats
 *
 * @package net.nemein.calendar
 */

if (class_exists('NemeinCalendar_event'))
{   
    class net_nemein_calendar_event extends NemeinCalendar_event
    {
        var $__table__ = 'event';    
        function net_nemein_calendar_event($guid = null) 
        {
            return @parent::NemeinCalendar_event($guid);
        }
        
        function update()
        {
            // This is because DM tries to save everything field by field, 
            // and since end hasn't been set yet, start saving fails
            if (   $this->start > 0
                && $this->end == 0)
            {
                $this->end = $this->start + 1;
            }

            error_reporting(E_WARNING);        
            return parent::save();
            error_reporting(E_ALL);            
        }
        
        function create()
        {
            error_reporting(E_WARNING);        
            return parent::save();
            error_reporting(E_ALL);        
        }
    }
}
?>