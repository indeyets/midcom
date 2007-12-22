<?php
/**
 * @package net.nemein.bookmarks
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.bookmarks
 */
class net_nemein_bookmarks_viewer
{
    var $_debug_prefix;
    var $_config;           // Configuration object
    var $_config_topic;     // Our Configuration topic (different from data topic if symlinks are in use)
    var $_topic;            // Our current Data Topic
    var $_article;          // The current Article (if present)
    var $_layout;
    var $_location;
    var $_submit_uri;
    var $errcode;
    var $errstr;
    var $_bookmarks;        // List of bookmarks matching URL criteria
    var $_bookmark;         // Selected single bookmark

    function net_nemein_bookmarks_viewer($topic, $config)
    {
        $this->_debug_prefix = "bookmarks viewer::";
        $this->_config = $config;
        $this->_config_topic = $topic;
        $this->_article = false;
        $this->_layout = false;
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_check_for_content_topic();
        $this->_go = false;
        $this->_bookmarks = array();
        $this->_bookmark = false;
    }

    function _check_for_content_topic() {
        //$guid = $this->_config->get("symlink_topic");
        $guid = null;
        if (is_null($guid)) {
            /* No Symlink Topic set */
            $this->_topic = $this->_config_topic;
            return;
        }
        /*
        $object = mgd_get_object_by_guid($guid);
        if (!$object || $object->__table__ != "topic") {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: "
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error(1, "Failed to open symlink content topic.");
        }

        // Check topic validity
        $root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->parameter("midcom", "component") != "net.nemein.bookmarks")
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error("Failed to open symlink content topic.");
        }

        $this->_topic = $object;
        */
    }

    function can_handle($argc, $argv) {
        global $net_nemein_bookmarks_layouts;
        debug_push ($this->_debug_prefix . "can_handle");
        // Load Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$net_nemein_bookmarks_layouts = Array ( " . $data . ");");

        if (!$this->_getView($argc, $argv)) {
            // errstr and errcode are already set by getArticle
            debug_add("could not get requested view. see above.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $_MIDCOM->add_link_head(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL."/net.nemein.bookmarks/bookmarks.css",
        ));

        debug_pop();
        return true;
    }

    function _getView($argc, $argv)
    {
        debug_push ($this->_debug_prefix . "_getView");
        if ($argc == 0)
        {
            // index, so no need for getting something...
            debug_pop();
            return true;
        }
        elseif ($argc == 1)
        {
            // Bookmark submission form
            if ($argv[0] == "submitbookmark")
            {
                $this->_location = $argv[0];
                return true;
            }
            // Bookmarks by tag page
            $this->_bookmarks = net_nemein_bookmarks_helper_list_bookmarks_by_tag($this->_topic->id, $argv[0], "nofilter");
            if (count($this->_bookmarks) > 0)
            {
                $this->_location = $argv[0];
                $GLOBALS['midcom_component_data']['net.nemein.bookmarks']['active_leaf'] = $this->_location;
                return true;
            }
            else
            {
                // No bookmarks for the given tag, give 404
                return false;
            }
        }
        else
        {   // argc > 3 => can't handle
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
            $topic = mgd_get_topic($topic_id);
            $path = $topic->name . "/" . $path;
            return $this->_getArticlePath($topic->up, $path);
        }
    }

