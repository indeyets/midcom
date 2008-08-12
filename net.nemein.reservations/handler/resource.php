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
class net_nemein_reservations_handler_resource extends midcom_baseclasses_components_handler
{
    /**
     * The resource which has been created
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * Extra resources
     *
     * @var Array containing @org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resources = array();

    /**
     * Calendar display widget
     *
     * @var org_openpsa_calendarwidget_month
     * @access private
     */
    var $_calendar;

    /**
     * Extra calendar display widgets
     *
     * @var Array
     * @access private
     */
    var $_calendars = Array();

    /**
     * Simple default constructor.
     */
    function net_nemein_reservations_handler_resource()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current resource. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_resource']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for resources.");
            // This will exit.
        }
    }

    function _load_calendarwidget()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if (   $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->can_do('midgard:create')
            &&  $this->_resource->can_do('org.openpsa.calendar:reserve'))
        {
            $url = "{$prefix}reservation/create/{$this->_resource->name}/";
        }
        else
        {
            $url = null;
        }

        if (   isset($_GET['resources'])
            && is_array($_GET['resources']))
        {
            $suffix = 'resources[]=' . implode('&resources[]=', $_GET['resources']);
        }
        else
        {
            $suffix = '';
        }

        $this->_calendar = new net_nemein_reservations_calendar($url);
        $this->_calendar->suffix = $suffix;
        $this->_request_data['calendar'] =& $this->_calendar;

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
            $this->_calendar->use_javascript = true;
            $this->_calendar->details_box = true;
        }

        // Prevent the robots from ending in an "endless" parsing cycle
        $_MIDCOM->add_meta_head
        (
            array
            (
                'name' => 'robots',
                'content' => 'none',
            )
        );
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_load_datamanager();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        $this->_request_data['description_before_listing'] = $this->_config->get('description_before_listing');
        $qb = org_openpsa_calendar_resource_dba::new_query_builder();
        $qb->add_order('type');
        $qb->add_order('title');

        $types_shown = array();
        $previous_type = null;

        $data['view_title'] = $this->_topic->extra;

        midcom_show_style('view-list-header');

        $resources = $qb->execute();
        foreach ($resources as $resource)
        {
            if (!array_key_exists($resource->type, $this->_request_data['schemadb_resource']))
            {
                // This resource type isn't available for our schema, skip
                continue;
            }

            if (!in_array($resource->type, $types_shown))
            {
                if (!is_null($previous_type))
                {
                    // End previous type
                    midcom_show_style('view-type-footer');
                }

                // Show type header
                $data['resource_type'] = $resource->type;
                $previous_type = $resource->type;
                $types_shown[] = $resource->type;
                midcom_show_style('view-type-header');
            }

            $this->_datamanager->autoset_storage($resource);
            $data['view_resource'] = $this->_datamanager->get_content_html();

            $data['resource'] = $resource;

            midcom_show_style('view-list-resource');
        }
        midcom_show_style('view-type-footer');
        midcom_show_style('view-list-footer');
    }

    /**
     * Looks up a resource to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_resource = net_nemein_reservations_viewer::load_resource($args[0]);

        if (!$this->_resource)
        {
            return false;
            // This will 404
        }

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_resource);

        $tmp = Array();
        $arg = $this->_resource->name ? $this->_resource->name : $this->_resource->guid;
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "resource/{$arg}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_request_data['resource'] =& $this->_resource;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/create/{$this->_resource->name}/",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('reserve %s'), $this->_resource->title),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'r',
                MIDCOM_TOOLBAR_ENABLED => ($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->can_do('midgard:create') && $this->_resource->can_do('org.openpsa.calendar:reserve')),
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_resource->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                MIDCOM_TOOLBAR_ENABLED => $this->_resource->can_do('midgard:update')
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_resource->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                MIDCOM_TOOLBAR_ENABLED => $this->_resource->can_do('midgard:delete')
            )
        );

        // Populate calendar events for the resource
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_load_calendarwidget();
        $reservations = $this->_resource->get_reservations($this->_calendar->get_start(), $this->_calendar->get_end());

        // Populate the main calendar
        foreach ($reservations as $reservation)
        {
            $event = new org_openpsa_calendar_event($reservation->event);
            $calendar_event = new org_openpsa_calendarwidget_event($event);
            $calendar_event->link = "{$prefix}reservation/{$event->guid}/";

            $this->_calendar->add_event($calendar_event);
        }

        $data['suffix'] = '';
        $data['hidden_fields'] = '';

        // Store the year and month parameters if available
        if (isset($_GET['year']))
        {
            $data['hidden_fields'] .= '<input type="hidden" name="year" value="' . $_GET['year'] . '" />'."\n";
        }

        if (isset($_GET['month']))
        {
            $data['hidden_fields'] .= '<input type="hidden" name="month" value="' . $_GET['month'] . '" />'."\n";
        }

        // Populate the comparison calendars
        if (   $this->_config->get('enable_multiple_calendars')
            && isset($_GET['resources'])
            && is_array($_GET['resources']))
        {
            $this->_calendars = array ();
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            $data['suffix'] = '?';

            // Store the year and month parameters if available
            if (isset($_GET['year']))
            {
                $data['suffix'] .= "&year={$_GET['year']}";
            }

            if (isset($_GET['month']))
            {
                $data['suffix'] .= "&month={$_GET['month']}";
            }

            // Common resources list
            if (   isset($_GET['resources'])
                && is_array($_GET['resources']))
            {
                $suffix = 'resources[]=' . implode('&resources[]=', $_GET['resources']);
            }
            else
            {
                $suffix = '';
            }

            foreach ($_GET['resources'] as $key => $guid)
            {
                $this->_resources[$guid] = net_nemein_reservations_viewer::load_resource($guid);

                if (   !$this->_resources[$guid]
                    || !isset($this->_resources[$guid]->guid))
                {
                    continue;
                }

                $resource = new org_openpsa_calendar_resource_dba($guid);

                $url = "{$prefix}reservation/create/{$resource->name}/";

                $this->_calendars[$guid] = new net_nemein_reservations_calendar($url);
                $this->_calendars[$guid]->suffix = $suffix;

                if ($this->_config->get('javascript_hover'))
                {
                    $this->_calendars[$guid]->use_javascript = true;
                    $this->_calendars[$guid]->details_box = true;
                }

                foreach ($this->_resources[$guid]->get_reservations($this->_calendar->get_start(), $this->_calendar->get_end()) as $reservation)
                {
                    $event = new org_openpsa_calendar_event($reservation->event);
                    $calendar_event = new org_openpsa_calendarwidget_event($event);
                    $calendar_event->link = "{$prefix}reservation/{$event->guid}/";

                    $this->_calendars[$guid]->add_event($calendar_event);
                }
            }
        }

        $_MIDCOM->bind_view_to_object($this->_resource, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_resource->metadata->revised, $this->_resource->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_resource->title}");

        return true;
    }

    /**
     * Shows the loaded resource.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view ($handler_id, &$data)
    {
        $_MIDCOM->load_library('org.openpsa.contactwidget');

        // Get the resources list
        $qb = org_openpsa_calendar_resource_dba::new_query_builder();
        $qb->add_order('type');
        $qb->add_constraint('guid', '<>', $this->_resource->guid);
        $data['resources'] = array ();

        foreach ($qb->execute() as $resource)
        {
            if (!array_key_exists($resource->type, $this->_request_data['schemadb_resource']))
            {
                // This resource type isn't available for our schema, skip
                continue;
            }

            $data['resources'][$resource->guid] = $resource->title;
        }

        $data['view_resource'] = $this->_datamanager->get_content_html();

        // Should extra calendars be allowed?
        $data['show_extra'] = $this->_config->get('enable_multiple_calendars');

        midcom_show_style('view-resource-header');
        midcom_show_style('view-resource-calendar');
        midcom_show_style('view-resource-footer');

        if (!$this->_config->get('enable_multiple_calendars'))
        {
            return;
        }

        // Show the extra calendar requests
        foreach ($this->_calendars as $guid => $calendar)
        {
            // This is an extra calendar
            $this->_resource =& $this->_resources[$guid];

            $this->_datamanager->autoset_storage($this->_resource);
            $data['view_resource'] = $this->_datamanager->get_content_html();
            $this->_calendar = $this->_calendars[$guid];

            midcom_show_style('view-resource-header');
            midcom_show_style('view-resource-calendar');
            midcom_show_style('view-resource-footer');
        }

        // Show the resources list
        midcom_show_style('view-resource-list');
    }
}

?>