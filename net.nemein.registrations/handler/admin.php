<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration administration handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_admin extends midcom_baseclasses_components_handler
{
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
     * The processing message to show.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['processing_msg'] =& $this->_processing_msg;

    }


    function net_nemein_registrations_handler_admin()
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
     * Manages the root event
     */
    function _handler_rootevent($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midcom:component_config');

        // Add additional request keys related to form processing
        $this->_request_data['create_new_value'] = 'NEW';
        $this->_request_data['select_action'] = 'net_nemein_registrations_admin_select_rootevent';
        $this->_request_data['select_guid'] = 'net_nemein_registrations_admin_select_rootevent_guid';

        if (   array_key_exists($this->_request_data['select_action'], $_REQUEST)
            && array_key_exists($this->_request_data['select_guid'], $_REQUEST))
        {
            if ($this->_request_data['root_event'])
            {
                $old_root = $this->_request_data['root_event'];
            }
            else
            {
                $old_root = null;
            }

            if ($_REQUEST[$this->_request_data['select_guid']] != $this->_request_data['create_new_value'])
            {
                $guid = $_REQUEST[$this->_request_data['select_guid']];
                $event = new midcom_db_event($guid);
                if (! $event)
                {
                    $this->_processing_msg = sprintf($this->_l10n->get('failed to open root event %s: %s'), $guid, mgd_errstr());
                }
                else
                {
                    $this->_request_data['root_event'] = new net_nemein_registrations_event($event);
                    $this->_topic->set_parameter('net.nemein.registrations', 'root_event_guid', $guid);

                    // Adjust privileges (use SUDO to be sure of privs)
                    if ($_MIDCOM->auth->request_sudo())
                    {
                        $event->set_privilege('midgard:create', 'USERS');
                        if ($old_root)
                        {
                            $old_root->unset_privilege('midgard:create', 'USERS');
                        }
                        $_MIDCOM->auth->drop_sudo();
                    }
                }
            }
            else
            {
                $event = new midcom_db_event();
                $event->title = "registrations for {$this->_topic->extra} (#{$this->_topic->id})";
                if (! $event->create())
                {
                    $this->_processing_msg = sprintf($this->_l10n->get('failed to create root event: %s'), mgd_errstr());
                }
                else
                {
                    $this->_request_data['root_event'] = new net_nemein_registrations_event($event);
                    $this->_topic->set_parameter('net.nemein.registrations', 'root_event_guid', $event->guid);

                    // Adjust privileges (use SUDO to be sure of privs)
                    if ($_MIDCOM->auth->request_sudo())
                    {
                        $event->set_privilege('midgard:create', 'USERS');
                        if ($old_root)
                        {
                            $old_root->unset_privilege('midgard:create', 'USERS');
                        }
                        $_MIDCOM->auth->drop_sudo();
                    }
                }
            }
        }
        else if (! $this->_root_event)
        {
            // No processing was done, and we do not have a root event:

            $this->_processing_msg = $this->_l10n->get('no root event warning');
        }


        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), null);
        $title = "{$this->_topic->extra}: " . $this->_l10n->get('manage root event');
        $_MIDCOM->set_pagetitle($title);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'admin/rootevent.php',
            MIDCOM_NAV_NAME => $this->_l10n->get('manage root event'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Lists the registrations of a particular event, manage permissions required.
     */
    function _show_rootevent($handler_id, &$data)
    {
        // Load list root events:
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', '');
        $this->_request_data['root_events'] = $qb->execute();

        midcom_show_style('admin-rootevent');
    }


}

?>