<?php

class net_nemein_reservations_midcom {

    function initialize() {
        global $midcom;

        debug_push("net.nemein.reservationsmidcom::initialize");
        
        $prefix = MIDCOM_ROOT . "/net/nemein/reservations";
        require("{$prefix}/viewer.php");
        require("{$prefix}/navigation.php");
        require("{$prefix}/admin.php");
        
        require("{$prefix}/_base.php");
        require("{$prefix}/auth.php");
        require("{$prefix}/resource.php");
        require("{$prefix}/reservation.php");
        
        $midcom->load_library("midcom.helper.datamanager");
        
        // default configuration
        debug_add("Loading default configuration", MIDCOM_LOG_DEBUG);
        $data = file_get_contents("${prefix}/config/config_default.dat");
        eval("\$component_default = Array ( {$data} );");
        $default_config = new midcom_helper_configuration($component_default);
        
        if (mgd_snippet_exists("/sitegroup-config/net.nemein.reservations/config")) {
            $snippet = mgd_get_snippet_by_path("/sitegroup-config/net.nemein.reservations/config");
            eval("\$local_default = Array ( " . $snippet->code . ");");
            if (!$default_config->store($local_default)) {
                debug_add("Sitegroup configuration is invalid, configuration management failed, aborting", MIDCOM_LOG_CRIT);
                debug_pop();
                return FALSE;
            }
        }
        $GLOBALS["net_nemein_reservations__default_config"] = new midcom_helper_configuration($default_config->get_all());
        debug_pop();
        return TRUE;
    }

    function properties() {
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $l10n = $i18n->get_l10n("net.nemein.reservations");
        
        return array(
            MIDCOM_PROP_NAME => $l10n->get("net.nemein.reservations"),
            MIDCOM_PROP_PURECODE => FALSE,
            MIDCOM_PROP_VERSION => 1
        );
    }

} // midcom



class net_nemein_reservations_component {

    var $configs;
    var $components;

    function net_nemein_reservations_component() {
        $this->configs = Array ();
        $this->components = Array ();
    }

    function configure($configuration, $contextid) {
        $tmp = $GLOBALS["net_nemein_reservations__default_config"];

        if (!$tmp->store($configuration))
            return FALSE;            
        $this->configs[$contextid] = $tmp;
        return TRUE;
    }

    function can_handle($current_object, $argc, $argv, $contextid) {
        $this->components[$contextid] = new net_nemein_reservations_viewer($current_object, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }
    
    function handle($current_object, $argc, $argv, $contextid) {
        return $this->components[$contextid]->handle($argc, $argv);
    }

    function errcode($contextid) {
        return $this->components[$contextid]->errcode;
    }
    
    function errstr($contextid) {
        return $this->components[$contextid]->errstr;
    }

    function get_metadata($contextid) {
        return $this->components[$contextid]->get_metadata();
    }

    function show_content($contextid) {
        $this->components[$contextid]->show();
    }

} // component



class net_nemein_reservations_contentadmin {

    var $configs;
    var $components;

    function net_nemein_reservations_contentadmin() {
        $this->configs = Array ();
        $this->components = Array ();
    }

    function configure($configuration, $contextid) {
        $tmp = $GLOBALS["net_nemein_reservations__default_config"];

        if (!$tmp->store($configuration))
            return FALSE;            
        $this->configs[$contextid] = $tmp;
        return TRUE;
    }

    function can_handle($current_object, $argc, $argv, $contextid) {
        $this->components[$contextid] = new net_nemein_reservations_admin($current_object, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }

    function handle($current_object, $argc, $argv, $contextid) {
        return $this->components[$contextid]->handle($argc, $argv);
    }

    function errcode($contextid) {
        return $this->components[$contextid]->errcode;
    }
    
    function errstr($contextid) {
        return $this->components[$contextid]->errstr;
    }

    function get_metadata($contextid) {
        return $this->components[$contextid]->get_metadata();
    }

    function show_content($contextid) {
        $this->components[$contextid]->show();
    }

} // contentadmin



class net_nemein_reservations_nap {

    var $napclass;

    function net_nemein_reservations_nap() {
        $this->napclass = new net_nemein_reservations_navigation();
    }

    function is_internal() {
        return $this->napclass->is_internal();
    }

    function set_object($object) {
        return $this->napclass->set_object($object);
    }

    function get_node() {
        return $this->napclass->get_node();
    }

    function get_current_leaf() {
        return $this->napclass->get_current_leaf();
    }

    function get_leaves() {
        return $this->napclass->get_leaves();
    }

} // nap

?>