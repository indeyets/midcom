<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.feedcollector
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
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {

        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['permalinks'] = new midcom_services_permalinks();
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $_MIDCOM->set_pagetitle($this->_content_topic->extra);

        $topics = array();

        $qb_feedtopics = net_nemein_feedcollector_topic_dba::new_query_builder();
        $qb_feedtopics->add_constraint('node', '=', (int)$this->_content_topic->id);
        $qb_feedtopics->add_order($this->_config->get('sort_order'));
        $feedtopics = $qb_feedtopics->execute();
        if(count($feedtopics) > 0)
        {
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
        }


        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        if(isset($this->topics) && count($this->topics) > 0)
        {
            // Counts topics
            $this->_request_data['counters']['topic'] = 0;
            $this->_request_data['counters']['topics'] = count($this->topics);
            // Counts items under one topic
            $this->_request_data['counters']['topic_item'] = 0;
            $this->_request_data['counters']['topic_items'] = 0;
            // Counts overall items
            $this->_request_data['counters']['items'] = 0;


            // We only do this to get overall count of items
            foreach($this->topics as $topic)
            {
                $this->_request_data['counters']['items'] .= count($topic['items']);
            }

            midcom_show_style('index-header');

            foreach($this->topics as $topic)
            {
                $this->_request_data['counters']['topic_items'] = count($topic['items']);
                $this->_request_data['counters']['topic']++;
                $this->_request_data['feedtopic'] = $topic;
                $this->_request_data['topic'] = new midcom_db_topic($topic['object']->feedtopic);
                midcom_show_style('topic-header');
                if(count($topic['items']) > 0)
                {
                    foreach($topic['items'] as $item)
                    {
                        $this->_request_data['counters']['topic_item']++;
                        $this->_request_data['item'] = $item;
                        midcom_show_style('topic-item');
                    }
                }
                else
                {
                    midcom_show_style('index-no-items');
                }
                midcom_show_style('topic-footer');
                $this->_request_data['counters']['topic_item'] = 0;
                $this->_request_data['counters']['topic_items'] = 0;
            }

            midcom_show_style('index-footer');

        }
        else
        {
            midcom_show_style('index-no-topics');
        }
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
