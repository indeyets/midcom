<?php

class net_nemein_reservations_reservation extends net_nemein_reservations__base {

    var $_debug_prefix;

    var $resource;
    var $event;
    var $dm;

    function net_nemein_reservations_reservation($id = null) {
        parent::net_nemein_reservations__base();
        debug_push($this->_debug_prefix . "c'tor");
        if (is_null($id)) {
            $this->resrouce = null;
            $this->event = null;
        } else if (mgd_is_guid($id)) {
            $this->event = mgd_get_object_by_guid($id);
            if (   ! $this->event
                || $this->event->__table__ != "event")
            {
                debug_add("Could not load event [$id], invalid GUID: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Retrieved object was:", $this->event);
                $x =& $this;
           		$x = false;
                debug_pop();
                return false;
            }
        } else if (is_numeric($id)) {
            $this->event = mgd_get_event($id);
            if (! $this->event) {
                debug_add("Could not load event [$id]: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }
        } else if (is_object($id) && is_a($id, "net_nemein_reservations_resource")) {
            $this->resource =& $resource;
            $this->event = null;
        } else {
            $this->event = mgd_get_event($id);
            debug_add("Unknown constructor argument.", MIDCOM_LOG_ERROR);
            debug_print_r("Argument was:", $id);
            $x =& $this;
            $x = false;
            debug_pop();
            return false;
        }

        if (! is_null($this->event)) {
            if ($this->event->up != $this->_root_event->id) {
                debug_add("The event " . $this->event->id . " is not a child of the root event "
                          . $this->_root_event->id . ", aborting object creation.", MIDCOM_LOG_ERROR);
                debug_print_r("Event record was: ", $this->event);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }

            $members = mgd_list_event_members($this->event->id);
            if (! $members) {
                debug_add("The event " . $this->event->id . " has no event member records: "
                          . mgd_errstr(), MIDCOM_LOG_ERROR);

                debug_print_r("Event record was: ", $this->event);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }
            $members->fetch(); /* Fetch 1st record, there should only be one */
            $this->resource = new net_nemein_reservations_resource($members->uid);
            if (! $this->resource) {
                debug_add("The event " . $this->event->id . " has an invalid event member record.",
                          MIDCOM_LOG_ERROR);
                debug_print_r("Event record was: ", $this->event);
                debug_print_r("Last membership fetchable state: ", $members);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }
            if ($members->fetch() != false) {
                debug_add("The event " . $this->event->id . " has more then one event member record.",
                          MIDCOM_LOG_ERROR);
                debug_print_r("Event record was: ", $this->event);
                debug_print_r("Last membership fetchable state: ", $members);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }
        }

        if (!$this->_init_datamanager()) {
            debug_pop();
            $x =& $this;
            $x = false;
            return false;
        }

        debug_pop();
    }

    function delete () {
        debug_push($this->_debug_prefix . "delete");

        if (is_null($this->event)) {
            debug_add("Delete request for a null'd event, igonring.", MIDCOM_LOG_INFO);
            debug_pop();
            return true;
        }

        /* First, set the event to corrupt, in case anything goes wrong. This will
         * also catch an access denied at this point already.
         */
        $this->event->type = $this->_config->get("event_type_corrupt");
        if (! $this->event->update()) {
            debug_add("Could not set corrupt flag, aborting:", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $memberships = mgd_list_event_members($this->event->id);
        if ($memberships) {
            while ($memberships->fetch()) {
                if (! mgd_delete_event_member($memberships->id)) {
                    debug_add("Could not delete event membership: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_print_r("State of the membership fetchable: ", $memberships);
                    debug_pop();
                    return false;
                }
            }
        } else {
            debug_add ("There don't seem any event members acciociated with this record, which is strange, "
                       . "there should be at least one, this is ignored silently. Last Midgard error was: " . mgd_errstr(),
                       MIDCOM_LOG_ERROR);
        }

        if (! mgd_delete_extensions($this->event)) {
            debug_add("Could not delete object extensions: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->event);
            debug_pop();
            return false;
        }

        if (! $this->event->delete()) {
            debug_add("Could not delete the event record: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->event);
            debug_pop();
            return false;
        }

        $this->event = null;

        debug_pop();
        return true;
    }

    function finish() {
        debug_push($this->_debug_prefix . "finish");

        debug_add("Parsing E-Mail Template");
        $template = new midcom_helper_mailtemplate($this->_config_dm->data["mail_newreservation"]);
        $parameters = Array(
            "RESOURCE" => $this->_resource->dm,
            "RESERVATION" => $this->dm,
            "ISOSTART" => $this->dm->data["start"]["strfulldate"],
            "ISOEND" => $this->dm->data["end"]["strfulldate"],
            "LOCALSTART" => $this->dm->data["start"]["local_strfulldate"],
            "LOCALEND" => $this->dm->data["end"]["local_strfulldate"],
        );
        $template->set_parameters($parameters);
        $template->parse();
        $failed = $template->send($this->dm->data["email"]);
        if ($failed > 0) {
            debug_add("$failed E-Mails could not be sent.", MIDCOM_LOG_WARN);
            debug_print_r("Failed addresses:", $template->failed);
        }


        $this->event->type = $this->_config->get("event_type_complete");
        $this->event->busy = true;
        if (! $this->event->update()) {
            debug_print_r("Set event to corrupt, something went wrong while updating it, errstr was " . mgd_errstr(), $this->event);

            $memberships = mgd_list_event_members($this->event);
            if ($memberships) {
                while ($memberships->fetch()) {
                    if (! mgd_delete_event_member($memberships->id)) {
                        debug_add("Could not delete event membership: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                        debug_print_r("State of the membership fetchable: ", $memberships);
                        $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Could not set event to corrupt, this is really strange.");
                    }
                }
            }
            $this->event->busy = false;
            $this->event->type = $this->_config->get("event_type_corrupt");
            if (! $this->event->update()) {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Could not set event to corrupt, this is really strange.");
            }

            $this->errstr = "Could not update event to a finished one, possible data corruption.";
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    function approve() {
        debug_push($this->_debug_prefix . "approve");

        debug_add("Setting timestamp at the object.");
        $this->event->parameter("midcom.helper.datamanager", "data_approved", time());

        debug_add("Reloading datamanger");
        $this->dm->init($this->event);

        debug_add("Parsing E-Mail Template");
        $template = new midcom_helper_mailtemplate($this->_config_dm->data["mail_acceptreservation"]);
        $parameters = Array(
            "RESOURCE" => $this->_resource->dm,
            "RESERVATION" => $this->dm,
            "ISOSTART" => $this->dm->data["start"]["strfulldate"],
            "ISOEND" => $this->dm->data["end"]["strfulldate"],
            "LOCALSTART" => $this->dm->data["start"]["local_strfulldate"],
            "LOCALEND" => $this->dm->data["end"]["local_strfulldate"],
        );
        $template->set_parameters($parameters);
        $template->parse();
        $failed = $template->send($this->dm->data["email"]);
        if ($failed > 0) {
            debug_add("$failed E-Mails could not be sent.", MIDCOM_LOG_WARN);
            debug_print_r("Failed addresses:", $template->failed);
        }

        $GLOBALS["view_contentmgr"]->msg .= $this->_l10n->get("reservation has been approved") . "<br>\n";
    }

    function reject_and_delete() {
        debug_push($this->_debug_prefix . "reject");

        debug_add("Parsing E-Mail Template");
        $template = new midcom_helper_mailtemplate($this->_config_dm->data["mail_rejectreservation"]);
        $parameters = Array(
            "RESOURCE" => $this->_resource->dm,
            "RESERVATION" => $this->dm,
            "ISOSTART" => $this->dm->data["start"]["strfulldate"],
            "ISOEND" => $this->dm->data["end"]["strfulldate"],
            "LOCALSTART" => $this->dm->data["start"]["local_strfulldate"],
            "LOCALEND" => $this->dm->data["end"]["local_strfulldate"],
        );
        $template->set_parameters($parameters);
        $template->parse();
        $failed = $template->send($this->dm->data["email"]);
        if ($failed > 0) {
            debug_add("$failed E-Mails could not be sent.", MIDCOM_LOG_WARN);
            debug_print_r("Failed addresses:", $template->failed);
        }

        return $this->delete();
    }

    /****************** Internal Helper Functions ****************************/

    function _init_datamanager() {
        debug_push($this->_debug_prefix . "_init_datamanager");

        $this->dm = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (!$this->dm) {
            debug_add("Failed to create the datamanager", MIDCOM_LOG_ERROR);
            debug_add("Schema path was: " . $this->_config->get("schemadb"));
            debug_pop();
            return false;
        }

        if (! is_null($this->event)) {
            if (! $this->dm->init($this->event)) {
                debug_add("Failed to initialize datamanager for the selected event ["
                          . $this->event->id . "]", MIDCOM_LOG_ERROR);
                debug_print_r("Object dump:", $this->event);
                debug_pop();
                return false;
            }
            debug_add("Successfully initialized the datamanager");
        } else {
            debug_add("No initialization done, we have an null event.");
        }

        debug_pop();
        return true;
    }

    function init_creation_mode() {
        if (! $this->dm->init_creation_mode($this->_config->get("schema_reservation"), $this)) {
            debug_add("Failed to initialize the datamanagers creation mode.", MIDCOM_LOG_ERROR);
            debug_add("Schema to use was: ", $this->_config->get("schema_reservation"));
            debug_pop();
            return false;
        }
        debug_add("Successfully initialized the datamanager in creation mode.");
        return true;
    }

    function _dm_create_callback(&$datamanager) {
        debug_push($this->_debug_prefix . "_dm_create_callback");

        $result = Array (
            "success" => true,
            "storage" => null
        );

        $this->event = $this->create_empty_event(0, 0, $this->_resource);

        if (is_null($this->event)) {
            debug_pop();
            return null;
        }

        debug_print_r("Assigning this event as new storage object: ", $this->event);
        $result["storage"] =& $this->event;

        debug_pop();
        return $result;
    }


    /******************** STATIC Query Functions for Reservations **********************/

    function create_empty_event($start, $end, $resource) {
        debug_push($this->_debug_prefix . "_create_empty_event");

        $topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $root_event =& $GLOBALS["midcom"]->get_custom_context_data("root_event");
        $config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");

        $event = mgd_get_event();
        $event->start = $start;
        $event->end = $end;
        $event->up = $root_event->id;
        $event->owner = $topic->owner;
        $event->busy = false;
        $event->type = $config->get("event_type_incomplete");
        $id = $event->create();
        if ($id === false) {
            debug_add("Could not create event, last error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return null;
        }

        $event = mgd_get_event($id);

        if (mgd_create_event_member($event->id, $resource->person->id, "") === false) {
            debug_add("Could not create event member: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Removing created event record:", $event);
            if (! $event->delete())
                debug_add("Could not delete created event record: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->event = null;
            debug_pop();
            return null;
        }

        debug_pop();
        return $event;
    }



}

?>