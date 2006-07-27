<?php

/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo Gallery Viewer Class
 *
 * @todo document
 *
 * @package net.siriux.photos
 */
class net_siriux_photos_viewer {

    var $_debug_prefix;

    var $_config;    // our configuration
    var $_topic;     // topic
    var $_photo;     // current Photo object
    var $_latest;    // show that many latest Photos
    var $_random;    // show that many random Photos
    var $_random_mode = 'thumbnail'; //Show random photo in which size
    var $_indexmode;  // show latest thumbnail in index mode
    var $_rss;       // show that many entries in RSS feed
    var $_enable_notes; // Whether to show Flash-powered notes

    var $errcode;
    var $errstr;

    var $_l10n;
    var $_l10n_midcom;


    function net_siriux_photos_viewer($topic, $config) {
        $this->_debug_prefix = "net.siriux.photos viewer::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_photo = FALSE;
        $this->_latest = FALSE;
        $this->_random = FALSE;
        $this->_rss = FALSE;
        $this->_indexmode = FALSE;

        $this->_enable_notes = $this->_config->get("enable_flash_notes");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.siriux.photos");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
    }


    function can_handle($argc, $argv) {
        debug_push($this->_debug_prefix . "can_handle");

        $GLOBALS["midcom"]->set_custom_context_data("configuration", $this->_config);
        $GLOBALS["midcom"]->set_custom_context_data("l10n", $this->_l10n);
        $GLOBALS["midcom"]->set_custom_context_data("l10n_midcom", $this->_l10n_midcom);
        $GLOBALS["midcom"]->set_custom_context_data("errstr", $this->errstr);

        if (! $this->_getArticle($argc, $argv)) {
            // errstr and errcode are already set by getArticle
            debug_add("Could not get Article. See above.");
            debug_pop();
            return FALSE;
        }

        debug_pop();
        return TRUE;
    }

    function _getPhoto($name)
    {
        $article = mgd_get_article_by_name($this->_topic->id, $name);
        if (! $article)
        {
            $this->errstr = "Photo '$name' not found";
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return FALSE;
        }
        $this->_photo = new siriux_photos_Photo($article->id);
        if (! $this->_photo)
        {
            $this->errstr = "Can't get Photo ".$article->id;
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return FALSE;
        }
        $metadata =& midcom_helper_metadata::retrieve($article);
        if (   ! $GLOBALS['midcom_config']['show_unapproved_objects']
            && ! $metadata->is_approved())
        {
            $this->errstr = "Photo is not approved";
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return FALSE;
        }
        if (   ! $GLOBALS['midcom_config']['show_hidden_objects']
            && ! $metadata->is_visible())
        {
            $this->errstr = "Photo is not visible";
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return FALSE;
        }
        debug_add("Got Photo. id=".$article->id, MIDCOM_LOG_DEBUG);
        debug_pop();
        return TRUE;
    }

    function _getArticle($argc, $argv) {
        debug_push($this->_debug_prefix . "_getArticle");

        if ($argc == 0) {        // no params -> gallery index
            debug_add("No parameters, showing gallery index", MIDCOM_LOG_DEBUG);
            debug_pop();
            return TRUE;
        }
        elseif (($argc == 1)
            && ($argv[0] == "rss.xml"))
        {
            // RSS, default count
            if (! $this->_config->get("rss_enable")) {
                debug_add("RSS view disabled by configuration", MIDCOM_LOG_WARN);
                debug_pop();
                return FALSE;
            }
            $this->_latest = $this->_config->get("rss_count");
            $this->_rss = TRUE;
            debug_pop();
            return TRUE;
        }
        elseif (($argc == 1)
            && ($argv[0] == "index"))
        {
            $this->_indexmode = TRUE;
            return TRUE;
        }
        else if ($argc == 1) {   // one param -> photo name
            debug_add("Looking for Photo '$argv[0]'", MIDCOM_LOG_DEBUG);
            return $this->_getPhoto($argv[0]);
        }
        else if ($argc == 2) {    // Two params -> Latest photos and number
            debug_add("Two parameters, showing latest N images", MIDCOM_LOG_DEBUG);
            debug_pop();
            if ($argv[0] == "latest") {
                $this->_latest = $argv[1];
                return TRUE;
            }
            elseif (   $argv[0] == "random"
                    || $argv[0] == "random-thumb")
            {
                $this->_random = $argv[1];
                $this->_random_mode = 'thumbnail';
                return TRUE;
            }
            elseif ($argv[0] == "random-view")
            {
                $this->_random = $argv[1];
                $this->_random_mode = 'viewscale';
                return TRUE;
            }
            elseif ($argv[0] == "rss")
            {
                // RSS, count in argv[1]
                if (! $this->_config->get("rss_enable"))
                {
                    debug_add("RSS view disabled by configuration", MIDCOM_LOG_WARN);
                    debug_pop();
                    return FALSE;
                }
                $this->_latest = $argv[1];
                $this->_rss = TRUE;
                return TRUE;
            }
            elseif ($argv[0] == "notes")
            {
                // Notes view, count in argv[1]
                if (! $this->_enable_notes)
                {
                    debug_add("Flash notes disabled by configuration", MIDCOM_LOG_WARN);
                    debug_pop();
                    return FALSE;
                }

                if ($this->_getPhoto($argv[1]))
                {
                    debug_add('Photo loaded, executing XML note loader handler', MIDCOM_LOG_DEBUG);
                    $notes = $this->_photo->load_notes();
                    if ($notes)
                    {
                        header("Content-type: text/xml; charset=UTF-8");
                        echo $notes;
                        exit();
                    }
                }

            } else {
                return FALSE;
            }
        }
        else {                   // too many params
            $this->errstr = "Too many parameters";
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return FALSE;
        }
    }


