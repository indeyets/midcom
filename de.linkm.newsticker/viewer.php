<?php
/**
 * @package de.linkm.newsticker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker Viewer interface class
 *
 * @todo document
 */
function de_linkm_newsticker__list_articles($topic_id, $show_all, $sort_order = "reverse created", $subdirectory_mode = FALSE)
{
    if ($subdirectory_mode)
    {
        // Fetch news from all newsticker subdirectories
        $newsticker_topics = array();
        $ids = array();

        // We need the legacy variant of this call for now, since we don't
        // have recursive queries on the core level yet.
        $articles = mgd_list_topic_articles_all($topic_id);
        if ($articles)
        {
            while ($articles->fetch())
            {
                $article = new midcom_baseclasses_database_article($articles->id);
                if (! $article)
                {
                    continue;
                }

                // Check topics status if needed
                if (! array_key_exists($article->topic, $newsticker_topics))
                {
                    $articles_topic = new midcom_db_topic($article->topic);

                    if ($articles_topic->get_parameter("midcom", "component") == "de.linkm.newsticker")
                    {
                        $newsticker_topics[$articles_topic->id] = TRUE;
                    }
                    else
                    {
                        $newsticker_topics[$articles_topic->id] = FALSE;
                    }
                }

                if ($newsticker_topics[$article->topic])
                {
                    // This article is a news item
                    $ids[$article->id] = $article;
                }
            }
        }
    }
    else
    {
        // Regular newsticker
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic_id);
        $result = $qb->execute();

        if (! $result)
        {
            $ids = false;
        }
        else
        {
            $ids = Array();
            foreach ($result as $article)
            {
                $ids[$article->id] = $article;
            }
        }
    }

    if (!$ids)
    {
        return FALSE;
    }
    if (count($ids) == 0)
    {
        return Array();
    }

    // filter Articles
    $result = Array();
    $now = time();
    foreach ($ids as $id => $article)
    {
        if (trim($article->extra1) != "")
        {
            $starttime = strtotime($article->extra1);
        }
        else
        {
            $starttime = 0;
        }
        if (trim($article->extra2) != "")
        {
            $endtime = strtotime($article->extra2);
        }
        else
        {
            $endtime = 0;
        }

        if (   ! $article
            || $starttime == -1
            || $endtime == -1)
        {
            continue; // Skipped Article $id: article = $article, starttime = $starttime, endtime = $endtime
        }

        // Checking $starttime < $now < $endtime");
        if (   $starttime < $now
            && $now < $endtime)
        {
            $result[] = $article;
        }
        else if ($show_all == true)
        {
            $result[] = $article; // We should not filter, so we add this anyway
        }
    }

    if ($sort_order)
    {
        mgd_sort_object_array($result, $sort_order);
    }

    return $result;
}



class de_linkm_newsticker_viewer
{

    var $_debug_prefix;

    var $_config;     // Configuration object
    var $_config_topic; // Our Configuration topic (different from data topic if symlinks are in use)
    var $_topic;      // Our current Data Topic
    var $_article;    // The current Article (if present)
    var $_layout;
    var $_latest;     // "latest" view: show that many latest news
    var $_rss;        // TRUE if in RSS view
    var $_rsd;        // TRUE if in RSD view
    var $_public_publish; // true if in public publish mode
    var $_public_publish_id; // Contains the id for an edit loop
    var $_pp_dm;      // Public Publishing Datamanager
    var $_filters;     // Array of text/value filters
    var $_datefilters; // Array of date filters

    var $errcode;
    var $errstr;


