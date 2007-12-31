<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for org.maemo.socialnews
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_handler_index  extends midcom_baseclasses_components_handler
{
    private $articles = array();
    private $articles_scores = array();
    private $articles_scores_initial = array();
    private $nodes = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_socialnews_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    private function get_node($node_id)
    {
        static $nap = null;
        if (is_null($nap))
        {
            $nap = new midcom_helper_nav();
        }

        if (!isset($this->nodes[$node_id]))
        {
            $this->nodes[$node_id] = $nap->get_node($node_id);
        }

        return $this->nodes[$node_id];
    }

    private function get_initial_score($id)
    {
        $score = 0;
        $sc = org_maemo_socialnews_score_article_dba::new_collector('article', $id);
        $sc->add_value_property('score');
        $sc->execute();
        $score_caches = $sc->list_keys();
        foreach ($score_caches as $guid => $cache)
        {
            $score = $sc->get_subkey($guid, 'score');
        }
        return $score;
    }

    private function count_age($score, $timestamp)
    {
        $article_age = round((time() - $timestamp) / 3600);
        return $score - ($article_age * $this->_config->get('frontpage_score_hour_penalty'));
    }

    private function seek_articles($limit)
    {
        // Get list of all articles inside the hard time limit
        // FIXME: Use Midgard_Collector here once it supports metadata properties as value properties
        $articles_scores = array();
        $articles_by_guid = array();
        $articles_by_url = array();
        $qb = midcom_db_article::new_query_builder();
        $cutoff_date = gmdate('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - $this->_config->get('frontpage_limit_days'), date('Y')));
        $qb->add_constraint('metadata.published', '>', $cutoff_date);
        $qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            $this->articles_scores_initial[$article->guid] = $this->get_initial_score($article->id);
            if ($this->articles_scores_initial[$article->guid] < $this->_config->get('frontpage_score_start'))
            {
                continue;
            }

            // Ensure all items have links
            if (empty($article->url))
            {
                // Local item
                $article->url = $_MIDCOM->permalinks->create_permalink($article->guid);
            }

            if (isset($articles_by_url[$article->url]))
            {
                // We already have item with this URL, skip
                continue;
            }

            $articles_by_url[$article->url] = $article->guid;
            $articles_by_guid[$article->guid] = $article;
            $this->articles_scores[$article->guid] = $this->count_age($this->articles_scores_initial[$article->guid], $article->metadata->published);
        }

        arsort($this->articles_scores);

        $found = 0;
        foreach ($this->articles_scores as $guid => $score)
        {
            if ($found >= $limit)
            {
                break;
            }


            $this->articles[$guid] = $articles_by_guid[$guid];
            $found++;
        }

    }

    private function generate_caption($data, $getCnt)
    {
        if (strlen($data) == 0)
        {
            return false;
        }

        $data = preg_replace('/<\/?(p|br)([^>]*)>/', ' ', $data);
        $data = strip_tags($data,'<a>');

        if (strlen($data) <= $getCnt)
        {
            return $data;
        }

        $ret='';
        $cnt=0;
        $inTag=FALSE;
        $chars=preg_split('//',$data);

        foreach($chars as $k => $char)
        {
            if ($char == '<')
            {
                $inTag = false;
            }
            if (   $char == '>'
                && $inTag)
            {
                $inTag = false;
            }

            if (!$inTag)
            {
                $cnt++;
            }

            if (   !$inTag
                && ($cnt >= $getCnt)
                && preg_match('/\s/', $char))
            {
                $ret .= '...';
                break;
            }
            else
            {
                $ret .= $char;
            }
        }
        return $ret;
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        // Find items matching our criteria
        $limit = (int) $this->_config->get('frontpage_show_main_items') + $this->_config->get('frontpage_show_secondary_items');

        $this->seek_articles($limit);

        $revised = $this->_topic->metadata->revised;

        // Normalize articles
        foreach ($this->articles as $guid => $article)
        {
            if (empty($article->abstract))
            {
                $article->abstract = $this->generate_caption($article->content, $this->_config->get('frontpage_show_abstract_length'));
            }
            else
            {
                $article->abstract = $this->generate_caption($article->abstract, $this->_config->get('frontpage_show_abstract_length'));
            }

            $this->articles[$guid] = $article;

            if ($article->metadata->revised > $revised)
            {
                $revised = $article->metadata->revised;
            }
        }

        $_MIDCOM->set_26_request_metadata($revised, $this->_topic->guid);

        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.maemo.socialnews/social.css",
            )
        );

        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');

        $data['node_title'] = $this->_config->get('socialnews_title');
        if (empty($data['node_title']))
        {
            $data['node_title'] = $this->_topic->extra;
        }

        if ($handler_id == 'rss20_items')
        {
            $_MIDCOM->load_library('de.bitfolge.feedcreator');
            $_MIDCOM->cache->content->content_type('text/xml');
            $_MIDCOM->header('Content-type: text/xml; charset=UTF-8');
            $_MIDCOM->skip_page_style = true;
            $data['feedcreator'] = new UniversalFeedCreator();
            $data['feedcreator']->title = $data['node_title'];
            $data['feedcreator']->link = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $data['feedcreator']->cssStyleSheet = false;
            $data['feedcreator']->syndicationURL = "{$data['feedcreator']->link}rss.xml";
        }

        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        if ($handler_id == 'rss20_items')
        {
            $this->_show_rss_items($handler_id, &$data);
            return;
        }
        midcom_show_style('index_header');

        $main_items = array_slice($this->articles, 0, (int) $this->_config->get('frontpage_show_main_items'));
        $secondary_items = array_slice($this->articles, (int) $this->_config->get('frontpage_show_main_items'));

        midcom_show_style('index_main_header');
        foreach ($main_items as $article)
        {
            // TODO: Datamanager
            $data['article'] = $article;
            $data['node'] = $this->get_node($article->topic);
            $data['score'] = $this->articles_scores[$article->guid];
            $data['score_initial'] = $this->articles_scores_initial[$article->guid];
            midcom_show_style('index_main_item');
        }
        midcom_show_style('index_main_footer');

        if ($handler_id != 'main')
        {
            midcom_show_style('index_secondary_header');
            foreach ($secondary_items as $article)
            {
                // TODO: Datamanager
                $data['article'] = $article;
                $data['node'] = $this->get_node($article->topic);
                $data['score'] = $this->articles_scores[$article->guid];
                $data['score_initial'] = $this->articles_scores_initial[$article->guid];
                midcom_show_style('index_secondary_item');
            }
            midcom_show_style('index_secondary_footer');
        }
        midcom_show_style('index_footer');
    }

    /**
     * Displays the feed
     */
    function _show_rss_items($handler_id, &$data)
    {
        // Add each article now.
        if ($this->articles)
        {
            foreach ($this->articles as $article)
            {
                $data['article'] =& $article;
                midcom_show_style('feed-item');
            }
        }
        echo $data['feedcreator']->createFeed('RSS2.0');
    }
}
?>
