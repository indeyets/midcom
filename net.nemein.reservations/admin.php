<?php

class net_nemein_reservations_admin {

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
    var $_auth;

    var $errcode;
    var $errstr;
    
    var $_local_toolbar;
    var $_topic_toolbar;


    function net_nemein_reservations_admin($topic, $config) {
        $this->_debug_prefix = "net.nemein.reservations admin::";
        
        $this->_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_config = $config;
        $this->_topic = $topic;
        $this->_root_event = null;
        $this->_root_group = null;
        $this->_config_dm = null;
        $this->_mode = "";
        $this->_auth = null;
        
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.reservations");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
        
        $this->_check_root_event();
        $this->_check_root_group();
        $this->_prepare_config_dm();
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
        $GLOBALS["midcom"]->set_custom_context_data("resource", $this->_resource);
        $GLOBALS["midcom"]->set_custom_context_data("reservation", $this->_reservation);
        
        $this->_auth = new net_nemein_reservations_auth();
        
        if ($argc == 0)
            return TRUE;
        
        switch ($argv[0]) {
            case "config":
                return ($argc == 1); /* config.html */
            case "resource":
                if ($argc < 2)
                    return false;
                switch ($argv[1]) {
                    case "create": /* resource/config.html */
                        return ($argc == 2);
                    case "view": /* resource/(view|edit|delete)/$id.html */
                    case "edit":
                    case "delete":
                        return ($argc == 3);
                }
                break;
            case "reservation":
                switch ($argv[1]) {
                    case "maintain": /* reservation/maintain.html */
                        return ($argc == 2);
                    case "approve": /* reservation/(...)/$id.html */
                    case "reject":
                    case "edit":
                    case "delete":
                    case "view":
                        return ($argc == 3);
                }
                break;
        }
        return false;
    }


    function handle($argc, $argv) {
        debug_push($this->_debug_prefix . "handle");
                
        if (! $this->_root_event) {
            $msg = sprintf($this->_l10n->get("failed to open root event %s: %s"),
                           $this->_config->get("root_event_guid"),
                           mgd_errstr());
            debug_add ("Root event undefined, last midgard error was: " . mgd_errstr());
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        }
        
        $this->_prep_toolbar();
        
        debug_pop();
        
        if ($argc == 0) {
            return $this->_init_welcome();
        }
        

        switch($argv[0]){
            case "config":
                $this->_auth->check_is_admin();
                return $this->_init_config();
                
            case "resource":
                switch ($argv[1]) {
                    case "create":
                        $this->_auth->check_is_admin();
                        return $this->_init_create_resource();
                    case "view":
                        return $this->_init_view_resource($argv[2]);
                    case "edit":
                        $this->_auth->check_is_admin();
                        return $this->_init_edit_resource($argv[2]);
                    case "delete":
                        $this->_auth->check_is_admin();
                        return $this->_init_delete_resource($argv[2]);
                }
                
            case "reservation":
                switch ($argv[1]) {
                    case "view":
                        return $this->_init_view_reservation($argv[2]);
                    case "edit":
                        return $this->_init_edit_reservation($argv[2]);
                    case "delete":
                        return $this->_init_delete_reservation($argv[2]);
                    case "approve":
                        return $this->_init_approve_reservation($argv[2]);
                    case "reject":
                        return $this->_init_reject_reservation($argv[2]);
                    case "maintain":
                        return $this->_init_maintain_reservation();
                }
        }
        
        /* We should not get to this point */
        
        $this->errcode = MIDCOM_ERRCRIT;
        $this->errstr = "Method unknown";
        return false;
        
    }

    
    function show() {
        
        eval("\$this->_show_" . $this->_mode . "();");
        
        return TRUE;
    }
    
    function get_metadata() {
        return FALSE;
    }
    
    /******************* PAGE: Welcome ******************************/
    
    function _init_welcome() {
        debug_push($this->_debug_prefix . "_init_welcome");
        
        $this->_mode = "welcome";
        
        debug_pop();
        return true;
    }
    
    function _show_welcome() {
        midcom_show_style("admin-heading-welcome");
        $resources = net_nemein_reservations_resource::list_resources();
        foreach ($resources as $rouid => $resource) {
            $reservations = $resource->list_unapproved_reservations();
            if (count($reservations) == 0) {
                continue;
            }
            $this->_resource = $resource;
            midcom_show_style("admin-unapproved-start");
            foreach ($reservations as $resid => $reservation) {
                $this->_reservation = $reservation;
                midcom_show_style("admin-unapproved-item");
            }
            midcom_show_style("admin-unapproved-end");
        }
    }
    
    
    /******************* PAGE: Configuration ******************************/
    
