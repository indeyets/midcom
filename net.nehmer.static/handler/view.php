<?php
/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer index page handler
 *
 * @package net.nehmer.static
 */

class net_nehmer_static_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The article to display
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

    /**
     * The Datamanager of the article to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        if ($this->_article->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ));
        }
        if ($this->_article->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            ));
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nehmer_static_handler_view()
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

    /**
     * Can-Handle check against the article name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     *
     * Not applicable for the "index" handler, where the article name is fixed (see handle).
     */
    function _can_handle_view ($handler_id, $args, &$data)
    {
        if ($handler_id == 'index')
        {
            return true;
        }

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('name', '=', $args[0]);
        $qb->add_constraint('up', '=', 0);
        $qb->set_limit(1);
        
        // Include the article links to the indexes if enabled
        if ($this->_config->get('enable_article_links'))
        {
            $mc = net_nehmer_static_link_dba::new_collector('topic', $this->_content_topic->id);
            $mc->add_value_property('article');
            $mc->add_constraint('topic', '=', $this->_content_topic->id);
            
            // Get the results
            $mc->execute();
            
            $links = $mc->list_keys();
            $qb->begin_group('OR');
                foreach ($links as $guid => $link)
                {
                    $article_id = $mc->get_subkey($guid, 'article');
                    $qb->add_constraint('id', '=', $article_id);
                }
                $qb->add_constraint('topic', '=', $this->_content_topic->id);
            $qb->end_group();
        }
        else
        {
            $qb->add_constraint('topic', '=', $this->_content_topic->id);
        }
        
        $result = $qb->execute();

        if ($result)
        {
            $this->_article = $result[0];
            return true;
        }

        return false;
    }


    /**
     * Looks up an article to display. If the handler_id is 'index', the index article is tried to be
     * looked up, otherwise the article name is taken from args[0]. Triggered error messages are
     * generated accordingly. A missing index will trigger a forbidden error, a missing regular
     * article a 404 (from can_handle).
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        if ($handler_id == 'index')
        {
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('name', '=', 'index');
            $qb->set_limit(1);
            
            // Include the article links to the indexes if enabled
            if ($this->_config->get('enable_article_links'))
            {
                $mc = net_nehmer_static_link_dba::new_collector('topic', $this->_content_topic->id);
                $mc->add_value_property('article');
                $mc->add_constraint('topic', '=', $this->_content_topic->id);
                
                // Get the results
                $mc->execute();
                
                $links = $mc->list_keys();
                $qb->begin_group('OR');
                    foreach ($links as $guid => $link)
                    {
                        $article_id = $mc->get_subkey($guid, 'article');
                        $qb->add_constraint('id', '=', $article_id);
                    }
                    $qb->add_constraint('topic', '=', $this->_content_topic->id);
                $qb->end_group();
            }
            else
            {
                $qb->add_constraint('topic', '=', $this->_content_topic->id);
            }
            
            $result = $qb->execute();

            if (empty($result))
            {
                if ($this->_content_topic->can_do('midgard:create'))
                {
                    $schemas = array_keys($this->_request_data['schemadb']);
                    $_MIDCOM->relocate("createindex/{$schemas[0]}.html");
                    // This will exit.
                }
                else
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Directory index forbidden');
                    // This will exit.
                }
            }

            $this->_article = $result[0];
        }
        
        if ($handler_id == 'view_raw')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_load_datamanager();
        
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_article);
            $this->_request_data['controller']->process_ajax();
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_article->metadata->revised, $this->_article->guid);
        $_MIDCOM->bind_view_to_object($this->_article, $this->_datamanager->schema->name);

        if (   $this->_config->get('indexinnav')
            || $this->_config->get('autoindex')
            || $this->_article->name != 'index')
        {
            $this->_component_data['active_leaf'] = $this->_article->id;
        }

        if ($this->_config->get('folder_in_title'))
        {
            $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_article->title}");
        }
        else
        {
            $_MIDCOM->set_pagetitle($this->_article->title);
        }

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_article))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded article.
     */
    function _show_view ($handler_id, &$data)
    {
        if (   $this->_config->get('enable_ajax_editing')
            && isset($data['controller']))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_article'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_article'] = $data['datamanager']->get_content_html();
        }
        midcom_show_style('show-article');
    }
}
?>