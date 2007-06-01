<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog Feed handler
 *
 * Prints the various supported feeds using the FeedCreator library.
 *
 * @package net.nehmer.blog
 */

class net_nehmer_blog_handler_feed extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The articles to display
     *
     * @var Array
     * @access private
     */
    var $_articles = null;

    /**
     * The datamanager for the currently displayed article.
     *
     * @var midcom_helper_datamanger2_datamanager
     */
    var $_datamanager = null;

    /**
     * The de.bitfolge.feedcreator instance used.
     *
     * @var UniversalFeedCreator
     * @access private
     */
    var $_feed = null;

    /**
     * Simple default constructor.
     */
    function net_nehmer_blog_handler_feed()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * Shows the autoindex list. Nothing to do in the handle phase except setting last modified
     * dates.
     */
    function _handler_feed ($handler_id, $args, &$data)
    {
        $_MIDCOM->load_library('de.bitfolge.feedcreator');
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $_MIDCOM->skip_page_style = true;

        // Prepare control structures
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        // Get the articles,
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);

        // TODO: 1.7 support is only temporary, I'd rather drop it as soon as 1.8 goes somehting like RC.
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            $qb->add_order('metadata.published', 'DESC');
        }
        else
        {
            $qb->add_order('created', 'DESC');
        }

        if ($handler_id == 'feed-category-rss2')
        {
            if (!in_array($args[0], $this->_request_data['categories']))
            {
                // TODO: In some cases we might want to allow displaying by custom categories
                return false;
            }

            // TODO: Check for ".xml" suffix
            $this->_request_data['category'] = $args[0];

            $qb->add_constraint('extra1', 'LIKE', "%|{$this->_request_data['category']}|%");
        }

        $qb->set_limit($this->_config->get('rss_count'));
        $this->_articles = $qb->execute_unchecked();

        // Prepare the feed (this will also validate the handler_id)
        $this->_create_feed($handler_id);

        // Add each article now.
        if ($this->_articles)
        {
            foreach ($this->_articles as $article)
            {
                $this->_add_article_to_feed($article);
            }
        }

        $_MIDCOM->set_26_request_metadata(net_nehmer_blog_viewer::get_last_modified($this->_topic, $this->_content_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Adds the given article to the feed.
     *
     * @param midcom_db_article $article The article to add.
     */
    function _add_article_to_feed($article)
    {
        $this->_datamanager->autoset_storage($article);

        $item = new FeedItem();
        $author_user = $_MIDCOM->auth->get_user($article->author);
        if ($author_user)
        {
            $author = $author_user->get_storage();
            
            if (empty($author->email))
            {
                $author->email = "webmaster@{$_SERVER['SERVER_NAME']}";
            }
            
            $item->author = trim("{$author->name} <{$author->email}>");
        }

        $item->title = $article->title;
        $arg = $article->name ? $article->name : $article->guid;
        
        if (   $this->_config->get('link_to_external_url')
            && !empty($article->url))
        {
            $item->link = $article->url;
        }
        else
        {
            if ($this->_config->get('view_in_url'))
            {
                $item->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "view/{$arg}.html";
            }
            else
            {
                $item->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$arg}.html";
            }
        }
        
        $item->guid = $_MIDCOM->permalinks->create_permalink($article->guid);

        // TODO: 1.7 support is only temporary, I'd rather drop it as soon as 1.8 goes somehting like RC.
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            $item->date = $article->metadata->published;
        }
        else
        {
            $item->date = $article->created;
        }

        $item->description = '';

        if ($article->abstract != '')
        {
            $item->description .= '<div class="abstract">' .  $this->_datamanager->types['abstract']->convert_to_html() . '</div>';
        }

        if (   array_key_exists('image', $this->_datamanager->types)
            && $this->_config->get('rss_use_image'))
        {
            $item->description .= "\n<div class=\"image\">" . $this->_datamanager->types['image']->convert_to_html() .'</div>';
        }

        if ($this->_config->get('rss_use_content'))
        {
            $item->description .= "\n" . $this->_datamanager->types['content']->convert_to_html();
        }
        
        // Replace links
        $item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

        // TODO: Figure out the RSS multi-category support for real
        $categories = explode('|', $article->extra1);
        if (count($categories) > 1)
        {
            $item->category = $categories[1];
        }

        if ($GLOBALS['midcom_config']['positioning_enable'])
        {
            // Attach coordinates to the item if available
            $object_position = new org_routamc_positioning_object($article);
            $coordinates = $object_position->get_coordinates();
            if (!is_null($coordinates))
            {
                $item->lat = $coordinates['latitude'];
                $item->long = $coordinates['longitude'];
            }
        }

        $this->_feed->addItem($item);

    }

    /**
     * Creates the Feedcreator instance.
     */
    function _create_feed($handler_id)
    {
        $this->_feed = new UniversalFeedCreator();
        if ($this->_config->get('rss_title'))
        {
            $this->_feed->title = $this->_config->get('rss_title');
        }
        else
        {
            $this->_feed->title = $this->_topic->extra;
        }
        $this->_feed->description = $this->_config->get('rss_description');
        $this->_feed->language = $this->_config->get('rss_language');
        $this->_feed->editor = $this->_config->get('rss_webmaster');
        $this->_feed->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_feed->cssStyleSheet = false;

        switch($handler_id)
        {
            case 'feed-rss2':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss.xml";
                break;

            case 'feed-rss1':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss1.xml";
                break;

            case 'feed-rss091':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss091.xml";
                break;

            case 'feed-atom':
                $this->_feed->syndicationURL = "{$this->_feed->link}atom.xml";
                break;

            case 'feed-category-rss2':
                $this->_feed->title = sprintf($this->_request_data['l10n']->get('%s category %s'), $this->_feed->title, $this->_request_data['category']);
                $this->_feed->syndicationURL = "{$this->_feed->link}feeds/category/{$this->_request_data['category']}";
                break;

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The feed handler {$handler_id} is unsupported");
                // This will exit.
        }

    }

    /**
     * Displays the feed
     */
    function _show_feed($handler_id, &$data)
    {
        switch($handler_id)
        {
            case 'feed-rss2':
            case 'feed-category-rss2':
                echo $this->_feed->createFeed('RSS2.0');
                break;

            case 'feed-rss1':
                echo $this->_feed->createFeed('RSS1.0');
                break;

            case 'feed-rss091':
                echo $this->_feed->createFeed('RSS0.91');
                break;

            case 'feed-atom':
                echo $this->_feed->createFeed('ATOM');
                break;
        }
    }


    /**
     * Shows a simple available-feeds page.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_component_data['active_leaf'] = NET_NEHMER_BLOG_LEAFID_FEEDS;
        $_MIDCOM->set_26_request_metadata($this->_topic->metadata->revised, $this->_topic->guid);
        return true;
    }

    /**
     * Displays the feeds page
     */
    function _show_index ($handler_id, &$data)
    {
        midcom_show_style('feeds');
    }


}

?>
