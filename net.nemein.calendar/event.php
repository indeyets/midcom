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
    function net_nemein_calendar_event($guid = null) 
    {
        return parent::__net_nemein_calendar_event($guid);
    }

    function _on_created()
    {    
        midcom_baseclasses_core_dbobject::generate_urlname($this);
        return parent::_on_created();
    }
    
    function _on_updated()
    {    
        midcom_baseclasses_core_dbobject::generate_urlname($this);
        return parent::_on_updated();
    }
}
?>