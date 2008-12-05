<?php
/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * News item display controller
 *
 * @package net_nemein_news
 */
class net_nemein_news_controllers_show
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_article($route_id, &$data, $args)
    {
        $topic_guid = $this->configuration->get('news_topic');
        if (!$topic_guid)
        {
            throw new midcom_exception_notfound("No news topic defined");
        }
        $data['topic'] = new midgard_topic($topic_guid);
        
        $qb = midgard_article::new_query_builder();
        $qb->add_constraint('topic', '=', $data['topic']->id);
        $qb->add_constraint('name', '=', $args['name']);        
        $articles = $qb->execute();        
        if (count($articles) == 0)
        {
            throw new midcom_exception_notfound("Article {$args['name']} not found.");
        }
        $data['article'] = $articles[0];
    }
}
?>