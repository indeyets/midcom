<?php

/* Admin interface, called by the "contentadmin" interface class */

class pl_olga_vv_admin {

  var $_debug_prefix;

  var $_config;
  var $_topic;
  var $errcode;
  var $errstr;
  var $form_prefix;
  // ...


  function pl_olga_vv_admin($topic, $config) {

    $this->_debug_prefix = "pl.olga.vv admin::";

    $this->_config = $config;
    $this->_topic = $topic;

    $this->errcode = MIDCOM_ERROK;
    $this->errstr = "";
        
    $this->form_prefix = "pl_olga_vv_";
    // ...
  }


  function can_handle($argc, $argv) {

    debug_push($this->_debug_prefix . "can_handle");

    // see if we can handle this request
    if ($argc == 0) {
      return true;
    }
    if ($argc == 1 && $argv[0] == "create") {
      return true;
    }
    switch ($argv[0]) {
      case "view":
      case "edit":
      case "delete":
      case "create":
        return true;
      default:
        return false;
    }
 
    debug_pop();
    return true;
  }


  function handle($argc, $argv) {

    debug_push($this->_debug_prefix . "handle");

    // handle args, parse the url, save data from forms, prepare output

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

  function _init_create($id = null) {
    if (is_null($id)) {
    
      global $midgard;
    
      return true;
    
    }
  }

  function _init_edit() {
  
    if (array_key_exists($this->form_prefix."submit", $_REQUEST)) {
     if(array_key_exists($this->form_prefix."allow_create_by_uri", $_REQUEST)) $this->_topic->parameter("pl.olga.vv","allow_create_by_uri","true");
     else $this->_topic->parameter("pl.olga.vv","allow_create_by_uri","false");
     if(array_key_exists($this->form_prefix."antispam", $_REQUEST)) $this->_topic->parameter("pl.olga.vv","antispam","true");
     else $this->_topic->parameter("pl.olga.vv","antispam","false");
     $this->_topic->update();
    }
    $this->_view = "welcome";
    $GLOBALS["pl_olga_vv_nap_activeid"] = false;
    return true;
  
  }
  
  function _init_delete($id,$init=true) {
  
    if ($init) {
      if (!$this->_init_view($id)) {
        return false;
      }
      $thread = $this->_thread;
    } else {
      $thread = mgd_get_article($id);
    }
    $replies = mgd_list_reply_articles($thread->id);
    if ($replies) {
      while ($replies->fetch()) {
        $this->_init_delete($replies->id,false);
      }
    }
    $param_domains = $thread->listparameters();
    if ($param_domains) {
      while ($param_domains->fetch()) {
        $params = $thread->listparameters($param_domains->domain);
        if ($params) {
          while ($params->fetch()) {
            $thread->parameter($params->domain,$params->value,"");
          }
        }
      }
    }
    $stat = $thread->delete();
    if ($stat) {
      $this->_view = "welcome";
    }
    return $stat;
  
  }
  
  function _init_view($id) {
    global $pl_olga_vv_layouts;

    $thread = mgd_get_article($id);
    if (!$thread) {
      debug_add("Thread $id could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
      $this->errstr = "Thread $id could not be loaded: " . mgd_errstr();
      $this->errcode = MIDCOM_ERRNOTFOUND;
      return false;
    }
    $this->_thread = $thread;
    $GLOBALS["pl_olga_vv_nap_activeid"] = false;

   return true;
  }
  
  function show() {

    global $pl_olga_vv_layouts;
    global $midcom;
    
    debug_push($this->_debug_prefix . "show");

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("pl.olga.vv");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

    global $view;

    $view["allow_create_by_uri"] = $this->_topic->parameter("pl.olga.vv","allow_create_by_uri");
    $view["antispam"] = $this->_topic->parameter("pl.olga.vv","antispam");

    midcom_show_style("admin-welcome");
    debug_pop();
    return true;
  }


  function get_metadata() {
      return false;
  }


  function get_current_leaf() {

    // return id of current leaf, e.g. article id
    // return id of current leaf, e.g. article id
      return false;
  }

} // admin

?>