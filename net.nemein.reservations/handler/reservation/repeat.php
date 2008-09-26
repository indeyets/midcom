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
class net_nemein_reservations_handler_reservation_repeat extends midcom_baseclasses_components_handler
{
    /**
     * The event which has been created
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;
    
    /**
     * The event which has been created
     *
     * @var org_openpsa_calendar_event
     * @access private
     */
    var $_event = null;
    
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();

        $_MIDCOM->load_library('net.nemein.repeathandler');
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
     * Looks up an event to display.
     * 
     * @access private
     * @param string $handler_id     Request switch identificator
     * @param array $args            Variable URI arguments passed to the handler
     * @param Array &$data            Data passed to UI
     * @return boolean               Indicating success
     */
    function _handler_repeat($handler_id, $args, &$data)
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate("reservation/{$args[0]}/");
            // This will exit
        }
        
        $this->_event = new org_openpsa_calendar_event($args[0]);
        
        if (   !$this->_event
            || !$this->_event->guid
            || count($this->_event->resources) < 1)
        {
            return false;
            // This will 404
        }
        
        // If repeating has already been set, redirect to the original master event
        if (   $this->_event->get_parameter('net.nemein.repeathandler', 'master_guid')
            && $this->_event->guid !== $this->_event->get_parameter('net.nemein.repeathandler', 'master_guid'))
        {
            $_MIDCOM->relocate("reservation/repeat/" . $this->_event->get_parameter('net.nemein.repeathandler', 'master_guid') . "/");
        }
        
        $this->_event->require_do('midgard:update');
        $this->_duplicate_reservations = array();
        $this->_duplicate_reservations['resources'] = array();
        $this->_duplicate_reservations['members'] = array();
        
        if (isset($_POST['f_submit']))
        {
            $this->_form_handling();
        }
        
        foreach ($this->_event->resources as $resource => $included)
        {
            $this->_resource = new org_openpsa_calendar_resource_dba($resource);
            break;
        }

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_event);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "reservation/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => "{$this->_event->title} " . strftime('%x', $this->_event->start),
        );
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "reservation/repeat/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('repeating'),
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/edit/{$this->_event->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:update')
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/delete/{$this->_event->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:delete')
            )
        );
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/repeat/{$this->_event->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('repeating'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-master-document.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:update'),
            )
        );
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/net.nemein.reservations/check_visibility.js');

        $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_event->revised, $this->_event->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title} " . strftime('%x', $this->_event->start));
        
        $data['rules'] = net_nemein_repeathandler::get_repeat_rules($this->_event->guid);
        
        return true;
    }

    /**
     * Shows the loaded event.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_repeat($handler_id, &$data)
    {
        $_MIDCOM->load_library('org.openpsa.contactwidget');

        $this->_request_data['view_reservation'] = $this->_datamanager->get_content_html();
        $this->_request_data['duplicate_reservations'] = $this->_duplicate_reservations;
        
        midcom_show_style('view-reservation');
        midcom_show_style('view-reservation-repeathandler');
    }
    
    /**
     * 
     * 
     * @access private
     */
    function _form_handling()
    {
        $_MIDCOM->componentloader->load_graceful('org.openpsa.calendar');
        debug_push_class(__CLASS__, __FUNCTION__);
        $master_guid = $this->_event->guid;
        
        // Set the repeat rules
        $repeat_rule = array ();
        $repeat_rule['type'] = $_POST['type'];
        $repeat_rule['interval'] = $_POST['interval'];
        
        $repeat_rule['from'] = @strtotime($_POST['from']);
        
        if ($_POST['end_switch'] == 'num')
        {
            $repeat_rule['num'] = $_POST['num'];
        }
        else
        {
            $repeat_rule['to'] = @strtotime($_POST['to'] . ' 23:59:59');
        }
        
        if ($_POST['type'] === '')
        {
            $this->_request_data['errors'][] = 'type not set';
        }
        
        if ($repeat_rule['interval'] == 0)
        {
            $this->_request_data['errors'][] = 'interval cannot be less than one';
        }
        
        if (!is_numeric($repeat_rule['interval']))
        {
            $this->_request_data['errors'][] = 'interval has to be numeric';
        }
        
        // Weekly by day
        if ($_POST['type'] === 'weekly_by_day')
        {
            for ($i = 0; $i < 7; $i++)
            {
                if (in_array($i, $_POST['days']))
                {
                    $repeat_rule['days'][$i] = 1;
                }
                else
                {
                    $repeat_rule['days'][$i] = 0;
                }
            }
        }
        
        if (   isset($this->_request_data['errors'])
            && count($this->_request_data['errors']) > 0)
        {
            return false;
        }
        
        $calculator = new net_nemein_repeathandler_calculator($this->_event, $repeat_rule);
        $instances = $calculator->calculate_instances();
        
        // Store the rules to DB
        foreach ($repeat_rule as $key => $value)
        {
            if (is_array($value))
            {
                $this->_event->set_parameter('net.nemein.repeathandler', "rule.{$key}", serialize($value));
            }
            else
            {
                $this->_event->set_parameter('net.nemein.repeathandler', "rule.{$key}", $value);
            }
        }
        
        $this->_event->set_parameter('net.nemein.repeathandler', 'master_guid', $this->_event->guid);
        
        $duplicate_reservations_resource = array();
        $duplicate_reservations_members = array();
        foreach($instances as $date => $instance)
        {
            if($instance['start'] == $this->_event->start && $instance['end'] == $this->_event->end)
            {
                continue;
            }
            $resolver = new org_openpsa_calendar_event();
            $resolver->resources = $this->_event->resources;
            $resolver->participants = $this->_event->resources;
            $resolver->start = $instance['start'];
            $resolver->end = $instance['end'];
            $resolver->busy = 1;
            if (!$resolver->busy_em())
            {
                // no conflicts, skip processing
                continue;
            }

            // handle conflicts
            $duplicate_reservations_resource[] = $resolver->busy_er[2];
            $duplicate_reservations_members[] = $resolver->busy_em[2];
        }
        
        $this->_duplicate_reservations['resources'] = $duplicate_reservations_resource;
        $this->_duplicate_reservations['members'] = $duplicate_reservations_members;
        
        if(count($duplicate_reservations_resource) == 0 && count($duplicate_reservations_members) == 0)
        {
            $repeat_handler = new net_nemein_repeathandler(&$this->_event);
            $repeat_handler->delete_stored_repeats($this->_event->guid());
            
            foreach ($instances as $date => $instance)
            {
                if (array_key_exists('guid', $instance))
                {
                    $previous_guid = $instance['guid'];
                }
                else
                {
                    // These are the instances we must create
                    $previous_guid = $repeat_handler->create_event_from_instance($instance, $previous_guid);
                    if ($previous_guid)
                    {
                        $instance['guid'] = $previous_guid;
                        $instances[$date] = $instance;
                    }
                }
            }
            
            $_MIDCOM->relocate("reservation/{$this->_event->guid}/");
            // This will exit
        }
        else
        {
            return true;
        }
    }
}
?>