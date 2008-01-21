<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event abstraction class
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_event_dba extends __net_nemein_calendar_event_dba
{
    function net_nemein_calendar_event_dba($guid = null) 
    {
        return parent::__net_nemein_calendar_event_dba($guid);
    }

    function _on_created()
    {
        if (isset($GLOBALS['net_nemein_calendar_event_dba__on_created_loop_{$this->guid}']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Detected _on_updated loop on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
        }
        else
        {
            $GLOBALS['net_nemein_calendar_event_dba__on_created_loop_{$this->guid}'] = true;
            midcom_baseclasses_core_dbobject::generate_urlname($this);
            unset($GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}']);
        }
        return true;
    }

    function _on_updated()
    {
        if (isset($GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Detected _on_updated loop on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
        }
        else
        {
            $GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}'] = true;
            midcom_baseclasses_core_dbobject::generate_urlname($this);
            unset($GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}']);
        }
        return true;
    }
}
?>