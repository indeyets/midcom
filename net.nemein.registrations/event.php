<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

// Make sure our dependencies are loaded
if (!class_exists('net_nemein_calendar_event_dba'))
{
    $_MIDCOM->componentloader->load('net.nemein.calendar');
}

/**
 * Event registration system: Event class
 *
 * This class encapsulates an event which can be registered to.
 *
 * TODO...
 *
 * @package net.nemein.registrations
 */
class net_nemein_registrations_event extends net_nemein_calendar_event_dba
{
   /**
    * Human readable start and end date
    */
    var $start_hr_date;
    var $end_hr_date;
    var $start2end_hr_date;
    var $start_hr_datetime;
    var $end_hr_datetime;
    var $start2end_hr_datetime;

    /**
     * Request data information
     *
     * @access private
     */
    var $_request_data;

    /**
     * Request data information
     *
     * @access private
     */
    var $_config;

    /**
     * Request data information
     *
     * @access private
     */
    var $_topic;

    /**
     * Request data information
     *
     * @access private
     */
    var $_l10n;

    /**
     * Request data information
     *
     * @access private
     */
    var $_l10n_midcom;

    /**
     * Request data information
     *
     * @access private
     */
    var $_content_topic;

    /**
     * The DM2 datamanager instance encapsulating this object. Initialized on first access
     * via get_datamanager.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_dm;

    /**
     * Internal cache used for is_registered / get_registration.
     *
     * @var Array
     * @access private
     */
    var $_registration_cache = Array();


    /**
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * It will bind the instance to the current request data to access configuration
     * data.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _on_created()
    {
        $this->_do_bindings();
        return true;
    }

    function _do_bindings()
    {
        $this->_bind_to_request_data();
        $this->_content_topic =& $this->_request_data['content_topic'];

        $start_ts = strtotime($this->start);
        $end_ts = strtotime($this->end);
        $start_ts_hr_date = strftime('%x', $start_ts);
        $end_ts_hr_date = strftime('%x', $end_ts);
        $start_ts_hr_datetime = strftime('%x', $start_ts) . date(' H:i', $start_ts);
        $end_ts_hr_datetime = strftime('%x', $end_ts) . date(' H:i', $end_ts);
        $start_ts2end_hr_datetime = net_nemein_calendar_functions_daylabel('start', $start_ts, $end_ts) . ' - ' . midcom_helper_generate_daylabel('end', $start_ts, $end_ts);
        $start_ts2end_hr_date = net_nemein_calendar_functions_daylabel('start', $start_ts, $end_ts, false) . ' - ' . midcom_helper_generate_daylabel('end', $start_ts, $end_ts, false);
    }

    function _on_loaded()
    {
        if (!parent::_on_loaded())
        {
            return false;
        }
        $this->_do_bindings();
        return true;
    }

    function _on_updating()
    {
        if (!parent::_on_updating())
        {
            return false;
        }
        // This will also recalculate the human-readable properties that might prove usefull
        $this->_do_bindings();
        return true;
    }

    /**
     * Binds the object to the current request data. This populates the members
     * _request_data, _config, _topic, _l10n and _l10n_midcom accordingly.
     */
    function _bind_to_request_data()
    {
        // CAVEAT: This is likely to only work correctly when registrations is in fact in charge of the request 
        $this->_request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $this->_config =& $this->_request_data['config'];
        $this->_topic =& $this->_request_data['topic'];
        $this->_l10n =& $this->_request_data['l10n'];
        $this->_l10n_midcom =& $this->_request_data['l10n_midcom'];
    }

    /**
     * Returns a DM2 datamanager instance for this object.
     *
     * @return midcom_helper_datamanager2_datamanager A reference to the newly created datamanager instance.
     */
    function & get_datamanager()
    {
        $this->_populate_dm();

        return $this->_dm;
    }

