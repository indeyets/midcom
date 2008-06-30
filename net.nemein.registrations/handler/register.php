<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Registrations register page handler
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_handler_register extends midcom_baseclasses_components_handler
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
     * The schema database used for the nullstorage controller. It consists of the merged registrar
     * and add registration schemas in a single schema named 'merged'. No further schemas will be
     * part of this database.
     *
     * @var Array
     * @access private
     */
    var $_nullstorage_schemadb = Array();

    /**
     * Currently active controller instance.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    var $_admin_mode = false;

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
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_registrations_handler_register()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     * Loads the registrar record of the currently authenticated user, if available.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
        $this->_schemadb =& $this->_request_data['schemadb'];
        if ($_MIDCOM->auth->user)
        {
            $this->_registrar = new net_nemein_registrations_registrar($_MIDCOM->auth->user->get_storage());
            // If read-access fails here, we revert transparently to anonymous mode.
        }
        $this->_request_data['admin_mode'] =& $this->_admin_mode;
    }

    /**
     * Registration uses a, lets say, creative way of using the DM2 architecture: If the current
     * user can register to an event, a new schema is constructed out of the registrar and
     * registeration schemas. They need to have unique fieldnames for exactly this operation.
     * Upon successful save, two individual DM2 instances are used to actually process the
     * data.
     *
     * If an event is not open for registration, a 404 is triggered. The same will be done
     * if an user is already registered to the event.
     */
    function _handler_register($handler_id, $args, &$data)
    {
        // Validate args.
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        if ($handler_id === 'admin-register')
        {
            $this->_event->require_do('net.nemein.registrations:manage');
            $this->_admin_mode = true;
        }
        if (   !$this->_admin_mode
            && !$this->_event->is_open())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} is not open for registration.");
            // This will exit.
        }

        if ($this->_admin_mode)
        {
            // Admin mode, register other people...
            $this->_registrar = null;
        }
        elseif ($this->_event->is_registered())
        {
            // In case we are already registered, we relocate to the view registration page.
            $registration = $this->_event->get_registration();
            $_MIDCOM->relocate("registration/view/{$registration->guid}.html");
            // This will exit.
        }

        // Before we do anything, check whether there is a cancel button in the request.
        // If yes, redirect back to the welcome page.
        // This will shortcut without creating any datamanager to avoid the possibly
        // expensive creation process.
        if (midcom_helper_datamanager2_formmanager::get_clicked_button() == 'cancel')
        {
            if ($this->_admin_mode)
            {
                $_MIDCOM->relocate("event/view/{$this->_event->guid}/");
            }
            $_MIDCOM->relocate('');
            // This will exit.
        }

        // $this->_process_nullstorage_controller() might change this!
        $data['show_style'] = 'register-form';

        // Further startup work
        $this->_validate_permissions();
        $this->_prepare_nullstorage_schemadb();
        if ($this->_admin_mode)
        {
            // Don't bother with confirms for admin mode even if set in schema...
            if (isset($this->_nullstorage_schemadb['merged']->operations['next']))
            {
                unset($this->_nullstorage_schemadb['merged']->operations['next']);
            }
        }
        
        $this->_prepare_nullstorage_controller();

        // Process the form
        $this->_process_nullstorage_controller();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = sprintf($this->_l10n->get('register for %s'), $this->_event->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        return true;
    }

    /**
     * The register handler loades the currently visible events and displays them.
     *
     * @access private
     */
    function _show_register($handler_id, &$data)
    {
        midcom_show_style($data['show_style']);
    }

    /**
     * Processes data sent back from the client. If the form validates, we do the save cycle and
     * redirect to a thank-you(tm) page. Only the save event is handled here, cancel is already
     * processed during startup.
     *
     * @access private
     */
    function _process_nullstorage_controller()
    {
        //$schema =& $this->_controller->datamanager->schema;
        $schema =& $this->_controller->schemadb[$this->_controller->schemaname];
        switch ($this->_controller->process_form())
        {
            default:
            case 'previous':
            case 'edit':
                // If we have both next and save defined in schema, clear the save
                if (   isset($schema->operations['save'])
                    && isset($schema->operations['next']))
                {
                    unset($schema->operations['save']);
                }
                // If we have previous in schema clear it, this is the "first" operation
                if (isset($schema->operations['previous']))
                {
                    unset($schema->operations['previous']);
                }
                // re-initialize the formmanager after mucking the schema
                $this->_controller->formmanager->initialize();
                $this->_controller->formmanager->process_form();
                break;
            case 'next':
                // If we don't have save defined in schema (weird but valid) add it
                if (!isset($schema->operations['save']))
                {
                    $schema->operations['save'] = 'confirm registration';
                }
                // If we don't have previous defined add it
                if (!isset($schema->operations['previous']))
                {
                    $schema->operations['previous'] = 'back';
                }
                // And remove the next option (which obviously was there or we would never reach here)
                unset($schema->operations['next']);
                
                // re-initialize the formmanager after mucking the schema
                $this->_controller->formmanager->initialize();
                // Should not be strictly neccessary anymore to be exact
                $this->_controller->formmanager->process_form();
                $this->_controller->formmanager->freeze(); // Make all elements read-only!
                $this->_request_data['show_style'] = 'register-confirm';
                break;
            case 'save':
                // TODO: refactor to smaller methods
                if (   $this->_config->get('allow_multiple')
                    && isset($this->_controller->datamanager->types['events'])
                    && isset($this->_controller->datamanager->types['events']->selection)
                    && is_array($this->_controller->datamanager->types['events']->selection)
                    && !empty($this->_controller->datamanager->types['events']->selection)
                    )
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add('entered handler for multiple events registration');
                    // We have multiple courses to process, start by verifying the registrar
                    $registar_created = false;
                    if ($this->_registrar)
                    {
                        $this->_update_registrar();
                    }
                    else
                    {
                        $this->_create_registrar();
                        $registar_created = true;
                    }
                    // Make a backup copy
                    $this_event_backup = $this->_event;
                    $registration_ids = array();
                    foreach ($this->_controller->datamanager->types['events']->selection as $guid)
                    {
                        $event = new net_nemein_registrations_event($guid);
                        if (   !is_object($event)
                            || empty($event->id))
                        {
                            // invalid id/guid
                            debug_add("Identifier '{$guid}' does not point to a valid event", MIDCOM_LOG_ERROR);
                            continue;
                        }
                        if (   !$this->_admin_mode
                            && !$event->is_open())
                        {
                            // not open for registration
                            debug_add("Event {$event->title} (#{$event->id}) is not open for registration", MIDCOM_LOG_ERROR);
                            continue;
                        }
                        debug_add("Creating registration for event {$event->title} (#{$event->id})", MIDCOM_LOG_INFO);
                        $this->_event = $event;
                        // TODO: how to handle failure in the middle of successes ??
                        $this->_create_registration(false);
                        $registration_ids[] = $this->_registration->id;
                    }
                    // Restore backup
                    $this->_event = $this_event_backup;
                    // List the successfull registrations
                    $session = new midcom_service_session();
                    $session->set('registration_ids', $registration_ids);
                    // just to keep defaults from barfing
                    $session->set('registration_id', $this->_registration->id);
                    debug_pop();
                    // TODO: redirect elsewhere in admin mode ?
                    $_MIDCOM->relocate('register/success.html');
                }
                // First, update/create the person
                // Then, create the registration.
                if ($this->_registrar)
                {
                    $this->_update_registrar();
                    $this->_create_registration(false);
                }
                else
                {
                    $this->_create_registrar();
                    $this->_create_registration(true);
                }
    
                $session = new midcom_service_session();
                $session->set('registration_id', $this->_registration->id);
                if ($this->_admin_mode)
                {
                    if (!$this->_config->get('admin_register_loop'))
                    {
                        $_MIDCOM->relocate("registration/view/{$this->_registration->guid}/");
                        // This will exit()
                    }
                    $_MIDCOM->uimessages->add('net.nemein.registrations', sprintf($this->_l10n->get('registered person %s'), $this->_registrar->name));
                    // copy _POST variables
                    $copies = array();
                    $skip = $this->_config->get('admin_register_loop_clear_fields');
                    foreach ($schema->fields as $fieldname => $typedef)
                    {
                        if (in_array($fieldname, $skip))
                        {
                            continue;
                        }
                        $copies[$fieldname] = $_POST[$fieldname];
                    }
                    $session =& new midcom_service_session();
                    $session->set('prepopulate_data', $copies);
                    $_MIDCOM->relocate("admin/register/{$this->_event->guid}/");
                    // This will exit()
                }
                $_MIDCOM->relocate('register/success.html');
                // This will exit()
                break;
        }
    }

    /**
     * This function will create a new registrar record to save the form data to.
     * If saving fails, generate_error is triggered and the created record is dropped again.
     *
     * Optionally, this class will drop the registrar record in $this->_registrar on any error.
     * This should only be activated for registrars created previously using create_registration.
     * Sudo mode will be used for this.
     *
     * This function will run under sudo privileges if no user is authenticated, otherwise
     * we would be unable to correctly create the new record (we don't have an owner).
     *
     * @param bool $drop_registrar Set this to true if you want to delete the registrar record
     *     on any error.
     * @access private
     */
    function _create_registration($drop_registrar)
    {
        /**
         * FIXME: The whole flow here needs to be seriously re-though for non-sudo usage with unprivileged users
         * thus we use sudo explicitly to make it work for now.
         * 
        if (! $_MIDCOM->auth->user)
        {
        */
            if (! $_MIDCOM->auth->request_sudo())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Your registrations has not been processed: Failed to obtain sudo privileges.');
                // This will exit.
            }
        /*
        }
        */

        $this->_registration = new net_nemein_registrations_registration_dba();
        $this->_registration->eid = $this->_event->id;
        $this->_registration->uid = $this->_registrar->id;
        if (! $this->_registration->create())
        {
            /*
            if (! $_MIDCOM->auth->user)
            {
            */
                $_MIDCOM->auth->drop_sudo();
            /*
            }
            */

            if ($drop_registrar)
            {
                // This will succeed, as it has been requested once before already
                // during create_registrar.
                $_MIDCOM->auth->request_sudo();
                $this->_registrar->delete();
                $_MIDCOM->auth->drop_sudo();
            }
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to create the registration record.');
            // This will exit.
        }

        // Set privileges: Registrar is owner, deny anonymous read privileges
        $this->_registration->unset_all_privileges();
        $user =& $_MIDCOM->auth->get_user($this->_registrar->guid);
        $this->_registration->set_privilege('midgard:owner', $user);
        $this->_registration->set_privilege('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY);

        // Prepare other required objects now
        $event_dm =& $this->_event->get_datamanager();

        // Set the schema name on the object.
        if (count($event_dm->types['additional_questions']->selection) == 0)
        {
            $registration_schema = 'aq-default';
        }
        else
        {
            $registration_schema = $event_dm->types['additional_questions']->selection[0];
        }
        
        $this->_registration->set_parameter('midcom.helper.datamanager2', 'schema_name', $registration_schema);

        // Update the account with the selected information
        $controller =& $this->_registration->create_simple_controller($registration_schema);
        if ($controller->process_form() != 'save')
        {
            /*
            if (! $_MIDCOM->auth->user)
            {
            */
                $_MIDCOM->auth->drop_sudo();
            /*
            }
            */

            if ($drop_registrar)
            {
                // This will succeed, as it has been requested once before already
                // during create_registrar.
                $_MIDCOM->auth->request_sudo();
                $this->_registrar->delete();
                $_MIDCOM->auth->drop_sudo();
            }
            $this->_registration->delete();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to save the registration data.');
            // This will exit.
        }

        if (   $this->_admin_mode
            || $event_dm->types['auto_approve']->value)
        {
            $this->_event->approve_registration($this->_registration, !($this->_admin_mode));
        }
        else
        {
            // Send message to event administrators about the new registration that waits for approval
            $this->_send_approval_notification();
            
            if ($this->_registrar->email)
            {
                // Send email to the user also that the event is pending approval
                $this->_send_pending_notification();
            }
        }

        /*
        if (! $_MIDCOM->auth->user)
        {
        */
            $_MIDCOM->auth->drop_sudo();
        /*
        }
        */
    }

    /**
     * Sends the notification E-Mail about event pending approval to the registrar's Mail address
     *
     * @access private
     */
    function _send_pending_notification()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $bodies = $registration->compose_mail_bodies();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $text_event = $this->_l10n->get('event');
        $text_registrar = $this->_l10n->get('registrar');

        $mail = new org_openpsa_mail();
        $mail->from = $this->_config->get('mail_registration_sender');
        $mail->to = $this->_registrar->email;        
        $mail->subject = $this->_config->get('mail_registration_pending_subject');
        $mail->subject = $registration->expand_keywords($mail->subject);
        $mail->body = $bodies['pending_text'];
        $mail->body = $registration->expand_keywords($mail->body);
        
        if (!empty($bodies['pending_html']))
        {
            // if we have non-empty html body composed, add it to the mail object, resolving embeds etc
            $mail->html_body = $registration->expand_keywords($bodies['pending_html']);
            list ($mail->html_body, $mail->embeds) = $mail->html_get_embeds($this, $mail->html_body, $mail->embeds);
        }

        if (!$mail->send())
        {
            debug_add("Could not send E-Mail to '{$this->_registrar->email}' with subject '{$mail->subject}', got error: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
            //debug_print_r('Mail object:', $mail);
        }
        else
        {
            debug_add("Sent E-Mail to {$this->_registrar->email} with subject '{$mail->subject}'.", MIDCOM_LOG_ERROR);
            //debug_print_r('Mail object:', $mail);
        }

        debug_pop();

    }

    /**
     * Sends the approval notification E-Mail to the configured notification Mail address
     *
     * @access private
     */
    function _send_approval_notification()
    {
        $subject = $this->_l10n->get('new registration pending for approval');
        $sender = $this->_config->get('mail_registration_sender');
        $cc = explode(',', $this->_event->get_notification_email());

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $text_event = $this->_l10n->get('event');
        $text_registrar = $this->_l10n->get('registrar');
        $body = <<<EOF
{$subject}:

{$text_event}: {$this->_event->title}
{$text_registrar}: {$this->_registrar->name}
URL: {$prefix}registration/view/{$this->_registration->guid}.html
EOF;

        $headers = "From: {$sender}\r\nReply-To: {$sender}\r\nX-Mailer: PHP/" . phpversion();

        foreach ($cc as $email)
        {
            $email = trim ($email);
            if ($email == '')
            {
                // Skipping an empty cc line, perhaps a comma too much
                continue;
            }
            $mail = new org_openpsa_mail();
            $mail->to = $email;
            $mail->subject = $subject;
            $mail->body = $body;
            $mail->from = $sender;
            if (!$mail->send())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not send E-Mail to '{$email}' with subject '{$subject}', got error: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
                //debug_print_r('Mail object:', $mail);
                debug_pop();
            }
        }
    }


    /**
     * This function will create a new registrar record to save the form data to.
     * If saving fails, generate_error is triggered and the created record is dropped again.
     *
     * @access private
     */
    function _create_registrar()
    {
        if (! $_MIDCOM->auth->request_sudo())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to obtain sudo privileges.');
            // This will exit.
        }

        // Create the record
        $this->_registrar = new net_nemein_registrations_registrar();
        if (! $this->_registrar->create())
        {
            $_MIDCOM->auth->drop_sudo();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to create a new registrar record.');
            // This will exit.
        }

        // Update the account with the selected information
        $controller =& $this->_registrar->create_simple_controller();
        if ($controller->process_form() != 'save')
        {
            $this->_registrar->delete();
            $_MIDCOM->auth->drop_sudo();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to save the registrar data.');
            // This will exit.
        }

        // Finally, Add to group
        if ($this->_config->get('account_grp'))
        {
            // Add the new account to the configured group. If this fails, we bail out.
            $group = new midcom_db_group($this->_config->get('account_grp'));
            if (! $group)
            {
                $this->_registrar->delete();
                $_MIDCOM->auth->drop_sudo();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Your registrations has not been processed: Failed to load the account group.');
                // This will exit.
            }
            $group->add_member($this->_registrar);
        }

        $_MIDCOM->auth->drop_sudo();
    }

    /**
     * This function updates the currently loaded registrar with the information obtained through
     * the original nullstorage controller. If saving fails, generate_error is triggered.
     *
     * This function will exit silently if we don't have update privileges on the person record.
     *
     * @access private
     */
    function _update_registrar()
    {
        if (! $_MIDCOM->auth->can_do('midgard:update', $this->_registrar))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Missing update privileges on registrar record, skipping registrar update.');
            debug_pop();
            return;
        }

        $controller =& $this->_registrar->create_simple_controller();
        if ($controller->process_form() != 'save')
        {
            // This points to some more esoteric error condition, maybe privileges
            // changing during runtime etc. Normally saving should succeed as we had full
            // validation in the process stage already.
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Your registrations has not been processed: Failed to save the registrar data even though all ' .
                'starup checks succeeded, check the debug log for more information.');
            // This will exit.
        }
    }

    /**
     * Creates the nullstorage controller used to process the registration.
     *
     * @access private
     */
    function _prepare_nullstorage_controller()
    {
        if ($this->_registrar)
        {
            $dm =& $this->_registrar->get_datamanager();
            $defaults = $dm->get_content_raw();
        }
        else
        {
            $defaults = Array();
            if ($this->_config->get('allow_multiple'))
            {
                $defaults['events'] = array($this->_event->guid => true);
            }
        }

        
        $session =& new midcom_service_session();
        if ($session->exists('prepopulate_data'))
        {
            $defaults = array_merge($defaults, $session->get('prepopulate_data'));
            /*
            echo "DEBUG: defaults after session data<pre>\n";
            print_r($defaults);
            echo "</pre>\n";
            */
            $session->remove('prepopulate_data');
        }
        unset($session);

        $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->set_schemadb($this->_nullstorage_schemadb);
        $this->_controller->schemaname = 'merged';
        $this->_controller->defaults = $defaults;
        $this->_controller->initialize();
    }

    /**
     * This function prepares the schemadb containing merged schema for the nullstorage controller.
     * The registrar fields will be set readonly if the current user does not have write permission
     * to his own record. On anonymous access, no such thing is made as we create a new record.
     *
     * @access private
     */
    function _prepare_nullstorage_schemadb()
    {
        // A bit hackish but we need the full instance...
        $interface = $_MIDCOM->componentloader->get_interface_class('net.nemein.registrations');
        $viewer = $interface->_context_data[$_MIDCOM->get_current_context()]['handler'];
        $this->_nullstorage_schemadb['merged'] = $viewer->create_merged_schema($this->_event, $this->_registrar);
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('Merged schema:', $registrar_schema);
        debug_pop();
        */
    }

    /**
     * Validates permissions during request startup.
     *
     * @access private
     */
    function _validate_permissions()
    {
        $this->_event->require_do('midgard:create');
    }

    /**
     * This page shows a success page. It uses sessioning to receive its argument from the registration
     * sequece for security reasons.
     *
     * The record is loaded in sudo mode if no user is authenticated, since anonymous users don't
     * have access to any registration in the system.
     */
    function _handler_success($handler_id, $args, &$data)
    {
        // Validate args.
        $session = new midcom_service_session();
        if (! $session->exists('registration_id'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "No session data set.");
            // This will exit.
        }
        $id = $session->remove('registration_id');

        if (! $_MIDCOM->auth->user)
        {
            if (! $_MIDCOM->auth->request_sudo())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to obtain sudo privileges.');
                // This will exit.
            }
        }
        $this->_registration = new net_nemein_registrations_registration_dba($id);
        if (! $_MIDCOM->auth->user)
        {
            $_MIDCOM->auth->drop_sudo();
        }

        if (! $this->_registration )
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The registration {$id} could not be found.");
            // This will exit.
        }

        $this->_registrar = $this->_registration->get_registrar();
        $this->_event = $this->_registration->get_event();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_event->guid);
        $title = sprintf($this->_l10n->get('register for %s'), $this->_event->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        return true;
    }

    /**
     * Shows the success page.
     */
    function _show_success($handler_id, &$data)
    {
        midcom_show_style('register-complete');
    }

}

?>