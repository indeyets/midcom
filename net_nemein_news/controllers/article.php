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
class net_nemein_news_controllers_article extends midcom_core_controllers_baseclasses_crud
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
        $this->data['topic'] = new midgard_topic($topic_guid);
        
        $qb = new midgard_query_builder('midgard_article');
        $qb->add_constraint('topic', '=', $this->data['topic']->id);
        $qb->add_constraint('name', '=', $args['name']);        
        $articles = $qb->execute();        
        if (count($articles) == 0)
        {
            throw new midcom_exception_notfound("Article {$args['name']} not found.");
        }
        $this->object = $articles[0];
    }
    
    public function prepare_new_object($args)
    {
        $topic_guid = $this->configuration->get('news_topic');
        if (!$topic_guid)
        {
            throw new midcom_exception_notfound("No news topic defined");
        }
        $this->data['topic'] = new midgard_topic($topic_guid);
        $this->data['parent'] =& $this->data['topic'];
        
        $this->object = new midgard_article();
        $this->object->topic = $this->data['topic']->id;
    }
    
    public function get_url_read()
    {
        return $_MIDCOM->dispatcher->generate_url('read', array('name' => $this->object->name));
    }

    public function get_url_update()
    {
        return $_MIDCOM->dispatcher->generate_url('update', array('name' => $this->object->name));
    }
}
?>