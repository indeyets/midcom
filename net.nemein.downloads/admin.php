<?php

/* Admin interface, called by the "contentadmin" interface class */

class net_nemein_downloads_admin {

  var $_debug_prefix;

  var $_config;
  var $_topic;
  var $_release;
  var $_view;
  var $_layout;
  var $_current_release;
  var $_l10n;
  var $errcode;
  var $errstr;
  var $form_prefix;
  var $_l10n_midcom;
  var $_local_toolbar;
  var $_topic_toolbar;


  function net_nemein_downloads_admin($topic, $config) {

    $this->_debug_prefix = "net.nemein.downloads admin::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_release = false;
    $this->_view = "";
    $this->_layout = false;
    $this->_current_release = $this->_config->get("current_release");
       
    $this->errcode = MIDCOM_ERROK;
    $this->errstr = "";
        
    $this->form_prefix = "net_nemein_downloads_";

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $this->_l10n = $i18n->get_l10n("net.nemein.downloads");
    $this->_l10n_midcom = $i18n->get_l10n("midcom");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
    $toolbars =& midcom_helper_toolbars::get_instance();
    $this->_topic_toolbar =& $toolbars->top;
    $this->_local_toolbar =& $toolbars->bottom;        
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
  
    global $_REQUEST;

    debug_push($this->_debug_prefix . "handle");

    // handle args, parse the url, save data from forms, prepare output
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('set current release'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    
    /* Add the new article link at the beginning*/
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create new release'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ), 0);

    if ($argc == 0) {
      $this->_view = "welcome";
      debug_add ("viewport = welcome");
      
      if (isset($_REQUEST["net_nemein_downloads_setcurrentrelease"])) {
        $this->_topic->parameter("net.nemein.downloads","current_release",$_REQUEST["net_nemein_downloads_setcurrentrelease"]);
        debug_add ("Set current release");
        $this->_current_release = $_REQUEST["net_nemein_downloads_setcurrentrelease"];
      }
      
      debug_pop ();
      return true;
    }

    switch ($argv[0]) {

      case "view":
        $result = $this->_init_view($argv[1]);
        break;

      case "edit":
        $result = $this->_init_edit($argv[1]);
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
    
      $release = mgd_get_article();
      $release->topic = $this->_topic->id;
      $release->up = 0;
      $release->author = $midgard->user;
      
      $stat = $release->create();
      if (!$stat) {
        $this->errstr = "Could not create Article: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRFORBIDDEN;
        return false;
      } else {
        debug_add("Event $stat created");
        
        // Invalidate the cache
        $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());
        
        $this->_init_view($stat);
        return true;
      }
    
    }
  }

  function _init_edit($id) {
  
    if (!$this->_init_view($id)) {
      return false;
    }
    $this->_view = "edit";
    $GLOBALS["net_nemein_downloads_nap_activeid"] = $id;
    return true;
  
  }
  
  function _init_delete($id) {
  
    debug_add("Delete release $id called");
    if (!$this->_init_view($id)) {
      return false;
    }
    $guid = $this->_release->guid();
    
    $stat = midcom_helper_purge_object($guid);
    
    if ($stat) {
        // Update the index
	    $indexer =& $GLOBALS['midcom']->get_service('indexer');
	    $indexer->delete($guid);
	    
	    // Invalidate the cache modules
	    $GLOBALS['midcom']->cache->invalidate($guid);
	    
        $this->_view = "welcome";
    } else {
      debug_add ("Failed to delete release, reason ".mgd_errstr());
    }
    return $stat;
  
  }
  
  function _init_view($id) {
    global $net_nemein_downloads_layouts;
    
    /* Add the toolbar items */
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => "view/{$id}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('view'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => "edit/{$id}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => "delete/{$id}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));

    $release = mgd_get_article($id);
    if (!$release) {
      debug_add("release $id could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
      $this->errstr = "release $id could not be loaded: " . mgd_errstr();
      $this->errcode = MIDCOM_ERRNOTFOUND;
      return false;
    }
    $this->_release = $release;
    $GLOBALS["net_nemein_downloads_nap_activeid"] = $id;

    $this->_layout = new midcom_helper_datamanager($net_nemein_downloads_layouts);
          
    if (! $this->_layout) {
      $this->errstr = "Could not create layout, see Debug Log";
      $this->errcode = MIDCOM_ERRCRIT;
      debug_add($this->errstr, MIDCOM_LOG_CRIT);
      return false;
    }

    if (! $this->_layout->init($this->_release)) {
      $this->errstr = "Could not initialize layout, see Debug Log";
      $this->errcode = MIDCOM_ERRCRIT;
      debug_add($this->errstr, MIDCOM_LOG_CRIT);
      return false;
    }

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
  return true;
  }
  
  function show() {

    global $net_nemein_downloads_layouts;
    global $midcom;
    global $view_topic;
    $view_topic = $this->_topic;
    $GLOBALS["view_l10n"] = $this->_l10n;
    
    debug_push($this->_debug_prefix . "show");

    if ($this->_view == "welcome") {
    
      global $title;
      global $view_layouts;
      global $view_releases;
      global $view_current_release;

      $view_layouts = array();
      if (is_array($net_nemein_downloads_layouts)) {
        foreach ($net_nemein_downloads_layouts as $layout) {
          $view_layouts[$layout["name"]] = $layout["description"];
        }
      }
      
      $view_releases = array();
      $releases = mgd_list_topic_articles($this->_topic->id,"reverse created");
      if ($releases) {
        while ($releases->fetch()) {
          $view_releases[$releases->guid()] = $releases->title;
        }
      }
      
      $view_current_release = $this->_current_release;
      midcom_show_style("admin-welcome");
    
    } elseif ($this->_view == "view") {

      global $view;
      global $view_id;
      global $view_title;
      global $view_descriptions;

      $view_descriptions = $this->_layout->get_fieldnames();
      $view_title = $GLOBALS["view_l10n"]->get("view release");
      $view = $this->_layout;
      $view_id = $this->_release->id;

      midcom_show_style("admin-view");
    
    } elseif ($this->_view == "edit") {
    
      global $view;
      global $view_id;
      global $view_title;
      global $view_descriptions;

      $view_descriptions = $this->_layout->get_fieldnames();
      $view_title = $GLOBALS["view_l10n"]->get("edit release");
      $view = $this->_layout;
      $view_id = $this->_release->id;

      midcom_show_style("admin-edit");
    
    }
    
    debug_pop();
    return true;
  }


  function get_metadata() {

    if ($this->_release) {
      return array (
        MIDCOM_META_CREATOR => $this->_release->creator,
        MIDCOM_META_EDITOR  => $this->_release->revisor,
        MIDCOM_META_CREATED => $this->_release->created,
        MIDCOM_META_EDITED  => $this->_release->revised
      );
    } else {
      return false;
    }
  }

} // admin

?>