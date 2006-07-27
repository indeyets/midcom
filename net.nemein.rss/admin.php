<?php
/**
 * @package net.nemein.rss
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * RSS Aggregator Admin interface class.
 * 
 * @package net.nemein.rss
 */
class net_nemein_rss_admin {

    var $_debug_prefix;

    var $_config;
    
    var $_feed;
    var $_topic;
    var $_view;
    var $_channels;
    var $_mode;
 
    var $form_prefix;
    
    var $_l10n;
    var $_l10n_midcom;
    
    var $_local_toolbar;
    var $_topic_toolbar;

    function net_nemein_rss_admin($topic, $config) {

        $this->_debug_prefix = "net.nemein.rss admin::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_feed = false;
        $this->_view = false;
        $this->_channels = array();
        $this->_mode = $this->_config->get("subscription_mode");
        
        $this->form_prefix ="net_nemein_rss_";

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.rss");
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

        } elseif ($argc == 1) {
          switch ($argv[0]) {
            case "config":
                return true;
          }       

        } elseif ($argc == 2) {
          switch ($argv[0]) {
            case "edit":
            case "delete":
                return true;
          }
        }
        debug_pop();
        return false;
    }

    function _subscribe_channel($channel_url,$channel_title = false) {

      global $midgard;

      // Bloglines API doesn't allow us to add subscriptions
      if ($this->_mode == "bloglines") {
        return false;
      }

      // Fetch list of subscribed channels if necessary
      if (count($this->_channels) < 1) {
        $channels = mgd_list_topic_articles($this->_topic->id);
        if ($channels) {
          while ($channels->fetch()) {
            $this->_channels[$channels->name] = $channels->id;
          }
        }
      }

      // Try to fetch the new feed
      error_reporting(E_WARNING);
      $rss = fetch_rss($channel_url);
      error_reporting(E_ALL);
      // TODO: display error on invalid feed

      if (!$channel_title) {

        // If we didn't get the channel title preset
        $channel_title = "";
        if ($rss) {

          // Get the channel title from the feed
          if (isset($rss->channel["title"])) {
            $channel_title = $rss->channel["title"];
          }
        }
      }

      // Use MD5sum of the feed URL as article name to prevent duplicates
      $channel_key = md5($channel_url);
 
      if (array_key_exists($channel_key,$this->_channels)) {
        // If we're updating existing feed
        $article = mgd_get_article($this->_channels[$channel_key]);
        $article->url = $channel_url;
        $article->title = $channel_title;
        return $article->update();

      } else {
        // Otherwise create new feed

        $article = mgd_get_article();
        $article->topic = $this->_topic->id;
        $article->author = $_MIDGARD['user'];
        $article->url = $channel_url;
        $article->title = $channel_title;
        $article->name = $channel_key;
        $stat = $article->create();
        return $stat;

      }

    }

    function _init_create() {

      if ($this->_mode == "bloglines") {
        // Store bloglines user ID if needed
        $dirty = false;
        if (isset($_REQUEST[$this->form_prefix . "bloglines_username"])) {
          $this->_topic->parameter("net.nemein.rss", "bloglines_username", $_REQUEST[$this->form_prefix . "bloglines_username"]);
          $dirty = true;
        }
        if (isset($_REQUEST[$this->form_prefix . "bloglines_password"])) {
          $this->_topic->parameter("net.nemein.rss", "bloglines_password", $_REQUEST[$this->form_prefix . "bloglines_password"]);
          $dirty = true;
        }
        if ($dirty) {
          $this->_config->reset_local();
        }
      }

      if (isset($_REQUEST[$this->form_prefix . "newfeed"])) {
        if ($_REQUEST[$this->form_prefix . "newfeed"]["url"]) {
          $this->_subscribe_channel($_REQUEST[$this->form_prefix . "newfeed"]["url"]);
          // TODO: display error messages
          // TODO: redirect user to edit page if creation succeeded
        }
      }

      // OPML subscription list import support
      $opml_file = false;
      if (version_compare(phpversion(),"4.3.0",">=")) {
        if (array_key_exists($this->form_prefix."opml", $_FILES) &&
             is_uploaded_file($_FILES[$this->form_prefix."opml"]["tmp_name"])) {
          $opml_file = $_FILES[$this->form_prefix."opml"]["tmp_name"];
        }
      } else {
        if (array_key_exists($this->form_prefix."opml", $_REQUEST) &&
             is_uploaded_file($_REQUEST[$this->form_prefix."opml"]["tmp_name"])) {
          $opml_file = $_REQUEST[$this->form_prefix."opml"]["tmp_name"];
        } 
      }
      if ($opml_file) {

        // We have OPML file, parse it
        $opml_handle = fopen($opml_file, "r");
        $opml_data = fread($opml_handle, filesize($opml_file));
        fclose($opml_handle);
        unlink($opml_file);

        $opml_parser = xml_parser_create();
        xml_parse_into_struct($opml_parser, $opml_data, $opml_values );
        foreach ($opml_values as $opml_element) {
          if ($opml_element["tag"] === "OUTLINE" ) {
            // Subscribe to found channels
            if (isset($opml_element["attributes"]["TITLE"])) {
              $this->_subscribe_channel($opml_element["attributes"]["XMLURL"],$opml_element["attributes"]["TITLE"]);
            } else {
              $this->_subscribe_channel($opml_element["attributes"]["XMLURL"]);
            }
          }
        }
        xml_parser_free($opml_parser);
      }
      
      $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());

    }

    function handle($argc, $argv) {
        debug_push($this->_debug_prefix . "handle");

        // handle args, parse the url, save data from forms, prepare output
        
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        /* Add the new article link at the beginning*/
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('add new feed'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ), 0);
        
        if ($argc == 0) {

          if (isset($_REQUEST[$this->form_prefix . "submit"])) {
            $this->_init_create();
          }

          $this->_view = "welcome";
          debug_add ("viewport = welcome");
          debug_pop ();
          return true;

        } elseif ($argc == 1) {

          switch ($argv[0]) {

            case "config":
              return $this->_init_config();
              break;

          }

        } elseif ($argc == 2) {

          switch ($argv[0]) {
            
            case "edit":
              return $this->_init_edit($argv[1]);

            case "delete":
              return $this->_init_delete($argv[1]);

          }

        }
        debug_pop();
        return false;
    }

    function _init_view($id) {

      // Get the requested article
      $feed = mgd_get_article($id);

      if ($feed->topic == $this->_topic->id) {

        // Accept only articles in correct topic
        $this->_feed = $feed;
        $GLOBALS['midcom_component_data']['net.nemein.rss']['active_leaf'] = $id;
        $this->_view = "view";
        
        /* Add the toolbar items */
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
        
        return true;

      }
      return false;
    }

    function _init_edit($id) {

      // Get the feed
      if (!$this->_init_view($id)) {
        return false;
      }
      
      $this->_local_toolbar->disable_item("edit/{$id}.html");
      
      $this->_view = "edit";
      if (isset($_REQUEST[$this->form_prefix . "submit"]) && $_REQUEST[$this->form_prefix . "submit"]) {
        if (isset($_REQUEST[$this->form_prefix . "feed"])) {
          $feed = $_REQUEST[$this->form_prefix . "feed"];

          $article_changed = false;
          if ($feed["url"] != $this->_feed->url) {
            $this->_feed->url = $feed["url"];
            $article_changed = true;
          }
          if ($feed["title"] != $this->_feed->title) {
            $this->_feed->title = $feed["title"];
            $article_changed = true;
          }
          if ($feed["name"] != $this->_feed->name) {
            $this->_feed->name = $feed["name"];
            $article_changed = true;
          }
          if ($feed["icon_url"] != $this->_feed->extra1) {
            $this->_feed->extra1 = $feed["icon_url"];
            $article_changed = true;
          }
          if ($article_changed) {
            // Save document if needed
            $stat = $this->_feed->update();
            $GLOBALS['midcom']->cache->invalidate($this->_feed->guid());
          }
        }  
      }
      return true;
    }

    function _init_delete($id) {
      global $midgard;

      // Get the feed
      if (!$this->_init_view($id)) {
        return false;
      }
      
      $guid = $this->_feed->guid();
      $status = $this->_feed->delete();
      $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

      if ($status) {
        $uri = $prefix;
        $GLOBALS['midcom']->cache->invalidate($guid);
      } else {
        $uri = $prefix."edit/".$this->_feed->id;
      }
      debug_add("Relocating to $uri");
      $GLOBALS["midcom"]->relocate($uri);

      return true;
    }

  function _init_config() {
    debug_push($this->_debug_prefix . "_init_config");
    
    /* Add the toolbar items */
    $this->_local_toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    
    if (isset($_REQUEST) && (array_key_exists("fconfig_submit", $_REQUEST) or array_key_exists("fconfig_cancel", $_REQUEST))) {
      if (array_key_exists("fconfig_submit", $_REQUEST)) {
        debug_add("Saving preferences", MIDCOM_LOG_DEBUG);

        // text fields
        foreach (Array (
          "topic_mode", 
          "show_latest",
          "topic_item_display",
          "topic_name_display",
          "rss_description",
          "rss_items",
          "subscription_mode",
        ) as $k) {
          $this->_topic->parameter("net.nemein.rss", $k, $_REQUEST["fconfig_".$k]);
        }

      } else {
        debug_add("Not saving preferences, Cancel was pressed.", MIDCOM_LOG_DEBUG);
      }

      // Flush MidCOM cache
      debug_add("Invalidating MidCOM cache", MIDCOM_LOG_DEBUG);
      $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());

      $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
      $uri = $prefix;
      debug_add("Relocating to $uri");
      $GLOBALS["midcom"]->relocate($uri);
      exit();
    }

    $this->_view = "config";
    debug_pop();
    return true;
  }


    function show() {
        global $view;
        global $view_topic;
        global $view_feed;
        global $view_form_prefix;

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.rss");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
        
        debug_push($this->_debug_prefix . "show");

        $view_topic = $this->_topic;
        $view_form_prefix = $this->form_prefix;

        if ($this->_view == "welcome") {

          // display the active part of the administration interface
          midcom_show_style("admin-header");

          if ($this->_mode == "bloglines") {
            // Bloglines user account setup
            global $view_config;
            $view_config = $this->_config;
            midcom_show_style("admin-bloglines");
          } else {
            // Subscription dialogue
            midcom_show_style("admin-feed-new");
            midcom_show_style("admin-feed-opml");
          }

          midcom_show_style("admin-footer");

        } elseif ($this->_view == "edit") {

          $view_feed = $this->_feed;
          midcom_show_style("admin-edit");

        } elseif ($this->_view == "config") {

          global $view_config;
          $view_config = $this->_config;
          midcom_show_style("admin-config");

        }

        debug_pop();
        return true;
    }


  function get_metadata() {

    if ($this->_feed) {
      return array (
        MIDCOM_META_CREATOR => $this->_feed->creator,
        MIDCOM_META_EDITOR  => $this->_feed->revisor,
        MIDCOM_META_CREATED => $this->_feed->created,
        MIDCOM_META_EDITED  => $this->_feed->revised
      );
    } else {
      return false;
    }
  }

} // admin

?>