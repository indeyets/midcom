<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_handler_reservation_list extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Internal helper, loads the datamanager for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_reservation']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for events.");
            // This will exit.
        }
    }

    /**
     * Looks up list of events to display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_component_data['active_leaf'] = $this->_topic->id . ':list';
        $this->_load_datamanager();
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = org_openpsa_calendar_resource_dba::new_query_builder();
        $resources = $qb->execute();
        unset($qb);
        $qb = org_openpsa_calendar_event_resource_dba::new_query_builder();
        // In case we can't see all resources or something
        $data['resources_by_id'] = array();
        $qb->begin_group('OR');
        foreach ($resources as $resource)
        {
            $data['resources_by_id'][$resource->id] = $resource;
            $qb->add_constraint('resource', '=', $resource->id);
        }
        $qb->end_group();

        // Which date to use (today or given)
        switch ($handler_id)
        {
            case 'list_reservations_date':
                $data['show_date'] = strtotime($args[0]);
                break;
            default:
                $data['show_date'] = time();
                break;
        }
        $day_start = mktime(0, 0, 1, date('n', $data['show_date']), date('j', $data['show_date']), date('Y', $data['show_date']));
        $day_end = mktime(23, 59, 59, date('n', $data['show_date']), date('j', $data['show_date']), date('Y', $data['show_date']));

        //Target event starts or ends inside selected days window or starts before and ends after
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('event.start', '>=', $day_start);
                $qb->add_constraint('event.start', '<=', $day_end);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('event.end', '<=', $day_end);
                $qb->add_constraint('event.end', '>=', $day_start);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('event.start', '<=', $day_start);
                $qb->add_constraint('event.end', '>=', $day_end);
            $qb->end_group();
        $qb->end_group();

        // In 1.8 sort on QB
        if (class_exists('midgard_query_builder'))
        {
            $qb->add_order('event.start');
        }

        $eventresources = $qb->execute();
        $data['events'] = array();
        foreach($eventresources as $eventresource)
        {
            $eid =& $eventresource->event;
            if (isset($data['events'][$eid]))
            {
                // We have already seen this event, skip (in theory possible if we have multiple resources)
                continue;
            }
            $event = new org_openpsa_calendar_event($eid);
            if (   !is_object($event)
                || !isset($event->id)
                || empty($event->id))
            {
                // Could not get event (ACL issue ??)
                continue;
            }
            $data['events'][$eid] = $event;
        }
        unset($eventresources, $qb);

        if (!class_exists('midgard_query_builder'))
        {
            // Can't use QB to sort by linked values, sort events by hand...
            /* HACK: usort can't use even static methods so we create an "anonymous" function from code received via method */
            uasort($data['events'], create_function('$a,$b', $this->_code_for_sort_events_by_start()));
        }
        $data['page_title'] = sprintf($this->_l10n->get('reservations on %s'), strftime('%x', $data['show_date']));

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['page_title']}");
        $tmp = Array
        (
            array
            (
                MIDCOM_NAV_URL => 'reservation/list/' . date('Y-m-d', $data['show_date']) . '/',
                MIDCOM_NAV_NAME => $data['page_title'],
            ),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $previous_day = date('Y-m-d', $day_start - 100);
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'reservation/list/' . $previous_day . '/',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('previous day'),
                MIDCOM_TOOLBAR_HELPTEXT => strftime('%x', $day_start - 100),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/up.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $next_day = date('Y-m-d', $day_end + 100);
        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'reservation/list/' . $next_day . '/',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('next day'),
                MIDCOM_TOOLBAR_HELPTEXT => strftime('%x', $day_end + 100),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/down.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        debug_pop();
        return true;
    }

    /**
     * Shows the list of events.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        midcom_show_style('view-datereservations-title');
        if (empty($data['events']))
        {
            midcom_show_style('view-datereservations-noevents');
            debug_pop();
            return;
        }
        
        $_MIDCOM->load_library('org.openpsa.contactwidget');

        midcom_show_style('view-datereservations-header');
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        foreach ($data['events'] as $event)
        {
            $data['event'] =& $event;
            $data['event_widget'] = new org_openpsa_calendarwidget_event($event);
            if (!$this->_datamanager->autoset_storage($event))
            {
                debug_add("Could not initialize DM2 for event #{$event->id}", MIDCOM_LOG_ERROR);
                continue;
            }
            $data['event_url'] = "{$data['prefix']}reservation/{$event->guid}/";
            $data['view_reservation'] = $this->_datamanager->get_content_html();
            midcom_show_style('view-datereservations-item');
        }
        midcom_show_style('view-datereservations-footer');
        debug_pop();
    }

    /**
     * Code to sort array of events by event->start, from smallest to greatest
     *
     * Used by $this->_handler_view() when we don't have 1.8
     */
    function _code_for_sort_events_by_start()
    {
        return <<<EOF
        \$ap = \$a->start;
        \$bp = \$b->start;
        if (\$ap > \$bp)
        {
            return 1;
        }
        if (\$ap < \$bp)
        {
            return -1;
        }
        return 0;
EOF;
    }
}

?>