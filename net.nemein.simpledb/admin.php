<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simpledb Admin interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_admin {

  var $_debug_prefix;

  var $_config;
  var $_topic;
  var $_release;
  var $_view;
  var $_layout;
  var $_schema;
  var $_create_returnuri;
  var $errcode;
  var $errstr;
  var $form_prefix;
  // ...
  var $_l10n;
  var $_l10n_midcom;
  var $_local_toolbar;
  var $_topic_toolbar;
  
  function net_nemein_simpledb_admin($topic, $config) {

    $this->_debug_prefix = "net.nemein.simpledb admin::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_release = false;
    $this->_view = "";
    $this->_layout = false;
    $this->_schema = $this->_config->get("topic_schema");
    $this->_create_returnuri = $this->_config->get("create_returnuri");
       
    $this->errcode = MIDCOM_ERROK;
    $this->errstr = "";
        
    $this->form_prefix = "net_nemein_simpledb_";

    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $this->_l10n = $i18n->get_l10n("net.nemein.simpledb");
    $this->_l10n_midcom = $i18n->get_l10n("midcom");
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
  }


  function handle($argc, $argv) {
  
    global $_REQUEST;

    debug_push($this->_debug_prefix . "handle");

    // Add the topic configuration item
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => 
        (
               ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
            || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
        )
    ));

    // Add the new article link at the beginning
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => 'create.html',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create entry'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => (! $_MIDCOM->auth->can_do('midgard:create', $this->_topic))
    ), 0);
    
    // handle args, parse the url, save data from forms, prepare output

    if ($argc == 0) {
      $this->_view = "welcome";
      debug_add ("viewport = welcome");
      
      if (isset($_REQUEST["net_nemein_simpledb_setschema"])) 
      {
          $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
          $_MIDCOM->auth->require_do('midcom:component_config', $this->_topic);
                    
          $this->_topic->parameter("net.nemein.simpledb","topic_schema",$_REQUEST["net_nemein_simpledb_setschema"]);
          debug_add ("Set schema for topic");
          $this->_schema = $_REQUEST["net_nemein_simpledb_setschema"];
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
    
    $_MIDCOM->auth->require_do('midgard:create', $this->_topic);
  
    if (is_null($id)) {

      $article = new midcom_baseclasses_database_article();
      $article->topic = $this->_topic->id;
      $article->author = $_MIDGARD['user'];
      $article->name = time();
      
      if (! $article->create()) {
        $this->errstr = "Could not create Article: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRFORBIDDEN;
        return false;
      }
      debug_add ("Article {$article->id} created");

      $article->parameter("midcom.helper.datamanager", "layout", $this->_schema);

      if ($this->_create_returnuri) {
        $GLOBALS['midcom']->relocate("{$this->_create_returnuri}{$article->name}.html");
      } else { 
        $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) 
            . "create/{$article->id}.html");
      }
      // Both if branches will exit()
          
    } else {
      if (!$this->_init_view($id)) {
        return false;
      } else {
        $this->_topic_toolbar->disable_item('create.html');
        $this->_topic_toolbar->disable_item('');
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_item("delete/{$id}.html");
        $this->_local_toolbar->disable_view_page();
        switch ($this->_layout->process_form()) {
          case MIDCOM_DATAMGR_EDITING:
            $this->_view = "edit";
            $GLOBALS['midcom_component_data']['net_nemein_simpledb']['active_leaf'] = $id;
            return true;

          case MIDCOM_DATAMGR_SAVED:
            // Index the article 
            $indexer =& $GLOBALS['midcom']->get_service('indexer');
            $indexer->index($this->_layout);
            
            // Invalidate the cache
            $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());
            
            $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) 
                . "view/{$id}.html");
            // This will exit()

          case MIDCOM_DATAMGR_CANCELLED:
            return $this->_init_delete($id);
            // This will relocate (and exit()) to the welcome page on success
                    
          case MIDCOM_DATAMGR_FAILED:
            $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
      }
    }
  }

  function _init_edit($id) {
  
    if (!$this->_init_view($id)) {
      return false;
    }
    
  	$_MIDCOM->auth->require_do('midgard:update', $this->_release);
    
    $this->_topic_toolbar->disable_item('create.html');
    $this->_topic_toolbar->disable_item('');
    $this->_local_toolbar->disable_item("edit/{$id}.html");
    $this->_local_toolbar->disable_item("delete/{$id}.html");
    $this->_local_toolbar->disable_view_page();
    
    $this->_view = "edit";
    $GLOBALS['midcom_component_data']['net_nemein_simpledb']['active_leaf'] = $id;
    return true;
  
  }
  
  function _init_delete($id,$init=true) {
  
    if ($init) {
      if (!$this->_init_view($id)) {
        return false;
      }
      $release = $this->_release;
    } else {
      $release = new midcom_baseclasses_database_article($id);
      if (! $release)
      {
          return false;
      }
    }
  	$_MIDCOM->auth->require_do('midgard:delete', $release);
    
    // Save object guid
    $guid = $release->guid();
    
    $stat = midcom_helper_purge_object($guid, true);

    if ($stat) 
    {
        // Update the Index
    	$indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->delete($guid);
        
        // Invalidate the cache modules
        $GLOBALS['midcom']->cache->invalidate($guid);
        
        // Redirect to welcome page.
        $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        // This will exit()
    }
    return $stat;
  
  }
  
  function _init_view($id) {
    global $net_nemein_simpledb_layouts;

    $release = new midcom_baseclasses_database_article($id);
    if (!$release) 
    {
        debug_add("release $id could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
        $this->errstr = "release $id could not be loaded: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRNOTFOUND;
        return false;
    }
    $this->_release = $release;
    $GLOBALS['midcom_component_data']['net.nemein.simpledb']['active_leaf'] = $id;
    
    /* Add the toolbar items */
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => "edit/{$id}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:update', $this->_release) == false)
    ));
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => "delete/{$id}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:delete', $this->_release) == false)
    ));
    
    $GLOBALS['midcom_component_data']['net_nemein_simpledb']['active_leaf'] = $id;

    $this->_layout = new midcom_helper_datamanager($this->_config->get('schemadb'));
          
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
        $this->_view = "view";
        return true;

      case MIDCOM_DATAMGR_SAVED:
        // Reindex the article 
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->index($this->_layout);
        
      case MIDCOM_DATAMGR_CANCELLED:
        $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "view/{$id}.html");
        // This will exit()
        
                        
      case MIDCOM_DATAMGR_FAILED:
        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
        $this->errcode = MIDCOM_ERRCRIT;
        return false;
    }
  return true;
  }
  
  function show() {

    global $net_nemein_simpledb_layouts;
    global $view_topic;
    $view_topic = $this->_topic;
    
    debug_push($this->_debug_prefix . "show");
    
    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.simpledb");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

    if ($this->_view == "welcome") {
    
      global $title;
      global $view_layouts;
      global $view_schema;

      $view_layouts = array();
      if (is_array($net_nemein_simpledb_layouts)) {
        foreach ($net_nemein_simpledb_layouts as $layout) {
          $view_layouts[$layout["name"]] = $layout["description"];
        }
      }
      
      $view_schema = $this->_schema;

      midcom_show_style("admin-welcome");
    
    } elseif ($this->_view == "view") {

      global $view;
      global $view_id;
      global $view_title;
      global $view_descriptions;

      $view_descriptions = $this->_layout->get_fieldnames();
      $view_title = $GLOBALS["view_l10n"]->get("view entry");
      $view = $this->_layout;
      $view_id = $this->_release->id;

      midcom_show_style("admin-view");
    
    } elseif ($this->_view == "edit") {
    
      global $view;
      global $view_id;
      global $view_title;
      global $view_descriptions;

      $view_descriptions = $this->_layout->get_fieldnames();
      $view_title = $GLOBALS["view_l10n"]->get("entry");
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