    function handle() {
        debug_push($this->_debug_prefix . "handle");

        if ($this->errcode != MIDCOM_ERROK) {
            debug_pop();
            return FALSE;
        }


        // Add a toolbar link into AIS:
        // TODO: Permissions?
        if ($_MIDCOM->auth->admin)
        {
            $node_toolbar =& $_MIDCOM->toolbars->get_node_toolbar();
            $node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$GLOBALS['midcom_config']['midcom_ais_url']}{$this->_topic->id}/data/",
                MIDCOM_TOOLBAR_LABEL => 'Enter AIS',
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }


        // RSS autodetection
        $url_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($this->_config->get("rss_enable"))
        {
            $GLOBALS["midcom"]->add_link_head(
                array(
                    'rel' => 'alternate',
                    'type' => 'application/rss+xml',
                    'title' => 'RSS',
                    'href' => $url_prefix."rss.xml",
                )
            );
        }

        // set page title and nap element
        if ($this->_photo)
        {
            $GLOBALS['midcom']->set_pagetitle($this->_photo->datamanager->data["title"]);
            $GLOBALS['midcom_component_data']['net.siriux.photos']['active_leaf'] = $this->_photo->id;
        }
        else
        {
            $GLOBALS['midcom']->set_pagetitle($this->_topic->extra);
        }

        // special treatment for RSS display
        if ($this->_rss) {
            $GLOBALS["midcom"]->cache->content->content_type("text/xml");
            $GLOBALS["midcom"]->header("Content-type: text/xml");
            $this->show();
            // Now cleanup MidCOM
            $GLOBALS["midcom"]->finish();
            exit();
        }

    if ($this->_random) {
        $expires = $this->_config->get("random_cache_expire");
        if (!$expires) {
            $GLOBALS["midcom"]->cache->content->no_cache();
        } else {
        $GLOBALS["midcom"]->cache->content->expires(time()+$expires);
    }
}
        // noting else to do, the rest is done by show()...

