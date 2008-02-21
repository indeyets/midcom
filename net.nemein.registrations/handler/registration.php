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

class net_nemein_registrations_handler_registration extends midcom_baseclasses_components_handler
{
    /**
     * The events to register for
     *
     * @var array
     * @access private
     */
    var $_event = null;

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

    /**
     * The currently active registrar, initialized based on the currently authenticated user.
     * Needs write permission to update person details (they'll be read-only otherwise).
     *
     * This is null for anonymous access and will serve as store for the newly created registrar
     * during the save operation.
     *
     * @var net_nemein_registrations_registrar
     * @access private
     */
    var $_registrar = null;

    /**
     * The created registration record, used during the save operation.
     *
     * @var net_nemein_registrations_registration_dba
     * @access private
     */
    var $_registration = null;

    /**
     * The DM2 controller used to do edit operations on registrations.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The DM2 datamanager used to do view operations on registrations.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Management form fieldname: Reject Registration
     *
     * @var string
     * @access private
     */
    var $_reject_action = 'net_nemein_registration_reject';

    /**
     * Management form fieldname: Approve Registration
     *
     * @var string
     * @access private
     */
    var $_approve_action = 'net_nemein_registration_approve';

    /**
     * Management form fieldname: Reject Registration and delete registrar
     *
     * @var string
     * @access private
     */
    var $_rejectdelete_action = 'net_nemein_registration_rejectdelete';

    /**
     * Management form fieldname: Reject Registration notice
     *
     * @var string
     * @access private
     */
    var $_rejectnotice_fieldname = 'rejectnotice';

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['registrar'] =& $this->_registrar;
        $this->_request_data['registration'] =& $this->_registration;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['is_approved'] = $this->_registration->is_approved();

        // Compute a few URLs
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if ($_MIDCOM->auth->can_do('midgard:update', $this->_registration))
        {
            $this->_request_data['edit_url'] = "{$prefix}registration/edit/{$this->_registration->guid}.html";
        }
        else
        {
            $this->_request_data['edit_url'] = null;
        }

        if ($_MIDCOM->auth->can_do('midgard:delete', $this->_registration))
        {
            $this->_request_data['delete_url'] = "{$prefix}registration/delete/{$this->_registration->guid}.html";
        }
        else
        {
            $this->_request_data['delete_url'] = null;
        }

        if (   $_MIDCOM->auth->can_do('midgard:update', $this->_registration)
            && $_MIDCOM->auth->can_do('midgard:delete', $this->_registration)
            && $_MIDCOM->auth->can_do('net.nemein.registrations:manage', $this->_registration)
            && ! $this->_request_data['is_approved'])
        {
            $this->_request_data['manage_form_url'] = "{$prefix}registration/manage/{$this->_registration->guid}.html";
            $this->_request_data['approve_action'] = $this->_approve_action;
            $this->_request_data['reject_action'] = $this->_reject_action;
            $this->_request_data['rejectdelete_action'] = $this->_rejectdelete_action;
            $this->_request_data['rejectnotice_fieldname'] = $this->_rejectnotice_fieldname;
        }
        else
        {
            $this->_request_data['manage_form_url'] = null;
            $this->_request_data['approve_action'] = null;
            $this->_request_data['reject_action'] = null;
            $this->_request_data['rejectdelete_action'] = null;
            $this->_request_data['rejectnotice_fieldname'] = null;
        }
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_registrations_handler_registration()
    {
        parent::midcom_baseclasses_components_handler();
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
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "registration/view/{$this->_registration->guid}.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('registration for %s'), $this->_event->title),
        );

        switch ($handler_id)
        {
            case 'registration-edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "registration/edit/{$this->_registration->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;

            case 'registration-delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "registration/delete/{$this->_registration->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
        }


        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * This handler shows a registration along with a toolbar containing links to further
     * operations on the registration
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_registration = new net_nemein_registrations_registration_dba($args[0]);
        if (! $this->_registration)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_registrar = $this->_registration->get_registrar();
        $this->_event = $this->_registration->get_event();
        $this->_event->require_do('net.nemein.registrations:manage');
        
