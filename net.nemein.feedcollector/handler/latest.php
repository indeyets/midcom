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
    var $items = array();
    
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
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
        $data['view_title'] = $this->_topic->extra;
        $_MIDCOM->set_pagetitle($data['view_title']);

        $topics = array();

        $qb_feedtopics = net_nemein_feedcollector_topic_dba::new_query_builder();
        $qb_feedtopics->add_constraint('node', '=', (int) $this->_content_topic->id);
        $qb_feedtopics->add_order($this->_config->get('sort_order'));
        $feedtopics = $qb_feedtopics->execute();

        if (count($feedtopics) > 0)
        {
            $items_temp = array();
            if($handler_id == 'latest')
            {
                $limit = $this->_config->get('articles_count_index');
            }
            else
            {
                $limit = $args[0];
            }
            foreach ($feedtopics as $feedtopic)
            {
                $target_topic = new midcom_db_topic($feedtopic->feedtopic);
                net_nemein_feedcollector_viewer::_enter_language($target_topic);
                $qb_news =& net_nemein_feedcollector_viewer::get_article_qb($feedtopic, $target_topic, $this->_config);
                if (!$qb_news)
                {
                    continue;
                }
                $qb_news->add_order('metadata.published', 'DESC');
                $qb_news->set_limit($limit);
                $result = $qb_news->execute();
                net_nemein_feedcollector_viewer::_exit_language();
                if (!empty($result))
                {
                    $items_temp = array_merge($items_temp, $result);
                }
                unset($result);
            }
        }

        usort($items_temp, array($this, 'sort_items'));
        while(count($items_temp) > $limit)
        {
            array_pop($items_temp);
        }
        $this->items = $items_temp;

        return true;
    }

    function sort_items($a, $b)
    {
        $a_val =& $a->metadata->published;
        $b_val =& $b->metadata->published;
        if ($a_val > $b_val)
        {
            return -1;
        }
        if ($a_val < $b_val)
        {
            return 1;
        }
        return 0;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_latest($handler_id, &$data)
    {
        if (   is_array($this->items) 
            && count($this->items) > 0)
        {
            $this->_request_data['item_counter'] = 0;
            $this->_request_data['items'] = count($this->items);

            midcom_show_style('latest-header');

            foreach ($this->items as $item)
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