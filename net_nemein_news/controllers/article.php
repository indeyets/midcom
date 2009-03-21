<?php
/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Article management controller
 *
 * @package net_nemein_news
 */
class net_nemein_news_controllers_article extends midcom_core_controllers_baseclasses_manage
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function load_object($args)
    {
        $topic_guid = $this->configuration->get('news_topic');
        if (!$topic_guid)
        {
            throw new midcom_exception_notfound("No news topic defined");
        }
        $data['topic'] = new midgard_topic($topic_guid);
        
        $qb = new midgard_query_builder('midgard_article');
        $qb->add_constraint('topic', '=', $data['topic']->id);
        $qb->add_constraint('name', '=', $args['name']);        
        $articles = $qb->execute();        
        if (count($articles) == 0)
        {
            throw new midcom_exception_notfound("Article {$args['name']} not found.");
        }
        $this->object = $articles[0];
    }
    
    public function prepare_new_object(&$data, $args)
    {
        $topic_guid = $this->configuration->get('news_topic');
        if (!$topic_guid)
        {
            throw new midcom_exception_notfound("No news topic defined");
        }
        $data['topic'] = new midgard_topic($topic_guid);
        $data['parent'] =& $data['topic'];
        
        $this->object = new midgard_article();
        $this->object->topic = $data['topic']->id;
    }
    
    public function get_url_show()
    {
        return $_MIDCOM->dispatcher->generate_url('show', array('name' => $this->object->name));
    }

    public function get_url_edit()
    {
        return $_MIDCOM->dispatcher->generate_url('edit', array('name' => $this->object->name));
    }
}
?>