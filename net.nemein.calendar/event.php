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
class net_nemein_calendar_event extends __net_nemein_calendar_event
{
    function net_nemein_calendar_event($guid = null) 
    {
        return parent::__net_nemein_calendar_event($guid);
    }
    
    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->up != 0)
        {
            $parent = new net_nemein_calendar_event($this->up);
            return $parent;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Check that article by same name doesn't exist in the topic in
     * Classic Midgard API fashion.
     *
     * @return boolean Whether the name is acceptable
     */
    function _check_name()
    {
        if ($this->extra == '')
        {
            return true;
        }
        
        $qb = net_nemein_calendar_event::new_query_builder();
        $qb->add_constraint('extra', '=', $this->extra);
        
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        
        if ($this->up != 0)
        {
            $qb->add_constraint('up', '=', $this->up);
        }
        
        // Run the uniqueness check
        $matches = $qb->execute_unchecked();
        if (count($matches) > 0)
        {
            // This name is already taken
            return false;
        }
        return true;
    }

    function _on_creating()
    {    
        if (!$this->_check_name())
        {
            return false;
        }
        
        return parent::_on_creating();
    }
    
    function _on_updating()
    {    
        if (!$this->_check_name())
        {
            return false;
        }
        
        return parent::_on_updating();
    }
}
?>