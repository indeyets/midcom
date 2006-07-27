<?php

class net_nemein_reservations_viewer {
    
    var $_debug_prefix;
    
    var $_prefix;
    var $_config;
    var $_topic;
    var $_root_event;
    var $_root_group;
    var $_l10n;
    var $_l10n_midcom;
    var $_config_dm;
    var $_resource;
    var $_reservation;
    
    var $_mode;
    var $_toolbar;
    var $_auth;

    var $errcode;
    var $errstr;
    
    
    function net_nemein_reservations_viewer($topic, $config) {
        $this->_debug_prefix = "net.nemein.reservationsviewer::";

        $this->_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_config = $config;
        $this->_topic = $topic;
        $this->_root_event = null;
        $this->_root_group = null;
        $this->_config_dm = null;
        $this->_mode = "";
        $this->_toolbar = Array();
        $this->_auth = null;
        
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.reservations");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        
        $this->_check_root_event();
        $this->_check_root_group();
        $this->_prepare_config_dm();
    }
    
    function get_metadata() {
        return FALSE;
    }

    function can_handle($argc, $argv) {
        $GLOBALS["midcom"]->set_custom_context_data("configuration", $this->_config);
        $GLOBALS["midcom"]->set_custom_context_data("configuration_dm", $this->_config_dm);
        $GLOBALS["midcom"]->set_custom_context_data("root_group", $this->_root_group);
        $GLOBALS["midcom"]->set_custom_context_data("root_event", $this->_root_event);
        $GLOBALS["midcom"]->set_custom_context_data("l10n", $this->_l10n);
        $GLOBALS["midcom"]->set_custom_context_data("l10n_midcom", $this->_l10n_midcom);
        $GLOBALS["midcom"]->set_custom_context_data("errstr", $this->errstr);
        $GLOBALS["midcom"]->set_custom_context_data("auth", $this->_auth);
        $GLOBALS["midcom"]->set_custom_context_data("view_toolbar", $this->_view_toolbar);
        $GLOBALS["midcom"]->set_custom_context_data("resource", $this->_resource);
        $GLOBALS["midcom"]->set_custom_context_data("reservation", $this->_reservation);
        
        $this->_auth = new net_nemein_reservations_auth();
        
        if ($argc <= 1)
            return TRUE;
        
        if ($argc == 2) {
            switch ($argv[1]) {
                case "res_start":
                case "res_end":
                case "res_detail":
                case "res_complete":
                    return true;
            }
        }
        // /<resource_guid>/res_details/<reservation_guid>
        if (   $argc == 3
            && (   $argv[1] == 'reservation'
                || $argv[1] == 'reservation_print'))
        {
            return true;
        }
        
        return FALSE;
    }


    function handle($argc, $argv) {
        debug_push($this->_debug_prefix . "handle");
        
        
        debug_pop();
        
        if ($argc == 0) {
            $this->_mode = "welcome";
            return true;
        }
        
        if ($argc == 1) {
            return $this->_init_view($argv[0]);
        }
        
        if ($argc == 2) {
            switch($argv[1]){
                case "res_start":
                    return $this->_init_res_start($argv[0]);
                case "res_end":
                    return $this->_init_res_end($argv[0]);
                case "res_detail":
                    return $this->_init_res_detail($argv[0]);
                case "res_complete":
                    return $this->_init_res_complete($argv[0]);
            }
        }
        if (   $argc == 3
            && (   $argv[1] == 'reservation'
                || $argv[1] == 'reservation_print'))
        {
            return $this->_init_reservation_details($argv);
        }
        
        $this->errcode = MIDCOM_ERRCRIT;
        $this->errstr = "Method unknown";
        return TRUE;
    }
    
    function show() {
        eval("\$this->_show_" . $this->_mode . "();");
        return TRUE;
    }
    
    /************** PAGE: Welcome ***************************/
    
