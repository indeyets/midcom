<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_event_view  extends midcom_baseclasses_components_handler
{
    /**
     * The events to register for
     *
     * @var array
     * @access private
     */
    var $_event = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The Datamanager of the event to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_controller = null;

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_event_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _prepare_request_data()
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['controller'] =& $this->_controller;
    }

    function _load_event($identifier,$hacked=false)
    {
        $event = new org_maemo_calendar_event($identifier);
        if (!is_object($event))
        {
            return false;
        }

        if ($hacked)
        {
            return $event->return_as_dm2_hacked();
        }

        return $event;
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];

        /*
         * Callback needs to know current event creator if it is not us.
         * Populate widget config parameter person_guid
         */
        if (isset($this->_schemadb['default']->fields['tags']))
        {
            if ($this->_schemadb['default']->fields['tags']['widget'] == 'tags')
            {
                debug_add("tags widget is tags!");
                if ($this->_event->metadata->creator != $_MIDCOM->auth->user->guid)
                {
                    debug_add("Creator is not us, add '{$this->_event->metadata->creator}' to tags widget config!");
                    $this->_schemadb['default']->fields['tags']['type_config']['option_callback_args']['person_guid'] = $this->_event->metadata->creator;
                }
            }
        }
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();

        $this->_controller =& new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_controller
            || ! $this->_controller->set_schema('default') )
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }

        $this->_controller->set_storage($this->_event);
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_show($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if ($handler_id == 'ajax-event-show')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_event = $this->_load_event($args[0], true);
        if (!$this->_event)
        {
            return false;
        }

        $this->_load_controller();

        // Muck schema on private events
        if (!$this->_event->can_do('org.openpsa.calendar:read'))
        {
            foreach ($this->_controller->_layoutdb['default']['fields'] as $fieldname => $field)
            {
                switch ($fieldname)
                {
                    case 'title':
                    case 'start':
                    case 'end':
                        break;
                    default:
                        $this->_controller->_layoutdb['default']['fields'][$fieldname]['hidden'] = true;
                }
            }
        }

        $this->_prepare_request_data();

        // Load the document to datamanager
        //$this->_controller->set_storage($this->_event);
        // if (! $this->_controller->initialize($this->_event))
        // {
        //     $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
        //     // This will exit.
        // }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_show($handler_id, &$data)
    {
        if ($handler_id == 'ajax-event-show')
        {
            midcom_show_style('event-show-ajax');
        }
        else
        {
            midcom_show_style('event-show');
        }
    }


}

?>