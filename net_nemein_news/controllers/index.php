<?php
/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * News listing controller
 *
 * @package net_nemein_news
 */
class net_nemein_news_controllers_index
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    /**
     * List latest news items and pass them on to the template
     */
    public function action_latest($route_id, &$data, $args)
    {
        $topic_guid = $this->configuration->get('news_topic');
        if (!$topic_guid)
        {
            throw new midcom_exception_notfound("No news topic defined");
        }
        $data['topic'] = new midgard_topic($topic_guid);
        
        $_MIDCOM->componentloader->load('org_openpsa_qbpager');

        $qb = new org_openpsa_qbpager_pager('midgard_article');
        $qb->add_constraint('topic', '=', $data['topic']->id);
        $qb->add_order('metadata.published', 'DESC');

        if ($route_id == 'latest')
        {
            if (!is_numeric($args['number']))
            {
                throw new midcom_exception_notfound("Number expected as argument"); 
            }
            $qb->results_per_page = (int) $args['number'];
        }
        else
        {
            $qb->results_per_page = (int) $this->configuration->get('index_show_articles');
        }
        
        $data['news'] = array();
        
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            if (   !$article->url
                || !$this->configuration->get('link_articles_to_external_url'))
            {
                $article->url = $_MIDCOM->dispatcher->generate_url('show', array('name' => $article->name));
            }
            $data['news'][] = $article;
        }
        
        $data['previousnext'] = $qb->get_previousnext();
    }
}
?>