<?php

class pl_olga_files_midcom {

    function initialize() {

        global $midcom;
        global $pl_olga_files_nap_activeid;
        global $pl_olga_files_layouts;

        debug_push("pl.olga.files midcom::initialize");

        $prefix = MIDCOM_ROOT . "/pl/olga/files";
        require("{$prefix}/viewer.php");
        require("{$prefix}/admin.php");
        require("{$prefix}/navigation.php");

        $midcom->load_library("midcom.helper.datamanager");

        $pl_olga_files_nap_activeid = false;

        // default configuration
        debug_add("Loading default configuration", MIDCOM_LOG_DEBUG);
        $data = file_get_contents("${prefix}/config/config_default.dat");
        eval("\$component_default = Array ( {$data} );");
        $default_config = new midcom_helper_configuration($component_default);
        
        if (mgd_snippet_exists("/sitegroup-config/pl.olga.files/config")) {
            $snippet = mgd_get_snippet_by_path("/sitegroup-config/pl.olga.files/config");
            eval("\$local_default = Array ( " . $snippet->code . ");");
            if (!$default_config->store($local_default)) {
                debug_add("Sitegroup configuration is invalid, configuration management failed, aborting", MIDCOM_LOG_CRIT);
                debug_pop();
                return false;
            }
        }
        $GLOBALS["pl_olga_files__default_config"] = new midcom_helper_configuration($default_config->get_all());

        debug_pop();
        return true;
    }


    function properties() {

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $l10n = $i18n->get_l10n("pl.olga.files");
        
        return array(
            MIDCOM_PROP_NAME => $l10n->get("files browser"),
            MIDCOM_PROP_VERSION => 1
        );
    }

} // midcom



class pl_olga_files_component {

    var $configs;
    var $components;


    function configure($configuration, $contextid) {

        $tmp = $GLOBALS["pl_olga_files__default_config"];
        if (! $tmp->store($configuration))
            return false;
        $this->configs[$contextid] = $tmp;
        return true;
    }


    function can_handle($current_object, $argc, $argv, $contextid) {

        $this->components[$contextid] = new pl_olga_files_viewer
            ($current_object, $this->configs[$contextid]);
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


    function get_metadata ($contextid) {

        return $this->components[$contextid]->get_metadata ();
    }


    function show_content ($contextid) {

        $this->components[$contextid]->show();
    }

} // component



class pl_olga_files_contentadmin {

    var $configs;
    var $components;


    function pl_olga_files_contentadmin() {

        $this->configs = array ();
        $this->components = array ();
    }


    function configure($configuration, $contextid) {

        $tmp = $GLOBALS["pl_olga_files__default_config"];
        if (! $tmp->store($configuration))
            return false;
        $this->configs[$contextid] = $tmp;
        return true;
    }


    function can_handle($current_object, $argc, $argv, $contextid) {

        $this->components[$contextid] = new pl_olga_files_admin
            ($current_object, $this->configs[$contextid]);

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



class pl_olga_files_nap {

    var $napclass;


    function pl_olga_files_nap() {

        $this->napclass = new pl_olga_files_navigation();
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