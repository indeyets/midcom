<?php

class de_linkm_collector_admin {

    var $_topic;
    var $_config;
    var $_categories;
    var $_parameters;
    var $_modeessage;

    var $errcode;
    var $errstr;

    var $_mode;
    var $_l10n;
    var $_l10n_midcom;
    var $_datamanager;
    var $_local_toolbar;
    var $_topic_toolbar;

    function de_linkm_collector_admin ($topic, $config) {
        $this->_config = $GLOBALS['de_linkm_collector__default_config'];
        $this->_config->store($config->get_all());
        $this->_topic = $topic;

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_message = "";

        $this->_categories = Array();

        $this->_mode = "index";
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n =& $i18n->get_l10n("de.linkm.collector");
        $this->_l10n_midcom =& $i18n->get_l10n("midcom");
        $this->_datamanager = null;

        if (trim($this->_config->get("parameters")) != "") {
            $snippet = mgd_get_snippet_by_path ($this->_config->get("parameters"));
            if (!$snippet)
                die ("CRITICAL: Could not load Snippet " . $this->_config->get("parameters"));

            eval ("\$params = Array ( $snippet->code \n );");
            $this->_parameters = $params;
        } else {
            $this->_parameters = Array();
        }
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
    }

    function get_metadata() {
        return Array(
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITOR => 0,
            MIDCOM_META_EDITED => 0
        );
    }

    function can_handle ($argc, $argv) {
        if ($argc == 0)
            return true;

        return false;
    }

    function handle ($argc, $argv) {
        debug_push("de.linkm.collector::handle");

        /* Add the topic configuration item */
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

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
        $this->_datamanager->process_form();

        $result = true;

        if (array_key_exists("form_create_submit", $_REQUEST))
            $result = $this->_create_topic($_REQUEST["form_url"],$_REQUEST["form_name"]);

        if (array_key_exists("form_move_submit", $_REQUEST))
            $result = $this->_move_element($_REQUEST["form_id"], $_REQUEST["form_dest"]);

        $this->_load_categories();

        debug_pop();
        return $result;
    }

    function show () {
        $GLOBALS["view_l10n"] =& $this->_l10n;
        $GLOBALS["view_l10n_midcom"] =& $this->_l10n_midcom;
        $GLOBALS["view_config"] =& $this->_config;
        $GLOBALS["view_categories"] =& $this->_categories;
        $GLOBALS["view_name"] = $this->_topic->extra;
        $GLOBALS["view_datamanager"] =& $this->_datamanager;
        $GLOBALS["view_message"] =& $this->_message;

        switch ($this->_mode) {
            case "index":
                $this->_show_index();
                break;
            default:
                die ("We should not reach this line. Collector mode was " . $this->_mode . ", which is not supported.");
                break;
        }
    }

    function _show_index() {
        midcom_show_style("admin-index");
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

    function _create_topic ($url, $desc) {

        $topic = mgd_get_topic();
        $topic->up = $this->_topic->id;
        $topic->owner = $this->_topic->owner;
        $topic->name = $url;
        $topic->extra = $desc;

        if (!$topic->create()) {
            $this->errcode = MIDCOM_ERR_INTERNAL;
            $this->errstr = $this->_l10n->get("could not create category") . ": " . mgd_errstr();
            return false;
        }

        $topic->parameter("de.linkm.collector","id",$this->_config->get("id"));
        $topic->parameter("midcom","component",$this->_config->get("component"));
        $topic->parameter("midcom","style",$this->_config->get("style"));

        foreach ($this->_parameters as $key => $value)
            $topic->parameter($this->_config->get("component"), $key, $value);

        return true;
    }

    function _move_element ($id, $dest) {
        $article = mgd_get_article ($id);

        if (!$article) {
            $this->_message = $this->_l10n->get("move source element not found") . ": " . mgd_errstr();
            return true;
        }

        $topic = mgd_get_topic($article->topic);
        if (!$topic) {
            die("de_linkm_collector_admin::_move_element: Could not load article $id's Topic. This is definitly weird: "
              . mgd_errstr());
        }

        if ($topic->parameter("de.linkm.collector","id") != $this->_config->get("id")) {
            $this->_message = $this->_l10n->get("move source element in unkown category");
            return true;
        }

        $topic = mgd_get_topic($dest);
        if (!$topic) {
            die("de_linkm_collector_admin::_move_element: Could not load destination Topic $dest. This is definitly weird: "
              . mgd_errstr());
        }

        if ($topic->parameter("de.linkm.collector","id") != $this->_config->get("id")) {
            die("de_linkm_collector_admin::_move_element: Destination topic resembels no known category. This is definitly weird.");
            return true;
        }

        if (!mgd_move_article ($id, $dest)) {
            $this->_message = $this->_l10n->get("move failed") . ": " . mgd_errstr();
            return true;
        }

        $this->_message = $this->_l10n->get("move successful");

        return true;
    }
}

?>