    function _init_config() {
        debug_push($this->_debug_prefix . "_init_config");
        
        /* Configure toolbar */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        switch ($this->_config_dm->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                // We stay here whatever happens here, at least as long as
                // there is no fatal error.
                break;
            
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        $this->_mode = "config";
        
        debug_pop();
        return true;
    }
    
    function _show_config() {
        midcom_show_style("admin-config");
    }
    
    
    /******************* PAGE: View Reservation ******************************/
    
    function _init_view_reservation($id) {
        debug_push($this->_debug_prefix . "_init_view_reservation");
        
        $this->_reservation = new net_nemein_reservations_reservation($id);
        if (! $this->_reservation) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_resource = $this->_reservation->resource;
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "resource/view/{$this->_resource->person->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view resource'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/edit/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/delete/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if ($this->_reservation->dm->data["approved"]["timestamp"] == 0) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/approve/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('approve'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/reject/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reject and delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        $this->_mode = "view_reservation";
        
        debug_pop();
        return true;
    }
    
    function _show_view_reservation() {
        midcom_show_style("admin-heading-reservation-view");
        midcom_show_style("admin-dmview-reservation");
    }
    
    
    /******************* PAGE: Approve Reservation ******************************/
    
    function _init_approve_reservation($id) {
        debug_push($this->_debug_prefix . "_init_approve_reservation");
        
        $this->_reservation = new net_nemein_reservations_reservation($id);
        if (! $this->_reservation) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_resource = $this->_reservation->resource;
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Any error is set through the reservation class at this point. */
        $this->_reservation->approve();
        
        /* Set toolbar */
        /* Set toolbar */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "resource/view/{$this->_resource->person->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view resource'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/edit/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/delete/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if ($this->_reservation->dm->data["approved"]["timestamp"] == 0) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/approve/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('approve'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/reject/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reject and delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        $this->_mode = "view_reservation";
        
        debug_pop();
        return true;
    }
    
    /******************* PAGE: Approve Reservation ******************************/
    
    function _init_reject_reservation($id) {
        debug_push($this->_debug_prefix . "_init_reject_reservation");
        
        $this->_reservation = new net_nemein_reservations_reservation($id);
        if (! $this->_reservation) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_resource = $this->_reservation->resource;
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Any error is set through the reservation class at this point. */
        if ($this->_reservation->reject_and_delete()) {
            /* Success */
            $this->_relocate("resource/view/{$this->_resource->person->id}.html");
        }
        
        $this->errstr = "Rejection of the reseravtion failed. See debug log.";
        $this->errcode = MIDCOM_ERRCRIT;
        debug_pop();
        return false;
    }
    
    /******************* PAGE: Edit Reservation ******************************/
    
    function _init_edit_reservation($id) {
        debug_push($this->_debug_prefix . "_init_edit_reservation");
        
        $this->_reservation = new net_nemein_reservations_reservation($id);
        if (! $this->_reservation) {
            $this->errstr = "Could not load resource, see debug log.";

            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_resource = $this->_reservation->resource;
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        $this->_local_toolbar->disable_view_page();
        
        /* Process form */
        switch ($this->_reservation->dm->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("We are still editing.");
                break;
            
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Datamanger has saved or cancelled, return to view.");
                $this->_relocate("reservation/view/{$this->_reservation->event->id}.html");
                /* This will exit() */
            
            case MIDCOM_DATAMGR_FAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
        }
        
        $this->_mode = "edit_reservation";
        
        debug_pop();
        return true;
    }
    
    function _show_edit_reservation() {
        midcom_show_style("admin-heading-reservation-edit");
        midcom_show_style("admin-dmform-reservation");
    }
    
    
    /******************* PAGE: Delete Reservation ******************************/
    
    function _init_delete_reservation($id) {
        debug_push($this->_debug_prefix . "_init_delete_reservation");
        
        $this->_reservation = new net_nemein_reservations_reservation($id);
        if (! $this->_reservation) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_resource = $this->_reservation->resource;
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        /* Set toolbar */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "resource/view/{$this->_resource->person->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view resource'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/view/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "reservation/edit/${id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit reservation'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if ($this->_reservation->dm->data["approved"]["timestamp"] == 0) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/approve/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('approve'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "reservation/reject/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reject and delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        if (array_key_exists("ok", $_REQUEST) && $_REQUEST["ok"] == "yes") {
            if (! $this->_reservation->delete()) {
                $this->errstr = "Failed to delete the Resoruce, see the debug log for details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
            }
            $this->_relocate("resource/view/{$this->_resource->person->id}.html");
            /* This will exit() */
        }
        
        $this->_mode = "delete_reservation";
        
        debug_pop();
        return true;
    }
    
    function _show_delete_reservation() {
        midcom_show_style("admin-heading-reservation-delete");
        midcom_show_style("admin-dmview-reservation");
    }
    
    
    /******************* PAGE: View Resource ******************************/
    
    function _init_view_resource($id) {
        debug_push($this->_debug_prefix . "_init_view_resource");
        
        $this->_resource = new net_nemein_reservations_resource($id);
        if (! $this->_resource) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        if ($this->_auth->is_admin()) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/edit/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/delete/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        $this->_mode = "view_resource";
        
        debug_pop();
        return true;
    }
    
    function _show_view_resource() {
        midcom_show_style("admin-heading-resource-view");
        midcom_show_style("admin-dmview-resource");
        
        $reservations = $this->_resource->list_unapproved_reservations();
        if (count($reservations) > 0) {
            midcom_show_style("admin-unapproved-start");
            foreach ($reservations as $resid => $reservation) {
                $this->_reservation = $reservation;
                midcom_show_style("admin-unapproved-item");
            }
            midcom_show_style("admin-unapproved-end");
        }
        
        $reservations = $this->_resource->list_approved_reservations();
        if (count($reservations) > 0) {
            midcom_show_style("admin-approved-start");
            foreach ($reservations as $resid => $reservation) {
                $this->_reservation = $reservation;
                midcom_show_style("admin-approved-item");
            }
            midcom_show_style("admin-approved-end");
        }
    }
    
    
    /******************* PAGE: Delete Resource ******************************/
    
    function _init_delete_resource($id) {
        debug_push($this->_debug_prefix . "_init_delete_resource");
        
        $this->_resource = new net_nemein_reservations_resource($id);
        if (! $this->_resource) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        if ($this->_auth->is_admin()) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/view/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/edit/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        if (array_key_exists("ok", $_REQUEST) && $_REQUEST["ok"] == "yes") {
            if (! $this->_resource->delete()) {
                $this->errstr = "Failed to delete the Resoruce, see the debug log for details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
            }
            $this->_relocate("");
            /* This will exit() */
        }
        
        $this->_mode = "delete_resource";
        
        debug_pop();
        return true;
    }
    
    function _show_delete_resource() {
        midcom_show_style("admin-heading-resource-delete");
        midcom_show_style("admin-dmview-resource");
    }
    
    
    /******************* PAGE: Edit Resource ******************************/
    
    function _init_edit_resource($id) {
        debug_push($this->_debug_prefix . "_init_edit_resource");
        
        $this->_resource = new net_nemein_reservations_resource($id);
        if (! $this->_resource) {
            $this->errstr = "Could not load resource, see debug log.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        
        /* Set toolbar */
        if ($this->_auth->is_admin()) {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/view/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "resource/delete/${id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        
        /* Process form */
        switch ($this->_resource->dm->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("We are still editing.");
                break;
            
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Datamanger has saved or cancelled, return to view.");
                $this->_relocate("resource/view/{$this->_resource->person->id}.html");
                /* This will exit() */
            
            case MIDCOM_DATAMGR_FAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
        }
        
        $this->_mode = "edit_resource";
        
        debug_pop();
        return true;
    }
    
    function _show_edit_resource() {
        midcom_show_style("admin-heading-resource-edit");
        midcom_show_style("admin-dmform-resource");
    }
    
    
    /******************* PAGE: Create Resource ******************************/
    
    function _init_create_resource() {
        debug_push($this->_debug_prefix . "_init_create_resource");
        
        $session = new midcom_service_session();
        
        if (! $session->exists("admin_create_resource_guid")) {
            debug_add("We have to freshly create an object.");
            // $this->_resource =& net_nemein_reservations_resource::get_creation_instance();
            $this->_resource = new net_nemein_reservations_resource();
            $create = true;
        } else {
            $guid = $session->get("admin_create_resource_guid");
            $this->_resource = new net_nemein_reservations_resource($guid);
            $create = false;
        }
        if (   ! $this->_resource
            || ($create && ! $this->_resource->init_creation_mode())) 
        {
            $this->errstr = "Reservation could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Reservation could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }
        if (! $create) {
            $GLOBALS["net_nemein_reservations_current_leaf"] = $this->_resource->person->id;
        }
        
        switch ($this->_resource->dm->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    /* First call, display from. */
                    debug_add("First call within creation mode");
                    break;
                }
            
            case MIDCOM_DATAMGR_EDITING:
                if ($create) {
                    debug_print_type("Typeof resource:", $this->_resource);
                    debug_print_type("Typeof resoruce->person:", $this->_resource->person);
                    $guid = $this->_resource->person->guid();
                    debug_add("First time submit, the DM has created an object, adding GUID [$guid] to session data");
                    $session->set("admin_create_resource_guid", $guid);
                } else {
                    debug_add("Subsequent submit, we already have a guid in the session space.");
                }
                break;
            
            case MIDCOM_DATAMGR_SAVED:
                debug_add("Datamanger has saved, relocating to view.");
                $session->remove("admin_create_resource_guid");
                $this->_relocate("resource/view/" . $this->_resource->person->id . ".html");
                /* This will exit() */
            
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel without anything being created, redirecting to the welcome screen.");
                    $this->_relocate("");
                    /* This will exit() */
                }
            
            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel with a temporary object, deleting it and redirecting to the welcome screen.");
                    $this->_resource->delete();
                    $this->_relocate("");
                    /* This will exit() */
                }
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
            
        }
        
        $this->_mode = "create_resource";
        
        debug_pop();
        return true;
    }
    
    function _show_create_resource() {
        midcom_show_style("admin-heading-resource-create");
        midcom_show_style("admin-dmform-resource");
    }
    
    
    /******************* PAGE: Maintain Reservations ******************************/
    
    function _init_maintain_reservation() {
        debug_push($this->_debug_prefix . "_init_maintain_reservation");
        
        /* Configure toolbar */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->disable_view_page();
        
        if (array_key_exists("form_mode", $_REQUEST)) 
        {
            switch ($_REQUEST["form_mode"]) 
            {
                case "delete_incomplete":
                    if (! array_key_exists("form_age", $_REQUEST)) 
                    {
                        $this->errstr = "Request incomplete.";
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_print_r("Request was:", $_REQUEST);
                        debug_pop();
                        return false;
                    }
                    $count = $this->_delete_incomplete($_REQUEST["form_age"]);
                    if ($count == -1) 
                    {
                        debug_add("delete_incomplete returned -1.");
                        debug_pop();
                        return false;
                    }
                    $msg = sprintf($this->_l10n->get("cleaned up %d incomplete reservations."), $count);
                    $GLOBALS["view_contentmgr"]->msg .= "{$msg}<br>\n";
                    break;
                
                default:
                    $this->errstr = "Unknown operation.";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_print_r("Request was:", $_REQUEST);
                    debug_pop();
                    return false;
            }
        }
        
        $this->_mode = "reservation_maintain";
        
        debug_pop();
        return true;
    }
    
    function _show_reservation_maintain() {
        midcom_show_style("admin-heading-reservation-maintain");
        
        $found = false;
        
        $resources = net_nemein_reservations_resource::list_resources();
        
        foreach ($resources as $id => $resource) {
            $reservations = $resource->list_reservations(0, 2000000000, "start", $this->_config->get("event_type_corrupt"));
            
            if (count($reservations) > 0) {
                $found = true;
                midcom_show_style("admin-corrupt-start");
                foreach ($reservations as $id => $reservation) {
                    $this->_reservation = $reservation;
                    midcom_show_style("admin-corrupt-item");
                }
                midcom_show_style("admin-corrupt-end");
            }
            
        }
        
        if (! $found)
            midcom_show_style("admin-no-corrupt-reservations");
    }
    
    
    
    
    /************** PRIVATE HELPER METHODS **********************/
    
    function _delete_incomplete($age) {
        if (   ! is_numeric($age)
            || $age < 0)
        {
            $this->errstr = "Request invalid.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_print_r("Request was:", $_REQUEST);
            return -1;
        }
        $end = time() - ((integer) $age);
        debug_add("Querying reservations from 0 to {$end}");
        $reservations = mgd_list_events($this->_root_event->id, "start", $this->_config->get("event_type_incomplete"));
        $count = 0;
        if (! $reservations) {
            debug_add("No matches.");
            return 0;
        } else {
            $count++;
            while ($reservations->fetch()) {
                $reservation = new net_nemein_reservations_reservation($reservations->id);
                if ($reservation->event->created > $end)
                    continue;
                if (! $reservation) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_add("Failed to load reservation record!, see above.");
                    return -1;
                }
                debug_add("Deleting reservation ID {$reservations->id}", MIDCOM_LOG_INFO);
                if (! $reservation->delete()) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_add("Failed to delete reservation!, see above.");
                    return -1;
                }
            }
            return $count;
        }
        
    }
    
