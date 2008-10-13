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
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager2 create controller
     *
     * @access private
     * @var midcom_helper_datamanager2_controller_create
     */
    var $_controller;
    
    /**
     * Defaults for the creation mode
     * 
     * @access private
     * @var Array
     */
    var $_defaults;
    
    /**
     * Constructor. Connect to the parent class constructor.
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Load the creation controller
     * 
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * Event conflicts
     */
    function _event_resourceconflict_messages(&$conflict_event)
    {
        $messenger = new org_openpsa_helpers_uimessages();
        reset($conflict_event->busy_em);
        foreach ($conflict_event->busy_em as $pid => $events)
        {
            $person = new org_openpsa_contacts_person($pid);
            if (   !is_object($person)
                || !$person->id)
            {
                continue;
            }
            debug_add("{$person->name} is busy, adding DM errors");
            reset($events);
            foreach ($events as $eguid)
            {
                //We might need sudo to get the event
                $_MIDCOM->auth->request_sudo();
                $event = new org_openpsa_calendar_event($eguid);
                $_MIDCOM->auth->drop_sudo();
                if (   !is_object($event)
                    || !$event->id)
                {
                    continue;
                }
                //Then on_loaded checks again
                $event->_on_loaded();
                debug_add("{$person->name} is busy in event {$event->title}, appending error\n===\n".sprintf('%s is busy in event "%s" (%s)', $person->name, $event->title, $event->format_timeframe())."\n===\n");
                //TODO: Localize
                $messenger->addMessage(sprintf($this->_request_data['l10n']->get('%s is busy in event \'%s\' (%s)'), $person->name, $event->title, $event->format_timeframe()), 'error');
            }
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_event = new midcom_org_openpsa_event();
        $this->_event->up = $this->_root_event->id;

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
     * Handle the creation phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Get the root event
        $this->_root_event = new midcom_org_openpsa_event($this->_config->get('calendar_root_event'));
        
        if (   !$this->_root_event
            || !$this->_root_event->guid)
        {
            $this->_root_event =& org_openpsa_calendar_viewer::create_root_event();
        }
        
        // ACL handling: require create privileges
        $this->_root_event->require_do('midgard:create');
        
        if (isset($args[0]))
        {
            $this->_person = new midcom_db_person($args[0]);
            
            if (   $this->_person
                && $this->_person->guid)
            {
                $this->_defaults['participants'][$this->_person->id] = $this->_person;
            }
        }
        
        if (isset($args[1]))
        {
            $time = $args[1];
            
            if ($time)
            {
                $this->_defaults['start'] = $time;
                $this->_defaults['end'] = $time + 3600;
            }
        }
        
        // Load the controller instance
        $this->_load_controller();
        
        // Process form
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
            
            case 'cancel':
                $_MIDCOM->add_jsonload('window.close();');
                break;
        }
        
        // Hide the ROOT style
        $_MIDCOM->skip_page_style = true;
        
        return true;
    }
    
    /**
     * Show the create screen
     *
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_create($handler_id, &$data)
    {
        if (   array_key_exists('view', $this->_request_data)
            && $this->_request_data['view'] === 'conflict_handler')
        {
            $this->_request_data['popup_title'] = 'resource conflict';
            midcom_show_style('show-popup-header');
            $this->_request_data['event_dm'] =& $this->_controller;
            midcom_show_style('show-event-conflict');
            midcom_show_style('show-popup-footer');
        }
        else
        {
            // Set title to popup
            $this->_request_data['popup_title'] = $this->_request_data['l10n']->get('create event');
            // Show popup
            midcom_show_style('show-popup-header');
            $this->_request_data['event_dm'] =& $this->_controller;
            midcom_show_style('show-event-new');
            midcom_show_style('show-popup-footer');
        }
    }
}
?>