<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog Index handler page handler
 *
 * Shows the configured number of postings with their abstracts.
 *
 * @package net.nehmer.blog
 */

class net_nehmer_blog_handler_index extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The articles to display
     *
     * @var Array
     * @access private
     */
    var $_articles = null;

    /**
     * The datamanager for the currently displayed article.
     *
     * @var midcom_helper_datamanager2_datamanager
     */
    var $_datamanager = null;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];

        $_MIDCOM->load_library('org.openpsa.qbpager');
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
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
    function _handler_index ($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-latest')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_datamanager = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        $qb = new org_openpsa_qbpager('midcom_db_article', 'net_nehmer_blog_index');
        $data['qb'] =& $qb;

        // Hide the articles that have the publish time in the future and if
        // the user is not administrator
        if (   $this->_config->get('enable_scheduled_publishing')
            && !$_MIDCOM->auth->admin)
        {
            // Show the article only if the publishing time has passed or the viewer
            // is the author
            $qb->begin_group('OR');
                $qb->add_constraint('metadata.published', '<', date('Y-m-d H:i:s'));

                if (   $_MIDCOM->auth->user
                    && isset($_MIDCOM->auth->user->guid))
                {
                    $qb->add_constraint('metadata.authors', 'LIKE', '|' . $_MIDCOM->auth->user->guid . '|');
                }
            $qb->end_group();
        }

        // Include the article links to the indexes if enabled
        if ($this->_config->get('enable_article_links'))
        {
            $mc = net_nehmer_blog_link_dba::new_collector('topic', $this->_content_topic->id);
            $mc->add_value_property('article');
            $mc->add_constraint('topic', '=', $this->_content_topic->id);
            $mc->add_order('metadata.published', 'DESC');

            // Use sophisticated guess to limit the amount: there shouldn't be more than
            // the required amount of links that is needed. Even if some links would fall
            // off due to a broken link (i.e. removed article), there should be enough
            // of content to fill the blank
            switch ($handler_id)
            {
                case 'index':
                case 'index-category':
                    $mc->set_limit((int) $this->_config->get('index_entries'));
                    break;

                case 'latest':
                case 'ajax-latest':
                    $mc->set_limit((int) $args[0]);
                    break;

                case 'latest-category':
                    $mc->set_limit((int) $args[1]);
                    break;

                default:
                    $mc->set_limit((int) $this->_config->get('index_entries'));
                    break;
            }

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

        // Set default page title
        $data['page_title'] = $this->_topic->extra;

        // Filter by categories
        if (   $handler_id == 'index-category'
            || $handler_id == 'latest-category')
        {
            $data['category'] = $args[0];
            if (!in_array($data['category'], $data['categories']))
            {
                // This is not a predefined category from configuration, check if site maintainer allows us to show it
                if (!$this->_config->get('categories_custom_enable'))
                {
                    return false;
                }
                // TODO: Check here if there are actually items in this cat?
            }

            // TODO: check schema storage to get fieldname
            $multiple_categories = true;
            if (   isset($data['schemadb']['default']->fields['categories'])
                && array_key_exists('allow_multiple', $data['schemadb']['default']->fields['categories']['type_config'])
                && !$data['schemadb']['default']->fields['categories']['type_config']['allow_multiple'])
            {
                $multiple_categories = false;
            }
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("multiple_categories={$multiple_categories}");
            debug_pop();
            if ($multiple_categories)
            {
                $qb->add_constraint('extra1', 'LIKE', "%|{$this->_request_data['category']}|%");
            }
            else
            {
                $qb->add_constraint('extra1', '=', (string) $data['category']);
            }

            // Add category to title
            $data['page_title'] = sprintf($this->_l10n->get('%s category %s'), $this->_topic->extra, $data['category']);

            // Activate correct leaf
            if (   $this->_config->get('show_navigation_pseudo_leaves')
                && in_array($data['category'], $data['categories']))
            {
                $this->_component_data['active_leaf'] = "{$this->_topic->id}_CAT_{$data['category']}";
            }

            // Add RSS feed to headers
            if ($this->_config->get('rss_enable'))
            {
                $_MIDCOM->add_link_head
                (
                    array
                    (
                        'rel'   => 'alternate',
                        'type'  => 'application/rss+xml',
                        'title' => $this->_l10n->get('rss 2.0 feed') . ": {$data['category']}",
                        'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "feeds/category/{$data['category']}/",
                    )
                );
            }
        }

        $qb->add_order('metadata.published', 'DESC');
        //$qb->add_order('lang', 'DESC');

        switch ($handler_id)
        {
            case 'index':
            case 'index-category':
                $qb->results_per_page = $this->_config->get('index_entries');
                break;

            case 'latest':
            case 'ajax-latest':
                $qb->results_per_page = $args[0];
                break;

            case 'latest-category':
                $qb->results_per_page = $args[1];
                break;

            default:
                $qb->results_per_page = $this->_config->get('index_entries');
                break;
        }

        /**
         * execute_unchecked has issues with ML and since the windowed QB
         * it doesn't offer significant advantage for queries without offsets
        $this->_articles = $qb->execute_unchecked();
         */
        $this->_articles = $qb->execute();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(net_nehmer_blog_viewer::get_last_modified($this->_topic, $this->_content_topic), $this->_topic->guid);

        if ($qb->get_current_page() > 1)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX),
                MIDCOM_NAV_NAME => sprintf($_MIDCOM->i18n->get_string('page %s', 'org.openpsa.qbpager'), $qb->get_current_page()),
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        }

        return true;
    }

    /**
     * Displays the index page
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        $data['index_fulltext'] = $this->_config->get('index_fulltext');

        midcom_show_style('index-start');

        if ($this->_config->get('comments_enable'))
        {
            $_MIDCOM->componentloader->load('net.nehmer.comments');
            $this->_request_data['comments_enable'] = true;
        }

        if ($this->_articles)
        {
            $total_count = count($this->_articles);
            $data['article_count'] = $total_count;
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach ($this->_articles as $article_counter => $article)
            {
                if (! $this->_datamanager->autoset_storage($article))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for article {$article->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $article);
                    debug_pop();
                    continue;
                }

                $data['article'] =& $article;
                $data['article_counter'] = $article_counter;
                $arg = $article->name ? $article->name : $article->guid;

                if (   $this->_config->get('link_to_external_url')
                    && !empty($article->url))
                {
                    $data['view_url'] = $article->url;
                }
                else
                {
                    if ($this->_config->get('view_in_url'))
                    {
                        $data['view_url'] = "{$prefix}view/{$arg}.html";
                    }
                    else
                    {
                        $data['view_url'] = "{$prefix}{$arg}.html";
                    }
                }

                if ($article->topic === $this->_content_topic->id)
                {
                    $data['linked'] = false;
                }
                else
                {
                    $data['linked'] = true;

                    $nap = new midcom_helper_nav();
                    $data['node'] = $nap->get_node($article->topic);
                }

                midcom_show_style('index-item', array($article->guid));
            }
        }
        else
        {
            midcom_show_style('index-empty');
        }

        midcom_show_style('index-end');
        return true;
    }
}
?>