        debug_pop();
        return TRUE;
    }


    function show() {
        debug_push($this->_debug_prefix . "show");

        global $view;
        global $view_total;
        global $view_startfrom;
        global $view_title;
        global $view_thumbs_x;
        global $view_thumbs_y;

        // FIXME: copy config here
        $view_thumbs_x = $this->_config->get("index_cols");
        $view_thumbs_y = $this->_config->get("index_rows");

        $view_title = $this->_topic->extra;

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.siriux.photos");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

        if ($this->_photo)
        {
            // single photo page
            $view = $this->_photo;
            global $view_enable_notes;

            // Check if notes are enabled and photo actually has notes
            if ($this->_enable_notes && $this->_photo->load_notes())
            {
                $view_enable_notes = true;
            }
            else
            {
                $view_enable_notes = false;
            }

            midcom_show_style("show-photo");
        }
        elseif ($this->_latest)
        {
            // show N latest photos

            if ($this->_rss) {
                $server_url = $GLOBALS["midcom"]->get_host_name();
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $attachmentserver = $server_url.$GLOBALS["midcom"]->midgard->self . "midcom-serveattachmentguid-";
                include(MIDCOM_ROOT . "/net/siriux/photos/style/rss_header.php");
            } else {
                midcom_show_style("show-latest-start");
            }

            $articles = mgd_list_topic_articles($this->_topic->id, "reverse created");
            $only_approved = ! $GLOBALS['midcom_config']['show_unapproved_objects'];
            if ($articles) {
                $i = 1;
                while ($articles->fetch() && $i <= $this->_latest)
                {
                    $metadata =& midcom_helper_metadata::retrieve($articles->guid());
                    if (   ($only_approved && ! $metadata->is_approved())
                        || (! $GLOBALS['midcom_config']['show_hidden_objects'] && ! $metadata->is_visible()))
                    {
                        continue;
                    }
                    $view = new siriux_photos_Photo($articles->id);
                    if ($this->_rss)
                    include(MIDCOM_ROOT . "/net/siriux/photos/style/rss_item.php");
                    else
                        midcom_show_style("show-latest-element");
                    $i++;
                }
            }
            if ($this->_rss)
            {
                include(MIDCOM_ROOT . "/net/siriux/photos/style/rss_footer.php");
            }
            else
            {
                midcom_show_style("show-latest-end");
            }
        }
    elseif ($this->_random)
    {
        // show random images

        midcom_show_style("show-latest-start");
            // Instantiate NAP
            $nav = new midcom_helper_nav();

            // List leaves
            $leaves = $nav->list_leaves($nav->get_current_node());
            // Do some Random
        shuffle($leaves);
            $count=0;
            if (is_array($leaves) && count($leaves)>0) {
                foreach ($leaves as $leaf_id) {
                    if ($count >= $this->_random)
                    {
                        break;
                    }
                    $count++;
                    $leaf = $nav->get_leaf($leaf_id);
                    $view = new siriux_photos_Photo($leaf[MIDCOM_NAV_OBJECT]);
                    switch ($this->_random_mode)
                    {
                        case 'viewscale':
                            midcom_show_style("show-latest-element-viewscale");
                break;
                        default:
                        case 'thumbnail':
                            midcom_show_style("show-latest-element");
                            break;
                    }
             }
     }
            midcom_show_style("show-latest-end");
        }
        elseif ($this->_indexmode)
        {
            // show index thumbnail for gallery
            global $view_gallery;
            $view_gallery = $this->_topic;
            $gallery_topics = array();
            $articles = mgd_list_topic_articles_all($this->_topic->id, "reverse created");
            $only_approved = ! $GLOBALS['midcom_config']['show_unapproved_objects'];
            if ($articles) {
                $i = 0;
                while ($articles->fetch() && $i < 1)
                {

                    // Check topics status if needed
                    if (!array_key_exists($articles->topic,$gallery_topics)) {
                        $articles_topic = mgd_get_topic($articles->topic);
                        if ($articles_topic->parameter("midcom","component") == "net.siriux.photos") {
                            $gallery_topics[$articles_topic->id] = TRUE;
                        } else {
                            $gallery_topics[$articles_topic->id] = FALSE;
                        }
                    }

                    if ($gallery_topics[$articles->topic]) {

                        $article = mgd_get_article($articles->id);
                        $metadata =& midcom_helper_metadata::retrieve($article);
                        if (   ($only_approved && ! $metadata->is_approved())
                            || (! $GLOBALS['midcom_config']['show_hidden_objects'] && ! $metadata->is_visible()))
                        {
                            continue;
                        }
                        $view = new siriux_photos_Photo($article->id);
                        midcom_show_style("show-indexmode-element");
                        $i++;
                    }
                }
            }
        }
        else
        {
            // gallery index
            $view_total = 0;

            // Instantiate NAP
            $nav = new midcom_helper_nav();

            // List subgalleries
            $nodes = $nav->list_nodes($nav->get_current_node());
            $view_galleries = array();
            if ($this->_config->get("list_subgalleries") && count($nodes) > 0)
            {
                foreach ($nodes as $node_id)
                {
                    $node = $nav->get_node($node_id);
                    if ($node[MIDCOM_NAV_COMPONENT] == "net.siriux.photos")
                    {
                        $view_galleries[$node[MIDCOM_NAV_URL]] = $node[MIDCOM_NAV_NAME];
                        $view_total++;
                    }
                }
            }

            // List photos in this gallery, use NAP for speed.
            $leaves = $nav->list_leaves($nav->get_current_node());

            // Update total item count
            $view_total += count($leaves);

            if ($view_total > 0)
            {
                $numpics = ($view_thumbs_x * $view_thumbs_y);
                if (isset($_GET) && array_key_exists("startfrom", $_GET))
                {
                    $view_startfrom = $_GET["startfrom"];
                }
                else
                {
                    $view_startfrom = 0;
                }
                if ($view_startfrom % $numpics > 0)
                {
                    $view_startfrom -= ($view_startfrom % $numpics);
                }

                midcom_show_style("show-index-start");
                $count = 0;

                if (count($view_galleries) > 0)
                {
                    $nav = new midcom_helper_nav();
                    $node = $nav->get_node($nav->get_current_node());
                    foreach ($view_galleries as $name => $title)
                    {
                        if (($count >= $view_startfrom)
                            && ($count < $view_startfrom + $numpics))
                        {
                            $GLOBALS["view_gallery_name"] = $name;
                            $GLOBALS["view_gallery_title"] = $title;
                            $GLOBALS['view_gallery_dl_url'] = "{$node[MIDCOM_NAV_RELATIVEURL]}{$name}index";
                            midcom_show_style("show-index-gallery");
                        }
                        $count++;
                    }
                }

                foreach ($leaves as $leaf_id)
                {
                    // We haven't reached the first element.
                    if ($count < $view_startfrom)
                    {
                        $count++;
                        continue;
                    }

                    // Check wether we have run past the end of the display area, break out of the
                    // loop then
                    if ($count >= ($view_startfrom + $numpics))
                    {
                        break;
                    }

                    // We are in the area to display
                    $leaf = $nav->get_leaf($leaf_id);
                    $view = new siriux_photos_Photo($leaf[MIDCOM_NAV_OBJECT]);
                    midcom_show_style("show-index-element");

                    $count++;
                }

                midcom_show_style("show-index-end");
            }
            else {
                midcom_show_style("show-index-empty");
            }

        }

        debug_pop();
        return TRUE;
    }


    function get_metadata() {
        return array (
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR  => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITED  => 0
        );
    }

} // viewer

?>
