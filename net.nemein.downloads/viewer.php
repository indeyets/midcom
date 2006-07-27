<?php

/* Processing and output, called by the "component" interface class */

class net_nemein_downloads_viewer {

  var $_debug_prefix;

  var $_config;
  var $_release;
  var $_current_release;
  var $_topic;
  var $_layout;
  var $_view;
  var $_relocate;
  var $_relocate_prefix;

  function net_nemein_downloads_viewer($topic, $config) {

    global $midgard;
    
    $this->_debug_prefix = "net.nemein.downloads viewer::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_release = false;
    $this->_current_release = $this->_config->get("current_release");
    $this->_layout = false;
    $this->_view = false; 
    $this->_relocate = false;
    $this->_relocate_prefix = "";
    $this->form_prefix = "net_nemein_downloads_viewer_";

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.downloads");

  }


  function can_handle($argc, $argv) {

    debug_push($this->_debug_prefix . "can_handle");

    // see if we can handle this request.
    if (!$this->_getRelease($argc, $argv)) {
      // errstr and errcode are already set by getArticle
      debug_add("could not get release. see above.");
      debug_pop();
      return false;
    }
    $GLOBALS["net_nemein_downloads_nap_activeid"] = $this->_release->id;

    debug_pop();
    return true;
  }

  function _getRelease($argc, $argv) {
  
    global $net_nemein_downloads_layouts;

    debug_push($this->_debug_prefix . "_getRelease");

    $this->_layout = new midcom_helper_datamanager($net_nemein_downloads_layouts);

    if (! $this->_layout) {
      $this->errstr = "Could not create layout, see Debug Log";
      $this->errcode = MIDCOM_ERRCRIT;
      debug_add($this->errstr, MIDCOM_LOG_CRIT);
      return false;
    }

    if ($argc == 0) {
      // Try to view current release
      if ($this->_current_release) {
        $this->_release = mgd_get_object_by_guid($this->_current_release);

        if (!$this->_release) {
        
          debug_add("current release could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
          $this->errstr = "current release could not be loaded: " . mgd_errstr();
          $this->errcode = MIDCOM_ERRNOTFOUND;
          return false;
          
        }
      } else {
      
        // No release is the default, show index
        $this->_view = "index";
        return true;
        
      }
    
    } elseif ($argc == 1) {
      if ($argv[0] == 'archive')
      {
          $this->_release =  false;
          $this->_view = 'index';
          return true;
      }
      else
      {
          $this->_release = mgd_get_article_by_name($this->_topic->id,$argv[0]);
          if (!$this->_release) {
            
            debug_add("release $argv[0] could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
            $this->errstr = "release $argv[0] could not be loaded: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
              
          }
      }
      
    } elseif ($argc == 2) {

      // Redirections for current release
      if ($argv[0] == "latest") {

        if ($this->_current_release) {
          // Use set "current release"
          $this->_release = mgd_get_object_by_guid($this->_current_release);
        } else {
          // Use latest added release
          $releases = mgd_list_topic_articles($this->_topic->id);
          if ($releases) {
            if ($releases->fetch()) {
              $this->_release = mgd_get_article($releases->id);
            }
          }
        }

        if (!$this->_release) {        
          debug_add("current release could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
          $this->errstr = "current release could not be loaded: " . mgd_errstr();
          $this->errcode = MIDCOM_ERRNOTFOUND;
          return false;          
        }

        // Load needed prefixes for relocations
        $host = mgd_get_host($GLOBALS["midgard"]->host);
        $host_prefix = $host->prefix."/";

        switch ($argv[1]) {

          case "web":
            // Relocate to release's web page
            $this->_relocate = $this->_release->name.".html";
            break;

          case "file":
            // Relocate to first file in release
            $files = $this->_release->listattachments();
            if ($files) {
              if ($files->fetch()) {
                $this->_relocate = "midcom-serveattachmentguid-".$files->guid()."/".$files->name;   
                $this->_relocate_prefix = $host_prefix;
              }
            }
            break;

          default:
            // Relocate to first file in release
            $files = $this->_release->listattachments();
            if ($files) {
              while ($files->fetch()) {
                if ($files->name == $argv[1]) {
                  $this->_relocate = $host_prefix."midcom-serveattachmentguid-".$files->guid()."/".$files->name;   
                  $this->_relocate_prefix = $host_prefix;
                }
              }
            }
            break;
  
        }

        if (!$this->_relocate) {
          return false;
        }
      }

    }
    
    if ($this->_release) {
      
      $this->_view = "release";
      
      if ($this->_release && ! $this->_layout->init($this->_release)) {
        $this->errstr = "Could not initialize layout, see Debug Log";
        $this->errcode = MIDCOM_ERRCRIT;
        debug_add($this->errstr, MIDCOM_LOG_CRIT);
        return false;
      } 
      
      return true;

    } else {

      debug_add("Release could not be loaded", MIDCOM_LOG_DEBUG);
      debug_pop();
      return false;
    }
  }

  function handle() {
    global $_REQUEST;
    
    debug_push($this->_debug_prefix . "handle");

    if (!$this->_relocate_prefix) {
      $this->_relocate_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    }
    if ($this->_relocate) {
      debug_add("Relocating to ".$this->_relocate);
      $GLOBALS["midcom"]->relocate($this->_relocate_prefix.$this->_relocate);
    }

    debug_pop();
    return true;
  }

  function show() {

    global $midcom;
    global $view;
  
    debug_push($this->_debug_prefix . "show");
    
    if ($this->_view == "release") {
    
      $view = $this->_layout->get_array();
      midcom_show_style("view-release-header");
      midcom_show_style("view-release");
      midcom_show_style("view-release-footer");

    } elseif (!$this->_release) {
      // Main topic view / release listing
      $releases = mgd_list_topic_articles($this->_topic->id,"reverse created");
      if ($releases) {
        $view = $this->_topic;
        midcom_show_style("view-index-header");
      
        while ($releases->fetch()) {
          $release = mgd_get_article($releases->id);
          $this->_layout->init($release);
          $view = $this->_layout->get_array();
          midcom_show_style("view-index-item");
        }
      
        midcom_show_style("view-index-footer");
      }
      
      
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