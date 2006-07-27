<?php

class de_linkm_collector_midcom {

    function initialize() 
    {
        global $midcom;
        
        $prefix = MIDCOM_ROOT . "/de/linkm/collector";
        require("{$prefix}/viewer.php");
        require("{$prefix}/navigation.php");
        require("{$prefix}/admin.php");
        
        $midcom->load_library("midcom.helper.datamanager");
        
        // Create Default configuration
        debug_add("Loading sitewide configuration", MIDCOM_LOG_DEBUG);
        $data = file_get_contents("${prefix}/config/config_default.dat");
        eval ("\$component_default = Array (\n{$data}\n);");
        $default_config = new midcom_helper_configuration($component_default);
        
        if (mgd_snippet_exists("/sitegroup-config/de.linkm.collector/config")) {
            $snippet = mgd_get_snippet_by_path("/sitegroup-config/de.linkm.collector/config");
            eval ("\$local_default = Array ( " . $snippet->code . ");");
            if (!$default_config->store($local_default)) {
                deubg_add("de.linkm.collector: Sitegroup configuration is invalid, configuration management faild, aborting",MIDCOM_LOG_CRIT);
                return false;
            }
        }
        
        $GLOBALS["de_linkm_collector__default_config"] = new midcom_helper_configuration($default_config->get_all());
        return true;
    }

    function properties()
    {
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $l10n = $i18n->get_l10n("de.linkm.collector");
        
        return array(
            MIDCOM_PROP_NAME => $l10n->get("de.linkm.collector"),
            MIDCOM_PROP_VERSION => 1
        );
    }

}

class de_linkm_collector_component {

    var $configs;
    var $components;

    function de_linkm_collector_component ()
    {
        $this->configs = array();
        $this->components = array();
    }

    function configure($configuration, $contextid) 
    {
        $tmp = $GLOBALS["de_linkm_collector__default_config"];
        if (!$tmp->store($configuration))
            return false;            
        $this->configs[$contextid] = $tmp;
        return true;
    }

    function can_handle($current_object, $argc, $argv, $contextid)
    {
        $this->components[$contextid] = new de_linkm_collector_viewer($current_object, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }

    function handle($current_object, $argc, $argv, $contextid)
    {
        return $this->components[$contextid]->handle($argc, $argv);
    }

    function show_content($contextid)
    {
        $this->components[$contextid]->show();
    }

    function errcode ($contextid)
    {
        return $this->components[$contextid]->errcode;
    }

    function errstr ($contextid)
    {
        return $this->components[$contextid]->errstr;
    }

    function get_metadata ($contextid)
    {
        return $this->components[$contextid]->get_metadata();
    }

}

class de_linkm_collector_contentadmin {

    var $configs;
    var $components;

    function de_linkm_collector_contentadmin ()
    {
        $this->configs = array();
        $this->components = array();
    }

    function configure($configuration, $contextid) 
    {
        $tmp = $GLOBALS["de_linkm_collector__default_config"];
        if (!$tmp->store($configuration))
            return false;            
        $this->configs[$contextid] = $tmp;
        return true;
    }

    function can_handle($current_object, $argc, $argv, $contextid)
    {
        $this->components[$contextid] = new de_linkm_collector_admin($current_object, $this->configs[$contextid]);
        return $this->components[$contextid]->can_handle($argc, $argv);
    }

    function handle($current_object, $argc, $argv, $contextid)
    {
        return $this->components[$contextid]->handle($argc, $argv);
    }

    function show_content($contextid)
    {
        $this->components[$contextid]->show();
    }

    function errcode ($contextid)
    {
        return $this->components[$contextid]->errcode;
    }

    function errstr ($contextid)
    {
        return $this->components[$contextid]->errstr;
    }

    function get_metadata ($contextid)
    {
        return $this->components[$contextid]->get_metadata();
    }

}

class de_linkm_collector_nap {

    var $napclass;

    function de_linkm_collector_nap () 
    {
        $this->napclass = new de_linkm_collector_navigation();
    }

    function is_internal () 
    {
        return $this->napclass->is_internal();
    }

    function set_object ($object) 
    {
        return $this->napclass->set_object($object);
    }

    function get_node () 
    {
        return $this->napclass->get_node();
    }

    function get_current_leaf()
    {
        return $this->napclass->get_current_leaf();
    }

    function get_leaves ()
    {
        return $this->napclass->get_leaves();
    }
}

?>