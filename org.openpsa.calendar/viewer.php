<?php
/**
 * @package org.openpsa.calendar
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.46 2006/06/08 16:24:37 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.calendar site interface class.
 * 
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_viewer extends midcom_baseclasses_components_request
{

    var $_datamanager;
    var $_selected_time = null;
    var $_dm_createfailed_event = null;

    /**
     * Constructor.
     *
     * @todo OpenPSA Calendar handles its URL space how?
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);

        $this->_selected_time = time();

        // Load schema database
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        
        $this->_request_data['schemadb'] =& $this->_schemadb;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        if (!$this->_config->get('calendar_root_event'))
        {
            // Match /
            $this->_request_switch['not_initialized'] = array
            (
                'handler' => 'notinitialized',
            );
        }
        else
        {
            // Match /month/<date>
            $this->_request_switch['month_view_with_date'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'month',
                ),
                'fixed_args' => 'month',
                'variable_args' => 1,
            );
            // Match /month/
            $this->_request_switch['month_view'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'month',
                ),
                'fixed_args' => 'month',
            );
            // Match /week/<date>
            $this->_request_switch['week_view_with_date'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'week',
                ),
                'fixed_args' => 'week',
                'variable_args' => 1,
            );
            // Match /week/
            $this->_request_switch['week_view'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'week',
                ),
                'fixed_args' => 'week',
            );
            // Match /day/<date>
            $this->_request_switch['day_view_with_date'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'day',
                ),
                'fixed_args' => 'day',
                'variable_args' => 1,
            );
            // Match /day/
            $this->_request_switch['day_view'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'day',
                ),
                'fixed_args' => 'day',
            );
            // Match /event/new/<person_guid>/<timestamp>
            $this->_request_switch['new_event_for_person_with_time'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_create',
                    'create'
                ),
                'fixed_args' => array
                (
                    'event',
                    'new'
                ),
                'variable_args' => 2,
            );
            // Match /event/new/<person_guid>
            $this->_request_switch['new_event_for_person'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_create',
                    'create'
                ),
                'fixed_args' => array
                (
                    'event',
                    'new'
                ),
                'variable_args' => 1,
            );
            // Match /event/new
            $this->_request_switch['new_event'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_create',
                    'create'
                ),
                'fixed_args' => array
                (
                    'event',
                    'new'
                ),
            );
            // Match /event/raw/<guid>
            $this->_request_switch['event_view_raw'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'event',
                ),
                'fixed_args' => array('event', 'raw'),
                'variable_args' => 1,
            );
            // Match /event/<guid>/<action>
            $this->_request_switch['event_action'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_admin',
                    'admin',
                ),
                'fixed_args' => 'event',
                'variable_args' => 2,
            );
            // Match /event/<guid>
            $this->_request_switch['event_view'] = array
            (
                'handler' => array
                (
                    'org_openpsa_calendar_handler_view',
                    'event',
                ),
                'fixed_args' => 'event',
                'variable_args' => 1,
            );
            
            // This will redirect to the selected mode
            // Match /
            $this->_request_switch[] = array
            (
                'handler' => 'frontpage',
            );

            // Match /filters
            $this->_request_switch['filters_edit'] = array
            (
                'handler' => Array('org_openpsa_calendar_handler_filters', 'edit'),
                'fixed_args' => Array('filters'),
            );

            // Match /agenda/day/<timestamp>
            $this->_request_switch['agenda_day'] = array
            (
                'handler' => Array('org_openpsa_calendar_handler_agenda', 'day'),
                'fixed_args' => Array('agenda', 'day'),
                'variable_args' => 1,
            );

            // Match /ical/events/<username>
            $this->_request_switch['ical_user_feed'] = array
            (
                'handler' => Array('org_openpsa_calendar_handler_ical', 'user_events'),
                'fixed_args' => Array('ical', 'events'),
                'variable_args' => 1,
            );

            // Match /ical/busy/<username>
            $this->_request_switch['ical_user_busy'] = array
            (
                'handler' => Array('org_openpsa_calendar_handler_ical', 'user_busy'),
                'fixed_args' => Array('ical', 'busy'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_calendar_handler_ical', 'user_busy'),
            );


            // Match /config/
            $this->_request_switch['config'] = Array
            (
                'handler' => Array('midcom_core_handler_configdm', 'configdm'),
                'schemadb' => 'file:/org/openpsa/calendar/config/schemadb_config.inc',
                'schema' => 'config',
                'fixed_args' => Array('config'),
            );

            //Add common relatedto request switches
            org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.calendar');
            //If you need any custom switches add them here
        }

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.openpsa.helpers/ajaxutils.js');
        $this->_request_data['view'] = 'default';

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/org.openpsa.core/ui-elements.css',
            )
        );

    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_notinitialized($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_notinitialized($handler_id, &$data)
    {
        midcom_show_style('show-not-initialized');
    }

    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        switch($this->_config->get('start_view'))
        {
            case 'day':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'day/' . date('Y-m-d', $this->_selected_time) . '/');
                // This will exit()
            break;
            case 'month':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'month/' . date('Y-m-d', $this->_selected_time) . '/');
                // This will exit()
                break;
            default:
            case 'week':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . 'week/' . date('Y-m-d', $this->_selected_time) . '/');
                // This will exit()
                break;
        }
    }
    
    function create_root_event()
    {
        // Create the root event
        $event = new midcom_db_event();
        
        if (!$event->create())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create the root event');
            // This will exit
        }
        
        $topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $topic->set_parameter($this->_component, 'calendar_root_event', $event->guid);
        
        return $event;
    }
}
?>