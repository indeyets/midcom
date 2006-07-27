<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration system: Event class
 *
 * This class encaspulates an event which can be registered to.
 *
 * TODO...
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_event extends midcom_db_event
{
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
    var $_root_event;

    /**
     * The DM2 datamanager instance encaspulating this object. Initialized on first access
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
    function net_nemein_registrations_event($id = null)
    {
        parent::midcom_db_event($id);
        // Intercept failed class instantinations.
        if ($this)
        {
            $this->_bind_to_request_data();
            $this->_root_event =& $this->_request_data['root_event'];
        }
    }

    /**
     * Binds the object to the current request data. This populates the members
     * _request_data, _config, _topic, _l10n and _l10n_midcom accordingly.
     */
    function _bind_to_request_data()
    {
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

        return $this->_dm->types['additional_questions']->selection[0];
    }

    /**
     * Checks if the event is open for registration.
     *
     * @return bool True if open.
     */
    function is_open()
    {
        $this->_populate_dm();

        $now = new Date();
        $open = $this->_dm->types['open_registration']->value;
        $close = $this->_dm->types['close_registration']->value;

        // Sanity
        if ($close->before($open))
        {
            $close = null;
        }

        if ($open->before($now))
        {
            if ($close)
            {
                return $close->after($now);
            }
            else
            {
                return true;
            }
        }

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
     * Returns a list of registrar records accociated with this object.
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
     * @return net_nemein_registrations_registration The found registration record, or false if
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
     * 2. If an use is authenticated, we first check wether we have sufficient privileges to
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
        if (! $this->_root_event->can_do('midgard:create'))
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
     * This is essentially a reimplementation of the midcom_db_event::get_event_members_qb,
     * but with net_nemein_registrations_registration as type.
     *
     * @return midcom_core_querybuilder A prepared QB instance.
     * @see midcom_db_event::get_event_members_qb
     */
    function get_registrations_qb()
    {
        $qb = net_nemein_registrations_registration::new_query_builder();
        $qb->add_constraint('eid', '=', $this->id);
        return $qb;
    }

    /**
     * Returns a list of registration records accociated with this object.
     *
     * @return Array of net_nemein_registration_registration records.
     * @todo Once QB supports it, order by Names
     * @todo Once QB supports it, add functions with approved/unapproved filtering
     */
    function get_registrations()
    {
        $qb = $this->get_registrations_qb();
        // Cannot do this, QB is too stupid.
        // $qb->add_order('uid.lastname');
        // $qb->add_order('uid.firstname');

        return $qb->execute();
    }

    // ***************** QUERY TOOLS *********************

    /**
     * This function returns a query builder prepared to query all events linked to the
     * root event accociated with the current request state. If an event type filter is
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
        $root_event =& $this->_request_data['root_event'];
        $config =& $this->_request_data['config'];

        $qb = net_nemein_registrations_event::new_query_builder();

        $qb->add_constraint('up', '=', $root_event->id);
        if ($config->get('event_type') !== null)
        {
            $qb->add_constraint('type', '=', $config->get('event_type'));
        }

        return $qb;
    }

    /**
     * Overwrite the query builder getter with a version retrieving the right type.
     * We need a better solution here in DBA core actually, but it will be difficult to
     * do this as we cannot determine the current class in a polymorphic environment without
     * having a this (this call is static).
     */
    function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder(__CLASS__);
    }

    /**
     * Returns a list of all events open for registration.
     *
     * Implementation note: Unfortunalety, open registration processing must still be done using
     * the PHP level, as the open/close timestamps are contained in parameters.
     *
     * This is built on the list_all function for now, see there for further comments about
     * query operations.
     *
     * @return Array A list of Events.
     */
    function list_open()
    {
        $all_events = net_nemein_registrations_event::list_all();
        $result = Array();
        if ($all_events)
        {
            foreach ($all_events as $event)
            {
                if ($event->is_open())
                {
                    $result[] = $event;
                }
            }
        }
        return $result;
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
        $qb->add_constraint('end', '>', time());
        $qb->add_order('start');
        return $qb->execute();
    }


    // ***************** REGISTRATION APPROVAL *********************

    /**
     * Approves a given registration for this event.
     *
     * This requires update and parameters privileges on the registration and the registrar (the
     * latter for OpenPSA compatibility).
     *
     * @param net_nemein_registrations_registration $registration A reference to the registration
     *     to be approved.
     * @todo Rewrite Mail handling to PEAR_Mail.
     */
    function approve_registration(&$registration)
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
        $registration->set_parameter('net.nemein.registrations', 'approver', $_MIDCOM->auth->user->guid);

        // OpenPSA 1 Compatibility
        $registrar_object->set_parameter('campaign', $this->guid, 'on');

        // Update privileges: drop ownership of the registrar, replace by simple read privilege.
        // Approved registrations may not be modified by the registrars.
        $user =& $_MIDCOM->auth->get_user($registrar_object);
        $registration->unset_privilege('midgard:owner', $user);
        $registration->set_privilege('midgard:read', $user);

        // Finally, send out the E-Mails

        // Get base data
        $subject = $this->_config->get('mail_registration_subject');
        $sender = $this->_config->get('mail_registration_sender');
        $cc = explode(',', $this->get_notification_email());
        $body = midcom_get_snippet_content($this->_config->get('mail_registration_body'));

        $registrar_dm =& $registrar_object->get_datamanager();
        $registrar_data = $registrar_dm->get_content_csv();
        $registrar_all = $this->_dm_array_to_string($registrar_dm);

        $registration_dm =& $registration->get_datamanager();
        $registration_data = $registration_dm->get_content_csv();
        $registration_all = $this->_dm_array_to_string($registration_dm);

        //syntax: _REGISTRAR_arraykey_ bzw. REGISTRATION
        $search = Array (
            '/__REGEVENT_([^ \.>"-]*?)__/e',
            '/__REGISTRAR__/', /* Order important here ! */
            '/__REGISTRAR_([^"]*?)__/e',
            '/__REGISTRATION__/', /* Order important here ! */
            '/__REGISTRATION_([^_"]*?)__/e',
            '/__URL__/',
        );
        $replace = Array (
            '$this->\1',
            $registrar_all,
            '$registrar_data["\1"]',
            $registration_all,
            '$registration_data["\1"]',
            $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
        );
        $subject = $this->_encode_subject(preg_replace($search, $replace, $subject));
        $body = preg_replace($search, $replace, $body);
        $headers = "From: {$sender}\r\nReply-To: {$sender}\r\nX-Mailer: PHP/" . phpversion();

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
            if (! mail ($email, $subject, $body, $headers))
            {
                debug_add("Could not send E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                debug_print_r('Extra Headers:', $headers);
                debug_print_r('Body:', $body);
            }
            else
            {
                debug_add("Sent E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                debug_print_r('Extra Headers:', $headers);
                debug_print_r('Body:', $body);
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
     * @param net_nemein_registrations_registration $registration A reference to the registration
     *     to be rejected.
     * @param string $reason The reason entered by the admin when he rejected the registration.
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

        // Get base data
        $subject = $this->_config->get('mail_registration_reject_subject');
        $sender = $this->_config->get('mail_registration_reject_sender');
        $cc = explode(',', $this->get_notification_email());
        $body = midcom_get_snippet_content($this->_config->get('mail_registration_reject_body'));

        $registrar_object = $registration->get_registrar();
        $registrar_dm =& $registrar_object->get_datamanager();
        $registrar_data = $registrar_dm->get_content_csv();
        $registrar_all = $this->_dm_array_to_string($registrar_dm);

        $registration_dm =& $registration->get_datamanager();
        $registration_data = $registration_dm->get_content_csv();
        $registration_all = $this->_dm_array_to_string($registration_dm);

        //syntax: _REGISTRAR_arraykey_ bzw. REGISTRATION
        $search = Array (
            '/__REGEVENT_([^ \.>"-]*?)__/e',
            '/__REGISTRAR__/', /* Order important here ! */
            '/__REGISTRAR_([^"]*?)__/e',
            '/__REGISTRATION__/', /* Order important here ! */
            '/__REGISTRATION_([^_"]*?)__/e',
            '/__REASON__/',
            '/__URL__/',
        );
        $replace = Array (
            '$this->\1',
            $registrar_all,
            '$registrar_data["\1"]',
            $registration_all,
            '$registration_data["\1"]',
            $reason,
            $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
        );
        $subject = $this->_encode_subject(preg_replace($search, $replace, $subject));
        $body = preg_replace($search, $replace, $body);
        $headers = "From: {$sender}\r\nReply-To: {$sender}\r\nX-Mailer: PHP/" . phpversion();

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
            if (! mail ($email, $subject, $body, $headers))
            {
                debug_add("Could not send E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                debug_print_r('Extra Headers:', $headers);
                debug_print_r('Body:', $body);
            }
            else
            {
                debug_add("Sent E-Mail to {$email} with subject '{$subject}'.", MIDCOM_LOG_ERROR);
                debug_print_r('Extra Headers:', $headers);
                debug_print_r('Body:', $body);
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
     * @param net_nemein_registrations_registration $registration A reference to the registration
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
    function _dm_array_to_string(&$dm)
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

            $result .= $field["title"] . ":\n";
            $data = $dm->types[$name]->convert_to_csv();
            $result .= "  " . wordwrap ($data, 70, "\n  ");
            $result .= "\n\n";
        }
        return trim($result);
    }

    /**
     * Internal helper, encodes a mail subject line with Latin 1 encoding. This
     * is hacky and needs an immediate rewrite to PEAR Mail (which'll deprecate
     * this function).
     *
     * @todo Rewrite to PEAR_Mail.
     */
    function _encode_subject ($subject)
    {
        preg_match_all("/[^\x20-\x7e]/", $subject, $matches);
        if (count ($matches[0])>0) {
            $newSubj=$subject;
            while (list ($k, $char) = each ($matches[0])) {
                $code="=".dechex(ord($char));
                $newSubj=str_replace($char, $code, $newSubj);
            }
            return "=?ISO-8859-1?Q?".$newSubj."?=";
        } else {
            return $subject;
        }
    }


    /**
     * Indexes an entry.
     *
     * This function is usually called statically.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (is_object($topic))
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
        $author = $_MIDCOM->auth->get_user($dm->storage->object->creator);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->author = $author->name;
        $document->created = $dm->storage->object->created;
        $document->edited = $dm->storage->object->revised;
        $indexer->index($document);
    }

}

?>