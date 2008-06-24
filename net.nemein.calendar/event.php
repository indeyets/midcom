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

    /**
     * Get topic guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of topic to get the guid for
     */
    function _get_parent_guid_uncached_static_topic($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = midcom_baseclasses_database_topic::new_collector('id', $parent_id);
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

    /**
     * Checks that the event time range is sane
     * @return bool indicating sanity
     */
    function _check_time_range()
    {
        static $valid_date_format = '/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/';
        // Safety against stupid mistakes (probably asgard)
        if (   !preg_match($valid_date_format, $this->start)
            && is_numeric($this->start))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Start looks like UNIX timestamp, must be ISO date, rewriting ({$this->start})", MIDCOM_LOG_WARN);
            debug_pop();
            $this->start = date('Y-m-d H:i:s', $this->start);
        }
        if (   !preg_match($valid_date_format, $this->end)
            && is_numeric($this->end))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("End looks like UNIX timestamp, must be ISO date, rewriting ({$this->end})", MIDCOM_LOG_WARN);
            debug_pop();
            $this->end = date('Y-m-d H:i:s', $this->end);
        }
        // Final format safeguard
        if (   !preg_match($valid_date_format, $this->start)
            || !preg_match($valid_date_format, $this->end))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Start or end not in valid format ({$this->start} & {$this->end})", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   $this->start === '0000-00-00 00:00:00'
            && $this->end === '0000-00-00 00:00:00')
        {
            // Allow true zeros as valid range
            return true;
        }
        $start_comparable = str_replace(array('-', ':'), '', $this->start);
        $end_comparable = str_replace(array('-', ':'), '', $this->end);
        if ($end_comparable < $start_comparable)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Cannot end before starting ({$end_comparable} < {$start_comparable})", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_RANGE);
            return false;
        }
        // Avoid problems with events too close to the epoch (highly unlikely usage scenario in any case)
        static $epoch = '19720102000000';
        if (   $start_comparable < $epoch
            || $end_comparable < $epoch)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("start or end less than epoch ({$start_comparable} < {$epoch} || {$end_comparable} < {$epoch})", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_RANGE);
            return false;
        }
        return true;
    }

    /**
     * Checks that the event name is unique (in node/event tree)
     * @return bool indicating uniqueness
     */
    function name_is_unique()
    {
        if (empty($this->name))
        {
            return true;
        }
        $qb = new midgard_query_builder('net_nemein_calendar_event');
        if (!empty($this->id))
        {
            $qb->add_constraint('id', '<>', $this->id);
        }        
        $qb->add_constraint('name', '=', $this->name);
        $qb->add_constraint('node', '=', $this->node);
        if (!empty($this->up))
        {
            $qb->add_constraint('up', '=', $this->up);
        }        
        
        $results = $qb->count();
        unset($qb);

        if ($results === false)
        {
            // QB error (I wonder if this raises some specific error ?
            //mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }

        if ($results > 0)
        {
            mgd_set_errno(MGD_ERR_OBJECT_NAME_EXISTS);
            return false;
        }

        return true;
    }

    /**
     * Clears the name field if it's not unique (making it go through the title based name generation)
     *
     * But only when in the fallback language
     */
    function _check_clear_name()
    {
        // FIXME: use midgard_connection::get_default_lang() with 1.9/2.0
        $fallback_language = mgd_get_default_lang();
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("checking lang");
        $fallback_language = mgd_get_default_lang();
        debug_add("if (\$this->lang={$this->lang} != \$fallback_language={$fallback_language})");
        debug_pop();
        */
        if ($this->lang != $fallback_language)
        {
            return;
        }
        if (!$this->name_is_unique())
        {
            $this->name = '';
        }
    }

    function _on_creating()
    {
        $this->_check_clear_name();
        if (!$this->_check_time_range())
        {
            return false;
        }
        if (!$this->name_is_unique())
        {
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        if (!isset($GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}']))
        {
            $this->_check_clear_name();
        }

        if (!$this->_check_time_range())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("\$this->_check_time_range() returned false on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!$this->name_is_unique())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("\$this->name_is_unique() returned false on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        return true;
    }

    function _on_created()
    {
        // In theory we should never be able to loop here since it goes on to the update callbacks, but better to be safe
        if (isset($GLOBALS['net_nemein_calendar_event_dba__on_created_loop_{$this->guid}']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Detected _on_created loop on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
        }
        else
        {
            $GLOBALS['net_nemein_calendar_event_dba__on_created_loop_{$this->guid}'] = true;
            if (empty($this->name))
            {
                /*
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Calling midcom_baseclasses_core_dbobject::generate_urlname(\$this) on #{$this->id}");
                debug_pop();
                */
                midcom_baseclasses_core_dbobject::generate_urlname($this);
            }
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
            if (empty($this->name))
            {
                /*
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Calling midcom_baseclasses_core_dbobject::generate_urlname(\$this) on #{$this->id}");
                debug_pop();
                */
                midcom_baseclasses_core_dbobject::generate_urlname($this);
            }
            unset($GLOBALS['net_nemein_calendar_event_dba__on_updated_loop_{$this->guid}']);
        }
        return true;
    }
}
?>