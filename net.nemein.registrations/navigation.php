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
     * @var net_nemein_calendar_event_dba
     * @access private
     */
    var $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_registrations_navigation()
    {
        parent::__construct();
    }

    function get_leaves()
    {
        if (   !$this->_config->get('content_topic_guid')
            || !$this->_config->get('display_leaves'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Component not configured for topic {$this->_topic->id}, not adding leaves.", MIDCOM_LOG_WARN);
            debug_pop();
            return Array();
        }

        $result = Array();

        $this->_load_content_topic();
        $qb = net_nemein_calendar_event_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_content_topic->id);
        
        if ($this->_config->get('event_type') !== null)
        {
            $qb->add_constraint('type', '=', $this->_config->get('event_type'));
        }
        $qb->add_constraint('end', '>', gmdate('Y-m-d H:i:s'));
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
                     MIDCOM_META_CREATOR => $event->metadata->creator,
                     MIDCOM_META_EDITOR => $event->metadata->revisor,
                     MIDCOM_META_CREATED => $event->metadata->created,
                     MIDCOM_META_EDITED => $event->metadata->revised
                );
            }
        }
        return $result;
    }

    /**
     * Tries to load the Systemwide root event.
     */
    function _load_content_topic()
    {
        if (! $this->_content_topic)
        {
            $guid = $this->_config->get('content_topic_guid');
            $this->_content_topic = new midcom_db_topic($guid);
            if (   !$this->_content_topic
                || !$topic->_content_topic->guid)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the root event '{$guid}', last Midgard error was: " . mgd_errstr());
                // This will exit.
            }
        }
    }


}

?>