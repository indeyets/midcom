<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker index page handler
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
            ));
        }

        if ($this->_article->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
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
        debug_add('mgd_version: ' . mgd_version());
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            debug_add('1.8.x detected, doing with single QB');
            // 1.8 allows us to do this the easy way
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('topic', '=', $this->_content_topic->id);
            $qb->add_constraint('up', '=', 0);
            $qb->begin_group('OR');
                $qb->add_constraint('name', '=', $args[0]);
                $qb->add_constraint('guid', '=', $args[0]);
            $qb->end_group();
            $articles = $qb->execute();
            if (count($articles) == 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND,
                    "The article '{$args[0]}' has not been found in the content topic.");
                // This will exit.
            }
            $this->_article = $articles[0];
        }
        else
        {
            debug_add('1.7.x detected, doing separate checks');
            // 1.7 requires that we check for guid and name separately
            if (mgd_is_guid($args[0]))
            {
                debug_add('mgd_is_guid returned true, trying to fetch with guid');
                $article = new midcom_db_article($args[0]);
                if (   !is_object($article)
                    || !is_a($article, 'midcom_db_article')
                    || $article->up != 0
                    || $article->topic != $this->_content_topic->id)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND,
                        "The article '{$args[0]}' has not been found in the content topic.");
                    // This will exit.
                }
                $this->_article = $article;
            }
            else
            {
                debug_add('mgd_is_guid returned false, trying to fetch with name');
                $qb = midcom_db_article::new_query_builder();
                $qb->add_constraint('topic', '=', $this->_content_topic->id);
                $qb->add_constraint('up', '=', 0);
                $qb->add_constraint('name', '=', $args[0]);
                $articles = $qb->execute();
                if (count($articles) == 0)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND,
                        "The article '{$args[0]}' has not been found in the content topic.");
                    // This will exit.
                }
                $this->_article = $articles[0];
            }
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
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$arg}.html",
            MIDCOM_NAV_NAME => $this->_article->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->substyle_append($this->_datamanager->schema->name);
        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_article);
        $_MIDCOM->set_26_request_metadata($this->_article->revised, $this->_article->guid);
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
            $comments_node = midcom_helper_find_node_by_component('net.nehmer.comments');
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