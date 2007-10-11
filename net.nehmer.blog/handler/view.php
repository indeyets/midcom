<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog index page handler
 *
 * @package net.nehmer.blog
 */
class net_nehmer_blog_handler_view extends midcom_baseclasses_components_handler
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
    function net_nehmer_blog_handler_view()
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
     */
    function _can_handle_view ($handler_id, $args, &$data)
    {
        $qb = midcom_db_article::new_query_builder();

        // Include the article links to the indexes if enabled
        if ($this->_config->get('enable_article_links'))
        {
            $mc = net_nehmer_blog_link_dba::new_collector('topic', $this->_content_topic->id);
            $mc->add_value_property('article');
            $mc->add_constraint('topic', '=', $this->_content_topic->id);
            $mc->add_order('metadata.published', 'DESC');
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
        
        $qb->add_constraint('up', '=', 0);
        $qb->begin_group('OR');
            $qb->add_constraint('name', '=', $args[0]);
            $qb->add_constraint('guid', '=', $args[0]);
        $qb->end_group();
        $articles = $qb->execute();
        if (count($articles) > 0)
        {
            $this->_article = $articles[0];
        }
        
        if (!$this->_article)
        {
            return false;
            // This will 404
        }
        
        return true;
    }

    /**
     * Handle actual article display
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        if (!$this->_article)
        {
            return false;
            // This will 404
        }

        $this->_load_datamanager();

        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_article);
            $this->_request_data['controller']->process_ajax();
        }

        $tmp = Array();
        $arg = $this->_article->name ? $this->_article->name : $this->_article->guid;
        if ($this->_config->get('view_in_url'))
        {
            $view_url = "view/{$arg}.html";
        }
        else
        {
            $view_url = "{$arg}.html";
        }        
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => $view_url,
            MIDCOM_NAV_NAME => $this->_article->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_article, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_article->metadata->revised, $this->_article->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_article->title}");

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
     * Try to find a comments node (cache results)
     * 
     * @access private
     */
    function _seek_comments()
    {
        if ($this->_config->get('comments_topic'))
        {
            // We have a specified photostream here
            $comments_topic = new midcom_db_topic($this->_config->get('comments_topic'));
            if (   !is_object($comments_topic)
                || !isset($comments_topic->guid)
                || empty($comments_topic->guid))
            {
                return false;
            }

            // We got a topic. Make it a NAP node
            $nap = new midcom_helper_nav();
            $comments_node = $nap->get_node($comments_topic->id);
            
            return $comments_node;
        }
        
        // No comments topic specified, autoprobe
        $comments_node = midcom_helper_find_node_by_component('net.nehmer.comments');

        // Cache the data
        if ($_MIDCOM->auth->request_sudo('net.nehmer.blog'))
        {
            $this->_topic->parameter('net.nehmer.blog', 'comments_topic', $comments_node[MIDCOM_NAV_GUID]);
            $_MIDCOM->auth->drop_sudo();
        }

        return $comments_node;
    }

    /**
     * Shows the loaded article.
     */
    function _show_view ($handler_id, &$data)
    {
        if ($this->_config->get('enable_ajax_editing'))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_article'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_article'] = $this->_datamanager->get_content_html();
        }

        if ($this->_config->get('comments_enable'))
        {
            $comments_node = $this->_seek_comments();
            if ($comments_node)
            {
                $this->_request_data['comments_url'] = $comments_node[MIDCOM_NAV_RELATIVEURL] . "comment/{$this->_article->guid}";
            }
            // TODO: Should we tell admin to create a net.nehmer.comments folder?
        }

        midcom_show_style('view');
    }
}
?>