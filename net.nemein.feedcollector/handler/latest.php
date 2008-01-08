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
 *
 * @see midcom_baseclasses_components_handler
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_handler_latest  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function net_nemein_feedcollector_handler_latest()
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
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_latest ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['permalinks'] = new midcom_services_permalinks();
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $topics = array();

        $qb_feedtopics = net_nemein_feedcollector_topic_dba::new_query_builder();
        $qb_feedtopics->add_constraint('node', '=', (int)$this->_content_topic->id);
        $qb_feedtopics->add_order($this->_config->get('sort_order'));
        $feedtopics = $qb_feedtopics->execute();

        if(count($feedtopics) > 0)
        {
            $qb_news = midcom_db_article::new_query_builder();
            $qb_news->begin_group('OR');
            foreach($feedtopics as $feedtopic)
            {
                $qb_news->begin_group('AND');
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
                $qb_news->end_group();
            }
            $qb_news->end_group();
            if($handler_id == 'latest')
            {
                $qb_news->set_limit($this->_config->get('articles_count_index'));
            }
            else
            {
                $qb_news->set_limit($args[0]);
            }
            $qb_news->add_order('metadata.published', 'DESC');
            $items = $qb_news->execute();
            $this->items = $items;
        }


        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_latest($handler_id, &$data)
    {
        if(isset($this->items) && is_array($this->items) && count($this->items) > 0 )
        {
            $this->_request_data['item_counter'] = 0;
            $this->_request_data['items'] = count($this->items);

            midcom_show_style('latest-header');

            foreach($this->items as $item)
            {
                $this->_request_data['item'] = $item;
                midcom_show_style('latest-item');
            }

            midcom_show_style('latest-footer');

        }
        else
        {
            midcom_show_style('latest-no-items');
        }
    }


    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('latest'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
