<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: feed.php 11149 2007-07-10 10:29:17Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Social News Feed handler
 *
 * Prints the various supported feeds using the FeedCreator library.
 *
 * @package org.maemo.socialnews
 */

class org_maemo_socialnews_handler_feed extends midcom_baseclasses_components_handler
{
    /**
     * The issues to display
     *
     * @var Array
     * @access private
     */
    var $_issues = null;

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
    function org_maemo_socialnews_handler_feed()
    {
        parent::midcom_baseclasses_components_handler();
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
    function _handler_feed ($handler_id, $args, &$data)
    {
        $_MIDCOM->load_library('de.bitfolge.feedcreator');
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $_MIDCOM->skip_page_style = true;

        // Get the issues,
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);

        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit($this->_config->get('rss_count'));
        $this->_issues = $qb->execute();

        $data['node_title'] = $this->_config->get('socialnews_title');
        if (empty($data['node_title']))
        {
            $data['node_title'] = $this->_topic->extra;
        }

        // Prepare the feed (this will also validate the handler_id)
        $this->_create_feed($handler_id);

        $_MIDCOM->set_26_request_metadata($this->_topic->metadata->revised, $this->_topic->guid);
        return true;
    }

    /**
     * Creates the Feedcreator instance.
     */
    function _create_feed($handler_id)
    {

        $this->_feed = new UniversalFeedCreator();
        $this->_feed->title = $this->_request_data['node_title'];
        $this->_feed->link = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_feed->cssStyleSheet = false;
        $this->_feed->syndicationURL = "{$this->_feed->link}rss.xml";
    }

    /**
     * Displays the feed
     */
    function _show_feed($handler_id, &$data)
    {
        $data['feedcreator'] =& $this->_feed;

        // Add each article now.
        if ($this->_issues)
        {
            foreach ($this->_issues as $issue)
            {
                $data['issue'] =& $issue;
                midcom_show_style('archive-feed-item');
            }
        }
        echo $this->_feed->createFeed('RSS2.0');
    }
}
?>