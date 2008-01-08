<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * n.n.reservations create page handler
 *
 * @package net.nemein.reservations
 */

class net_nemein_reservations_handler_reservation_create extends midcom_baseclasses_components_handler
{
    /**
     * The resource which we're reserving
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * The event which has been created
     *
     * @var org_openpsa_calendar_event
     * @access private
     */
    var $_event = null;

    /**
     * The Controller of the reservation used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new reservation.
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    var $_indexmode = false;

    /**
     * The defaults to use for the new reservation.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schema'] =& $this->_schema;
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_reservations_handler_reservation_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->componentloader->load('org.openpsa.mail');
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb_reservation'];

        $this->_defaults['start'] = mktime(date('H'), 0, 0, date('m', $this->_request_data['selected_day']), date('d', $this->_request_data['selected_day']), date('Y', $this->_request_data['selected_day']));

        switch ($this->_resource->period)
        {
            case 'h':
            default:
                // Default to one hour later ending
                $this->_defaults['end'] = $this->_defaults['start'] + 3600;
                break;
        }

        $session =& new midcom_service_session();
        if ($session->exists('failed_POST_data'))
        {
            $this->_defaults = $session->get('failed_POST_data');
            /*
            echo "DEBUG: defaults after session data<pre>\n";
            print_r($this->_defaults);
            echo "</pre>\n";
            */
            $session->remove('failed_POST_data');
        }
        unset($session);
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback(&$controller)
    {
        $this->_resource->require_do('org.openpsa.calendar:reserve');
        $this->_event = new org_openpsa_calendar_event();

        $add_creator = $this->_config->get('add_creator_to_reservation');

        /* PONDER: Shouldn't we populate the user (if any) as participant ??
             - in fact o.o.calendar does it for us...
        */
        if($add_creator === true)
        {
            // Populate the resource
            if ($_MIDCOM->auth->user)
            {
                $this->_event->participants = array
                (
                    $_MIDGARD['user'] => true,
                );
            }
        }

        // Populate the resource
        $this->_event->resources = array
        (
            $this->_resource->id => true,
        );

        // Try to be smart about setting the *events* location
        switch(true)
        {
            case (   !empty($this->_resource->location)
                  && !empty($this->_resource->title)):
                $this->_event->location = "{$this->_resource->title} ({$this->_resource->location})";
                break;
            default:
                $this->_event->location = $this->_resource->title;
                break;
        }

        if (array_key_exists('start', $_POST))
        {
            $this->_event->start = strtotime($_POST['start']);
        }
        if (array_key_exists('end', $_POST))
        {
            $this->_event->end = strtotime($_POST['end']);
        }

        // TODO: Add support for tentatives?
        $this->_event->busy = true;

        if (! $this->_event->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_event);
            debug_pop();
            if (   is_array($this->_event->busy_em)
                || !empty($this->_event->busy_em)
                || is_array($this->_event->busy_er)
                || !empty($this->_event->busy_er))
            {
                // Raise UImessage
                $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.reservations'), $this->_l10n->get('failed to create a new event due to resourcing conflict'), 'error');
                // Store form values
                $post_data = array();
                foreach ($this->_controller->datamanager->schema->field_order as $fieldname)
                {
                    if (!isset($_POST[$fieldname]))
                    {
                        continue;
                    }
                    $post_data[$fieldname] = $_POST[$fieldname];
                }
                $session =& new midcom_service_session();
                $session->set('failed_POST_data', $post_data);
                // Return user to create view
                $_MIDCOM->relocate("reservation/create/{$this->_resource->name}/" . date('Y-m-d', $this->_event->start) . '/');
                // This will exit
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }

        return $this->_event;
    }

    /**
     * Displays a reservation edit view.
     *
     * Note, that the reservation for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation reservation
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Must be able to create events under the root event
        $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->require_do('midgard:create');

        $this->_resource = net_nemein_reservations_viewer::load_resource($args[0]);
        // TODO: Check from resource privilege for reserving said resource.
        if (!$this->_resource)
        {
            return false;
            // This will 404
        }
        // Must be able to reserve the resource
        $this->_resource->require_do('org.openpsa.calendar:reserve');

        if ($handler_id == 'create_reservation_date')
        {
            // Go to the chosen week instead of current one
            // TODO: Check format as YYYY-MM-DD via regexp
            $requested_time = @strtotime($args[1]);
            if ($requested_time)
            {
                $data['selected_day'] = $requested_time;
            }
            else
            {
                // We couldn't generate a date
                return false;
            }
        }
        else
        {
             $data['selected_day'] = time();
        }

        $this->_load_controller();
        $this->_prepare_request_data();

        if (!$_MIDCOM->auth->user)
        {
            // We don't have user but since we got so far anonymous reservations are allowed, use sudo...
            if (!$_MIDCOM->auth->request_sudo())
            {
                // Could not get sudo
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not get sudo to handle anonymous reservations", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        $_MIDCOM->auth->request_sudo();
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the reservation
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_reservations_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                if (!$_MIDCOM->auth->user)
                {
                    $_MIDCOM->auth->drop_sudo();
                }

                if ($email = $this->_event->get_parameter('midcom.helper.datamanager2', 'email'))
                {
                    $this->_send_notification($email);
                }

                $_MIDCOM->relocate("reservation/{$this->_event->guid}/");
                // this will exit.

            case 'cancel':
                if (!$_MIDCOM->auth->user)
                {
                    $_MIDCOM->auth->drop_sudo();
                }
                $_MIDCOM->relocate("view/{$this->_resource->name}/");
                // This will exit.
        }

        if ($this->_event != null)
        {
            $_MIDCOM->set_26_request_metadata($this->_event->revised, $this->_event->guid);
        }
        $data['view_title'] = sprintf($this->_l10n->get('reserve %s'), $this->_resource->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _send_notification($email)
    {
        if ($this->_config->get('new_reservation_email'))
        {
            $mail = new org_openpsa_mail();
            $mail->to = $email;
            $mail->from = $this->_config->get('new_reservation_email');
            $mail->reply = $this->_config->get('new_reservation_email_reply');
            $mail->cc = $this->_config->get('new_reservation_email');
            $mail->bcc = $this->_config->get('new_reservation_email_bcc');
            $mail->subject = $this->_replace($this->_config->get('new_reservation_email_title'));
            $mail->body = $this->_replace($this->_config->get('new_reservation_email_body'));

            return $mail->send();
        }
    }

    function _replace($string)
    {
        $result = str_replace('__RESERVATION_name__', $this->_event->title, $string);
        $result = str_replace('__RESOURCE_name__', $this->_resource->title, $result);
        $result = str_replace('__ISOSTART__', date("d.m.y", $this->_event->start), $result);
        $result = str_replace('__ISOEND__', date("d.m.y", $this->_event->end), $result);
        $result = str_replace('__RESERVATION__', $this->_event->description, $result);

        return $result;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "reservation/create/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded reservation.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('view-reservation-create');
    }



}

?>
