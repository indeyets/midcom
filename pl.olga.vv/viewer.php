<?php

/* Processing and output, called by the "component" interface class */

class pl_olga_vv_viewer {

  var $_debug_prefix;

  var $_config;
  var $_phantom;
  var $_topic;
  var $_layout;
  var $_create_by_uri;
  var $_allow_create_by_uri;
  var $_user;
  var $_is_owner;
  var $_argv;
  var $_view;
  var $do_vote;
  var $_antispam;
  var $form_prefix;
 

  function pl_olga_vv_viewer($topic, $config) {

    global $midgard;
    
    $this->_debug_prefix = "pl.olga.vv viewer::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_phantom = false;
    $this->_layout = false;
    $this->_allow_create_by_uri = $this->_config->get("allow_create_by_uri");
    $this->_antispam = $this->_config->get("antispam");
    $this->_create_by_uri = false;
    $this->_view = false;
    $this->_is_owner = false;
    $this->do_vote=false;
    $this->_user = $midgard->user;
    if ($this->_user) {
      if (mgd_is_topic_owner($this->_topic->id)) {
        $this->_is_owner = true;
      }
    }
    
    $this->form_prefix = "pl_olga_vv_viewer_";

  }


  function can_handle($argc, $argv) {

    debug_push($this->_debug_prefix . "can_handle");

    $this->_argv = $argv;

    // see if we can handle this request.
    if (!$this->_getPhantom($argc, $argv)) {
      // errstr and errcode are already set by getArticle
      debug_add("could not locate phantom article. see above.");
      debug_pop();
      return false;
    }
    $GLOBALS["pl_olga_vv_nap_activeid"] = $this->_thread->id;

    debug_pop();
    return true;
  }

  function _getPhantom($argc, $argv) {
  
    global $pl_olga_vv_layouts;

    debug_push($this->_debug_prefix . "_getPhantom");

    if ($argc == 0) {
      // Ooops
      return false;
    }

    if ($argc == 1) {

      $this->_phantom = mgd_get_article_by_name($this->_topic->id,$argv[0]);
      if (!$this->_phantom) {
      
        // Allow access non-existing URIs if accepted by config
        if ($this->_allow_create_by_uri) {
          debug_add("Allowing autocreation of phantom", MIDCOM_LOG_INFO);
          return true;
          
        } else {
        
          debug_add("Phantom $argv[0] could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
          $this->errstr = "Phantom $argv[0] could not be loaded: " . mgd_errstr();
          $this->errcode = MIDCOM_ERRNOTFOUND;
          return false;
          
        }
      }
      
      return true;

    } else {

      debug_add("too many parameters", MIDCOM_LOG_DEBUG);
      debug_pop();
      return false;
    }
  }

  function handle() {
    global $_REQUEST,$_SERVER;
    
    debug_push($this->_debug_prefix . "handle");

   if (!$this->_phantom) {
// Create missing phantom article by URI if requested
    if ($this->_allow_create_by_uri && $this->_is_owner) {

     $article = mgd_get_article();
     $article->author = $this->_user;
          
// Usually we refer here with resource GUID
     $target_object = mgd_get_object_by_guid($this->_argv[0]);
     if ($target_object) {
      if (isset($target_object->title)) {
       $article->title = $target_object->title;
      }
      if (isset($target_object->author)) {
       $article->author = $target_object->author;
      } else {
       $article->author = $target_object->creator;
      }
     }
        
     $article->name =$this->_argv[0];
     $article->topic = $this->_topic->id;
     $stat = $article->create();
     if ($stat) {
      $this->_phantom = mgd_get_article($stat);
     debug_add("Phantom $this->_argv[0] was created: " . mgd_errstr(), MIDCOM_LOG_INFO);
     } else {
      debug_add("Phantom $this->_argv[0] could not be created: " . mgd_errstr(), MIDCOM_LOG_INFO);
      return false;
     }
    } else {
// Phantom are not allowed to be autocreated
     return false;
    }
   }

// If we got Anti-Spam set check who voted lately

   if($this->_antispam){
    $last_vote=$this->_phantom->extra3;
    if($last_vote==$_SERVER["REMOTE_ADDR"]){
     $this->do_vote=false;
    }else{
     $this->do_vote=true;
    }
   }else{
    $this->do_vote=true;
   }

// Vote form sent
    if (array_key_exists($this->form_prefix."vote_submit", $_REQUEST)) {
// Save only votes with values and do not increase view counter
     if ($_REQUEST[$this->form_prefix."vote_value"]) {
      debug_add("Trying to save a vote", MIDCOM_LOG_DEBUG);
     
      if($this->do_vote){
       $this->_phantom->extra1=$this->_phantom->extra1+$_REQUEST[$this->form_prefix."vote_value"];
       $this->_phantom->extra2++;
       if($this->_antispam) $this->_phantom->extra3=$_SERVER["REMOTE_ADDR"];
       $this->_phantom->update();
       $this->do_vote=false;
      }
      return true;
     }
    }
    $this->_phantom->view++;
    $this->_phantom->update();
    debug_pop();
    return true;
  }
 
  function show() {
  
    global $midcom;
    global $view;
    global $view_form_prefix;
    
    $view_form_prefix = $this->form_prefix;
  
    debug_push($this->_debug_prefix . "show");

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("pl.olga.vv");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
    
    $view["views"] = $this->_phantom->view*1;
    $view["vote"] = $this->_phantom->extra1*1;
    $view["votes"] = $this->_phantom->extra2*1;
    $view["result"] = ($view["votes"]>0)?round($view["vote"]/$view["votes"],2):0;

    midcom_show_style("view-result");
    if($this->do_vote) midcom_show_style("vote-widget");
    debug_pop();
    return true;
  }


  function get_metadata() {

        // metadata for the current element
 /*
        return array (
            MIDCOM_META_CREATOR => ...,
            MIDCOM_META_EDITOR => ...,
            MIDCOM_META_CREATED => ...,
            MIDCOM_META_EDITED => ...
        );*/
  }

} // viewer

?>