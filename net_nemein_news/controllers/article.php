<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Page management controller
 *
 * @package midcom_core
 */
class net_nemein_news_controllers_article
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    private function load_article(&$data, $args)
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
    
    public function action_show($route_id, &$data, $args)
    {
        $this->load_article($data, $args);
    }
    
    public function action_edit($route_id, &$data, $args)
    {
        $this->load_article($data, $args);

        $_MIDCOM->authorization->require_do('midgard:update', $data['article']);

        if (isset($_POST['save']))
        {
            $data['article']->title = $_POST['title'];
            $data['article']->content = $_POST['content'];
            $data['article']->update();
            
            header('Location: ' . $_MIDCOM->dispatcher->generate_url('show', array('name' => $data['article']->name)));
            exit();
        }
    }
}
?>