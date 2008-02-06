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
class org_maemo_socialnews_handler_latest  extends midcom_baseclasses_components_handler
{
    private $nodes = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_socialnews_handler_lates()
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

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_latest($handler_id, $args, &$data)
    {
        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.maemo.socialnews/social.css",
            )
        );

        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_latest($handler_id, &$data)
    {
        $data['node_title'] = $this->_config->get('socialnews_title');
        if (empty($data['node_title']))
        {
            $data['node_title'] = $this->_topic->extra;
        }
        midcom_show_style('index_latest_header');
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit((int) $this->_config->get('frontpage_show_area_latest_items'));
        $articles = $qb->execute();
        $date = '';
        foreach ($articles as $article)
        {
            $date_id = date('Y-m-d', $article->metadata->published);
            if ($date != $date_id)
            {
                $data['date'] = $article->metadata->published;
                midcom_show_style('index_latest_date');
            }
            $date = $date_id;

            if (empty($article->url))
            {
                // Local item
                $article->url = $_MIDCOM->permalinks->create_permalink($article->guid);
            }

            // TODO: Datamanager
            $data['article'] = $article;
            $data['node'] = $this->get_node($article->topic);
            midcom_show_style('index_latest_item');
        }
        midcom_show_style('index_latest_footer');
    }
}
?>