    function de_linkm_newsticker_viewer($topic, $config)
    {
        $this->_debug_prefix = "newsticker viewer::";

        $this->_config = $config;
        $this->_config_topic = $topic;

        $this->_article = false;
        $this->_layout = false;
        $this->_latest = false;
        $this->_rss = false;
        $this->_rsd = false;
        $this->_public_publish = false;
        $this->_public_publish_id = null;
        $this->_pp_dm = null;

        // article filtering support
        $this->_filters = null;
        $this->_datefilters = null;
        if (is_array($_REQUEST))
        {

            // Text/value filters
            if (   $this->_config->get("enable_filters_get")
                && isset($_REQUEST["de_linkm_newsticker_filter"])
                && is_array($_REQUEST["de_linkm_newsticker_filter"]))
            {
                $this->_filters = $_REQUEST["de_linkm_newsticker_filter"];
            }

            // date-based filtering
            if (   $this->_config->get("enable_datefilters_get")
                && isset($_REQUEST["de_linkm_newsticker_filter_date"])
                && is_array($_REQUEST["de_linkm_newsticker_filter_date"]))
            {
                $this->_datefilters = $_REQUEST["de_linkm_newsticker_filter_date"];
            }
        }

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $this->_check_for_content_topic();

        $this->_go = false;
    }

    function _check_for_content_topic()
    {
        $guid = $this->_config->get("symlink_topic");
        if (is_null($guid))
        {
            /* No Symlink Topic set */
            $this->_topic = $this->_config_topic;
            return;
        }
        $object = new midcom_db_topic($guid);
        if (! $object)
        {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: "
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        /* Check topic validity */
        $root = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->get_parameter("midcom", "component") != "de.linkm.newsticker")
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        $this->_topic = $object;
    }

