<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Registrations welcome page handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_welcome extends midcom_baseclasses_components_handler
{
    /**
     * The events to list on the front page.
     *
     * @var array
     * @access private
     */
    var $_events = null;

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
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['events'] =& $this->_events;
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_registrations_handler_welcome()
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
     * The welcome handler loads the currently visible events and displays them.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        if ($this->_config->get('welcome_page_list_closed'))
        {
            $this->_events = net_nemein_registrations_event::list_all();
        }
        else
        {
            $this->_events = net_nemein_registrations_event::list_open();
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), null);
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        $_MIDCOM->bind_view_to_object($this->_root_event);

        if ($this->_root_event->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item
            (
                Array
                (
                    MIDCOM_TOOLBAR_URL => "events/create.html",
                    MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('create an event'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        if ($this->_root_event->can_do('net.nemein.registrations:manage'))
        {
            $this->_node_toolbar->add_item
            (
                Array
                (
                    MIDCOM_TOOLBAR_URL => "events/list_all.html",
                    MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('list all events'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        return true;
    }

    /**
     * The welcome handler loades the currently visible events and displays them.
     */
    function _show_welcome($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) ;

        midcom_show_style('welcome-start');

        if (! $this->_events)
        {
            midcom_show_style('welcome-nonefound');
        }
        else
        {
            foreach ($this->_events as $event)
            {
                $data['registration_allowed'] = $event->can_do('midgard:create');
                $data['registration_open'] = $event->is_open();
                $data['register_url'] = $event->get_registration_link();
                if ($event->is_registered())
                {
                    $registration = $event->get_registration();
                    $data['registration_url'] = "{$prefix}registration/view/{$registration->guid}.html";
                }
                else
                {
                    $data['registration_url'] = null;
                }
                $data['view_url'] = "{$prefix}event/view/{$event->guid}.html";
                $data['event'] =& $event;

                midcom_show_style('welcome-item');
            }
        }

        midcom_show_style('welcome-end');
    }

}

?>
