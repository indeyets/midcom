<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog Feed handler
 *
 * Prints the various supported feeds using the FeedCreator library.
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_handler_feed extends midcom_baseclasses_components_handler
{
    /**
     * The publications to display
     *
     * @var Array
     * @access private
     */
    var $_publications = null;

    /**
     * The datamanager for the currently displayed publication.
     *
     * @var midcom_helper_datamanager2_datamanager
     */
    var $_datamanager = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

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
    function net_nehmer_publications_handler_feed()
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

        // Prepare control structures
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        // Get the publications,
        $qb = net_nehmer_publications_entry::new_query_builder();
        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit($this->_config->get('rss_count'));
        $this->_publications = $qb->execute_unchecked();

        // Prepare the feed (this will also validate the handler_id)
        $this->_create_feed($handler_id);

        // Add each publication now.
        if ($this->_publications)
        {
            foreach ($this->_publications as $publication)
            {
                $this->_add_publication_to_feed($publication);
            }
        }

        $_MIDCOM->set_26_request_metadata(net_nehmer_publications_viewer::get_last_modified($this->_topic, $this->_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Adds the given publication to the feed.
     *
     * @param net_nehmer_publications_entry $publication The publication to add.
     */
    function _add_publication_to_feed($publication)
    {
        $this->_datamanager->autoset_storage($publication);

        $item = new FeedItem();
        $author = $_MIDCOM->auth->get_user($publication->creator);

        $item->title = $publication->title;
        $item->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "view/{$publication->guid}.html";
        $item->date = $publication->metadata->published;
        $item->author = $author->name;
        if ($this->_config->get('rss_use_content'))
        {
            $item->description = $this->_datamanager->types['content']->convert_to_html();
        }
        else
        {
            $item->description = $this->_datamanager->types['abstract']->convert_to_html();
        }

        $this->_feed->addItem($item);

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
                $this->_feed->syndicationURL = "{$this->_feed->link}rss2.xml";
                break;

            case 'feed-rss1':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss1.xml";
                break;

            case 'feed-rss091':
                $this->_feed->syndicationURL = "{$this->_feed->link}rss091.xml";
                break;

            case 'feed-atom':
                $this->_feed->syndicationURL = "{$this->_feed->link}atom.xml";
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
    function _show_feed($handler_id, &$data)
    {
        switch($handler_id)
        {
            case 'feed-rss2':
                echo $this->_feed->createFeed('RSS2.0');
                break;

            case 'feed-rss1':
                echo $this->_feed->createFeed('RSS1.0');
                break;

            case 'feed-rss091':
                echo $this->_feed->createFeed('RSS0.91');
                break;

            case 'feed-atom':
                echo $this->_feed->createFeed('ATOM');
                break;
        }
    }


    /**
     * Shows a simple available-feeds page.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_component_data['active_leaf'] = NET_NEHMER_PUBLICATIONS_LEAFID_FEEDS;
        $_MIDCOM->set_26_request_metadata($this->_topic->revised, $this->_topic->guid);
        return true;
    }

    /**
     * Displays the feeds page
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index ($handler_id, &$data)
    {
        midcom_show_style('feeds');
    }


}

?>