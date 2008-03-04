<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

 /** @ignore */
require('list.php');
/**
 * Created on 2006-Oct-Thu
 * @package org.routamc.photostream
 */
class org_routamc_photostream_handler_feed extends org_routamc_photostream_handler_list
{
    var $_feed;

    /**
     * Array of normalized file basenames (with extension removed) pointing
     * to de.bitfolge.feedcreator creator types
     */
    var $_supported_types = array
    (
        'rss' => 'RSS2.0',
        'rss1' => 'RSS1.0',
        'rss091' => 'RSS0.91',
        'atom' => 'ATOM',
    );

    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_feed()
    {
        parent::org_routamc_photostream_handler_list();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * We override this to avoid unnecessary controller creations
     */
    function _prepare_ajax_controllers()
    {
        // No need for ajax stuff here
    }

    /**
     * Check if the request can be handled
     * 
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @return boolean Indicating success.
     */
    function _can_handle_dispatcher($handler_id, $args)
    {
        if (!array_key_exists($args[0], $this->_supported_types))
        {
            return false;
        }
        
        return true;
    }

    /**
     * This rather sneaky dispatcher is able to create a feed from any
     * request_switch supported by org_routamc_photostream_handler_list
     * that populates $data['photos'] (with a little help from org_routamc_photostream_viewer)
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_dispatcher($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // This is required and will throw error if we can't load it
        $_MIDCOM->load_library('de.bitfolge.feedcreator');
        if ($GLOBALS['midcom_config']['positioning_enable'])
        {
            // Silently try to load this library if possible
            $_MIDCOM->componentloader->load_graceful('org.routamc.positioning');
        }

        $data['parent_handler_id'] = preg_replace('/^feed[0-9]*:/', '', $handler_id);
        if (!isset($data['request_switch'][$data['parent_handler_id']]))
        {
            // Fatal, cannot solve handler to use to get the actual data
            debug_add("Cannot find handler_id '{$data['parent_handler_id']}'", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $parent_method = "_handler_{$data['request_switch'][$data['parent_handler_id']]['handler'][1]}";
        debug_add("Resolved parent method to '{$parent_method}'");

        if (!is_callable(array($this, $parent_method)))
        {
            // Fatal, parent method is not callable
            debug_add("Handler method \$this->{$parent_method} is not callable", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Use array_pop so we can pass the args on to the parent handler and it will not get confused by too long argument list
        $feed_type_arg = array_pop($args);

        // This will also do some validations
        $this->_normalize_feed_type($feed_type_arg);
        $this->_resolve_feed_urls($args);

        debug_add("Calling \$this->{$parent_method}('{$data['parent_handler_id']}', \$args, \$data)");
        if (!$this->$parent_method($data['parent_handler_id'], $args, $data))
        {
            debug_add("\$this->{$parent_method}('{$data['parent_handler_id']}', \$args, \$data) returned false", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!isset($data['photos']))
        {
            debug_add("\$data['photos'] is not set, most probably we hit a method ('{$parent_method}') in the list handler that does not populate it", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $this->_create_feed();

        // Now that we have all that we need, we can remove this reference from cluttering our world
        unset($data['request_switch']);


        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        // No failures thus far, set content type etc
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");
        /* For simpler debugging
        $_MIDCOM->cache->content->content_type("text/plain");
        $_MIDCOM->header("Content-type: text/plain; charset=UTF-8");
        */
        $_MIDCOM->skip_page_style = true;

        debug_pop();
        return true;
    }

    /**
     * Reolves sane URL for both the HTML view and the feed
     */
    function _resolve_feed_urls($args)
    {
        $data =& $this->_request_data;
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $parent_switch =& $data['request_switch'][$data['parent_handler_id']];
        $data['list_url'] = $data['prefix'] . implode('/', $parent_switch['fixed_args']) . '/'. implode('/', $args) . '/';
        $data['feed_url'] = "{$data['prefix']}feed/" . implode('/', $parent_switch['fixed_args']) . '/'. implode('/', $args) . "/{$data['feed_filename']}";
    }

