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
    function __construct($guid = null) 
    {
        return parent::__construct($guid);
    }

    /**
     * Returns the Parent of the event, in this case we consider node to have priority over up
     *
     * @return string guid of parent topic/event
     */
    function get_parent_guid_uncached()
    {
        if (!empty($this->node))
        {
            return net_nemein_calendar_event_dba::_get_parent_guid_uncached_static_topic($this->node);
        }
        if (!empty($this->up))
        {
            return net_nemein_calendar_event_dba::_get_parent_guid_uncached_static_event($this->up);
        }
        return null;
    }

    /**
     * Statically callable method to get parent guid for event with given guid, in this case we consider node to have priority over up
     *
     * @param string $guid guid of event to get parent guid for
     * @return string guid of parent topic/event
     */
    function get_parent_guid_uncached_static($guid)
    {
        if (empty($guid))
        {
            return null;
        }
        $mc = net_nemein_calendar_event_dba::new_collector('guid', $guid);
        $mc->add_value_property('up');
        $mc->add_value_property('node');
        $mc->execute();
        $keys = $mc->list_keys();
        list ($key, $copy) = each ($keys);
        $node_id = (int) $mc->get_subkey($key, 'node');
        $up_id = (int) $mc->get_subkey($key, 'up');
        if (!empty($node_id))
        {
            return net_nemein_calendar_event_dba::_get_parent_guid_uncached_static_topic($node_id);
        }
        if (!empty($up_id))
        {
            return net_nemein_calendar_event_dba::_get_parent_guid_uncached_static_event($up_id);
        }
        return null;
    }

    /**
     * Get event guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of event to get the guid for
     */
    function _get_parent_guid_uncached_static_event($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = net_nemein_calendar_event_dba::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        $parent_guids = array_keys($mc_parent_keys);
        if (count($parent_guids) == 0)
        {
            return null;
        }
        
        $parent_guid = $parent_guids[0];
        if ($parent_guid === false)
        {
            return null;
        }
        return $parent_guid;
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