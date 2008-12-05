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
        $data['topic'] = new midgard_topic($this->configuration->get('news_topic'));

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