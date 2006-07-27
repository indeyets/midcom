<?php

class pl_olga_files_admin {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_view;

    var $_article;
    var $_attachment;
    var $_layout;

    var $errcode;
    var $errstr;


    function pl_olga_files_admin($topic, $config) {

        $this->_debug_prefix = "pl.olga.files admin::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_view = "";

        $this->_article = false;
        $this->_attachment = false;
        $this->_layout = false;

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
    }


    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "prepare");

        if ($argc > 2)
            return false;
        
        if ($argc == 0)
            return true;
        
        switch ($argv[0]) {
            case "view":
            case "edit":
                return true;
            case "delete":
            case "create":
            default:
                return false;
        }
        
    }


    function handle($argc, $argv) {

        debug_push($this->_debug_prefix . "handle");

        if ($argc == 0) {
            $this->_view = "welcome";
            debug_add ("viewport = welcome");
            debug_pop ();
            return true;
        }

        switch ($argv[0]) {

            case "view":
                $result = $this->_init_view($argv[1]);
                break;

            case "edit":
                $result = $this->_init_edit();
                break;

            case "delete":
                $result = $this->_init_delete($argv[1]);
                break;

            case "create":
                $result = $this->_init_create(($argc==2) ? $argv[1] : null);
                break;

            default:
                $result = false;
                break;
        }

        debug_pop();
        return $result;
    }


    function _init_view ($id) {

        global $pl_olga_files_layouts;

        $article = mgd_get_article($id);
        if (!$article) {
            debug_add("Article $id could not be loaded: " . mgd_errstr(),
              MIDCOM_LOG_INFO);
            $this->errstr = "Article $id could not be loaded: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
        }
        $this->_article = $article;
        $GLOBALS["pl_olga_files_nap_activeid"] = $id;

        $this->_layout = new midcom_helper_datamanager($pl_olga_files_layouts);
          
        if (! $this->_layout) {
            $this->errstr = "Could not create layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_CRIT);
            return false;
        }

        if (! $this->_layout->init($this->_article)) {
            $this->errstr = "Could not initialize layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_CRIT);
            return false;
        }
        
        /* Can't tell why this is here, disabling for now (tn)
         * Reason: process_form gets called twice, which is bad. (in _edit or so also)
         * Have to look after this though. (tn)
        switch ($this->_layout->process_form()) {

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                $this->_view = "view";
                return true;
                
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        */
        
        $this->_view = "view";
        
        return true;
    }


    function _init_edit() {

        if (!isset ($_REQUEST)) {
            $this->errstr = "No request data found.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_INFO);
            return false;
        }

        if (array_key_exists ("pl_olga_files_submit", $_REQUEST)) {            
            if (! $this->_topic->parameter("pl.olga.files","root_path",$_REQUEST["pl_olga_files_root_path"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter Root Path: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    return false;
                }
            }
        }
        $this->_view = "welcome";
        return true;
    }


    function _init_create($id = null) {

        if (is_null($id)) {
            $midgard = mgd_get_midgard();

            $article = mgd_get_article();
            $article->topic = $this->_topic->id;
            $article->author = $midgard->user;
            $id = $article->create();
            if (! $id) {
                $this->errstr = "Could not create Article: " . mgd_errstr();
                $this->errcode = MIDCOM_ERRFORBIDDEN;
                return false;
            }
            else
                debug_add("Article $id created");

            $article = mgd_get_article($id);

            if (array_key_exists("pl_olga_files_createlayout", $_REQUEST)) {
                debug_add("creating parameter layout for article");
                $article->parameter("midcom.helper.datamanager", "layout",
                $_REQUEST["pl_olga_files_createlayout"]);
            }

            $protocol = array_key_exists("SSL_PROTOCOL", $_SERVER) ? 
                "https" : "http";
            $location = "Location: $protocol://" . $_SERVER['HTTP_HOST']
              . $midgard->uri . "/$id";
            debug_add("Relocating to $location");
            header($location);
            exit();
        }
        else {
            if (!$this->_init_view($id))
                return false;

            switch ($this->_layout->process_form()) {

                case MIDCOM_DATAMGR_EDITING:
                    $this->_view = "edit";
                    $GLOBALS["pl_olga_files_nap_activeid"] = $id;
                    return true;

                case MIDCOM_DATAMGR_SAVED:
                    $this->_view = "view";
                    $GLOBALS["pl_olga_files_nap_activeid"] = $id;
                    return true;

                case MIDCOM_DATAMGR_CANCELLED:
                    $this->_view = "welcome";
                    $GLOBALS["pl_olga_files_nap_activeid"] = false;
                    return $this->_delete_record($id);
                    
                case MIDCOM_DATAMGR_FAILED:
                    $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
            }
        }    
    }


    function _init_delete ($id) {

        if (!$this->_init_view($id))
            return false;
        
        if (array_key_exists("pl_olga_files_deleteok", $_REQUEST)) {
            return $this->_delete_record($id);
        }
        else 
            if (array_key_exists("pl_olga_files_deletecancel", $_REQUEST)) {
                $GLOBALS["pl_olga_files_nap_activeid"] = $id;
                $this->_view = "view";
            }
            else {
                $GLOBALS["pl_olga_files_nap_activeid"] = $id;
                $this->_view = "deletecheck";
            }
        return true;
    }


    function show() {

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("pl.olga.files");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
  
        eval("\$result = \$this->_show_$this->_view();");
        return $result;
    }


    function _show_welcome() {

        global $midcom;
        global $title;

        global $pl_olga_files_layouts;

        global $view_layouts;

        global $view_config;

        $view_config = $this->_config;

        midcom_show_style("admin_welcome");
    }


    function _show_view() {

        global $midcom;
        global $view;
        global $view_mgr;
        global $view_id;
        global $view_title;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view_title = "Edit Article";
        $view_mgr = $this->_layout;
        $view = $this->_layout->get_array();
        $view_id = $this->_article->id;

        midcom_show_style("admin_view");
    }


    function _show_edit (){

        global $midcom;
        global $view;
        global $view_id;
        global $view_title;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view_title = "Article";
        $view = $this->_layout;
        $view_id = $this->_article->id;

        midcom_show_style("admin_edit");
    }


    function _show_deletecheck() {

        global $midcom;
        global $view;
        global $view_id;
        global $view_title;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view_title = "Article";
        $view = $this->_layout->get_array();
        $view_id = $this->_article->id;

        midcom_show_style("admin_deletecheck");
    }


    function _delete_record($id) {

        $article = mgd_get_article ($id);
        
        if (!mgd_delete_extensions($article)) {
            $this->errstr = "Could not delete Article $id extensions: " 
              . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }

        if (!$article->delete()) {
            $this->errstr = "Could not delete Article $id: ".mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }
        
        $this->_view = "welcome";
        $GLOBALS["pl_olga_files_nap_activeid"] = false;
        return true;
    }



    function get_metadata() {

        if ($this->_article) {
            return array (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        }
        else
            return false;
    }


    function get_current_leaf() {

        if ($this->_article)
            return $this->_article->id;
        else
            return false;
    }

} // admin

?>