    /**
     * Populates the _dm member.
     */
    function _populate_dm()
    {
        if (! $this->_dm)
        {
            $this->_dm = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);
            $this->_dm->set_schema($this->_config->get('event_schema'));
            $this->_dm->set_storage($this);
        }
    }

    /**
     * Creates a DM2 simple controller instance for this object, used for editing.
     *
     * @return midcom_helper_datamanage2_controller_simple A reference to the new
     *     DM2 controller.
     */
    function & create_simple_controller()
    {
        $controller =& midcom_helper_datamanager2_controller::create('simple');
        $controller->set_schemadb($this->_request_data['schemadb']);
        $controller->set_storage($this, $this->_config->get('event_schema'));
        $controller->initialize();
        return $controller;
    }


    /**
     * Creates a DM2 create controller instance for this object. The controller will not
     * be initialized, allowing you to do further modifications before actual startup.
     *
     * @param object $callback The creation mode callback reference.
     * @param array $defaults The defaults to use for the controller, defaults to an
     *     empty set.
     * @return midcom_helper_datamanage2_controller_create A reference to the new
     *     DM2 controller.
     */
    function & prepare_create_controller(&$callback, $defaults = Array())
    {
        $controller =& midcom_helper_datamanager2_controller::create('create');
        $controller->set_schemadb($this->_request_data['schemadb']);
        $controller->schemaname = $this->_config->get('event_schema');
        $controller->callback_object =& $callback;
        $controller->defaults = $defaults;
        return $controller;
    }



    /**
     * Returns the mail address to which to send notification E-Mails.
     *
     * @return string The notification E-Mail Addresses.
     */
    function get_notification_email()
    {
        $this->_populate_dm();

        if ($this->_dm->types['notification_email']->value)
        {
            return $this->_dm->types['notification_email']->value;
        }
        else
        {
            return $this->_config->get('mail_registration_ccs');
        }
    }

    /**
     * Returns the name of the additional questions schema.
     *
     * @return string The schema name.
     */
    function get_additional_questions_schema()
    {
        $this->_populate_dm();
        
        if (count($this->_dm->types['additional_questions']->selection) > 0)
        {
            return $this->_dm->types['additional_questions']->selection[0];
        }
        else
        {
            return 'aq-default';
        }
    }

    /**
     * Checks if the event is open for registration.
     *
     * @return bool True if open.
     */
    function is_open($use_dm = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for event #{$this->id}");
        debug_pop();
        if (!class_exists('Date'))
        {
            require('Date.php');
        }

        $now = new Date();
        $open = new Date($this->openregistration);
        $close = new Date($this->closeregistration);

        /**
         * FIXME: These calls foul up global server timezone configuration
         * in mysterious ways leading time format functions to always return
         * times in UTC in stead of local time.
         *
         * apparently they also don't quite work as they should
         */

        // Sanity
        if ($close->before($open))
        {
            $close = null;
        }

        if ($open->before($now))
        {
            if ($close)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$close is set, we return \$close->after(\$now)");
                debug_pop();
                return $close->after($now);
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$close is not set, returning true");
                debug_pop();
                return true;
            }
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("\$open->before(\$now) returned false, so do we");
        debug_pop();

        return false;
    }

    /**
     * Opens the event for registration. The close date will not be touched.
     *
     * This call requires midgard:update.
     */
    function open_registration()
    {
        $this->require_do('midgard:update');

        $this->_populate_dm();

        $this->_dm->types['open_registration']->value = new Date();
        
        // Handle closing time intelligently
        if ($this->closeregistration < time())
        {
            $this->_dm->types['close_registration']->value = new Date($this->start);
        }

        $this->_dm->save();
    }

    /**
     * Closes the event registration. The open date will not be touched.
     *
     * This call requires midgard:update.
     */
    function close_registration()
    {
        $this->require_do('midgard:update');

        $this->_populate_dm();

        $this->_dm->types['close_registration']->value = new Date();

        $this->_dm->save();
    }


    /**
     * Returns a list of registrar records associated with this object.
     *
     * @return Array of net_nemein_registration_registrar records.
     */
    function get_registrars()
    {
        $qb = $this->get_registrations_qb();
        $qb->add_order('uid.lastname');
        $qb->add_order('uid.firstname');
        $event_members = $qb->execute();

        $result = Array();
        if ($event_members)
        {
            foreach ($event_members as $id => $member)
            {
                $result[$member->uid] = new net_nemein_registrations_registrar($member->uid);
                if (! $member->uid)
                {
                    unset ($result[$member->uid]);
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to load the registrar person record {$member->uid}, skipping entry. Last Midgard error was: " . mgd_errstr, MIDCOM_LOG_WARN);
                    debug_pop();
                }
            }
        }
    }

    /**
     * Check if a given user/person is already registered to this event. If yes, the corresponding
     * registration record is returned.
     *
     * In case you default to the currently authenticated user when there is no user authenticated,
     * the function will return false unconditionally.
     *
     * This call will cache registrations for better performance.
     *
     * @param mixed $user This can either be a midcom_core_user, a midcom_baseclasses_database_person,
     *     or any valid person id/guid. If you omit the argument, it defaults to the currently
     *     authenticated user.
     * @return net_nemein_registrations_registration_dba The found registration record, or false if
     *     the user is not yet registered.
     */
    function get_registration($user = null)
    {
        if ($user == null)
        {
            if (! $_MIDCOM->auth->user)
            {
                // Anonymous mode.
                return false;
            }
            $user =& $_MIDCOM->auth->user;
            $person = $user->get_storage();
            $id = $person->id;
        }
        else if (is_a($user, 'midcom_core_user'))
        {
            $person = $user->get_storage();
            $id = $person->id;
        }
        else if (is_a($user, 'midcom_baseclasses_database_person'))
        {
            $id = $user->id;
        }
        else if (mgd_is_guid($user))
        {
            $person = new midcom_db_person($user);
            $id = $person->id;
        }
        else
        {
            $id = $user;
        }

        if (! array_key_exists($id, $this->_registration_cache))
        {
            $qb = $this->get_registrations_qb();
            $qb->add_constraint('uid', '=', $id);
            $result = $qb->execute();

            if ($result)
            {
                $this->_registration_cache[$id] = $result[0];
            }
            else
            {
                $this->_registration_cache[$id] = false;
            }
        }

        return $this->_registration_cache[$id];
    }

    /**
     * Check if a given user/person is already registered to this event.
     *
     * In case you default to the currently authenticated user when there is no user authenticated,
     * the function will return false unconditionally.
     *
     * @param mixed $user This can either be a midcom_core_user, a midcom_baseclasses_database_person,
     *     or any valid person id/guid. If you omit the argument, it defaults to the currently
     *     authenticated user.
     * @return bool Registration state.
     */
    function is_registered($user = null)
    {
        return (bool) $this->get_registration($user);
    }

    /**
     * Returns the registration link applicable for the current user. The following guidelines
     * are taken:
     *
     * 1. If we have an anonymous user, the registration link is available always. The final
     *    permission checks are done on the registration page, where registration will
     *    be allowed due to correct config/privileges, or, if denied, a login page will be shown.
     * 2. If an use is authenticated, we first check whether we have sufficient privileges to
     *    create a registration. If not, we return false.
     * 3. If the privileges are granted, we check if this event is open for registration and if
     *    we have not yet registered for it. If yes, the URL will be returned.
     *
     * @return string The full URL to the registration page or false if there are missing permissions.
     */
    function get_registration_link()
    {
        if (! $_MIDCOM->auth->user)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            return "{$prefix}register/{$this->guid}.html";
        }
        if (! $this->_content_topic->can_do('midgard:create'))
        {
            return false;
        }
        if (   $this->is_open()
            && ! $this->is_registered())
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            return "{$prefix}register/{$this->guid}.html";
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns an initialized querybuilder which will list all registrations of this event
     * without further constraints or orderings.
     *
     * This is essentially a reimplementation of the net_nemein_calendar_event_dba::get_event_members_qb,
     * but with net_nemein_registrations_registration_dba as type.
     *
     * @return midcom_core_querybuilder A prepared QB instance.
     * @see net_nemein_calendar_event_dba::get_event_members_qb
     */
    function get_registrations_qb()
    {
        $qb = net_nemein_registrations_registration_dba::new_query_builder();
        $qb->add_constraint('eid', '=', $this->id);
        return $qb;
    }

    /**
     * Returns a list of registration records associated with this object.
     *
     * @return Array of net_nemein_registration_registration records.
     * @todo Once QB supports it, order by Names
     * @todo Once QB supports it, add functions with approved/unapproved filtering
     */
    function get_registrations()
    {
        $qb = $this->get_registrations_qb();
        // TODO: 1.8.4 AFAIK can do this, so make a version_compare call
        // Cannot do this, QB is too stupid.
        // $qb->add_order('uid.lastname');
        // $qb->add_order('uid.firstname');

        return $qb->execute();
    }

    // ***************** QUERY TOOLS *********************

    /**
     * Overwrite the query builder getter with a version retrieving the right type.
     * We need a better solution here in DBA core actually, but it will be difficult to
     * do this as we cannot determine the current class in a polymorphic environment without
     * having a this (this call is static).
     */
    static function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder(__CLASS__);
    }

    /**
     * This function returns a query builder prepared to query all events linked to the
     * root event associated with the current request state. If an event type filter is
     * configured, this is taken into account as well.
     *
     * No ordering whatsoever is done in this helper. Also there is no restriction regarding
     * the timeframe displayed.
     *
     * @return midcom_core_querybuilder A prepared QB instance.
     */
    function get_events_querybuilder()
    {
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $content_topic =& $this->_request_data['content_topic'];
        $config =& $this->_request_data['config'];

        $qb = net_nemein_registrations_event::new_query_builder();
        $qb->add_constraint('node', '=', $content_topic->id);
        if ($config->get('event_type') !== null)
        {
            $qb->add_constraint('type', '=', $config->get('event_type'));
        }

        return $qb;
    }

    /**
     * Returns a list of all events open for registration.
     *
     * Implementation note: Unfortunately, open registration processing must still be done using
     * the PHP level, as the open/close timestamps are contained in parameters.
     *
     * This is built on the list_all function for now, see there for further comments about
     * query operations.
     *
     * @return Array A list of Events.
     */
    function list_open($use_dm = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // TODO: We could be smarter about this now...
        $all_events = net_nemein_registrations_event::list_all();
        $result = Array();
        $cnt = count($all_events);
        debug_add("got {$cnt} events from net_nemein_registrations_event::list_all()");
        if ($all_events)
        {
            foreach ($all_events as $event)
            {
                if (!$event->is_open($use_dm))
                {
                    debug_add("event #{$event->id} is not open for registration, skipping");
                    continue;
                }
                debug_add("adding event #{$event->id}");
                $result[] = $event;
            }
        }
        debug_add('Returning ' . count($result) . ' events');
        debug_pop();
        return $result;
    }

    /**
     * Lists open events in format suitable for DM2 select types
     *
     * NOTE: this is most often called before we can properly initialize DM2, thus 
     * the is_open check is made bypass DM2 and use the default storage for checking
     *
     * @param $key string property to use as key
     * @param $title string property to use as value
     * @return Array DM2 select type options array
     */
    function list_open_optionsarray($key = 'guid', $title = 'title')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret = array();
        $events = net_nemein_registrations_event::list_open(false);
        $cnt = count($events);
        debug_add("got {$cnt} events from net_nemein_registrations_event::list_open()");
        foreach ($events as $event)
        {
            $keyval = $event->$key;
            $titleval = $event->$title;
            debug_add("adding event #{$event->id} as '{$keyval}' => '{$titleval}'");
            $ret[$keyval] = $titleval;
        }
        debug_pop();
        return $ret;
    }

    /**
     * Returns a list of all events.
     *
     * The events are ordered by their start date, only events which have not yet expired
     * (meaning end date is still in the future) will be queried.
     *
     * @return Array A list of Events.
     */
    function list_all()
    {
        $qb = net_nemein_registrations_event::get_events_querybuilder();
        $qb->add_constraint('end', '>', gmdate('Y-m-d H:i:s'));
        $qb->add_order('start');
        //mgd_debug_start();
        $ret = $qb->execute();
        //mgd_debug_stop();
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Got ' . count($ret) . ' events that have not ended yet');
        debug_pop();
        return $ret;
    }


    // ***************** REGISTRATION APPROVAL *********************

    /**
     * Approves a given registration for this event.
     *
     * This requires update and parameters privileges on the registration and the registrar (the
     * latter for OpenPSA compatibility).
     *
     * @param net_nemein_registrations_registration_dba $registration A reference to the registration
     *     to be approved.
     * @todo Rewrite Mail handling to PEAR_Mail.
     * @todo move to the registration class
     */
    function approve_registration(&$registration, $send_mail = true)
    {
        // Get registrar
        $registrar_object = $registration->get_registrar();

        // Check registration privileges
        $_MIDCOM->auth->require_do('midgard:update', $registration);
        $_MIDCOM->auth->require_do('midgard:parameters', $registration);

        // Check registrar privileges (for OpenPSA 1 compatiblity)
        $_MIDCOM->auth->require_do('midgard:update', $registrar_object);
        $_MIDCOM->auth->require_do('midgard:parameters', $registrar_object);

        // Approve the registration
        $registration->set_parameter('net.nemein.registrations', 'approved', time());
        if (   isset($_MIDCOM->auth->user)
            && is_object($_MIDCOM->auth->user)
            && isset($_MIDCOM->auth->user->guid))
        {
            $registration->set_parameter('net.nemein.registrations', 'approver', $_MIDCOM->auth->user->guid);
        }

        // OpenPSA 1 Compatibility
        $registrar_object->set_parameter('campaign', $this->guid, 'on');

        // Update privileges: drop ownership of the registrar, replace by simple read privilege.
        // Approved registrations may not be modified by the registrars.
        $user =& $_MIDCOM->auth->get_user($registrar_object);
        $registration->unset_privilege('midgard:owner', $user);
        $registration->set_privilege('midgard:read', $user);

        if (!$send_mail)
        {
            return true;
        }
        // Finally, send out the E-Mails

        // Get base data
        $bodies = $registration->compose_mail_bodies();
        $subject = $this->_config->get('mail_registration_subject');
        $sender = $this->_config->get('mail_registration_sender');
        $cc = explode(',', $this->get_notification_email());
        $body = $bodies['accept_text'];

        $subject = $registration->expand_keywords($subject);
        $body = $registration->expand_keywords($body);

        $cc[] = $registrar_object->email;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('All recipients: ', $cc);
        foreach ($cc as $email)
        {
            $email = trim ($email);
            if ($email == '')
            {
                debug_add('Skipping an empty cc line, perhaps a comma too much');
                continue;
            }
            $mail = new org_openpsa_mail();
            $mail->to = $email;
            $mail->subject = $subject;
            $mail->body = $body;
            if (!empty($bodies['accept_html']))
            {
                // if we have non-empty html body composed, add it to the mail object, resolving embeds etc
                $mail->html_body = $registration->expand_keywords($bodies['accept_html']);
                list ($mail->html_body, $mail->embeds) = $mail->html_get_embeds($this, $mail->html_body, $mail->embeds);
            }
            $mail->from = $sender;
            if (!$mail->send())
            {
                debug_add("Could not send E-Mail to '{$email}' with subject '{$subject}', got error: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
                //debug_print_r('Mail object:', $mail);
            }
            else
            {
                debug_add("Sent E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                //debug_print_r('Mail object:', $mail);
            }
        }
        debug_pop();

        return true;
    }

    /**
     * Rejects a given registration for this event.
     *
     * The current implementation just deletes the registration record but sends no mails.
     *
     * Requires delete privileges on the registration.
     *
     * @param net_nemein_registrations_registration_dba $registration A reference to the registration
     *     to be rejected.
     * @param string $reason The reason entered by the admin when he rejected the registration.
     * @todo move to the registration class
     */
    function reject_registration(&$registration, $reason)
    {
        $_MIDCOM->auth->require_do('midgard:delete', $registration);

        if (! $registration->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to delete the registration record {$registration->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_print_r("Registration record is:", $registration);
            debug_pop();
            return false;
        }

        $this->request_data['reject_reason'] = $reason;

        // Get base data
        $bodies = $registration->compose_mail_bodies();
        $subject = $this->_config->get('mail_registration_reject_subject');
        $sender = $this->_config->get('mail_registration_reject_sender');
        $cc = explode(',', $this->get_notification_email());
        $body = $bodies['reject_text'];

        $subject = $registration->expand_keywords($subject);
        $body = $registration->expand_keywords($body);

        $cc[] = $registrar_object->email;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('All recipients: ', $cc);
        foreach ($cc as $email)
        {
            $email = trim ($email);
            if ($email == '')
            {
                debug_add('Skipping an empty cc line, perhaps a comma too much');
                continue;
            }
            $mail = new org_openpsa_mail();
            $mail->to = $email;
            $mail->subject = $subject;
            $mail->body = $body;
            if (!empty($bodies['reject_html']))
            {
                // if we have non-empty html body composed, add it to the mail object, resolving embeds etc
                $mail->html_body = $registration->expand_keywords($bodies['reject_html']);
                list ($mail->html_body, $mail->embeds) = $mail->html_get_embeds($this, $mail->html_body, $mail->embeds);
            }
            $mail->from = $sender;
            if (!$mail->send())
            {
                debug_add("Could not send E-Mail to '{$email}' with subject '{$subject}', got error: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
                //debug_print_r('Mail object:', $mail);
            }
            else
            {
                debug_add("Sent E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                //debug_print_r('Mail object:', $mail);
            }
        }
        debug_pop();

        return true;
    }

    /**
     * Rejects a given registration for this event and deletes the corresponding user record.
     *
     * The current implementation just deletes the records but sends no mails. The registration
     * itself is rejected using reject_registration().
     *
     * Requires delete privileges on the registration and the registrar.
     *
     * @param net_nemein_registrations_registration_dba $registration A reference to the registration
     *     to be rejected.
     * @param string $reason The reason entered by the admin when he rejected the registration.
     */
    function rejectdelete_registration(&$registration, $reason)
    {
        $registrar = $registration->get_registrar();
        $_MIDCOM->auth->require_do('midgard:delete', $registrar);

        // Reject the registration
        if (! $this->reject_registration($registration, $reason))
        {
            return false;
        }

        if (! $registrar->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to delete the registrar record {$registrar->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_print_r("Registrar record is:", $registrar);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Internal helper, converts a DM2 instance to a string based representation suitable
     * for mailing.
     *
     * TODO: This is a littlebit of a hack, as it is currently difficult to
     * get a plaint-text representation of a given datatype explicitly,
     * so this interface is used for a start (avoids arrays). Same is
     * true for the datamanager-completed field-definitions.
     *
     * We don't use the official get_csv_line interface btw., as this would
     * require us to un-csv-quote that string. (END TODO)
     *
     * @todo Rewrite to a more suitable implementaiton with DM2 side support.
     */
    function dm_array_to_string(&$dm)
    {
        $result = "";
        foreach ($dm->schema->fields as $name => $field)
        {
            // Skip fields with aisonly and hidden flags
            if (   $field["hidden"] == true
                || $field["aisonly"] == true)
            {
                continue;
            }

            $result .= $this->_l10n->get($field['title']) . ":\n";
            $data = $dm->types[$name]->convert_to_csv();
            $result .= "  " . wordwrap ($data, 70, "\n  ");
            $result .= "\n\n";
        }
        return trim($result);
    }

    /**
     * Indexes an entry.
     *
     * This function is usually called statically.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encapsulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->component = $topic->component;
        $indexer->index($document);
    }

}

?>
