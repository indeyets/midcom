<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Registrations registration management handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_events extends midcom_baseclasses_components_handler
{
    /**
     * The events to display
     *
     * @var array
     * @access private
     */
    var $_events = null;

    /**
     * The event just created in creation mode.
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_event = null;

    /**
     * The root event (taken from the request data area)
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_root_event = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The creation mode controller used for event creation.
     *
     * @var midcom_helper_datamanager2_controller_create
     * @access private
     */
    var $_controller = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_registrations_handler_events()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_root_event =& $this->_request_data['root_event'];
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();
        switch ($handler_id)
        {
            case 'events-create':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "events/create.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('create an event'),
                );
                break;
            default:
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "events/list_all.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('list all events'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Lists all events, regardless of dates.
     */
    function _handler_list_all($handler_id, $args, &$data)
    {
        $this->_root_event->require_do('net.nemein.registrations:manage');

        $qb = net_nemein_registrations_event::get_events_querybuilder();
        $qb->add_order('start');
        $qb->add_order('end');
        $this->_events = $qb->execute();

        $this->_prepare_request_data();
        $title = $this->_l10n->get('list all events');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }

    /**
     * Lists all events, regardless of dates.
     */
    function _show_list_all($handler_id, &$data)
    {
        midcom_show_style('events-list_all-start');

        if ($this->_events)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach (array_keys($this->_events) as $id)
            {
                $data['event'] =& $this->_events[$id];
                $data['view_url'] = "{$prefix}event/view/{$data['event']->guid}.html";
                midcom_show_style('events-list_all-item');
            }
        }
        else
        {
            midcom_show_style('events-list_all-nonefound');
        }

        midcom_show_style('events-list_all-end');
    }

    /**
     * Creates a new event
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_root_event->require_do('midgard:create');

        $this->_controller =& $this->_root_event->prepare_create_controller($this);
        $this->_controller->initialize();
        $this->_process_create_controller();


        $this->_prepare_request_data();
        
        if ($this->_event)
        {
            $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
            $title = $this->_event->title;
        }
        else
        {
            $title = $this->_l10n->get('create an event');
        }
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }

    /**
     * This function processes the creation mode controller
     */
    function _process_create_controller()
    {
        switch ($this->_controller->process_form())
        {
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.

            case 'save':
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_registrations_event::index($this->_controller->datamanager, $indexer, $this->_topic);

                $_MIDCOM->relocate("event/view/{$this->_event->guid}.html");
                // This will exit.
        }
    }

    /**
     * DM2 creation controller callback. Creates a new entry, initializes it. The reference is stored
     * in the class and then returned to DM2.
     */
    function & dm2_create_callback (&$controller)
    {
        // Create a fresh storage object. We need sudo for this.
        $this->_event = new net_nemein_registrations_event();
        $this->_event->up = $this->_root_event->id;
        if ($this->_config->get('event_type') !== null)
        {
            $this->_event->type = $this->_config->get('event_type');
        }

        if (! $this->_event->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_event);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_event;
    }

    /**
     * Creates a new event
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('events-create');
    }


}

?>