        $this->_datamanager =& $this->_registration->get_datamanager();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_registration->guid);
        $title = $this->_l10n->get('view registration');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * This handler shows a registration along with a toolbar containing links to further
     * operations on the registration
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('registration-view');
    }

    /**
     * This handler shows a registration edit form.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_registration = new net_nemein_registrations_registration_dba($args[0]);
        if (   !$this->_registration
            || !isset($this->_registration->guid)
            || empty($this->_registration->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$args[0]} could not be found.");
            // This will exit.
        }

        // Privilege check
        $_MIDCOM->auth->require_do('midgard:update', $this->_registration);
        $_MIDCOM->auth->require_do('net.nemein.registrations:manage', $this->_registration);

        $this->_event = $this->_registration->get_event();
        $this->_registrar = $this->_registration->get_registrar();
        $this->_controller =& $this->_registration->create_simple_controller();

        if ($this->_controller->process_form() != 'edit')
        {
            $_MIDCOM->relocate("registration/view/{$this->_registration->guid}.html");
            // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_registration->guid);
        $title = $this->_l10n->get('edit registration');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * This handler shows a registration edit form.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('registration-edit');
    }

    /**
     * This handler shows a registration delete confirmation page
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_registration = new net_nemein_registrations_registration_dba($args[0]);
        if (! $this->_registration)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$args[0]} could not be found.");
            // This will exit.
        }

        // Privilege check
        $_MIDCOM->auth->require_do('midgard:delete', $this->_registration);
        $_MIDCOM->auth->require_do('net.nemein.registrations:manage', $this->_registration);

        // Processing
        if (array_key_exists('net_nemein_registrations_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_registration->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete registration {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nemein_registrations_deletecancel', $_REQUEST))
        {
            // Delete cancelled, relocating to view.
            $_MIDCOM->relocate("registration/view/{$this->_registration->guid}.html");
            // This will exit.
        }

        $this->_registrar = $this->_registration->get_registrar();
        $this->_event = $this->_registration->get_event();
        $this->_datamanager =& $this->_registration->get_datamanager();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_registration->guid);
        $title = $this->_l10n->get('view registration');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * This handler shows a registration delete confirmation page
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('registration-delete');
    }


    /**
     * This handler processes the management POST requests generated by the forms described
     * in the view mode. This handler has no corresponding show function.
     */
    function _handler_manage($handler_id, $args, &$data)
    {
        $this->_registration = new net_nemein_registrations_registration_dba($args[0]);
        if (! $this->_registration)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$args[0]} could not be found.");
            // This will exit.
        }

        // Permission checks
        $_MIDCOM->auth->require_do('midgard:update', $this->_registration);
        $_MIDCOM->auth->require_do('midgard:delete', $this->_registration);
        $_MIDCOM->auth->require_do('net.nemein.registrations:manage', $this->_registration);

        $this->_registrar = $this->_registration->get_registrar();
        $this->_event = $this->_registration->get_event();

        // Process request
        if (array_key_exists($this->_approve_action, $_REQUEST))
        {
            // Approve registration, then relocate to registration list..
            if (! $this->_event->approve_registration($this->_registration))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to approve the registration {$args[0]}, see the debug level log for details.");
                // This will exit.
            }
            $_MIDCOM->relocate("event/list_registrations/{$this->_event->guid}.html");
            // This will exit.
        }
        else if (array_key_exists($this->_reject_action, $_REQUEST))
        {
            if (! array_key_exists($this->_rejectnotice_fieldname, $_REQUEST))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Request incomplete, notice missing.');
                // This will exit.
            }
            $notice = $_REQUEST[$this->_rejectnotice_fieldname];

            // Reject registration, then relocate to registration list.
            if (! $this->_event->reject_registration($this->_registration, $notice))
            {
                $_MIDCOM->generate_error("Failed to reject the registration {$args[0]}, see the debug level log for details.");
                // This will exit.
            }
            $_MIDCOM->relocate("event/list_registrations/{$this->_event->guid}.html");
            // This will exit.
        }
        else if (array_key_exists($this->_rejectdelete_action, $_REQUEST))
        {
            if (! array_key_exists($this->_rejectnotice_fieldname, $_REQUEST))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Request incomplete, notice missing.');
                // This will exit.
            }
            $notice = $_REQUEST[$this->_rejectnotice_fieldname];

            // Reject registration, then relocate to registration list.
            if (! $this->_event->rejectdelete_registration($this->_registration, $notice))
            {
                $_MIDCOM->generate_error("Failed to reject the registration {$args[0]}, see the debug level log for details.");
                // This will exit.
            }
            $_MIDCOM->relocate("event/list_registrations/{$this->_event->guid}.html");
            // This will exit.
        }

        // Unknown request key
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Incomplete request');
        // This will exit
    }



}
?>