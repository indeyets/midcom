<?php

class net_nemein_reservations_resource extends net_nemein_reservations__base {

    var $person;
    var $dm;

    function net_nemein_reservations_resource ($id = null) {
        parent::net_nemein_reservations__base();

        debug_push($this->_debug_prefix . "c'tor");

        if (is_null($id)) {
            $this->person = null;
        } else if (mgd_is_guid($id)) {
            $this->person = mgd_get_object_by_guid($id);
            if (   ! $this->person
                || $this->person->__table__ != "person")
            {
                debug_add("Could not load person [$id], invalid GUID: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Retrieved object was:", $this->person);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }
        } else if (is_numeric($id)) {
            $this->person = mgd_get_person($id);
            if (! $this->person) {
                debug_add("Could not load person [$id]: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                $x =& $this;
            	$x = false;
                debug_pop();
                return false;
            }

        } else if (is_object($id) && is_a($id, "MidgardPerson")) {
            $this->person = $id;
        } else {
            debug_add("Unknown constructor argument.", MIDCOM_LOG_ERROR);
            debug_print_r("Argument was:", $id);
            $x =& $this;
            $x = false;
            debug_pop();
            return false;
        }

        if (! is_null($this->person)) {
            if (! mgd_is_member($this->_root_group->id, $this->person->id)) {
                debug_add("The person " . $this->person->id . " is not member of the group "
                          . $this->_root_group->id . ", aborting object creation.", MIDCOM_LOG_ERROR);
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

    }

    function delete () {
        debug_push($this->_debug_prefix . "delete");

        if (is_null($this->person)) {
            debug_add("Delete request for a null'd person, igonring.", MIDCOM_LOG_INFO);
            debug_pop();
            return true;
        }

        // Save guid for index processing.
        $guid = $this->person->guid();

        $events = mgd_list_events_between_by_member($this->person->id, 0, 2000000000);
        if ($events) {
            while ($events->fetch()) {
                $event = mgd_get_event($events->id);
                $members = mgd_list_event_members($event->id);
                if ($members) {
                    while ($members->fetch()) {
                        $member = mgd_get_event_member($members->id);
                        if (! mgd_delete_extensions($member)) {
                            debug_add("Could not delete object extensions of the event member {$members->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
                            debug_pop();
                            return false;
                        }
                        if (! $member->delete()) {
                            debug_add("Could not delete the event member {$members->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
                            debug_pop();
                            return false;
                        }
                    }
                }
                if (! mgd_delete_extensions($event)) {
                    debug_add("Could not delete object extensions of the event {$event->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
                    debug_pop();
                    return false;
                }
                if (! $event->delete()) {
                    debug_add("Could not delete the event {$event->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
                    debug_pop();
                    return false;
                }
            }
        }

        $memberships = mgd_list_memberships($this->person->id);
        if ($memberships) {
            while ($memberships->fetch()) {
                if (! mgd_delete_member($memberships->id)) {
                    debug_add("Could not delete group memberships: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_print_r("State of the membership fetchable: ", $memberships);
                    debug_pop();
                    return false;
                } else {
                    debug_print_r("This Membership has been successfully deleted:", $memberships);
                }
            }
        } else {
            debug_add ("There don't seem any groups acciociated with this person, which is strange, "
                       . "there should be at least one. Last Midgard error was: " . mgd_errstr(),
                       MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (! mgd_delete_extensions($this->person)) {
            debug_add("Could not delete object extensions: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
            debug_pop();
            return false;
        }

        if (! $this->person->delete()) {
            debug_add("Could not delete the person record: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("You will now have an inconsistent object, what we have left is: ", $this->person);
            debug_pop();
            return false;
        }

        $GLOBALS['midcom']->cache->invalidate($guid);

        $this->person = null;

        debug_pop();
        return true;
    }


    /****************** Query Functions ****************************/

    function list_unapproved_reservations() {
        debug_push($this->_debug_prefix . "list_unapproved_reservations");
        $result = Array();

        $reservations = $this->list_reservations(time());
        foreach ($reservations as $id => $reservation) {
            if ($reservation->dm->data["approved"]["timestamp"] == 0) {
                $result[$id] = $reservation;
            }
        }

        debug_pop();
        return $result;
    }

    function list_approved_reservations() {
        debug_push($this->_debug_prefix . "list_approved_reservations");
        $result = Array();

        $reservations = $this->list_reservations(time());
        foreach ($reservations as $id => $reservation) {
            if ($reservation->dm->data["approved"]["timestamp"] != 0) {
                $result[$id] = $reservation;
            }
        }

        debug_pop();
        return $result;
    }

    function list_reservations ($start = 0, $end = 2000000000, $sort = "start", $type = null) {
        debug_push($this->_debug_prefix . "list_reservations");
        if (is_null($type)) {
            $type = $this->_config->get("event_type_complete");
        }
        debug_add("Listing reservations for person " . $this->person->id
                  . " from $start to $end sorting by $sort and filtered by type $type.");
        $events = mgd_list_events_between_by_member($this->person->id, $start, $end, $sort, $type);
        $result = Array();
        if (! $events) {
            debug_add("No events found, last midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
        } else {
            while ($events->fetch()) {
                $reservation = new net_nemein_reservations_reservation($events->id);
                if ($reservation == false) {
                    debug_print_r("An event could not be instantinated, see above for details, skipping it. Fetchable state: ",
                                  $events);
                } else {
                    $result[$events->id] = $reservation;
                }
            }
        }
        debug_pop();
        return $result;
    }

    function get_reservation_at ($time, $type = null) {
        debug_push($this->_debug_prefix . "list_reservations");
        if (is_null($type)) {
            $type = $this->_config->get("event_type_complete");
        }
        debug_add("Listing reservations for person " . $this->person->id
                  . " at $time sorting by start and filtered by type $type for person {$this->person->id}.");
        $events = mgd_list_events_between_by_member($this->person->id, $time, $time, "start", $type);
        if (! $events) {
            debug_add("No events found, last midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
            return null;
        } else {
            $events->fetch();
            /* There should only be one match */
            $reservation = new net_nemein_reservations_reservation($events->id);
            debug_print_type("Reservation is of type", $reservation);
            if ($reservation == false) {
                debug_print_r("An event could not be instantinated, see above for details, skipping it. Fetchable state: ",
                              $events);
                return null;
            } else {
                debug_add("Matched a reservation from {$reservation->event->start} to {$reservation->event->end} against {$time}");
                return $reservation;
            }
        }
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

        if (! is_null($this->person)) {
            if (! $this->dm->init($this->person)) {
                debug_add("Failed to initialize datamanager for the selected person ["
                          . $this->person->id . "]", MIDCOM_LOG_ERROR);
                debug_print_r("Object dump:", $this->person);
                debug_pop();
                return false;
            }
        }
        debug_add("Successfully initialized the datamanager");
        debug_pop();
        return true;
    }

    function init_creation_mode() {
        if (! $this->dm->init_creation_mode($this->_config->get("schema_resource"), $this)) {

            debug_add("Failed to initialize the datamanagers creation mode.", MIDCOM_LOG_ERROR);
            debug_add("Schema to use was: ", $this->_config->get("schema_resource"));
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

        if (! $this->_create_empty_person()) {
            debug_add("This is really strange, we could not create anything: " . mgd_errstr(),
                      MIDCOM_LOG_ERROR);
            debug_pop();
            return null;
        }

        debug_print_r("Assigning this person as new storage object: ", $this->person);
        $result["storage"] =& $this->person;

        debug_pop();
        return $result;
    }

    function _create_empty_person() {
        debug_push($this->_debug_prefix . "_create_empty_person");

        $person = mgd_get_person();
        $id = $person->create();
        if ($id === false) {
            debug_pop();
            return false;
        }

        $this->person = mgd_get_person($id);

        if (! mgd_create_member($this->person->id, $this->_root_group->id, "")) {
            debug_add("Creating group membership from person ID {$this->person->id} to group ID {$this->_root_group->id} failed.");
            debug_add("Could not create group member: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $midgard = $GLOBALS["midcom"]->get_midgard();
            debug_print_r("Midgard Object showing current permissions:", $midgard);
            debug_add("Removing created person record.");
            if (! $this->person->delete())
                debug_add("Could not delete created person record: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->person = null;
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }


    /******************** STATIC Query Functions for Resources **********************/

    function list_resources () {
        debug_push("n.n.reservations_list_resources");
        $group =& $GLOBALS["midcom"]->get_custom_context_data("root_group");
        $result = Array();

        $memberships = mgd_list_members($group->id);
        if (! $memberships) {
            debug_add("No persons found, last Midgard error was: "  . mgd_errstr(), MIDCOM_LOG_INFO);
        } else {
            while ($memberships->fetch()) {
                $resource = new net_nemein_reservations_resource($memberships->uid);
                if ($resource == false) {
                    debug_print_r("A person could not be instantinated, see above for details, skipping it. Fetachable state:",
                                  $memberships);
                } else {
                    $result[$memberships->uid] = $resource;
                }
            }
        }
        debug_pop();
        return $result;
    }

}


?>