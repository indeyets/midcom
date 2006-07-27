<?php

/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickpoll AIS interface class.
 * 
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_admin {

  var $_debug_prefix;

  var $_config;
  var $_topic;
  var $_quickpoll;
  var $_options;
  var $_votes;
  var $_total_votes;
  var $_view;
  var $errcode;
  var $errstr;
  var $form_prefix;
  var $_config_dm;
  var $_l10n;
  var $_l10n_midcom;
  
  var $_local_toolbar;
  var $_topic_toolbar;

  function net_nemein_quickpoll_admin($topic, $config) 
  {

    $this->_debug_prefix = "net.nemein.quickpoll admin::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_view = "";
    $this->_quickpoll = false;
    $this->_options = array();
    $this->_votes = array();
    $this->_total_votes;
    $this->_config_dm = null;
       
    $this->errcode = MIDCOM_ERROK;
    $this->errstr = "";

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $this->_l10n = $i18n->get_l10n("net.nemein.quickpoll");
    $this->_l10n_midcom = $i18n->get_l10n("midcom");
    $GLOBALS["view_l10n"] = $this->_l10n;
    $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
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

    // handle args, parse the url, save data from forms, prepare output

    /* Add the topic configuration item */
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
        
    /* Add the new article link at the beginning*/
    $this->_topic_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => 'create.html',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('create'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ), 0);
    
    if ($argc == 0) {
      $this->_view = "welcome";
      debug_add ("viewport = welcome");

      // Provide configuration form
      $this->_init_config();
            
      debug_pop ();
      return true;
    }

    switch ($argv[0]) {

      case "view":
          $id = $argv[1];
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
          $result = $this->_init_view($id);
          break;

      case "edit":
	      $id = $argv[1];
	      $this->_local_toolbar->add_item(Array(
	          MIDCOM_TOOLBAR_URL => "view/{$id}.html",
	          MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('view'),
	          MIDCOM_TOOLBAR_HELPTEXT => null,
	          MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
	          MIDCOM_TOOLBAR_ENABLED => true
	      ));
	      $this->_local_toolbar->add_item(Array(
	          MIDCOM_TOOLBAR_URL => "delete/{$id}.html",
	          MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
	          MIDCOM_TOOLBAR_HELPTEXT => null,
	          MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
	          MIDCOM_TOOLBAR_ENABLED => true
	      ));
          $result = $this->_init_edit($id);
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

  function _init_config() {

    $schemadbs = $this->_config->get("schemadbs");
    $GLOBALS["view_schemadbs"] = array_merge(Array("" => $GLOBALS["view_l10n_midcom"]->get("default setting")), $schemadbs);
    $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/quickpoll/config/schemadb_config.inc");
        
    if ($this->_config_dm == false) {
      debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
      $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Failed to instantinate configuration datamanager.");
    }

    if (! $this->_config_dm->init($this->_topic)) {
      debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
      debug_print_r("Topic object we tried was:", $this->_topic);
      $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to initialize configuration datamanager.");
    }


    switch ($this->_config_dm->process_form()) {
      case MIDCOM_DATAMGR_SAVED:
        debug_add("Invalidating MidCOM cache", MIDCOM_LOG_DEBUG);
        break;
                
      case MIDCOM_DATAMGR_EDITING:
      case MIDCOM_DATAMGR_CANCELLED:
        // We stay here whatever happens here, at least as long as
        // there is no fatal error.
        break;

      case MIDCOM_DATAMGR_FAILED:
        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
        $this->errcode = MIDCOM_ERRCRIT;
        debug_pop();
        return false;
    }

  }

  function _init_create($id = null) {
    if (is_null($id)) {
    
      global $midgard;
    
      $release = mgd_get_article();
      $release->topic = $this->_topic->id;
      $release->author = $midgard->user;
      
      $stat = $release->create();
      if (!$stat) {
        $this->errstr = "Could not create Article: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRFORBIDDEN;
        return false;
      } else {
        // Invalidate the cache
        $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());

        debug_add("Article $stat created");
        $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $GLOBALS["midcom"]->relocate($prefix."edit/".$stat);
      }
    
    }
  }

  function _init_edit($id) {
  
    if (!$this->_init_view($id)) {
      return false;
    }
    
    if (isset($_REQUEST["net_nemein_quickpoll_submit"])) {
    
      if (isset($_REQUEST["net_nemein_quickpoll_field"]) && is_array($_REQUEST["net_nemein_quickpoll_field"])) {

        foreach ($_REQUEST["net_nemein_quickpoll_field"] as $field => $value) {
        
          switch ($field) {
          
            case "subject":
              // Update name and title
              $this->_quickpoll->title = $value;
              $this->_quickpoll->name = midcom_generate_urlname_from_string($value);
              break;

            case "option":
              // Update options
              if (is_array($value)) {
                foreach ($value as $key => $option) {
                  // Get votes from original
                  $votes = $this->_quickpoll->parameter("net.nemein.quickpoll.option",$this->_options[$key]);
                  // Delete the old option
                  $this->_quickpoll->parameter("net.nemein.quickpoll.option",$this->_options[$key],'');
                  // Create the new option with old votes
                  $this->_quickpoll->parameter("net.nemein.quickpoll.option",$option,$votes);
                }
              }
              break;

            case "new_option":
              // Add new option
              if ($value != "") {
                $this->_quickpoll->parameter("net.nemein.quickpoll.option",$value,0);
                break;
              }
          }
        
        }
        
        $stat = $this->_quickpoll->update();
        if ($stat) {
          $this->_init_view($id);
        } else {
          debug_add("Failed to update quickpoll article, reason " . mgd_errstr());
        }
        
      }
    }
    
    $this->_view = "edit";
    $GLOBALS['midcom_component_data']['net.nemein.quickpoll']['active_leaf'] = $id;
    return true;
  
  }
  
  function _init_delete($id) {
  
    $this->_init_view($id);

    $guid = $this->_quickpoll->guid();
    $stat = midcom_helper_purge_object($guid, true);
    
    // Invalidate the cache modules
    $GLOBALS['midcom']->cache->invalidate($guid);

    if ($stat) {
      $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
      $GLOBALS["midcom"]->relocate($prefix);
      // This will exit()
    } 
    $this->errstr = "Failed to delete quickpoll page article, reason ".mgd_errstr();
    return $stat;
  }
  
  function _init_view($id) {
    global $net_nemein_quickpoll_layouts;

    $quickpoll = mgd_get_article($id);
    if (!$quickpoll) {
      debug_add("quickpoll $id could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
      $this->errstr = "quickpoll $id could not be loaded: " . mgd_errstr();
      $this->errcode = MIDCOM_ERRNOTFOUND;
      return false;
    }
    $this->_quickpoll = $quickpoll;
        
    $options = $this->_quickpoll->listparameters("net.nemein.quickpoll.option");
    $options_shown = 0;
    if ($options) {
      while ($options->fetch()) {
        $options_shown++;
        $this->_options[$options_shown] = $options->name;
        $this->_votes[$options_shown] = $this->_quickpoll->parameter("net.nemein.quickpoll.option",$options->name);
        $this->_total_votes = $this->_total_votes + $this->_votes[$options_shown];

      }
    }
    
    $this->_view = "view";
    $GLOBALS['midcom_component_data']['net.nemein.quickpoll']['active_leaf'] = $id;

    return true;
  }
  
  function show() {

    global $net_nemein_quickpoll_layouts;
    global $midcom;
    global $view_topic;
    $view_topic = $this->_topic;
    $GLOBALS["view_l10n"] = $this->_l10n;
    
    debug_push($this->_debug_prefix . "show");

    if ($this->_view == "welcome") {
    
      global $title;
      global $view_config;

      $view_config = $this->_config_dm;

      midcom_show_style("admin-welcome");
    
    } elseif ($this->_view == "view") {

      global $view;
      global $view_id;
      global $view_title;
      global $view_options;
      global $view_votes;
      global $view_total_votes;

      $view_title = $GLOBALS["view_l10n"]->get("view quickpoll"). ": {$this->_quickpoll->title}";
      $view = $this->_quickpoll;
      $view_id = $this->_quickpoll->id;
      $view_options = $this->_options;
      $view_votes = $this->_votes;
      $view_total_votes = $this->_total_votes;

      midcom_show_style("admin-view");
    
    } elseif ($this->_view == "edit") {
    
      global $view;
      global $view_id;
      global $view_title;
      global $view_options;

      $view_title = $GLOBALS["view_l10n"]->get("edit quickpoll");
      $view = $this->_quickpoll;
      $view_id = $this->_quickpoll->id;
      $view_options = $this->_options;

      midcom_show_style("admin-edit");
    
    }
    
    debug_pop();
    return true;
  }


  function get_metadata() {

    if ($this->_quickpoll) {
      return array (
        MIDCOM_META_CREATOR => $this->_quickpoll->creator,
        MIDCOM_META_EDITOR  => $this->_quickpoll->revisor,
        MIDCOM_META_CREATED => $this->_quickpoll->created,
        MIDCOM_META_EDITED  => $this->_quickpoll->revised
      );
    } else {
      return false;
    }
  }

} // admin

?>