    function _show_welcome() {
        debug_push($this->_debug_prefix . "_show_welcome");
        
        midcom_show_style("heading-resource-index");
        midcom_show_style("resource-index-begin");
        
        $resources = net_nemein_reservations_resource::list_resources();
        
        if (count($resources) == 0) {
            midcom_show_style("resource-index-none");
        } else {
            foreach ($resources as $id => $resource) {
                $this->_resource = $resource;
                $GLOBALS["view_guid"] = $resource->person->guid();
                midcom_show_style("resource-index-item");
            }
        }
        
        midcom_show_style("resource-index-end");
        
        debug_pop();
    }
    
    /************** PAGE: Reservation step 1: Start time ***************************/
    
    function _init_res_start($guid) {
        debug_push($this->_debug_prefix . "_init_res_start");
        
        // Load JScalendar
        $this->_prepare_jscalendar('net_nemein_reservations_form_start');
        
        $session = new midcom_service_session();
        
        $this->_resource = new net_nemein_reservations_resource($guid);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Do we have a cancel button. */
        if (array_key_exists("form_cancel", $_REQUEST)) {
            $this->_relocate($this->_resource->dm->data["_storage_guid"] . ".html");
            /* This will exit() */
        }
        
        /* Check for submit, if it is there, check the date. */
        if (array_key_exists("form_submit", $_REQUEST)) {
            if (! array_key_exists("form_start", $_REQUEST)) {
                $this->errstr = "Request incomplete";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_print_r("Request was:", $_REQUEST);
                debug_pop();
                return false;
            }
            
            if (trim($_REQUEST["form_start"]) == "") {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("start time is invalid."));
                $this->_mode = "res_start";
                debug_pop();
                return true;
            }
            $start = strtotime($_REQUEST["form_start"]);
            
            if ($start == -1) {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("start time is invalid."));
                $this->_mode = "res_start";
                debug_pop();
                return true;
            }
            
