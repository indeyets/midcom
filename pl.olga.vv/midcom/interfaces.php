<?php

/* Interface classes between midcom.application and the component */

class pl_olga_vv_midcom {

    function initialize() {

        global $midcom;
        global $pl_olga_vv_nap_activeid;
        global $pl_olga_vv_layouts;

        // include code snippets
        $prefix = MIDCOM_ROOT . "/pl/olga/vv";
        require("{$prefix}/viewer.php");
        require("{$prefix}/admin.php");
        require("{$prefix}/navigation.php");

        // load pure code libraries (if needed)
        //$midcom->load_library("...");
        $midcom->load_library("midcom.helper.datamanager");
        //...

        // default configuration
        debug_add("Loading default configuration", MIDCOM_LOG_DEBUG);
        $data = file_get_contents("${prefix}/config/config_default.dat");
        eval("\$component_default = Array ( {$data} );");
        $default_config = new midcom_helper_configuration($component_default);
        
        if (mgd_snippet_exists("/sitegroup-config/pl.olga.vv/config")) {
            $snippet = mgd_get_snippet_by_path("/sitegroup-config/pl.olga.vv/config");
            eval("\$local_default = Array ( " . $snippet->code . ");");
            if (!$default_config->store($local_default)) {
                debug_add("Sitegroup configuration is invalid, configuration management failed, aborting", MIDCOM_LOG_CRIT);
                debug_pop();
                return false;
            }
        }
        $GLOBALS["pl_olga_vv__default_config"] = new midcom_helper_configuration($default_config->get_all());

        // set active id for nap class
        $pl_olga_vv_nap_activeid = false;

        return true;
    }


    function properties() {

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $l10n = $i18n->get_l10n("pl.olga.vv");
        return array(
            MIDCOM_PROP_NAME => $l10n->get("views & votes"),
            MIDCOM_PROP_VERSION => "0.1"
        );
    }

} // midcom



class pl_olga_vv_component {

    var $configs;
    var $components;


    function configure($configuration, $contextid) {

        // initalize configuration instance for this context
        $tmp = new midcom_helper_configuration($configuration);
        if ($tmp === false)
            return false;
        else {
            $this->configs[$contextid] = $tmp;
            return true;
        }
    }


    function can_handle($topic, $argc, $argv, $contextid) {

        // initialize component instance for this context
        $this->components[$contextid] = new pl_olga_vv_viewer
            ($topic, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }


    function handle($topic, $argc, $argv, $contextid) {

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



class pl_olga_vv_contentadmin {

    var $configs;
    var $components;


    function pl_olga_vv_contentadmin() {

        $this->configs = array ();
        $this->components = array ();
    }


    function configure($configuration, $contextid) {

        $tmp = new midcom_helper_configuration($configuration);
        if ($tmp === false)
            return false;
        else {
            $this->configs[$contextid] = $tmp;
            return true;
        }
    }


    function can_handle($topic, $argc, $argv, $contextid) {

        $this->components[$contextid] = new pl_olga_vv_admin
            ($topic, $this->configs[$contextid]);

        return $this->components[$contextid]->can_handle($argc, $argv);
    }

 
    function handle($topic, $argc, $argv, $contextid) {

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



class pl_olga_vv_nap {

    var $napclass;


    function pl_olga_vv_nap() {

        $this->napclass = new pl_olga_vv_navigation();
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