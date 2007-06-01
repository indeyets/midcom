<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration navigation interface
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * The root event. This is now registration-specific event instance since we don't
     * have a full request available in the navigation (and don't need it either).
     *
     * Set on demand.
     *
     * @var net_nemein_calendar_event
     * @access private
     */
    var $_root_event = null;

    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_registrations_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        if (   !$this->_config->get('root_event_guid')
            || !$this->_config->get('display_leaves'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Component not configured for topic {$this->_topic->id}, not adding leaves.", MIDCOM_LOG_WARN);
            debug_pop();
            return Array();
        }

        $result = Array();

        $this->_load_root_event();
        $qb = net_nemein_calendar_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_root_event->id);
        if ($this->_config->get('event_type') !== null)
        {
            $qb->add_constraint('type', '=', $this->_config->get('event_type'));
        }
        $qb->add_constraint('end', '>', time());
        $qb->add_order('start');
        $query_result = $qb->execute();

        if ($query_result)
        {
            foreach ($query_result as $event)
            {
                $result[$event->guid] = array
                (
                     MIDCOM_NAV_ADMIN => null,
                     MIDCOM_NAV_SITE => Array
                     (
                         MIDCOM_NAV_URL => "event/view/{$event->guid}.html",
                         MIDCOM_NAV_NAME => $event->title
                     ),
                     MIDCOM_NAV_GUID => $event->guid,
                     MIDCOM_NAV_TOOLBAR => null,
                     MIDCOM_META_CREATOR => $event->creator,
                     MIDCOM_META_EDITOR => $event->revisor,
                     MIDCOM_META_CREATED => $event->created,
                     MIDCOM_META_EDITED => $event->revised
                );
            }
        }
        return $result;
    }

    /**
     * Tries to load the Systemwide root event.
     */
    function _load_root_event()
    {
        if (! $this->_root_event)
        {
            $guid = $this->_config->get('root_event_guid');
            $this->_root_event = new net_nemein_calendar_event($guid);
            if (! $this->_root_event)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the root event '{$guid}', last Midgard error was: " . mgd_errstr());
                // This will exit.
            }
        }
    }


}

?>