            //2006-06-30, rambo: added 5 minute grace period
            if ($start+(60*5) < time()) {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("start time is in the past."));
                $this->_mode = "res_start";
                debug_pop();
                return true;
            }
            
            /* Check for other events */
            $res = $this->_resource->get_reservation_at($start);
            $newstart = -1;
            
            while(   ! is_null($res)
                  && $res->event->end >= $start)
            {
                debug_print_r("The following event conflicts. searching a new start date and checking again:", $res->event);
                $newstart = $res->event->end + 1;
                $res = $this->_resource->get_reservation_at($newstart);
            }
            // TODO: Check wether the gap found is at least as wide as the buffer time
            //$next = array_shift($this->_resource->list_reservations($newstart));
            
            
            if ($newstart != -1) {
                $session->set("reservation_errmsg", 
                    $this->_l10n->get("this resource is already booked at that time. advanced to next free time gap"));
                $_REQUEST["form_start"] = strftime("%Y-%m-%d %H:%M:%S", $newstart);
                $this->_mode = "res_start";
                debug_pop();
                return true;
            }
            
            $session->set("reservation_start", $start);
            $this->_relocate($this->_resource->dm->data["_storage_guid"] . "/res_end.html");
            /* This will exit() */
        }
        
        /* Nothing happened so far */
        $this->_mode = "res_start";
        debug_pop();
        return true;
    }
    
    function _show_res_start() {
        midcom_show_style("heading-reservation-start");
        midcom_show_style("show-resource-short");
        midcom_show_style("reservation-errmsg");
        midcom_show_style("form-reservation-starttime");
    }
    
    /************** PAGE: Reservation step 2: End time ***************************/
    
    function _init_res_end($guid) {
        debug_push($this->_debug_prefix . "_init_res_end");
        
        // Load JScalendar
        $this->_prepare_jscalendar('net_nemein_reservations_form_end');        
        
        $session = new midcom_service_session();
        
        $this->_resource = new net_nemein_reservations_resource($guid);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Do we have a cancel button. */
        if (array_key_exists("form_cancel", $_REQUEST)) {
            $session->remove("reservation_start");
            $this->_relocate($this->_resource->dm->data["_storage_guid"] . ".html");
            /* This will exit() */
        }
        
        /* Check for submit, if it is there, check the date. */
        if (array_key_exists("form_submit", $_REQUEST)) {
            if (! array_key_exists("form_end", $_REQUEST)) {
                $this->errstr = "Request incomplete";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_print_r("Request was:", $_REQUEST);
                debug_pop();
                return false;
            }
            
            if (trim($_REQUEST["form_end"]) == "") {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("end time is invalid."));
                $this->_mode = "res_end";
                debug_pop();
                return true;
            }
            
            $end = strtotime($_REQUEST["form_end"]);
            if ($end == -1) {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("end time is invalid."));
                $this->_mode = "res_end";
                debug_pop();
                return true;
            }
            /* Add buffer time to registration */
            $buffer = ((int) $this->_config->get("event_buffer_time")) * 60;
            $end += $buffer;
            
            $start = $session->get("reservation_start");
            if ($end < $start) {
                /* Error in timestamp */
                $session->set("reservation_errmsg", $this->_l10n->get("end time before start time."));
                $this->_mode = "res_end";
                debug_pop();
                return true;
            }
            
            /* Check for other events */
            $reservations = $this->_resource->list_reservations($start, $end);
            if (count($reservations) > 0) {
                foreach ($reservations as $id => $reservation) {
                    if ($reservation->event->start > $end || $reservation->event->end < $start) {
                        debug_print_r("Skipping this event, it is in the future.", $reservation->event);
                        continue;
                    }
                    debug_add("Another reservation does already exist here, can't create another one.", MIDCOM_LOG_INFO);
                    debug_print_r("Event was:", $reservation->event);
                    $session->set("reservation_errmsg", 
                        $this->_l10n->get("this resource is already booked at that time. end time adjusted"));
                    $_REQUEST["form_end"] = strftime("%Y-%m-%d %H:%M:%S", $reservation->event->start - $buffer - 1);
                    $this->_mode = "res_end";
                    debug_pop();
                    return true;
                }
            }
            
            /* Ok, we have a valid timespan, lets create an event and redirect to stage 3 */
            
            $this->_su();
            $event = net_nemein_reservations_reservation::create_empty_event($start, $end, $this->_resource);
            if (is_null($event)) 
            {
                // Format errors before mgd_unsetuid'ing, or mgd_errstr gets lost.
                $this->errstr = "Could not create an empty event, this is critical: " . mgd_errstr();
                $this->errcode = MIDCOM_ERRCRIT;
                $this->_su(false);
                debug_pop();
                return false;
            }
            $event->parameter("midcom.helper.datamanager", "layout", $this->_config->get("schema_reservation"));
            $this->_su(false);
            
            $session->remove("reservation_start");
            $session->remove("reservation_end");
            $session->set("reservation_guid", $event->guid());
            
            $this->_relocate($this->_resource->dm->data["_storage_guid"] . "/res_detail.html");
            /* This will exit() */
        }
        
        /* Nothing happened so far */
        $this->_mode = "res_end";
        debug_pop();
        return true;
    }
    
    function _show_res_end() {
        midcom_show_style("heading-reservation-end");
        midcom_show_style("show-resource-short");
        midcom_show_style("reservation-errmsg");
        midcom_show_style("form-reservation-endtime");
    }
    
    /************** PAGE: Reservation step 3: Reservation details ***************************/
    
    function _init_res_detail($guid) {
        debug_push($this->_debug_prefix . "_init_res_detail");
        
        $session = new midcom_service_session();
        
        $this->_resource = new net_nemein_reservations_resource($guid);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        $this->_reservation = new net_nemein_reservations_reservation($session->get("reservation_guid"));
        if (! $this->_reservation) {
            $this->errstr = "Failed to open reservation. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        $this->_su();
        $result = $this->_reservation->dm->process_form();
        $this->_su(false);
        
        $errors = $this->_reservation->dm->errstr;
        
        if (strlen(trim($errors)) > 0) 
            $session->set("reservation_errmsg", $errors);
        
        switch ($result) {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("Still editing");
                break;
            
            case MIDCOM_DATAMGR_SAVED:
                debug_add("Saved");
                $this->_su();
                $result = $this->_reservation->finish();
                $this->_su(false);
                if ($result) {
                    $this->_relocate($this->_resource->dm->data["_storage_guid"] . "/res_complete.html");
                } else {
                    // errstr is set by finsih().
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_pop();
                    return false;
                }
                // Relocate to success
                break;
            
            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Cancelled");
                $this->_reservation->delete();
                $this->_relocate($this->_resource->dm->data["_storage_guid"] . ".html");
                break;
            
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager failed to process the form. See the debug log.";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errstr = "Unknown Datamanager return code. See the debug log.";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        /* Nothing happened so far */
        $this->_mode = "res_detail";
        debug_pop();
        return true;
    }
    
    function _show_res_detail() {
        midcom_show_style("heading-reservation-detail");
        midcom_show_style("show-resource-short");
        midcom_show_style("reservation-errmsg");
        midcom_show_style("dmform-reservation");
    }
    
    
    /************** PAGE: Reservation step 4: Reservation complete ***************************/
    
    function _init_res_complete($guid) {
        debug_push($this->_debug_prefix . "_init_res_complete");
        
        $session = new midcom_service_session();
        
        $this->_resource = new net_nemein_reservations_resource($guid);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        $this->_reservation = new net_nemein_reservations_reservation($session->get("reservation_guid"));
        if (! $this->_reservation) {
            $this->errstr = "Failed to open reservation. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }        
        $session->remove("reservation_guid");
        
        /* Nothing happened so far */
        $this->_mode = "res_complete";
        debug_pop();
        return true;
    }
    
    function _show_res_complete() {
        midcom_show_style("heading-reservation-complete");
        midcom_show_style("show-resource-short");
        midcom_show_style("reservation-errmsg");
        midcom_show_style("dmview-reservation");
    }

    /************* PAGE: onsite Reservation details (after complete) ************************/
    function _init_reservation_details($args)
    {
        debug_push($this->_debug_prefix . "_init_reservation_details");
        
        
        $this->_resource = new net_nemein_reservations_resource($args[0]);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        $this->_reservation = new net_nemein_reservations_reservation($args[2]);
        if (! $this->_reservation) {
            $this->errstr = "Failed to open reservation. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }        

       // $GLOBALS["net_nemein_reservations_current_leaf"] = "event-{$this->_reservation->event->id}";
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_reservation->event->id;        

        /* Nothing happened so far */
        if ($args[1] == 'reservation_print')
        {
            $GLOBALS['midcom']->skip_page_style = true;
            $this->_mode = "reservation_details_print";
        }
        else
        {
            $this->_mode = "reservation_details";
        }
        debug_pop();
        return true;
    }
    
    function _show_reservation_details()
    {
        midcom_show_style("show-reservation_details");
    }

    function _show_reservation_details_print()
    {
        midcom_show_style("show-reservation_details_print");
    }
    
    /************** PAGE: Resource Detail ***************************/
    
    function _init_view($guid) {
        debug_push($this->_debug_prefix . "_init_view");
        
        $this->_resource = new net_nemein_reservations_resource($guid);
        if (! $this->_resource) {
            $this->errstr = "Failed to open resource. See Debug Log for details.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        $this->_mode = "view";
        
        debug_pop();
        return true;
    }
    
    function _show_view() {
        midcom_show_style("heading-resource-view");
        midcom_show_style("show-resource");
        $this->_show_reservations_for_resource();
    }
    
    
    /************** PRIVATE HELPER METHODS **********************/
    
    function _show_reservations_for_resource() {
        midcom_show_style("heading-reservations-index");
        midcom_show_style("reservations-index-start");
        
        //2006-06-27, rambo: show reservations that have only just passed 2h ago.
        $reservations = $this->_resource->list_reservations(time()-7200);
        if (count($reservations) == 0) {
            midcom_show_style("reservations-index-none");
        } else {
            foreach ($reservations as $id => $reservation) {
                $this->_reservation = $reservation;
                midcom_show_style("reservations-index-item");
            }
        }
        
        midcom_show_style("reservations-index-end");
    }
    
    function _su ($on = true) {
        //2006-06-26, rambo: extra checks for older midgard
        if (   (   isset($_MIDGARD)
                && is_array($_MIDGARD)
                && $_MIDGARD['admin'])
            || (   isset($GLOBALS['midgard'])
                && is_object($GLOBALS['midgard'])
                && $GLOBALS['midgard']->admin)
            || mgd_is_event_owner($this->_root_event->id))
        {
            // No need to sudo, we already have write permissions
            return true;
        }
    
        if ($on) {
            if (! mgd_auth_midgard ($this->_config->get("admin_user"), $this->_config->get("admin_password"), false)) {
                $this->errstr = "mgd_auth_midgard to admin level user failed.";
                $this->errcode = MIDCOM_ERRFORBIDDEN;
                return false;
            } else {
                /* Call mgd_get_midgard, seems to be required according to emile/piotras */
                $midgard = mgd_get_midgard();
                debug_print_r("New midgard object is: ", $midgard);
                return true;
            }
        } else {
            $result = mgd_unsetuid();
            /* Call mgd_get_midgard, seems to be required according to emile/piotras */
            $unused = mgd_get_midgard();
            return $result;
        }
    }
    
    function _relocate ($url) {
        $GLOBALS["midcom"]->relocate(
              $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . $url);
    }
    
    function _check_root_event () {
        debug_push($this->_debug_prefix . "_check_root_event" );
        
        $guid = $this->_config->get("root_event_guid");
        if (! is_null ($guid)) {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "event") {
                debug_add("We could not successfully load the root Event, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Resulting object dump: ", $object);
                $GLOBALS["midcom"]->generate_error("Invalid configuration, root_event_guid [$guid] is invalid.",
                                                   MIDCOM_ERRCRIT);
            }
            $this->_root_event = $object;
        } else {
            $GLOBALS["midcom"]->generate_error("Configuration incomplete, visit AIS to complete it.",
                                               MIDCOM_ERRCRIT);
        }
        debug_pop();
        return;
    }
    
    function _check_root_group () {
        debug_push($this->_debug_prefix . "_check_root_group");
        
        $guid = $this->_config->get("root_group_guid");
        if (! is_null ($guid)) {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "grp") {
                debug_add("We could not successfully load the root group, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);

                debug_print_r("Resulting object dump: ", $object);
                $GLOBALS["midcom"]->generate_error("Invalid configuration, root_group_guid [$guid] is invalid.",
                                                    MIDCOM_ERRCRIT);
            }
            $this->_root_group = $object;
        } else {
            $GLOBALS["midcom"]->generate_error("Configuration incomplete, visit AIS to complete it.",
                                               MIDCOM_ERRCRIT);
        }
        debug_pop();
        return;
    }
    
    function _prepare_config_dm (){
        debug_push($this->_debug_prefix . "_prepare_config_dm");
        
        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/reservations/config/schemadb_config.dat");

        if ($this->_config_dm == false) {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to instantinate configuration datamanager.");
        }
        
        if (! $this->_config_dm->init($this->_topic)) {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_topic);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to initialize configuration datamanager.");
        }
        

        debug_pop();
        return;
    }
    
    function _prepare_jscalendar($fieldname)
    {
        $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/jscript-calendar';
        $GLOBALS['midcom']->add_jsfile("{$prefix}/calendar.js");
            
        // Select correct locale
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $language = $i18n->get_current_language();
        switch ($language)
        {
            // TODO: Add more languages here when corresponding locale files exist
            case "fi":
                $GLOBALS['midcom']->add_jsfile("{$prefix}/calendar-fi.js");
                break;
            case "en":
            default:
                $GLOBALS['midcom']->add_jsfile("{$prefix}/calendar-en.js");
                break;
        }
                            
        $GLOBALS['midcom']->add_jsfile("{$prefix}/calendar-setup.js");

        $this->_initfuncname = "showCalendar" . md5($fieldname);
        $tmp = <<<EOT
function {$this->_initfuncname}() {
    Calendar.setup(
        {
            ifFormat   : "%Y-%m-%d %H:%M:%S",
            showsTime   : true,
            align       : "Br",
            firstDay    : 1,
            timeFormat  : 24,
            showOthers  : true,
            singleClick : false,
            inputField  : "{$fieldname}",
            button      : "{$fieldname}_trigger"
        }
    );
}
EOT;
        $GLOBALS['midcom']->add_jscript($tmp);    
    }
    
} // viewer

?>
