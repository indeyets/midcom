<?php
/**
 * @package net.nemein.rss
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Feed management class.
 *
 * @package net.nemein.rss
 */
class net_nemein_rss_manage extends midcom_baseclasses_components_handler
{
    function midcom_admin_folder_folder_management()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('net.nemein.rss');

        $this->_request_data['node'] = $this->_topic;

        /*
        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.folder/folder.css',
            )
        );
        */
    }

    function get_plugin_handlers()
    {
        return Array
        (
            'feeds_list' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'list'),
                'fixed_args' => array('feeds', 'list'),
            ),
            'feeds_opml' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'opml'),
                'fixed_args' => array('feeds.opml'),
            ),
            'feeds_subscribe' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'subscribe'),
                'fixed_args' => array('feeds', 'subscribe'),
            ),
            'feeds_edit' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'edit'),
                'fixed_args' => array('feeds', 'edit'),
                'variable_args' => 1,
            ),
            'feeds_delete' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'delete'),
                'fixed_args' => array('feeds', 'delete'),
                'variable_args' => 1,
            ),
            'feeds_fetch_all' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'fetch'),
                'fixed_args' => array('feeds', 'fetch', 'all'),
            ),
            'feeds_fetch' => Array
            (
                'handler' => Array('net_nemein_rss_manage', 'fetch'),
                'fixed_args' => array('feeds', 'fetch'),
                'variable_args' => 1,
            ),
        );
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_opml($handler_id, $args, &$data)
    {
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $_MIDCOM->skip_page_style = true;

        $qb = net_nemein_rss_feed_dba::new_query_builder();
        $qb->add_order('title');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $data['feeds'] = $qb->execute();

        $_MIDCOM->load_library('de.bitfolge.feedcreator');

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_opml($handler_id, &$data)
    {
        $opml = new OPMLCreator();
        $opml->title = $this->_topic->extra;

        foreach ($data['feeds'] as $feed)
        {
            $item = new FeedItem();
            $item->title = $feed->title;
            $item->xmlUrl = $feed->url;
            $opml->addItem($item);
        }

        echo $opml->createFeed();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $qb = net_nemein_rss_feed_dba::new_query_builder();
        $qb->add_order('title');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $data['feeds'] = $qb->execute();

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        $data['folder'] = $this->_topic;
        midcom_show_style('net-nemein-rss-feeds-list-header');

        foreach ($data['feeds'] as $feed)
        {
            $data['feed'] = $feed;
            $data['feed_toolbar'] = new midcom_helper_toolbar();
            if ($feed->can_do('midgard:update'))
            {
                $data['feed_toolbar']->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "feeds/edit/{$feed->guid}",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    )
                );
            }

            if ($this->_topic->can_do('midgard:create'))
            {
                $data['feed_toolbar']->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "feeds/fetch/{$feed->guid}",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('refresh feed', 'net.nemein.rss'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
                    )
                );
            }

            if ($feed->can_do('midgard:delete'))
            {
                $data['feed_toolbar']->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "feeds/delete/{$feed->guid}",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('delete feed', 'net.nemein.rss'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    )
                );
            }
            $data['feed_category'] = 'feed:' . md5($feed->url);

            $data['feed_items'] = 0;

            switch ($this->_topic->component)
            {
                case 'net.nehmer.blog':
                    $qb = midcom_db_article::new_query_builder();
                    $qb->add_constraint('topic', '=', $this->_topic->id);
                    $qb->add_constraint('extra1', 'LIKE', "%|{$data['feed_category']}|%");
                    $data['feed_items'] = $qb->count_unchecked();
                    break;
            }

            midcom_show_style('net-nemein-rss-feeds-list-item');
        }

        midcom_show_style('net-nemein-rss-feeds-list-footer');
    }

    function _subscribe_feed($feed_url, $feed_title = null)
    {
        // Try to fetch the new feed
        $rss = net_nemein_rss_fetch::raw_fetch($feed_url);
        // TODO: display error on invalid feed

        if (!$feed_title)
        {
            // If we didn't get the channel title preset
            $feed_title = '';
            if ($rss)
            {
                // Get the channel title from the feed
                if (isset($rss->channel['title']))
                {
                    $feed_title = $rss->channel['title'];
                }
            }
        }

        // Find out if the URL is already subscribed, and update it in that case
        $qb = net_nemein_rss_feed_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->add_constraint('url', '=', $feed_url);
        $feeds = $qb->execute();
        if (count($feeds) > 0)
        {
            // If we're updating existing feed
            $feed = $feeds[0];
            $feed->title = $feed_title;
            if ($feed->update())
            {
                $this->_request_data['feeds_updated'][$feed->id] = $feed->url;
                return true;
            }
            return false;
        }
        else
        {
            // Otherwise create new feed
            $feed = new net_nemein_rss_feed_dba();
            $feed->node = $this->_topic->id;
            $feed->url = $feed_url;
            $feed->title = $feed_title;
            if ($feed->create())
            {
                $this->_request_data['feeds_subscribed'][$feed->id] = $feed->url;
                return true;
            }
            return false;
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_subscribe($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        // Arrays for containing data on subscribed and updated feeds
        $data['feeds_subscribed'] = array();
        $data['feeds_updated'] = array();

        // Single feed addition
        if (   array_key_exists('net_nemein_rss_manage_newfeed', $_POST)
            && $_POST['net_nemein_rss_manage_newfeed']['url'])
        {
            $this->_subscribe_feed($_POST['net_nemein_rss_manage_newfeed']['url']);
            // TODO: display error messages
            // TODO: redirect user to edit page if creation succeeded

            $_MIDCOM->relocate('feeds/list/');
        }

        // OPML subscription list import support
        if (   array_key_exists('net_nemein_rss_manage_opml', $_FILES)
            && is_uploaded_file($_FILES['net_nemein_rss_manage_opml']['tmp_name']))
        {
            $opml_file = $_FILES['net_nemein_rss_manage_opml']['tmp_name'];

            // We have OPML file, parse it
            $opml_handle = fopen($opml_file, 'r');
            $opml_data = fread($opml_handle, filesize($opml_file));
            fclose($opml_handle);
            unlink($opml_file);

            $opml_parser = xml_parser_create();
            xml_parse_into_struct($opml_parser, $opml_data, $opml_values );
            foreach ($opml_values as $opml_element)
            {
                if ($opml_element['tag'] === 'OUTLINE')
                {
                    // Subscribe to found channels
                    if (isset($opml_element['attributes']['TITLE']))
                    {
                        $this->_subscribe_feed($opml_element['attributes']['XMLURL'], $opml_element['attributes']['TITLE']);
                    }
                    else
                    {
                        $this->_subscribe_feed($opml_element['attributes']['XMLURL']);
                    }
                }
            }
            xml_parser_free($opml_parser);

            $_MIDCOM->relocate('feeds/list/');
        }

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_subscribe($handler_id, &$data)
    {
        $data['folder'] = $this->_topic;
        midcom_show_style('net-nemein-rss-feeds-subscribe');
    }

    function _load_controller(&$data)
    {
        $data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_feed'));
        $data['controller'] =& midcom_helper_datamanager2_controller::create('simple');
        $data['controller']->schemadb =& $data['schemadb'];
        $data['controller']->set_storage($data['feed']);
        if (! $data['controller']->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for feed {$data['feed']->id}.");
            // This will exit.
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $data['feed'] = new net_nemein_rss_feed_dba($args[0]);
        if (!$data['feed'])
        {
            return false;
        }
        $data['feed']->require_do('midgard:update');

        $this->_load_controller(&$data);

        switch ($data['controller']->process_form())
        {
            case 'save':
                // TODO: Fetch the feed here?
                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate('feeds/list/');
                // This will exit.
        }

        $_MIDCOM->set_26_request_metadata($data['feed']->metadata->revised, $data['feed']->guid);
        $_MIDCOM->bind_view_to_object($data['feed']);

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('net-nemein-rss-feed-edit');
    }

    /**
     * Displays a downloadpage delete confirmation view.
     *
     * Note, that the downloadpage for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation downloadpage
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $data['feed'] = new net_nemein_rss_feed_dba($args[0]);
        if (!$data['feed'])
        {
            return false;
        }
        $data['feed']->require_do('midgard:delete');

        $this->_load_controller(&$data);

        if (array_key_exists('net_nemein_rss_deleteok', $_REQUEST))
        {
            // Deletion confirmed.

            if (! midcom_helper_purge_object($data['feed']))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete feed {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('feeds/list/');
            // This will exit.
        }

        if (array_key_exists('net_nemein_rss_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate('feeds/list/');
            // This will exit()
        }

        $_MIDCOM->set_26_request_metadata($data['feed']->metadata->revised, $data['feed']->guid);
        $this->_view_toolbar->bind_to($data['feed']);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['feed']->title}");

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded downloadpage.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('net-nemein-rss-feed-delete');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_fetch($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');
        $_MIDCOM->cache->content->enable_live_mode();

        if ($handler_id == 'feeds_fetch')
        {
            //Disable limits
            @ini_set('memory_limit', -1);
            @ini_set('max_execution_time', 0);

            $data['feed'] = new net_nemein_rss_feed_dba($args[0]);
            if (   !$data['feed']
                || !$data['feed']->guid)
            {
                return false;
            }

            $fetcher = new net_nemein_rss_fetch($data['feed']);
            $data['items'] = $fetcher->import();

            $_MIDCOM->set_26_request_metadata($data['feed']->metadata->revised, $data['feed']->guid);
            $_MIDCOM->bind_view_to_object($data['feed']);
        }
        else
        {
            //Disable limits
            @ini_set('memory_limit', -1);
            @ini_set('max_execution_time', 0);

            $data['items'] = array();
            $qb = net_nemein_rss_feed_dba::new_query_builder();
            $qb->add_order('title');
            $qb->add_constraint('node', '=', $this->_topic->id);
            $data['feeds'] = $qb->execute();
            foreach ($data['feeds'] as $feed)
            {
                $fetcher = new net_nemein_rss_fetch($feed);
                $items = $fetcher->import();
                $data['items'] = array_merge($data['items'], $items);
            }
        }

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_fetch($handler_id, &$data)
    {
        midcom_show_style('net-nemein-rss-feed-fetch');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "feeds/list/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('manage feeds', 'net.nemein.rss'),
        );

        switch ($handler_id)
        {
            case 'feeds_subscribe':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "feeds/subscribe/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('subscribe feeds', 'net.nemein.rss'),
                );
                break;
            case 'feeds_edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "feeds/edit/{$this->_request_data['feed']->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'feeds_delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "feeds/delete/{$this->_request_data['feed']->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
            case 'feeds_fetch_all':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "feeds/fetch/all/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('refresh all feeds', 'net.nemein.rss'),
                );
                break;
            case 'feeds_fetch':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "feeds/fetch/{$this->_request_data['feed']->guid}/",
                    MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('refresh feed', 'net.nemein.rss'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>