    function _check_root_event() {
        debug_push($this->_debug_prefix . "_check_root_event" );
        
        $guid = $this->_config->get("root_event_guid");
        if (! is_null ($guid)) {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "event") {
                debug_add("We could not successfully load the root Event, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Resulting object dump: ", $object);
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                                   "Invalid configuration, root_event_guid [$guid] is invalid.");
            }
            $this->_root_event = $object;
            debug_pop();
            return;
        }
        
        $event = null;
        $nn_event_title = $this->_config->get("root_event_name");
        
        $rootevents = mgd_list_events(0);
        if ($rootevents != false) {
            while ($rootevents->fetch()) {
                if ($rootevents->title == $nn_event_title) {
                    $event = mgd_get_event($rootevents->id);
                    debug_print_r("We have a match while searching for the event [$nn_event_title]:",
                                  $event);
                    $msg = sprintf($this->_l10n->get("detected existing root event <em>%s</em>"),
                                   $event->title);
                    $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
                    break;
                }
            }
        }
        
        if (is_null($event)) {
            debug_add("We could not found an already existing root event. So we create one.");
            
            $event = mgd_get_event();
            $event->owner = $this->_topic->owner;
            $event->title = $nn_event_title;
            $event->description = "Autocreated by net.nemein.reservations.";
            $event->up = 0;
            $event->type = 0;
            $id = $event->create();
            if ($id === false) {
                $msg = sprintf($this->_l10n->get("failed to create root event: %s"),
                               mgd_errstr());
                $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
                debug_add("Could not auto-create root event: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_print_r("Tried to create this event: ", $event);
                debug_pop();

                return;
            }
            $msg = sprintf($this->_l10n->get("created root event <em>%s</em>"),
                           $event->title);
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
            $event = mgd_get_event($id);
        }
        
        $this->_root_event = $event;
        $guid = $event->guid();
        
        /* Set the topic parameter and update our local configuration copy */

        $this->_topic->parameter("net.nemein.reservations","root_event_guid",$guid);
        $this->_config->store(Array ("root_event_guid" => $guid), false);
        
        debug_pop();
        return ;
    }
    
    function _check_root_group() {
        debug_push($this->_debug_prefix . "_check_root_group");
        
        $guid = $this->_config->get("root_group_guid");
        if (! is_null ($guid)) {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "grp") {
                debug_add("We could not successfully load the root group, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Resulting object dump: ", $object);
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                                   "Invalid configuration, root_group_guid [$guid] is invalid.");
            }
            $this->_root_group = $object;
            debug_pop();
            return;
        }
        
        $nn_group_title = $this->_config->get("root_group_name");
        $group = mgd_get_group_by_name(0, $nn_group_title);
        
        if ($group != false) {
            debug_print_r("There is a root group named [$nn_group_title]:",
                           $group);
            $msg = sprintf($this->_l10n->get("detected existing root group <em>%s</em>"),
                           $group->name);
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        
        } else {
            debug_add("We could not found an already existing root event. So we create one.");
            
            $group = mgd_get_group();
            $group->owner = 0;
            $group->name = $nn_group_title;
            $group->extra = "Autocreated by net.nemein.reservations.";
            $id = $group->create();
            if ($id === false) {
                $msg = sprintf($this->_l10n->get("failed to create root group: %s"),
                               mgd_errstr());
                $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
                debug_add("Could not auto-create root group: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_print_r("Tried to create this group: ", $group);
                debug_pop();
                return;
            }
            $msg = sprintf($this->_l10n->get("created root group <em>%s</em>"),
                           $group->name);
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
            $group = mgd_get_group($id);
        }
        
        $this->_root_group = $group;
        $guid = $group->guid();
        
        /* Set the topic parameter and update our local configuration copy */
        
        $this->_topic->parameter("net.nemein.reservations","root_group_guid",$guid);
        $this->_config->store(Array ("root_group_guid" => $guid), false);
        
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
    
    function _relocate ($url) {
        $GLOBALS["midcom"]->relocate(
              $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . $url);
    }
    
    function _prep_toolbar() {
        if ($this->_auth->is_poweruser()) {
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        
            /* Add the new article link at the beginning*/
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'resource/create.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ), 0);
        }
        if ($this->_auth->is_admin()) {
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'reservation/maintain.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('incomplete/corrupt reservations'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
    }
    
    
} // admin

?>