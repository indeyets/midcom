<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: event.php 6056 2007-05-25 13:52:04Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration management handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_compose extends midcom_baseclasses_components_handler
{
    /**
     * The event to compose for
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_event = null;

    /**
     * The event to compose for
     *
     * @var net_nemein_registrations_registration_dba
     * @access private
     */
    var $_registration = null;

    /**
     * The root event (taken from the request data area)
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_content_topic = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    var $_output_type = 'html';

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
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
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "event/view/{$this->_event->guid}.html",
            MIDCOM_NAV_NAME => $this->_event->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "registration/view/{$this->_registration->guid}.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('registration for %s'), $this->_event->title),
        );

        switch ($handler_id)
        {
            case 'show-invoice':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "invoice/{$this->_registration->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('invoice'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    function _handler_compose($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->request_sudo('net.nemein.registrations');
        $this->_registration = new net_nemein_registrations_registration_dba($args[0]);
        if (   !$this->_registration
            || !isset($this->_registration->guid)
            || empty($this->_registration->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_event = $this->_registration->get_event();
        if (   !$this->_event
            || !isset($this->_event->guid)
            || empty($this->_event->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "\$this->_registration->get_event() did not return valid event object.");
            // This will exit.
        }
        $this->_registration->populate_compose_data($data);
        $_MIDCOM->auth->drop_sudo();

        if ($handler_id === 'show-invoice')
        {
            $this->_output_type = 'html';
            $title = $this->_event->title;
            $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('invoice for %s'), $this->_event->title));
            $_MIDCOM->skip_page_style = true;
        }
        else
        {
            switch ($args[1])
            {
                case 'html':
                case 'text':
                    $this->_output_type = $args[1];
                    break;
                default:
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Unknown output mode '{$args[1]}'.");
                    // This will exit.
            }
        }

        if ($handler_id === 'compose_test')
        {
            $data['composed_mail_bodies'] = $this->_registration->compose_mail_bodies();
        }

        $_MIDCOM->set_26_request_metadata($this->_registration->metadata->revised, $this->_registration->guid);
        $this->_update_breadcrumb_line($handler_id);

        switch($this->_output_type)
        {
            case 'html':
                /* Default: No need to force
                $_MIDCOM->cache->content->content_type('text/html');
                */
                break;
            case 'text':
                $_MIDCOM->skip_page_style = true;
                $_MIDCOM->cache->content->content_type('text/plain');
                break;
        }

        return true;
    }

    /**
     * Lists the registrations of a particular event, manage permissions required.
     */
    function _show_compose($handler_id, &$data)
    {
        //midcom_show_style("show-{$handler_id}-{$this->_output_type}");
        switch ($handler_id)
        {
            case 'show-invoice':
                midcom_show_style($handler_id);
                break;
            default:
                midcom_show_style("show-{$handler_id}-{$this->_output_type}");
        }
    }

}

?>