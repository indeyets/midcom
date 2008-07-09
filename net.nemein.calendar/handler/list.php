<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event lister
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * GET field filters set for this view
     *
     * @var array
     * @access private
     */
    var $_filters = Array();

    /**
     * Viewed year for calendar view
     *
     * @access private
     */
    var $_year;

    /**
     * Viewed month for calendar view
     *
     * @access private
     */
    var $_month;

    /**
     * Calendar display widget
     *
     * @var org_openpsa_calendarwidget_month
     * @access private
     */
    var $_calendar;

    /**
     * Switch to determine if past elements should be shown
     *
     * @var boolean
     * @access private
     */
    var $_past = false;

    /**
     * Switch to determine if the past elements should be shown in the upcoming events
     * 
     * @var boolean
     * @access private
     */
    var $_show_past_in_upcoming = false;
    
    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _load_filters()
    {
        if ($this->_config->get('enable_filters'))
        {
            if (   array_key_exists('net_nemein_calendar_filter', $_REQUEST)
                && is_array($_REQUEST['net_nemein_calendar_filter']))
            {
                $this->_filters = $_REQUEST['net_nemein_calendar_filter'];
            }
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_open($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_request_data['archive_mode'] = false;

        $this->_request_data['index_count'] = $args[0];

        $this->_request_data['events'] = array();

        $this->_load_filters();

        // Filter the upcoming list by a type if required
        $type_filter = $this->_config->get('type_filter_upcoming');

        $qb = net_nemein_calendar_event_dba::new_query_builder();

        // Add root event constraints
        if ($this->_config->get('list_from_master'))
        {
            $qb->add_constraint('up', 'INTREE', $this->_request_data['master_event']);
        }
        else
        {
            $qb->add_constraint('node', '=', $data['content_topic']->id);
        }

        // Add filtering constraints
        if (!is_null($type_filter))
        {
            $qb->add_constraint('type', '=', (int) $type_filter);
        }
        foreach ($this->_filters as $field => $filter)
        {
            $qb->add_constraint($field, '=', $filter);
        }
        // QnD category filter (only in 1.8)
        if (   isset($_REQUEST['net_nemein_calendar_category'])
            && class_exists('midgard_query_builder'))
        {
            $qb->begin_group('AND');
                $qb->add_constraint('parameter.domain', '=', 'net.nemein.calendar');
                $qb->add_constraint('parameter.name', '=', 'categories');
                $qb->add_constraint('parameter.value', 'LIKE', "%|{$_REQUEST['net_nemein_calendar_category']}|%");
            $qb->end_group();
        }

        // Show only events that haven't started
        $qb->add_constraint('start', '>', gmdate('Y-m-d H:i:s', time()));

        // Show only open events
        $qb->add_constraint('closeregistration', '>', gmdate('Y-m-d H:i:s', time()));
        $qb->add_constraint('openregistration', '<=', gmdate('Y-m-d H:i:s', time()));

        $qb->set_limit($this->_request_data['index_count']);

        $qb->add_order('closeregistration');

        $this->_request_data['events'] = $qb->execute();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_open($handler_id, &$data)
    {
        $this->_show_event_listing($handler_id);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_upcoming($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_request_data['archive_mode'] = false;

        if (count($args) > 0)
        {
            $this->_request_data['index_count'] = $args[0];
        }
        else
        {
            $this->_request_data['index_count'] = $this->_config->get('index_count');
        }

        // Filter the upcoming list by a type if required
        if (!is_null($this->_config->get('type_filter_upcoming')))
        {
            $this->_show_past_in_upcoming = true;
        }
        
        $this->_request_data['events'] = array();

        $this->_load_filters();

        // Get the events
        $this->_get_event_listing(time(), null);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_upcoming($handler_id, &$data)
    {
        $this->_show_event_listing($handler_id);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_past($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_request_data['archive_mode'] = false;

        if (count($args) > 0)
        {
            $this->_request_data['index_count'] = $args[0];
        }
        else
        {
            $this->_request_data['index_count'] = $this->_config->get('index_count');
        }

        $this->_request_data['events'] = array();

        $this->_past = true;
        $this->_load_filters();

        // Get the events
        $this->_get_event_listing(time(), null);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_past($handler_id, &$data)
    {
        $this->_show_event_listing($handler_id);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_week($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_request_data['archive_mode'] = false;

        // Go to the chosen week instead of current one
        // TODO: Check format as YYYY-MM-DD via regexp
        $requested_time = @strtotime($args[0]);
        if (!$requested_time)
        {
            // We couldn't generate a date
            return false;
        }

        $this->_request_data['index_count'] = $this->_config->get('index_count');

        $this->_request_data['events'] = array();

        $this->_load_filters();

        // Figure out the week's time
        $start = $this->_get_week_start($requested_time);
        $end = $this->_get_week_end($requested_time);

        // Get all events in the week
        $this->_get_event_listing($start, $end, true);

        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "week/{$args[0]}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('week %1$s of year %2$s'), strftime('%W', $requested_time), (string) strftime('%Y', $requested_time)),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_week($handler_id, &$data)
    {
        $this->_show_event_listing($handler_id);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_between($handler_id, $args, &$data)
    {
        $this->_load_datamanager();

        // Get the requested date range
        // TODO: Check format as YYYY-MM-DD via regexp
        $start = @strtotime($args[0]);
        $end = @strtotime($args[1]);
        if (   !$start
            || !$end)
        {
            // We couldn't generate the dates
            return false;
        }

        if ($handler_id == 'archive-between')
        {
            if (!$this->_config->get('archive_enable'))
            {
                return false;
            }
            $this->_request_data['archive_mode'] = true;

            if ($this->_config->get('archive_in_navigation'))
            {
                $this->_component_data['active_leaf'] = "{$data['content_topic']->id}_ARCHIVE";
            }
            else
            {
                $this->_component_data['active_leaf'] = "{$data['content_topic']->id}_ARCHIVE_" . date('Y', $start);
            }
        }
        else
        {
            $this->_request_data['archive_mode'] = false;
        }



        $this->_request_data['start'] = $start;
        $this->_request_data['end'] = $end;
        $this->_request_data['index_count'] = $this->_config->get('index_count');

        $this->_request_data['events'] = array();

        $this->_load_filters();

        // Get all events in the requested time range
        $this->_get_event_listing($start, $end, true);

        if ($this->_request_data['archive_mode'])
        {
            if ($start)
            {
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "archive/between/{$args[0]}/{$args[1]}/",
                    MIDCOM_NAV_NAME => strftime('%B %Y', $start),
                );
                $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
            }
        }
        else
        {
            if ($start && $end)
            {
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "/between/{$args[0]}/{$args[1]}/",
                    MIDCOM_NAV_NAME => strftime('%x', $start) . ' - ' . strftime('%x', $end),
                );

                $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
            }
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_between($handler_id, &$data)
    {
        $this->_show_event_listing($handler_id);
    }

    /**
     * Timestamp for beginning of the selected week. Use this to tune queries for selecting reservations
     * @param integer $timestamp Timestamp inside the week required
     * @return integer Timestamp for when the week starts
     */
    function _get_week_start($timestamp)
    {
        // FIXME: Use the method from o.o.calendarwidget
        return mktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp) - strftime('%u', $timestamp) + 1, gmdate('Y',$timestamp));
    }

    /**
     * Timestamp for ending of the selected week. Use this to tune queries for selecting reservations
     * @param integer $timestamp Timestamp inside the week required
     * @return integer Timestamp for when the week ends
     */
    function _get_week_end($timestamp)
    {
        // FIXME: Use the method from o.o.calendarwidget
        return mktime(23, 59, 59, gmdate('m',$timestamp), strftime('%d', $this->_get_week_start($timestamp)) + 6, gmdate('Y',$timestamp));
    }

    /**
     * List events from the database according to component's configuration
     * @param integer $from Timestamp from which to fetch events
     * @param integer $to Timestamp to which to fetch events
     * @param boolean $list_all Whether to list all instead of limited index number
     */
    function _get_event_listing($from, $to, $list_all = false)
    {
        $qb = net_nemein_calendar_event_dba::new_query_builder();
        
        // Filter the upcoming list by a type if required
        $type_filter = $this->_config->get('type_filter_upcoming');
        
        // Switch for determining if the filters have already been initialized
        $filtered = false;
        
        $qb->begin_group('OR');

        // Add root event constraints
        if ($this->_config->get('list_from_master'))
        {
            $qb->add_constraint('up', 'INTREE', $this->_request_data['master_event']);
        }
        else
        {
            $qb->add_constraint('node', '=', $this->_request_data['content_topic']->id);
        }

        // Add all the folders that are configured
        if ($this->_config->get('list_from_folders'))
        {
            $guids = explode('|', $this->_config->get('list_from_folders'));
            foreach ($guids as $guid)
            {
                // Skip empty and broken guids
                if (   !$guid
                    || !mgd_is_guid($guid))
                {
                    continue;
                }

                $qb->add_constraint('node.guid', '=', $guid);
            }
        }

        $qb->end_group();

         // Add filtering constraints
        if ($this->_show_past_in_upcoming)
        {
            $qb->begin_group('OR');
                $qb->add_constraint('type', '=', $type_filter);
                $qb->add_constraint('end', '>', gmdate('Y-m-d H:i:s', $from));
            $qb->end_group();
            
            $qb->add_order('start');
            
            // Prevent the other time filters from being used again
            $filtered = true;
        }
        
        foreach ($this->_filters as $field => $filter)
        {
            $qb->add_constraint($field, '=', $filter);
        }
        // QnD category filter (only in 1.8)
        if (   isset($_REQUEST['net_nemein_calendar_category'])
            && class_exists('midgard_query_builder'))
        {
            $this->_request_data['category'] = $_REQUEST['net_nemein_calendar_category'];
            /**
             * Broken in 1.8.8 see http://trac.midgard-project.org/ticket/261
             *
            $qb->begin_group('AND');
                $qb->add_constraint('parameter.domain', '=', 'net.nemein.calendar');
                $qb->add_constraint('parameter.name', '=', 'categories');
                $qb->add_constraint('parameter.value', 'LIKE', "%|{$this->_request_data['category']}|%");
            $qb->end_group();
             */

            /** 
             * BEGIN: Workaround http://trac.midgard-project.org/ticket/261
             */
            $mc = new midgard_collector('midgard_parameter', 'domain', 'net.nemein.calendar');
            $mc->set_key_property('parentguid');
            $mc->add_constraint('name', '=', 'categories');
            $mc->add_constraint('value', 'LIKE', "%|{$this->_request_data['category']}|%");
            $mc->execute();
            $keys = $mc->list_keys();
            unset($mc);
            $guids = array_keys($keys);
            $qb->add_constraint('guid', 'IN', $guids);
            unset($keys, $guids);
            /** 
             * END: Workaround http://trac.midgard-project.org/ticket/261
             */

            if (!$this->_request_data['archive_mode'])
            {
                $this->_component_data['active_leaf'] = "{$this->_request_data['content_topic']->id}_CAT_{$this->_request_data['category']}";
            }
        }

        // Find all events that occur during [$from, $end]
        if (   !$this->_past
            && !$filtered)
        {
            $qb->begin_group('OR');
                // The event begins during [$from, $to]
                if (is_null($to))
                {
                    $qb->add_constraint('start', '>=', gmdate('Y-m-d H:i:s', $from));
                }
                else
                {
                    $qb->begin_group('AND');
                        $qb->add_constraint('start', '>=', gmdate('Y-m-d H:i:s', $from));
                        $qb->add_constraint('start', '<=', gmdate('Y-m-d H:i:s', $to));
                    $qb->end_group();
                }
                
                if ($this->_config->get('list_started'))
                {
                    // The event begins before and ends after [$from, $to]
                    $qb->begin_group('AND');
                        $qb->add_constraint('start', '<=', gmdate('Y-m-d H:i:s', $from));
                        if (!is_null($to))
                        {
                            $qb->add_constraint('end', '>=', gmdate('Y-m-d H:i:s', $to));
                        }
                        else
                        {
                            $qb->add_constraint('end', '>=', gmdate('Y-m-d H:i:s', $from));
                        }
                    $qb->end_group();
                    // The event ends during [$from, $to]
                    if (is_null($to))
                    {
                        $qb->add_constraint('end', '>=', gmdate('Y-m-d H:i:s', $from));
                    }
                    else
                    {
                        $qb->begin_group('AND');
                            $qb->add_constraint('end', '>=', gmdate('Y-m-d H:i:s', $from));
                            $qb->add_constraint('end', '<=', gmdate('Y-m-d H:i:s', $to));
                        $qb->end_group();
                    }
                }
            $qb->end_group();
            $qb->add_order('start');
            $filtered = true;
        }
        elseif ($filtered === false)
        {
            $qb->add_constraint('start', '>', '0000-00-00 00:00:00');
            $qb->add_constraint('end', '<', gmdate('Y-m-d H:i:s', time()));
            $qb->add_order('end', 'DESC');
        }

        if (!$list_all)
        {
            $qb->set_limit($this->_request_data['index_count']);
        }

        $this->_request_data['events'] = $qb->execute();
    }

    /**
     * Internal helper, loads datamanager. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Initializes datamanager for a particular event
     *
     * @access private
     */
    function _initialize_datamanager_for_event()
    {
        $this->_datamanager->autoset_storage($this->_request_data['event']);
    }

    /**
     * Generate link to the event
     * @param MidgardEvent $event Event object to generate link for
     * @return string URL for the event
     */
    function generate_event_link(&$event)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $event_url = '';
        if (   $event->node == $this->_request_data['content_topic']->id
            || $this->_config->get('show_events_locally'))
        {
            if ($this->_request_data['archive_mode'])
            {
                $event_url = "{$prefix}archive/view/{$event->name}/";
            }
            else
            {
                $event_url = "{$prefix}{$event->name}/";
            }
        }
        else
        {
            $event_url = $_MIDCOM->get_host_prefix().'midcom-permalink-'.$event->guid();
        }
        return $event_url;
    }

    /**
     * Show an event listing populated in $this->_request_data['events']
     *
     * @param mixed $handler_id The ID of the handler.
     */
    function _show_event_listing($handler_id)
    {
        $this->_request_data['in_listing'] = false;
        $this->_request_data['node_title'] = $this->_request_data['content_topic']->extra;
        midcom_show_style('show_listing_header');

        if (   $handler_id == 'upcoming'
            || $handler_id == 'upcoming-count'
            || $handler_id == 'between')
        {
            if ($handler_id == 'between')
            {
                if ($this->_request_data['events'])
                {
                    $start = max(strtotime($this->_request_data['events'][0]->start),
                        $this->_request_data['start']);
                }
                else
                {
                    $start = $this->_request_data['start'];
                }
                $year_shown = gmdate('Y', $start);
                $month_shown = gmdate('m', $start);
                $day_shown = gmdate('d', $start);
            }
            elseif (!is_null($this->_config->get('type_filter_upcoming'))
                && $this->_config->get('type_filter_show_old'))
            {
                $year_shown = gmdate('Y', strtotime($this->_request_data['events'][0]->start));
                $month_shown = gmdate('m', strtotime($this->_request_data['events'][0]->start));
                $day_shown = gmdate('d', strtotime($this->_request_data['events'][0]->start));
            }
            else
            {
                $year_shown = gmdate('Y');
                $month_shown = gmdate('m');
                $day_shown = gmdate('d');
            }

            // midcom_show_style('show_listing_end');

            $this->_request_data['event_year'] = $year_shown;
            $this->_request_data['event_month'] = $month_shown;
            $this->_request_data['event_day'] = $day_shown;

            midcom_show_style('show_listing_year_header');

            if (   count($this->_request_data['events']) > 0
                && array_key_exists(0, $this->_request_data['events'])
                && $month_shown == gmdate('m', strtotime($this->_request_data['events'][0]->start)))
            {
                midcom_show_style('show_listing_month_header');
            }

            midcom_show_style('show_listing_day_header');
        }
        else
        {
            $year_shown = 0;
            $month_shown = 0;
            $day_shown = 0;
        }
        $events_shown = 0;

        if ($this->_request_data['events'])
        {
            foreach ($this->_request_data['events'] as $event)
            {
                $this->_request_data['event'] = & $event;
                $this->_initialize_datamanager_for_event();

                // Handle headers for changing months and years
                $this->_request_data['event_year'] = gmdate('Y', strtotime($event->start));
                $this->_request_data['event_month'] = gmdate('m', strtotime($event->start));
                $this->_request_data['event_day'] = gmdate('d', strtotime($event->start));

                if ($this->_request_data['event_year'] !== $year_shown)
                {
                    midcom_show_style('show_listing_end');
                    $year_shown = $this->_request_data['event_year'];
                    $month_shown = $this->_request_data['event_month'];
                    $day_shown = $this->_request_data['event_day'];
                    midcom_show_style('show_listing_year_header');
                    midcom_show_style('show_listing_month_header');
                    midcom_show_style('show_listing_day_header');
                }
                elseif ($this->_request_data['event_month'] !== $month_shown)
                {
                    midcom_show_style('show_listing_end');
                    $month_shown = $this->_request_data['event_month'];
                    $day_shown = $this->_request_data['event_day'];
                    midcom_show_style('show_listing_month_header');
                    midcom_show_style('show_listing_day_header');
                }
                elseif ($this->_request_data['event_day'] !== $day_shown)
                {
                    $day_shown = $this->_request_data['event_day'];
                    midcom_show_style('show_listing_day_header');
                }
                $events_shown++;
                midcom_show_style('show_listing_start');

                $this->_request_data['event_url'] = $this->generate_event_link(&$event);
                midcom_show_style('show_listing_item');
            }
        }
        else
        {
            midcom_show_style('show_listing_nonefound');
        }

        midcom_show_style('show_listing_end');
        midcom_show_style('show_listing_finished');
    }

    /**
     * Initializes calendar view
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_calendar($handler_id, $args, &$data)
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel'  => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/org.openpsa.calendarwidget/monthview.css',
            )
        );

        if ($this->_config->get('javascript_hover'))
        {
            $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.openpsa.calendarwidget/hover.js');
        }

        // Initialize org.openpsa.calendarwidget.month to show the calendar
        $this->_calendar = new org_openpsa_calendarwidget_styled_month();

        if (!array_key_exists(0, $args))
        {
            $this->_year = (int) gmdate('Y');
            $this->_month = (int) gmdate('m');
        }
        else
        {
            $this->_year = $args[0];
            $this->_month = $args[1];
            $this->_calendar->set_year($this->_year, false);
            $this->_calendar->set_month($this->_month);
        }

        // Prevent the robots from ending in an "endless" parsing cycle by limiting the year range via first and last event
        if ($this->_config->get('list_from_master'))
        {
            $last_event = net_nemein_calendar_compute_last_event($this->_request_data['master_event_obj']);
            $first_event = net_nemein_calendar_compute_first_event($this->_request_data['master_event_obj']);
        }
        else
        {
            $last_event = net_nemein_calendar_compute_last_event($this->_request_data['content_topic']);
            $first_event = net_nemein_calendar_compute_first_event($this->_request_data['content_topic']);
        }        
        if ($last_event)
        {
            $this->_calendar->last_year = (int)date('Y', strtotime($last_event->end));
        }
        else
        {
            $this->_calendar->last_year = (int)date('Y');
        }
        if ($first_event)
        {
            $this->_calendar->first_year = (int)date('Y', strtotime($first_event->start));
        }
        else
        {
            $this->_calendar->first_year = (int)date('Y');
        }

        // Point to the request data
        $this->_calendar->_request_data =& $data;

        // Schemadb for the events
        $this->_calendar->schemadb =& $this->_request_data['schemadb'];

        if (!$this->_calendar)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'This feature requires the component org.openpsa.calendarwidget to be installed');
            // This will exit
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);


        // Write month navigation with path instead of GET parameters
        $this->_calendar->path_mode = true;
        $this->_calendar->path = $prefix . 'calendar/';

        // Should we use JavaScript to emulate hovering effect?
        $this->_calendar->use_javascript = $this->_config->get('javascript_hover');

        // Get the events
        $this->_add_calendar_events();

        return true;
    }

    /**
     * Get the events from the component
     *
     * @access private
     */
    function _add_calendar_events()
    {
        $this->_get_event_listing($this->_calendar->get_start(), $this->_calendar->get_end(), true);

        if (   !is_array($this->_request_data['events'])
            || count($this->_request_data['events']) === 0)
        {
            return;
        }

        foreach ($this->_request_data['events'] as $event)
        {
            $event->start = strtotime($event->start);
            $event->end = strtotime($event->end);
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $event->link = "{$prefix}{$event->name}/";
            $this->_calendar->add_event($event);
        }
    }

    /**
     * Shows the calendar styles
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_calendar($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $data['content_topic']->extra;
        midcom_show_style('show_calendar_header');
        $this->_calendar->show();
        midcom_show_style('show_calendar_footer');
    }
}
?>