<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for net.nemein.feedcollector
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_handler_index  extends midcom_baseclasses_components_handler 
{

    /**
     * Simple default constructor.
     */
    function net_nemein_feedcollector_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
         $this->_content_topic =& $this->_request_data['content_topic'];
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {

        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $_MIDCOM->set_pagetitle($this->_content_topic->extra);
        
        $topics = array();
        
        $qb_feedtopics = net_nemein_feedcollector_topic_dba::new_query_builder();
        $qb_feedtopics->add_constraint('node', '=', (int)$this->_content_topic->id);
        $qb_feedtopics->add_order($this->_config->get('sort_order'));
        $feedtopics = $qb_feedtopics->execute();
        foreach($feedtopics as $feedtopic)
        {
            $this->topics[$feedtopic->guid]['object'] = $feedtopic;
            $qb_news = midcom_db_article::new_query_builder();
            $qb_news->add_constraint('topic','=', (int)$feedtopic->feedtopic);
            if($feedtopic->categories != '||' && $feedtopic->categories != '')
            {
                $categories = explode('|', $feedtopic->categories);
                foreach($categories as $category)
                {
                    $category = str_replace('|', '', $category);
                    $qb_news->add_constraint('extra1', 'LIKE', "%|{$category}|%");
                }
            }
            $qb_news->set_limit($this->_config->get('articles_count_index'));
            $items = $qb_news->execute();
            $this->topics[$feedtopic->guid]['items'] = $items;
        }
        
        
        return true;
    }
    
    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index-header');
        // Counts topics
        $this->_request_data['counters']['topic'] = 0;
        // Counts items under one topic
        $this->_request_data['counters']['topic_item'] = 0;
        // Counts overall items
        $this->_request_data['counters']['items'] = 0;
        foreach($this->topics as $topic)
        {
            $this->_request_data['counters']['topic']++;
            $this->_request_data['topic'] = $topic;
            midcom_show_style('topic-header');
            foreach($topic['items'] as $item)
            {
                $this->_request_data['counters']['topic_item']++;
                $this->_request_data['counters']['items']++;
                $this->_request_data['item'] = $item;
                midcom_show_style('topic-item');
            }
            midcom_show_style('topic-footer');
            $this->_request_data['counters']['topic_item'] = 0;
        }
        midcom_show_style('index-footer');
    }
    

    function _update_breadcrumb_line()
    {
        $tmp = Array();

/*        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );*/

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
