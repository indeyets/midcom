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
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 *
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_handler_bestof extends midcom_baseclasses_components_handler
{
    private $articles = array();
    private $articles_scores = array();
    private $nodes = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_socialnews_handler_bestof()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Load the paged query builder
     */
    function _on_initialize()
    {
        $_MIDCOM->load_library('org.openpsa.qbpager');
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
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        // Find items matching our criteria
        $qb = new org_openpsa_qbpager('org_maemo_socialnews_score_article_dba', 'org_maemo_socialnews_best');
        $data['qb'] =& $qb;
        $qb->add_order('score', 'DESC');
        $qb->results_per_page = (int) $this->_config->get('bestof_items');
        $scores = $qb->execute();
        foreach ($scores as $score)
        {
            $article = new midcom_db_article($score->article);

            if (   empty($article)
                || empty($article->metadata->published))
            {
                // Skip this one
                continue;
            }

            $this->articles[$article->guid] = $article;
            $this->articles_scores[$article->guid] = $score->score;
        }

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

            if (empty($article->url))
            {
                // Local item
                $article->url = $_MIDCOM->permalinks->create_permalink($article->guid);
            }

            $this->articles[$guid] = $article;
        }

        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.maemo.socialnews/social.css",
            )
        );

        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');

        $title = $this->_config->get('socialnews_title');
        if (empty($title))
        {
            $title = $this->_topic->extra;
        }

        $data['view_title'] = sprintf($this->_l10n->get('best of %s'), $title);
        $_MIDCOM->set_pagetitle($data['view_title']);

        $this->_component_data['active_leaf'] = "{$this->_topic->id}_BEST";

        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('bestof_header');
        foreach ($this->articles as $article)
        {
            // TODO: Datamanager
            $data['article'] = $article;
            $data['node'] = $this->get_node($article->topic);
            $data['score'] = $this->articles_scores[$article->guid];
            midcom_show_style('bestof_item');
        }
        midcom_show_style('bestof_footer');
    }
}
?>
