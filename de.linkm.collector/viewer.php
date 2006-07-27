<?php

class de_linkm_collector_viewer {

    var $_topic;
    var $_config;
    var $_categories;
    var $_l10n;
    var $_datamanager;
    var $_l10nmidcom;
    
    var $errcode;
    var $errstr;

    function de_linkm_collector_viewer ($topic, $config) {
        $this->_topic = $topic;
        $this->_config = $GLOBALS['de_linkm_collector__default_config'];
        $this->_config->store($config->get_all());
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n =& $i18n->get_l10n("de.linkm.collector");
        $this->_l10nmidcom =& $i18n->get_l10n("midcom");
    }
    
    function can_handle ($argc, $argv) {
        if ($argc == 0)
            return true;
        
        return false;
    }

    function handle ($argc, $argv) {
        
        $this->_load_categories();
        
        $this->_datamanager = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        if ($this->_datamanager == false) {
            $this->errstr = "Could not create layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOGCRIT);
            return false;
        }
        if ($this->_datamanager->init($this->_topic) == false) {
            $this->errstr = "Could not create layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOGCRIT);
            return false;
        }
        
        $GLOBALS['midcom']->set_pagetitle($this->_topic->extra);
        
        return true;
        
    }

    function show () {
        $GLOBALS["view_cat"] = $this->_categories;
        $GLOBALS["view_l10n"] =& $this->_l10n;
        $GLOBALS["view_l10nmidcom"] =& $this->_l10nmidcom;
        $GLOBALS["view"] = $this->_datamanager->get_array();
        midcom_show_style("index");
    }

    function get_metadata() {
        return Array(
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITOR => 0,
            MIDCOM_META_EDITED => 0
        );
    }

    function _load_categories() {
        $this->_categories = Array();
        
        $contextid = $GLOBALS["midcom"]->get_current_context();
        $nap = new midcom_helper_nav($contextid);
        $subnodeids = $nap->list_nodes($this->_topic->id);
        
        if (count($subnodeids)>0)
            foreach ($subnodeids as $id) {
                $topic = mgd_get_topic($id);
                if ($topic->parameter("de.linkm.collector","id") != $this->_config->get("id"))
                    continue;
                
                $node = $nap->get_node($id);
                $this->_categories[$id] = Array(
                    "name" => $node[MIDCOM_NAV_NAME],
                    "url" => substr($node[MIDCOM_NAV_URL],0,-1)
                );
            }
        
    }

}



?>