<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: feed.php 11821 2007-08-29 15:08:28Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog Feed handler
 *
 * Prints the various supported feeds using the FeedCreator library.
 *
 * @package net.nemein.calendar
 */

class net_nemein_calendar_handler_feed extends midcom_baseclasses_components_handler
{
    /**
     * The events to display
     *
     * @var Array
     * @access private
     */
    var $_events = null;

    /**
     * GET field filters set for this view
     *
     * @var array
     * @access private
     */
    var $_filters = Array();

    /**
     * The datamanager for the currently displayed event.
     *
     * @var midcom_helper_datamanager2_datamanager
     */
    var $_datamanager = null;

    /**
     * The de.bitfolge.feedcreator instance used.
     *
     * @var UniversalFeedCreator
     * @access private
     */
    var $_feed = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_feed()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    function _load_filters()
    {
        if ($this->_config->get('enable_filters'))
        {
            if (   array_key_exists('net_nemein_calendar_filter', $_REQUEST)
                && is_array($_REQUEST['net_nemein_calendar_filter']))
            {
                $this->_filters = $_REQUEST['net_nemein_calendar_filter'];
            }
        }
    }

    /**
     * Shows the autoindex list. Nothing to do in the handle phase except setting last modified
     * dates.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_rss($handler_id, $args, &$data)
    {
        $_MIDCOM->load_library('de.bitfolge.feedcreator');
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $_MIDCOM->skip_page_style = true;

        // Prepare control structures
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($data['schemadb']);

        $this->_load_filters();

        // Filter the upcoming list by a type if required
        $type_filter = $this->_config->get('type_filter_upcoming');

        $qb = net_nemein_calendar_event_dba::new_query_builder();

        // Add root event constraints
        if ($this->_config->get('list_from_master'))
        {
            $qb->add_constraint('up', 'INTREE', $data['master_event']);
        }
        else
        {
            $qb->add_constraint('node', '=', $data['content_topic']->id);
        }

        // Add filtering constraints
        if (!is_null($type_filter))
        {
            $qb->add_constraint('type', '=', (int) $type_filter);
        }
        foreach ($this->_filters as $field => $filter)
        {
            $qb->add_constraint($field, '=', $filter);
        }
        // QnD category filter (only in 1.8)
        if (   isset($_REQUEST['net_nemein_calendar_category'])
            && class_exists('midgard_query_builder'))
        {
            $qb->begin_group('AND');
                $qb->add_constraint('parameter.domain', '=', 'net.nemein.calendar');
                $qb->add_constraint('parameter.name', '=', 'categories');
                $qb->add_constraint('parameter.value', 'LIKE', "%|{$_REQUEST['net_nemein_calendar_category']}|%");
            $qb->end_group();
        }

        // Show only events that haven't ended
        $qb->add_constraint('end', '>', time());

        $qb->set_limit($this->_config->get('rss_count'));

        $qb->add_order('closeregistration');

        $this->_events = $qb->execute();

        // Prepare the feed (this will also validate the handler_id)
        $this->_create_feed($handler_id);

        return true;
    }

    /**
     * Creates the Feedcreator instance.
     */
    function _create_feed($handler_id)
    {
        $this->_feed = new UniversalFeedCreator();
        $this->_feed->title = $this->_topic->extra;
        $this->_feed->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_feed->cssStyleSheet = false;

        switch($handler_id)
        {
            case 'feed-rss2':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss.xml";
                break;

            case 'feed-category-rss2':
                $this->_feed->title = sprintf($this->_request_data['l10n']->get('%s category %s'), $this->_feed->title, $this->_request_data['category']);
                $this->_feed->syndicationURL = "{$this->_feed->link}feeds/category/{$this->_request_data['category']}";
                break;

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The feed handler {$handler_id} is unsupported");
                // This will exit.
        }

    }

    /**
     * Displays the feed
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_rss($handler_id, &$data)
    {
        $data['feedcreator'] =& $this->_feed;

        // Add each event now.
        if ($this->_events)
        {
            foreach ($this->_events as $event)
            {
                $this->_datamanager->autoset_storage($event);
                $data['event'] =& $event;
                $data['datamanager'] =& $this->_datamanager;
                midcom_show_style('feeds-item');
            }
        }

        echo $this->_feed->createFeed('RSS2.0');
    }
}
?>