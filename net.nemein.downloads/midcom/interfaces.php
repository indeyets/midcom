<?php

/* Interface classes between midcom.application and the component */

class net_nemein_downloads_midcom {

    function initialize() {

        global $midcom;
        global $net_nemein_downloads_nap_activeid;
        global $net_nemein_downloads_layouts;

        // include code snippets
        $prefix = MIDCOM_ROOT . "/net/nemein/downloads";
        require("{$prefix}/viewer.php");
        require("{$prefix}/admin.php");
        require("{$prefix}/navigation.php");
        require("{$prefix}/helpers.php");

        // load pure code libraries (if needed)
        //$midcom->load_library("...");
        $midcom->load_library("midcom.helper.datamanager");
        //...

        // default configuration
        debug_add("Loading default configuration", MIDCOM_LOG_DEBUG);
        $snippet = mgd_get_snippet_by_path("/net/nemein/downloads/_config/config_default");
        $data = file_get_contents("${prefix}/config/config_default.dat");
        eval("\$component_default = Array ( {$data} );");
        
        $default_config = new midcom_helper_configuration($component_default);
        if (mgd_snippet_exists("/sitegroup-config/net.nemein.downloads/config")) {
            $snippet = mgd_get_snippet_by_path("/sitegroup-config/net.nemein.downloads/config");
            eval("\$local_default = Array ( " . $snippet->code . ");");
            if (!$default_config->store($local_default)) {
                debug_add("Sitegroup configuration is invalid, configuration management failed, aborting", MIDCOM_LOG_CRIT);
                debug_pop();
                return false;
            }
        }
        $GLOBALS["net_nemein_downloads__default_config"] = new midcom_helper_configuration($default_config->get_all());

        // Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $GLOBALS["net_nemein_downloads__default_config"]->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$net_nemein_downloads_layouts = Array (\n{$data}\n);");

        // set active id for nap class
        $net_nemein_downloads_nap_activeid = false;

        return true;
    }


    function properties() {

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $l10n = $i18n->get_l10n("net.nemein.downloads");

        return array(
            MIDCOM_PROP_NAME => $l10n->get("net.nemein.downloads"),
            MIDCOM_PROP_VERSION => "0.1"
        );
    }

} // midcom



class net_nemein_downloads_component {

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
        $this->components[$contextid] = new net_nemein_downloads_viewer
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



class net_nemein_downloads_contentadmin {

    var $configs;
    var $components;


    function net_nemein_downloads_contentadmin() {

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

        $this->components[$contextid] = new net_nemein_downloads_admin
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



class net_nemein_downloads_nap {

    var $napclass;


    function net_nemein_downloads_nap() {

        $this->napclass = new net_nemein_downloads_navigation();
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