    function handle($argc, $argv)
    {
        debug_push ($this->_debug_prefix . "handle");
        if ($this->_location == "submitbookmark")
        {

            // Start with empty bookmark
            $GLOBALS["net_nemein_bookmarks_bookmark"]["url"] = "";
            $GLOBALS["net_nemein_bookmarks_bookmark"]["title"] = "";
            $GLOBALS["net_nemein_bookmarks_bookmark"]["extended"] = "";
            $GLOBALS["net_nemein_bookmarks_bookmark"]["tags"] = "";

            // Check if the URL has already been bookmarked
            if ($_REQUEST["url"])
            {

                // Populate URL and title as given by the bookmarklet
                $GLOBALS["net_nemein_bookmarks_bookmark"]["url"] = $_REQUEST["url"];
                $GLOBALS["net_nemein_bookmarks_bookmark"]["title"] = $_REQUEST["title"];

                $articles = mgd_list_topic_articles($this->_topic->id);
                if ($articles)
                {
                    while ($articles->fetch())
                    {
                        if ($articles->url == $_REQUEST["url"])
                        {
                            $this->_bookmark = mgd_get_article($articles->id);
                            $GLOBALS["net_nemein_bookmarks_bookmark"]["url"] = $this->_bookmark->url;
                            $GLOBALS["net_nemein_bookmarks_bookmark"]["title"] = $this->_bookmark->title;
                            $GLOBALS["net_nemein_bookmarks_bookmark"]["extended"] = $this->_bookmark->abstract;
                            $GLOBALS["net_nemein_bookmarks_bookmark"]["tags"] = $this->_bookmark->content;
                        }
                    }
                }
            }

            if (isset($_POST["net_nemein_bookmarks_add_submit"]))
            {
                // Save the bookmark
                if (!$_MIDGARD['user'])
                {
                    // Authenticate using username/password inputted in form
                    $auth = mgd_auth_midgard($_POST['net_nemein_bookmarks_username'],
                                             $_POST['net_nemein_bookmarks_password'],
                                             0);
        			   $midgard = mgd_get_midgard();

                    if (!$auth)
                    {
                        $GLOBALS["net_nemein_bookmarks_processing_message"] = "Login failed";
                    }
                }

                if ($_MIDGARD['user'])
                {
                        $new_bookmark = false;
                        if (!$this->_bookmark)
                        {
                            // New bookmark, use empty article object
                            $this->_bookmark = mgd_get_article();
                            $this->_bookmark->topic = $this->_topic->id;
                            $this->_bookmark->author = $_MIDGARD['user'];
                            $this->_bookmark->name = time();
                            $new_bookmark = true;
                        }

    				       $this->_bookmark->title = $_POST['net_nemein_bookmarks_add_title'];
    				       $this->_bookmark->url = $_POST['net_nemein_bookmarks_add_url'];
    				       $this->_bookmark->abstract = $_POST['net_nemein_bookmarks_add_extended'];
    				       $this->_bookmark->content = $_POST['net_nemein_bookmarks_add_tags'];

                        if ($new_bookmark)
                        {
                            // Create the new bookmark
                            $stat = $this->_bookmark->create();
                            $this->_bookmark = mgd_get_article($stat);
                        }
                        else
                        {
                            $stat = $this->_bookmark->update();
                        }

                        if ($stat)
                        {
                            // Invalidate the cache
                            $_MIDCOM->cache->invalidate_all();

                            // Update the Index
                            $datamanager = new midcom_helper_datamanager($this->_config->get('schemadb'));
                            if (! $datamanager)
                            {
                                debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                                          MIDCOM_LOG_WARN);
                            }
                            else
                            {
                                if (! $datamanager->init($this->_bookmark))
                                {
                                    debug_add("Warning, failed to initialize datamanager for Article {$this->_bookmark->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
                                    debug_print_r('Article dump:', $this->_bookmark);
                                }
                                else
                                {
                                    $indexer =& $_MIDCOM->get_service('indexer');
                                    $indexer->index($datamanager);
                                }
                            }

                            // Redirect browser and exit
                            $_MIDCOM->relocate($this->_bookmark->url);
                        }
                        $GLOBALS["net_nemein_bookmarks_processing_message"] = "Trying to save bookmark... ".mgd_errstr();

                }

            }
        }
        $_MIDCOM->set_pagetitle($this->_config_topic->extra);
        debug_pop();
        return true;
    }

    function show()
    {
        $GLOBALS["view_config"] = $this->_config;
        $GLOBALS["view_config_topic"] = $this->_config_topic;
        debug_push ($this->_debug_prefix . "show");

        // get l10n libraries
        $i18n =& $_MIDCOM->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.bookmarks");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

        if ($this->_location == "submitbookmark")
        {
            $this->_show_submit();
        }
        elseif ($this->_location)
        {
            $this->_show_list();
        }
        else
        {
            $this->_show_index();
        }
        debug_pop();
        return true;
    }

    function _show_submit()
    {
        $GLOBALS["view_bookmark"] = $this->_bookmark;
        midcom_show_style("show-submit-form");
    }

    function _show_list()
    {
        global $view;
        global $view_tag;
        $view_tag = $this->_location;
        global $view_date;

        midcom_show_style("show-list-header");

        if ($this->_bookmarks)
        {
            // Get the datamanager
            $layout = new midcom_helper_datamanager($GLOBALS["net_nemein_bookmarks_layouts"]);
            if (! $layout)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Layout class could not be instantiated.");
            }

            foreach($this->_bookmarks as $bookmark_article)
            {
                // Load the bookmarks via datamanager
                if (! $layout->init($bookmark_article))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Layout class could not be initialized.");
                }
                $view = $layout->get_array();

                // List the tags
                global $view_tags;
                $view_tags = net_nemein_bookmarks_helper_list_tags_of_bookmark($view);

                $view_date = $bookmark_article->created;
                midcom_show_style("show-list-item");
            }
        }
        else
        {
            midcom_show_style("show-list-empty");
        	}
    }

    function _show_index()
    {
        global $view;
        global $view_date;
        global $view_topic;
        $view_topic = $this->_topic;

        midcom_show_style("show-index-header");

        $articles = net_nemein_bookmarks__list_articles($this->_topic->id, $this->_config->get("sort_order"));
        if ($articles)
        {
            $layout = new midcom_helper_datamanager($GLOBALS["net_nemein_bookmarks_layouts"]);
            if (! $layout)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Layout class could not be instantiated.");
            }

            $count = $this->_config->get("count");

            if ($count < 0)
            {
                $count = 999; // you don't wanna show more than 1000...
            }
            foreach ($articles as $id)
            {
                if ($count <= 0)
                {
                    break;
                }
                $article = mgd_get_article($id);
                if (! $layout->init($article))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Layout class could not be initialized.");
                }
                $view = $layout->get_array();

                $view_date = $article->created;

                global $view_tags;
                $view_tags = net_nemein_bookmarks_helper_list_tags_of_bookmark($view);
                midcom_show_style("show-index-item");

                $count--;
            }
        }
        else
        {
            midcom_show_style("show-index-empty");
        }
        midcom_show_style("show-index-footer");
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
