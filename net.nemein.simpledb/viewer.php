<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simpledb Viewer interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_viewer {

  var $_debug_prefix;

  var $_config;
  var $_entry;
  var $_topic;
  var $_layout;
  var $_view;
  var $_query;
  var $form_prefix;
  var $_schema;
  var $_user;
  var $_is_owner;
  var $_enable_editing;
  var $_enable_filtering;

  function net_nemein_simpledb_viewer($topic, $config) {

    global $midgard, $_REQUEST;
    global $net_nemein_simpledb_layouts;
    
    $this->_debug_prefix = "net.nemein.simpledb viewer::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_entry = false;
    $this->_layout = new midcom_helper_datamanager($this->_config->get('schemadb'));
    $this->_view = false; 
    $this->form_prefix = "net_nemein_simpledb_viewer_";
    $this->_schema = $this->_config->get("topic_schema");
    $this->_enable_editing = $this->_config->get("enable_onsite_editing");
    $this->_enable_filtering = $this->_config->get("enable_filtering");
    
    $this->_query = false;
    if (isset($_REQUEST[$this->form_prefix."query"])) {
      $this->_query = $_REQUEST[$this->form_prefix."query"];
    }
    
    $this->_is_owner = false;

  }


  function can_handle($argc, $argv) {

    debug_push($this->_debug_prefix . "can_handle");

    $this->_argv = $argv;

    // see if we can handle this request.
    if (!$this->_getRelease($argc, $argv)) {
      // errstr and errcode are already set by getArticle
      debug_add("could not get entry. see above.");
      debug_pop();
      return false;
    }
    $GLOBALS['midcom_component_data']['net_nemein_simpledb']['active_leaf'] = $this->_entry->id;

    debug_pop();
    return true;
  }

  function _getRelease($argc, $argv) {
  
    debug_push($this->_debug_prefix . "_getRelease");

    if (! $this->_layout) {
      $this->errstr = "Could not create layout, see Debug Log";
      $this->errcode = MIDCOM_ERRCRIT;
      debug_add($this->errstr, MIDCOM_LOG_CRIT);
      return false;
    }

    if ($argc == 0) {
      // Show index (search) view
      $this->_view = "index";
      return true;
    
    } elseif ($argc == 1) {

      $qb = midcom_baseclasses_database_article::new_query_builder();
      $qb->add_constraint('topic', '=', $this->_topic->id);
      $qb->add_constraint('name', '=', $argv[0]);
      $result = $qb->execute();
      
      if (count($result) == 1)
      {
      	$this->_entry = $result[0];
      }
      else
      {
        $this->_entry = false;
      }
      
      if (!$this->_entry) {
        debug_add("entry $argv[0] could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
        $this->errstr = "entry $argv[0] could not be loaded: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRNOTFOUND;
        return false;
      }
    }
    
    if ($this->_entry) {
      $this->_view = "entry";
      
      $this->_is_owner = $_MIDCOM->auth->can_do('midgard:update', $this->_entry);
      
      if ($this->_entry && ! $this->_layout->init($this->_entry)) {
        $this->errstr = "Could not initialize layout, see Debug Log";
        $this->errcode = MIDCOM_ERRCRIT;
        debug_add($this->errstr, MIDCOM_LOG_CRIT);
        return false;
      } 
      $GLOBALS['midcom_component_data']['net.nemein.simpledb']['active_leaf'] = $this->_entry->id;
      
      return true;

    } else {

      debug_add("Entry could not be loaded", MIDCOM_LOG_DEBUG);
      debug_pop();
      return false;
    }
  }

  function handle() {
    global $_REQUEST;
    
    debug_push($this->_debug_prefix . "handle");
    
    if ($this->_entry && $this->_is_owner) {
      switch ($this->_layout->process_form()) {

        case MIDCOM_DATAMGR_EDITING:
        case MIDCOM_DATAMGR_SAVED:
        case MIDCOM_DATAMGR_CANCELLED:
          return true;
                
        case MIDCOM_DATAMGR_FAILED:
          $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
          $this->errcode = MIDCOM_ERRCRIT;
          return false;
      }
    }
    
    debug_pop();
    return true;
  }

  function show() {
  
    global $midcom;
    global $view;
    global $view_layout;
    global $view_title;
    global $view_name;
    global $view_columns;
    global $view_columns_count;
    global $view_form_prefix;
    global $view_datamanager;
    global $view_query;
  
    debug_push($this->_debug_prefix . "show");
    
    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.simpledb");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

    
    // Make layout array visible to elements
    if ($this->_schema) {
      $view_layout = $this->_layout->_layoutdb[$this->_schema]["fields"];
    } else {
      $view_layout = $this->_layout->_layoutdb["default"]["fields"];
    }
    $view_columns = array();
    foreach ($view_layout as $key => $field) {
      $viewable = true;
      if (isset($field["hidden"])) {
        $viewable = false;
      }
      if (isset($field["net_nemein_simpledb_list"]) && $field["net_nemein_simpledb_list"] == false) {
        $viewable = false;
      }
      
      if ($viewable) {
        $view_columns[$key] = $field["description"];
      }
    } 
    $view_columns_count = count($view_columns);
    
    $view_form_prefix = $this->form_prefix;
    
    $view_title = $this->_topic->extra;
    
    $view_query = $this->_query;
    
    if ($this->_view == "entry") {
    
      $view = $this->_layout->get_array();
      $view_datamanager = $this->_layout;
      midcom_show_style("view-entry-header");
      if ($this->_enable_editing && $this->_is_owner) {
        midcom_show_style("view-entry-edit");
      } else {
        midcom_show_style("view-entry");
      }
      midcom_show_style("view-entry-footer");
      
    } elseif (!$this->_entry) {
      $_MIDCOM->cache->content->no_cache();

      midcom_show_style("view-header");
      midcom_show_style("view-search-form");
      
      // If search terms are defined
      if ($this->_query) {
        $this->_query = str_replace('*', '%', $this->_query);
        $this->_query = "%{$this->_query}%";
        
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->begin_group('OR');
        $qb->add_constraint('title', 'LIKE', $this->_query);
        $qb->add_constraint('extra1', 'LIKE', $this->_query);
        $qb->add_constraint('extra2', 'LIKE', $this->_query);
        $qb->add_constraint('extra3', 'LIKE', $this->_query);
        $qb->add_constraint('abstract', 'LIKE', $this->_query);
        $qb->add_constraint('content', 'LIKE', $this->_query);
        $qb->end_group();
        $qb->add_order('name');
        $entries = $qb->execute();
        
        $view = $this->_topic;
        $entries_found = 0;
        
        midcom_show_style("view-index-header");
    
        foreach ($entries as $entry)
        {
            $display_entry = true;
            
            $this->_layout->init($entry);
            $view = $this->_layout->get_array();
            
            // Filtering support
            if ($this->_enable_filtering)
            {
                // Regular text filters
                if (   array_key_exists('net_nemein_simpledb_filter', $_REQUEST)
                    && is_array($_REQUEST['net_nemein_simpledb_filter']))
                {
                    foreach ($_REQUEST['net_nemein_simpledb_filter'] as $field => $filter)
                    {
                        // Support for "view all"
                        if ($filter == "**") 
                        {
                            // Support for multiselect fields
                            // Note: this is strict search for now, full value needed
                        }
                        elseif (is_array($view[$field]))
                        {
                            if (!array_key_exists($filter,$view[$field])) 
                            {
                                $display_entry = false;
                            }
                            // Support for regular text fields
                        } 
                        elseif (!stristr($view[$field],$filter)) 
                        {
                            $display_entry = false;
                        }
                    }
                }
                
                // Less than (<) filters
                if (   array_key_exists('net_nemein_simpledb_filter_lessthan', $_REQUEST)
                    && is_array($_REQUEST['net_nemein_simpledb_filter_lessthan']))
                {
                    foreach ($_REQUEST['net_nemein_simpledb_filter_lessthan'] as $field => $filter)
                    {
                        if (   array_key_exists($field, $view)
                            && is_numeric($filter)
                            && is_numeric($view[$field]))
                        {
                            if ($view[$field] > $filter)
                            {
                                $display_entry = false;
                            }
                        }
                    }
                }
                
                // Greater than (>) filters
                if (   array_key_exists('net_nemein_simpledb_filter_greaterthan', $_REQUEST)
                    && is_array($_REQUEST['net_nemein_simpledb_filter_greaterthan']))
                {
                    foreach ($_REQUEST['net_nemein_simpledb_filter_greaterthan'] as $field => $filter)
                    {
                        if (   array_key_exists($field, $view)
                            && is_numeric($filter)
                            && is_numeric($view[$field]))
                        {
                            if ($view[$field] < $filter)
                            {
                                $display_entry = false;
                            }
                        }
                    }
                }                             
            }
            
            if ($display_entry) 
            {
                $view_name = $entry->name.".html";
                $view_datamanager = $this->_layout;
                $entries_found++;
                midcom_show_style("view-index-item");
            }
        }
          
        // Show "no matches found" message if no items are available
        if ($entries_found == 0) {
          midcom_show_style("view-index-nomatch");
        }

        midcom_show_style("view-index-footer");
      } else {
        midcom_show_style("view-index-noentries");
      }
      midcom_show_style("view-footer");
      
      
    }
    
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