    /**
     * Resolves and sanity-checks the feed type from the filename given as last argument to the dispatcher
     */
    function _normalize_feed_type($type_str)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $data =& $this->_request_data;
        $type = preg_replace('/\..*$/', '', strtolower($type_str));
        debug_add("normalized '{$type_str}' to '{$type}'");
        if (   !isset($this->_supported_types[$type])
            || empty($this->_supported_types[$type]))
        {
            debug_add("Feed type '{$type}' is not supported", MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Feed type '{$type}' is not supported");
            // This will exit()
        }
        $data['feed_type'] = $this->_supported_types[$type];
        $data['feed_filename'] = "{$type}.xml";
    }

    /**
     * Instantiate the feed object and set base value
     */
    function _create_feed()
    {
        $data =& $this->_request_data;
        $this->_feed = new UniversalFeedCreator();
        $this->_feed->cssStyleSheet = false;
        if (   isset($data['view_title'])
            && !empty($data['view_title']))
        {
            $this->_feed->title = $data['view_title'];
        }
        else
        {
            $this->_feed->title = $this->_topic->extra;
        }
        $this->_feed->language = $this->_config->get('rss_language');
        $this->_feed->editor = $this->_config->get('rss_webmaster');
        $this->_feed->link = $data['list_url'];
        $this->_feed->syndicationURL = $data['feed_url'];
    }

    /**
     * Add each photo as item to the feed then creates the XML and
     * finally calls an element if one wishes to mangle the raw feed data
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_dispatcher($handler_id, &$data)
    {
        foreach($data['photos'] as $photo)
        {
            $this->_add_photo_to_feed($photo);
        }
        $data['feed_data'] = $this->_feed->createFeed($data['feed_type']);
        // Disable output to keep feed sane, PONDER: temporarily turn on log_errors ??
        ob_start();
        midcom_show_style('mangle_feed_data');
        ob_end_clean();
        echo $data['feed_data'];
        unset($data['feed_data']);
    }

    /**
     * This method creates a feed item for a photo object given
     * It uses the style-engine to render the description and to
     * allow custom hacks to mangle the item data
     */
    function _add_photo_to_feed(&$photo)
    {
        $data =& $this->_request_data;
        $item = new FeedItem();
        $item->title = $photo->title;
        $item->link = "{$data['prefix']}photo/{$photo->guid}/";
        $item->date = $photo->taken;
        $data['item'] =& $item;

        if (!$this->_datamanager->autoset_storage($photo))
        {
            return false;
        }
        $data['datamanager'] =& $this->_datamanager;
        $data['photo'] =& $photo;
        $data['photo_view'] = $this->_datamanager->get_content_html();
        $thumbnail = false;
        if (   isset($data['datamanager']->types['photo'])
            && isset($data['datamanager']->types['photo']->attachments_info['thumbnail']))
        {
            $thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
        }
        if ($thumbnail)
        {
            $item->thumb = $thumbnail['url'];
        }
        $data['tags'] = net_nemein_tag_handler::get_object_tags($photo);
        if (!empty($tags))
        {
            // Use first tag as category
            foreach ($data['tags'] as $tag => $url)
            {
                $item->category = $tag;
                break;
            }
        }

        ob_start();
        midcom_show_style('render_feed_item');
        $item->description = ob_get_contents();
        ob_end_clean();

        if (class_exists('org_routamc_positioning_object'))
        {
            // Attach coordinates to the item if available
            $object_position = new org_routamc_positioning_object($photo);
            $coordinates = $object_position->get_coordinates();
            if (!is_null($coordinates))
            {
                $item->lat = $coordinates['latitude'];
                $item->long = $coordinates['longitude'];
            }
        }
        // Replace links, TODO: This should be a feature of feedcreator...
        $item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

        // Disable output to keep feed sane, PONDER: temporarily turn on log_errors ??
        ob_start();
        midcom_show_style('mangle_feed_item');
        ob_end_clean();
        $this->_feed->addItem($item);
    }
}
?>