    function can_handle($argc, $argv)
    {
        global $de_linkm_newsticker_layouts;
        debug_push ($this->_debug_prefix . "can_handle");
        // Load Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$de_linkm_newsticker_layouts = Array ( " . $data . ");");

        if (($argc == 1 || $argc == 2) && $argv[0] == "add_entry")
        {
            $this->_public_publish = true;
        }
        else if (! $this->_getArticle($argc, $argv))
        {
            // errstr and errcode are already set by getArticle
            debug_add("could not get article. see above.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }


    function _getArticle($argc, $argv)
    {
        debug_push ($this->_debug_prefix . "_getArticle");
        if ($argc == 0)
        {
            // index, so no need for getting something...
            debug_pop();
            return true;
        }
        else if (   $argc == 1
                 && $argv[0] == "rss.xml")
        {
            // RSS, default count
            if (! $this->_config->get("rss_enable"))
            {
                debug_add("RSS view disabled by configuration", MIDCOM_LOG_WARN);
                debug_pop();
                return FALSE;
            }
            $this->_latest = $this->_config->get("rss_count");
            $this->_rss = TRUE;
            debug_pop();
            return TRUE;

        }
        else if (   $argc == 1
                 && $argv[0] == "rsd.xml")
        {
            // RSS, default count
            $this->_rsd = TRUE;
            debug_pop();
            return TRUE;

        }
        else if (   $this->_config->get("enable_datefilters_path")
                 && is_numeric($argv[0])
                 && $argc <= 3
                 && $argv[0] <= date("Y",time()))
        {
            // Date filtering from path in format YYYY/MM/DD
            // First argument is the year filter
            $this->_datefilters["Y"] = $argv[0];

            debug_add("Filtering by dates in the path", MIDCOM_LOG_DEBUG);
            debug_pop();

            if ($argc > 1)
            {
                if (is_numeric($argv[1]) && $argv[1] <= 12)
                {
                    // Second argument is the month filter
                    $this->_datefilters["m"] = $argv[1];

                    if ($argc > 2)
                    {
                        if (is_numeric($argv[2]) && $argv[2] <= 31)
                        {
                            // Third argument is the day filter
                            $this->_datefilters["d"] = $argv[2];
                        }
                        else
                        {
                            return false;
                        }
                    }
                 }
                 else
                 {
                    return false;
                 }
            }

            // All 1-3 filters were numeric, continue
            return true;

        }
        else if ($argc == 1)
        {
            // article name
            debug_add("getting article '$argv[0]'", MIDCOM_LOG_DEBUG);

            $qb = midcom_baseclasses_database_article::new_query_builder();
            $qb->add_constraint('topic', '=', $this->_topic->id);
            $qb->add_constraint('name', '=', $argv[0]);
            $result = $qb->execute();

            if (! $result)
            {
                debug_add("article '$argv[0]' not found", MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }

            $this->_article = $result[0];

            $tmp = Array
            (
                Array
                (
                    MIDCOM_NAV_URL => "{$this->_article->name}.html",
                    MIDCOM_NAV_NAME => $this->_article->title,
                ),
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
            $_MIDCOM->set_26_request_metadata($this->_article->revised, $this->_article->guid);

            debug_add("found article", MIDCOM_LOG_DEBUG);
            $this->errcode = MIDCOM_ERROK;
            debug_pop();
            return true;
        }
        else if ($argc == 2)
        {
            // Two params -> Latest news and number
            debug_add("Two parameters, showing latest N items", MIDCOM_LOG_DEBUG);
            debug_pop();
            if ($argv[0] == "latest")
            {
                $this->_latest = $argv[1];
                return true;
            }
            else if ($argv[0] == "rss")
            {
                if (! $this->_config->get("rss_enable"))
                {
                  debug_add("RSS view disabled by configuration", MIDCOM_LOG_WARN);
                  debug_pop();
                  return FALSE;
                }
                $this->_latest = $argv[1];
                $this->_rss = true;
                return true;
            }
            else if ($argv[0] == "rpc")
            {
                if (! $this->_config->get("enable_blogging_apis"))
                {
                    debug_add("Blogging APIs disabled by configuration", MIDCOM_LOG_WARN);
                    debug_pop();
                    return FALSE;
                }
                if ($argv[1] == "metaweblog")
                {
                  // Include PEAR XML-RPC library
                  error_reporting(E_ALL & ~E_NOTICE);
                  include_once("XML/RPC/Server.php");
                  error_reporting(E_ALL);
                  // Check if the library was found
                  if (class_exists("XML_RPC_Server"))
                  {
                      $this->_metaWeblogAPI();
                  }
                  else
                  {
                      debug_add("Missing PEAR XML-RPC server library", MIDCOM_LOG_WARN);
                      debug_pop();
                      return FALSE;
                  }
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            // argc > 3 => can't handle
            debug_add("too many parameters", MIDCOM_LOG_DEBUG);
            debug_pop();
            return false;
        }
    }


    function _getArticlePath($topic_id, $path)
    {
        // Find Article's path
        if ($topic_id == $this->_topic->id)
        {
            return $path;
        }
        else
        {
            $topic = new midcom_db_topic($topic_id);
            if (! $topic)
            {
                return $path;
            }
            $path = $topic->name . "/" . $path;
            return $this->_getArticlePath($topic->up, $path);
        }
    }

    function _metaWeblogAPI()
    {
        // Would the author please reformat this to the 4 spaces when he
        // rewrites the MetaWeblog stuff to DBA ? Thanks. :)


      $GLOBALS["de_linkm_newsticker_topic"] = $this->_topic->id;
      $GLOBALS['midcom']->set_custom_context_data('config', $this->_config);

      // MidCOM's character encoding
      $i18n =& $GLOBALS["midcom"]->get_service("i18n");
      $encoding = $i18n->get_current_charset();

      header("Content-type: text/xml; charset=$encoding");

      // Include the server method functions
      require(MIDCOM_ROOT . '/de/linkm/newsticker/metaweblog_api.php');

      // Construct the dispatch map for the XML-RPC server
      $dispatchmap = array
      (
        // MetaWebLog API
        "metaWeblog.newPost" => array(
           "function" => "de_linkm_newsticker_metaweblog_newPost"
        ),
        "metaWeblog.getPost" => array(
           "function" => "de_linkm_newsticker_metaweblog_getPost"
        ),
        "metaWeblog.editPost" => array(
           "function" => "de_linkm_newsticker_metaweblog_editPost"
        ),
        "metaWeblog.getRecentPosts" => array(
           "function" => "de_linkm_newsticker_metaweblog_getRecentPosts"
        ),
        "metaWeblog.getCategories" => array(
           "function" => "de_linkm_newsticker_metaweblog_getCategories"
        ),
        "metaWeblog.newMediaObject" => array(
           "function" => "de_linkm_newsticker_metaweblog_newMediaObject"
        ),
        // Blogger API
        "blogger.deletePost" => array(
           "function" => "de_linkm_newsticker_blogger_deletePost"
        ),
        "blogger.getUsersBlogs" => array(
           "function" => "de_linkm_newsticker_blogger_getUsersBlogs"
        )
      );

      // Serve the RPC request and exit
      $server = new XML_RPC_Server($dispatchmap);
      exit();
    }

    function handle($argc, $argv)
    {
        debug_push ($this->_debug_prefix . "handle");

        $url_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // RSS autodetection
        if ($this->_config->get("rss_enable"))
        {
            $GLOBALS["midcom"]->add_link_head
            (
                array
                (
                    'rel' => 'alternate',
                    'type' => 'application/rss+xml',
                    'title' => 'RSS',
                    'href' => "{$url_prefix}rss.xml",
                )
            );
        }

        // RSD (Really Simple Discoverability) autodetection
        $GLOBALS["midcom"]->add_link_head
        (
            array
            (
                'rel' => 'EditURI',
                'type' => 'application/rsd+xml',
                'title' => 'RSD',
                'href' => "{$url_prefix}rsd.xml",
            )
        );

        if (   ! $this->_public_publish
            && $this->_article)
        {
            $this->_layout = new midcom_helper_datamanager($GLOBALS["de_linkm_newsticker_layouts"]);

            if (! $this->_layout)
            {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Layout class could not be instantinated.");
            }

            if (! $this->_layout->init($this->_article))
            {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Layout class could not be initialized.");
            }

            // set nap element
            // Temporarily disabled on-site: way to slow with large topics. needs nap rewrite.
            // $GLOBALS['midcom_component_data']['de.linkm.newsticker']['active_leaf'] = $this->_article->id;

            // initialize layout
            $substyle = $this->_layout->get_layout_name();
            if ($substyle != "default")
            {
                debug_add("pushing substyle $substyle", MIDCOM_LOG_DEBUG);
                $GLOBALS["midcom"]->substyle_append($substyle);
            }
            $GLOBALS['midcom']->set_pagetitle($this->_article->title);
        }
        else
        {
            $GLOBALS['midcom']->set_pagetitle($this->_config_topic->extra);

            // Modify last modified timestamp
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('topic', '=', $this->_topic->id);
            $qb->add_order('revised', 'DESC');
            $qb->set_limit(1);
            $result = $qb->execute();
            if ($result)
            {
                $article_time = $result[0]->revised;
            }
            else
            {
                $article_time = 0;
            }
            $topic_time = $this->_topic->revised;
            $_MIDCOM->set_26_request_metadata(max($article_time, $topic_time), null);
        }

        if ($this->_rss)
        {
            $GLOBALS["midcom"]->cache->content->content_type("text/xml");
            $GLOBALS["midcom"]->header("Content-type: text/xml; charset=UTF-8");
            $this->show();
            // Now cleanup MidCOM
            $GLOBALS["midcom"]->finish();
            exit();
        }

        if ($this->_rsd)
        {
            $GLOBALS["midcom"]->cache->content->content_type("text/xml");
            $GLOBALS["midcom"]->header("Content-type: text/xml; charset=UTF-8");
            $GLOBALS["view_blogging_enabled"] = $this->_config->get("enable_blogging_apis");
            $GLOBALS["view_topic"] = $this->_topic;
            $GLOBALS["view_config_topic"] = $this->_config_topic;
            include(MIDCOM_ROOT . "/de/linkm/newsticker/style/rsd.php");
            // Now cleanup MidCOM
            $GLOBALS["midcom"]->finish();
            exit();
        }

        if ($this->_public_publish)
        {
            return $this->_handle_public_publish($argc, $argv);
        }

        debug_pop();
        return true;
    }

    function _handle_public_publish ($argc, $argv)
    {
        if (! $this->_config->get("pp_enable"))
        {
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            $this->errstr = "Public publishing is disabled.";
            return false;
        }
        /*
        if (strlen($this->_config->get("pp_user")) > 0)
        {
            mgd_auth_midgard($this->_config->get("pp_user"), $this->_config->get("pp_pass"), false);
            // Call mgd_get_midgard, seems to be required according to emile/piotras
            $midgard = mgd_get_midgard();
            debug_print_r("Authenticated to public publishing user: ", $midgard);
        }
        */
        if (! $_MIDCOM->auth->request_sudo())
        {
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            $this->errstr = "Access denied. Sudo privilege escalation denied.";
            return false;
        }
        $result = false;
        if ($argc == 1)
        {
            $result = $this->_handle_public_publish_create();
        }
        else if ($argc == 2)
        {
            $result = $this->_handle_public_publish_createloop($argv[1]);
        }
        else
        {
            $this->errcode = MIDCOM_ERRCRIT;
            $this->errstr = "Unknown URL method.";
        }
        $_MIDCOM->auth->drop_sudo();
        return $result;
    }

    function _relocate ($url)
    {
        $_MIDCOM->relocate( $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . $url);
    }

    function _pp_dmcallback(&$datamanager)
    {
        $result = Array (
            "success" => true,
            "storage" => null
        );

        $maxtries = 99;
        $tries = 0;
        $article = new midgard_baseclasses_database_article();
        $article->topic = $this->_topic->id;
        $success = false;
        while(   ! $success
              && $tries < $maxtries)
        {
            $article->name = sprintf("%s-%03d", date("Y-m-d"), $tries);
            if (! $article->create())
            {
                debug_add("A entry with this name already exists, incrementing number...", MIDCOM_LOG_DEBUG);
                $tries++;
            }
            else
            {
                $success = true;
            }
        }

        if (! $id)
        {
            return null;
        }

        $this->_article =& $article;
        $this->_article->set_parameter("midcom.helper.datamanager", "data_client_ip", $_SERVER["REMOTE_ADDR"]);
        $this->_article->set_parameter("midcom.helper.datamanager", "data_client_ua", $_SERVER["HTTP_USER_AGENT"]);

        $result["storage"] =& $this->_article;
        return $result;
    }

    function _handle_public_publish_create ()
    {
        // Fire up a creation-mode datamanager
        $this->_pp_dm = new midcom_helper_datamanager ($GLOBALS["de_linkm_newsticker_layouts"]);
        if (! $this->_pp_dm)
        {
            $this->errstr = "Could not create datamanager, see Logfile";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        $schema = $this->_config->get("pp_schema");
        if (! $schema)
        {
            $this->errstr = "Public Publishing Schema not set, this must be definied.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        if (! $this->_pp_dm->init_creation_mode($schema, $this, "_pp_dmcallback"))
        {
            $this->errstr = "Could not initialize datamanager, see Logfile";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }

        $result = $this->_pp_dm->process_form();

        switch($result)
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add("First call of the Datamanager, display the form like usual.");
                return true;

            case MIDCOM_DATAMGR_SAVED:
                // Update the Index
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $indexer->index($this->_pp_dm);

                $this->_relocate("");
                // this will exit().

            case MIDCOM_DATAMGR_EDITING:
                debug_add("Datamanager wants to stay in the edit loop, so shall it be.");
                $this->_relocate(  "add_entry/" . $this->_article->guid() . ".html"
                                 . "?de_linkm_newsticker_pperr="
                                 . urlencode($this->_pp_dm->errstr));
                // this will exit().

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add("Process has been cancelled without anything done.");
                $this->_relocate("");
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                // there was sth. really wrong
                $this->errstr = "Datamanager failed critically:" . $this->_pp_dm->errstr;
                $this->errcode = MIDCOM_ERRCRIT;
                return false;

            default:
                // This should not happen
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown.";
                return false;
        }
        // We should not get here.
    }

    function _handle_public_publish_createloop ($guid)
    {
        $this->_article = new midcom_baseclasses_database_article($guid);
        if (! $this->_article)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "{$guid} is no valid article reference: " . mgd_errstr();
            return false;
        }

        $this->_pp_dm = new midcom_helper_datamanager ($GLOBALS["de_linkm_newsticker_layouts"]);
        if (! $this->_pp_dm)
        {
            $this->errstr = "Could not create datamanager, see Logfile";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        if (!$this->_pp_dm->init($this->_article))
        {
            $this->errstr = "Could not initialize datamanager, see Logfile";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }

        $result = $this->_pp_dm->process_form();

        switch ($result)
        {
            case MIDCOM_DATAMGR_EDITING:
                return true;

            case MIDCOM_DATAMGR_SAVED:
                // Update the Index
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $indexer->index($this->_pp_dm);
                $this->_relocate('');
                // This will exit.

            case MIDCOM_DATAMGR_CANCELLED:
                // Delete the article
                if (! $this->_article->delete())
                {
                    $this->errstr = "Could not delete the temporary article: " . mgd_errstr();
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                $this->_relocate('');

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
    }

    function show()
    {
        $GLOBALS["view_config"] = $this->_config;
        $GLOBALS["view_config_topic"] = $this->_config_topic;
        debug_push ($this->_debug_prefix . "show");

        if ($this->_public_publish)
        {
            $this->_show_public_publish();
        }
        else if ($this->_article)
        {
            $this->_show_article();
        }
        else if ($this->_latest)
        {
            $this->_show_latest();
        }
        else
        {
            $this->_show_index();
        }

        debug_pop();
        return true;
    }

    function _show_public_publish()
    {
        $GLOBALS["view_topic"] =& $this->_topic;
        $GLOBALS["view_config_topic"] =& $this->_config_topic;
        $GLOBALS["view_config"] =& $this->_config;
        $GLOBALS["view_dm"] =& $this->_pp_dm;
        $GLOBALS["view"] =& $this->_article;
        midcom_show_style("show_public_publish");
    }

    function _show_article()
    {
        global $view;
        global $view_name;
        global $view_date;
        $view = $this->_layout->get_array();
        $view_name = $this->_article->name;
        $view_date = $this->_article->created;
        midcom_show_style("show-detail");
    }


    function _show_index()
    {
        global $view;
        global $view_name;
        global $view_date;
        global $view_topic;
        global $view_enable_details;
        $view_topic = $this->_topic;

        midcom_show_style("show-index-header");

        $articles = de_linkm_newsticker__list_articles($this->_topic->id, $this->_config->get("index_list_old"), $this->_config->get("sort_order"), $this->_config->get("enable_subdirs"));
        if ($articles)
        {
            $layout = new midcom_helper_datamanager($GLOBALS["de_linkm_newsticker_layouts"]);
            if (! $layout)
            {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Layout class could not be instantinated.");
            }

            $view_enable_details = $this->_config->get("enable_details");
            $count = $this->_config->get("count");

            if ($count < 0)
            {
                $count = 999; // you don't wanna show more than 1000...
            }

            foreach ($articles as $article)
            {
                if (   $count <= 0
                    && ! $this->_datefilters)
                {
                    // Don't show more articles than the count except
                    // if we're listing articles based on a date
                    break;
                }
                if (! $layout->init($article))
                {
                    $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Layout class could not be initialized.");
                }
                $view = $layout->get_array();
                $view_name = $this->_getArticlePath($article->topic, $article->name);
                $view_date = $article->created;

                // Apply text/value filters
                $display_article = true;
                if (   $this->_filters !== null
                    && is_array($this->_filters))
                {
                    foreach ($this->_filters as $field => $value)
                    {
                        if (is_array($view[$field]))
                        {
                            if (!in_array($value, $view[$field]))
                            {
                                // Match from multiple select
                                $display_article = false;
                            }
                        }
                        else if ($view[$field] != $value)
                        {
                                $display_article = false;
                        }
                    }
                }

                // Date-based filtering
                if (   $this->_datefilters !== null
                    && is_array($this->_datefilters))
                {
                    foreach ($this->_datefilters as $date_formatter => $value)
                    {
                        if (date($date_formatter,$view_date) != $value)
                        {
                            $display_article = false;
                        }
                    }
                }

                if (! $display_article)
                {
                    continue;
                }

                midcom_show_style("show-index-item");

                $count--;
            }
        }
        else
            midcom_show_style("show-index-empty");

        midcom_show_style("show-index-footer");
    }


    function _show_latest()
    {
        global $view;
        global $view_name;
        global $view_date;
        global $view_author;

        $articles = de_linkm_newsticker__list_articles($this->_topic->id, $this->_config->get("index_list_old"), $this->_config->get("sort_order"), $this->_config->get("enable_subdirs"));
        $i = 1;
        if ($articles)
        {
            $layout = new midcom_helper_datamanager($GLOBALS["de_linkm_newsticker_layouts"]);
            $view = $this->_topic;
            if ($this->_rss)
            {
                $server_url = $GLOBALS["midcom"]->get_host_name();
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                include(MIDCOM_ROOT . "/de/linkm/newsticker/style/rss_header.php");
            }
            else
            {
                midcom_show_style("show-latest-header");
            }

            // Support for /latest/n-n to be able to skip n news items
            $highnumber=$this->_latest;
            $lownumber=0;
            if (ereg('-',$this->_latest))
            {
                $numbers = explode("-", $this->_latest);
                $highnumber = $numbers[1];
                $lownumber = $numbers[0];
            }

            foreach ($articles as $article)
            {
                if ($i > $highnumber)
                {
                    break;
                }

                if (! $layout->init($article))
                {
                    $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Layout class could not be initialized.");
                }
                $view = $layout->get_array();
                $view_name = $this->_getArticlePath($article->topic, $article->name);
                $view_date = $article->created;
                $view_author = new midcom_baseclasses_database_person($article->author);

                $display_article = true;

                // Apply text/value filters
                if (   $this->_filters !== null
                    && is_array($this->_filters))
                {
                    foreach ($this->_filters as $field => $value)
                    {
                        if (is_array($view[$field]))
                        {
                            if (!in_array($value,$view[$field]))
                            {
                                // Match from multiple select
                                $display_article = false;
                            }
                        }
                        else if ($view[$field] != $value)
                        {
                            // Match from straight value
                            $display_article = false;
                        }
                    }
                }

                // Date-based filtering
                if (   $this->_datefilters !== null
                    && is_array($this->_datefilters))
                {
                    foreach ($this->_datefilters as $date_formatter => $value)
                    {
                        if (date($date_formatter,$view_date) != $value)
                        {
                            $display_article = false;
                        }
                    }
                }

                if ($i < $lownumber)
                {
                        $display_article = false;
                        $i++;
                }
                if (! $display_article)
                {
                    continue;
                }

                if ($this->_rss)
                {
                    $GLOBALS["view_rss_include_image"] = $this->_config->get("rss_include_image");
                    include(MIDCOM_ROOT . "/de/linkm/newsticker/style/rss_item.php");
                }
                else
                {
                    midcom_show_style("show-latest-item");
                }
                $i++;
            }
            if ($this->_rss)
            {
                require(MIDCOM_ROOT . "/de/linkm/newsticker/style/rss_footer.php");
            }
            else
            {
                midcom_show_style("show-latest-footer");
            }
        }
    }


    function get_metadata()
    {
        if ($this->_article)
        {
            return array (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        }
        else
        {
            return false;
        }
    }

}

?>
