<?php

/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications welcome page handler
 *
 * Shows the configured number of postings with their abstracts or a full category listing.
 * The pulication listings can be limited by a latest directive and by a category. See the
 * individual handlers for details.
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_handler_welcome extends midcom_baseclasses_components_handler
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
     * @access private
     */
    var $_datamanager = null;

    /**
     * The category listing. Only applicable for _handler_categories. It maps
     * category numbers (1 to 6) to the valid option lists.
     *
     * @var Array
     * @access private
     */
    var $_categories = null;

    /**
     * Page title, set during processing depending on handler configuration.
     *
     * @var string
     * @access private
     */
    var $_title = null;

    /**
     * The total number of publications in the current index view.
     *
     * @var int
     * @access private
     */
    var $_total = null;

    /**
     * The current page shown in index view.
     *
     * @var int
     * @access private
     */
    var $_page = null;

    /**
     * The total number of pages in this index view result set.
     *
     * @var int
     * @access private
     */
    var $_total_pages = null;

    /**
     * First shown entry in the index view result set (1-based index).
     *
     * @var int
     * @access private
     */
    var $_first = null;

    /**
     * Last shown entry in the index view result set (1-based index).
     *
     * @var int
     * @access private
     */
    var $_last = null;

    /**
     * The prefix used for the page-links in the welcome-pagenav, you need to append
     * the ?page=x part to it.
     *
     * @var string
     * @access private
     */
    var $_pagelink_prefix = null;

    /**
     * Simple default constructor.
     */
    function net_nehmer_publications_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['categories'] =& $this->_categories;
        $this->_request_data['total'] =& $this->_total;
        $this->_request_data['page'] =& $this->_page;
        $this->_request_data['total_pages'] =& $this->_total_pages;
        $this->_request_data['first'] =& $this->_first;
        $this->_request_data['last'] =& $this->_last;
        $this->_request_data['pagelink_prefix'] =& $this->_pagelink_prefix;

        $this->_request_data['title'] =& $this->_title;
        $_MIDCOM->set_pagetitle($this->_title);
    }

    /**
     * Resolves a category identifier to a name. Used for creating page headings in category
     * limited listings.
     *
     * If resolving the category fails, the function will silently return the passed category
     * identifier again and log a warning to the debug log.
     *
     * @param string $category The category ID to resolve, fully qualified with group and local
     *     identifier.
     * @return string The resolved category name.
     */
    function _resolve_category($category)
    {
        $i = strpos($category, '-');
        $group = substr($category, 0, $i);
        $lister = new net_nehmer_publications_callbacks_categorylister($group);
        if (! $lister->key_exists($category))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not resolve category {$category} in group {$group}, it is unknown.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return $category;
        }
        else
        {
            return $lister->get_name_for_key($category);
        }
    }

    /**
     * Stores the URL to return to from the VIEW mode into the current session
     * as 'return URL'. The current anchor prefix will be added automatically, so
     * that the session key can be used directly.
     *
     * @param string $url The URL to return to.
     */
    function _set_return_url($url)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $session = new midcom_service_session();
        $session->set('return_url', "{$prefix}{$url}");
    }

    /**
     * Shows the full publications index.
     *
     * This handler supports full indexes and category-limited
     * indexes. By default, the full index is delivered. To invoke
     * a category index, trigger the handler under the handler_id 'welcome-category',
     * with two arguments (Category number and key).
     *
     * The sorting order is taken from the config key index_order.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $breadcrumb = Array();

        $qb = new net_nehmer_publications_query();
        $qb_count = new net_nehmer_publications_query();
        $qb->add_order($this->_config->get('index_order'));

        if (array_key_exists('page', $_REQUEST))
        {
            $this->_page = (int) $_REQUEST['page'];
            if ($this->_page < 1)
            {
                $this->_page = 1;
            }
        }
        else
        {
            $this->_page = 1;
        }

        $this->_pagelink_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if ($handler_id == 'welcome-category')
        {
            $category = $args[0];
            $this->_title = "{$this->_topic->extra}: " . $this->_resolve_category($category);
            $qb->add_category($category);
            $qb_count->add_category($category);

            if ($this->_page == 1)
            {
                $breadcrumb[] = Array
                (
                    MIDCOM_NAV_URL => "category/{$args[0]}.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('publications by category'),
                );
                $this->_set_return_url("category/{$args[0]}.html");
            }
            else
            {
                $breadcrumb[] = Array
                (
                    MIDCOM_NAV_URL => "category/{$args[0]}.html?page={$this->_page}",
                    MIDCOM_NAV_NAME => $this->_l10n->get('publications by category'),
                );
                $this->_set_return_url("category/{$args[0]}.html?page={$this->_page}");
            }
            $this->_pagelink_prefix .= "category/{$args[0]}.html";
        }
        else
        {
            $this->_title = $this->_topic->extra;
            if ($this->_page == 1)
            {
                $this->_set_return_url('');
            }
            else
            {
                $this->_set_return_url("?page={$this->_page}");
            }
        }

        // Get totals
        $page_size = (int) $this->_config->get('index_entries');
        $this->_total = $qb_count->count_unchecked();
        $this->_total_pages = max(1, ceil($this->_total / $page_size));

        // Sanity checks
        if ($this->_page > $this->_total_pages)
        {
            if ($this->_total_pages > 1)
            {
                $page_suffix = "?page={$this->_total_pages}";
            }
            else
            {
                $page_suffix = '';
            }

            // Ran beyond EOF, we relocate to the last known good page
            if ($handler_id == 'welcome-category')
            {
                $url = "category/{$args[0]}.html{$page_suffix}";
            }
            else
            {
                $url = $page_suffix;
            }
            $_MIDCOM->relocate($url);
            // this will exit.
        }

        // Calculate window & Result
        if ($this->_total == 0)
        {
            $this->_first = 0;
            $this->_last = 0;
            $this->_publications = Array();
        }
        else
        {
            $first = ($this->_page - 1) * $page_size;
            $this->_first = $first + 1;
            $this->_last = min(($first + $page_size), $this->_total);
            $qb->set_offset($first);
            $qb->set_limit($page_size);

            $this->_publications = $qb->execute();
        }

        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        $this->_prepare_request_data();
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        $_MIDCOM->set_26_request_metadata(net_nehmer_publications_viewer::get_last_modified($this->_topic, $this->_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Renders the alphabetic publications index page.
     */
    function _show_index($handler_id, &$data)
    {
        $this->_render_publication_index_page($handler_id, $data);
    }

    /**
     * Shows the latest-publications welcome page, it knows three modes:
     *
     * - By default, the newest entries are showed, using the count from the config option
     *   'index_entries'.
     * - When the handler_id is set to 'welcome-latest', the entry count is taken from
     *   $args[0].
     * - Finally, the handler_id 'welcome-latest-category' takes a category from
     *   $args[0], filters down to that category and displays
     *   the newest $args[1] entries.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_latest($handler_id, $args, &$data)
    {
        $breadcrumb = Array();

        $qb = new net_nehmer_publications_query();
        $qb->add_order('metadata.published', 'DESC');

        if ($handler_id == 'welcome')
        {
            $qb->set_limit($this->_config->get('latest_entries'));
            $this->_title = $this->_topic->extra;
            $this->_set_return_url('');
        }
        else if ($handler_id == 'welcome-latest')
        {
            if (! is_numeric($args[0]))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The limit count '{$args[0]}' is not valid.");
                // This will exit.
            }

            $qb->set_limit($args[0]);

            $breadcrumb[] = Array
            (
                MIDCOM_NAV_URL => "latest/{$args[0]}.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('latest publications'),
            );
            $this->_title = $this->_topic->extra;
            $this->_set_return_url("latest/{$args[0]}.html");
        }
        else if ($handler_id == 'welcome-latest-category')
        {
            if (! is_numeric($args[1]))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The limit count '{$args[1]}' is not valid.");
                // This will exit.
            }

            $category = $args[0];
            $this->_title = "{$this->_topic->extra}: " . $this->_resolve_category($category);
            $qb->add_category($category);
            $qb->set_limit($args[1]);

            $breadcrumb[] = Array
            (
                MIDCOM_NAV_URL => "category/latest/{$category}/{$args[1]}.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('latest publications'),
            );
            $this->_set_return_url("category/latest/{$category}/{$args[0]}.html");
        }

        $this->_publications = $qb->execute_unchecked();

        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        $this->_prepare_request_data();
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        $_MIDCOM->set_26_request_metadata(net_nehmer_publications_viewer::get_last_modified($this->_topic, $this->_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Renders the latest publications page.
     */
    function _show_latest($handler_id, &$data)
    {
        $this->_render_publication_index_page($handler_id, $data);
    }

    /**
     * Renders a publication index page according to the current object state. This is
     * useable in all standard publication-listing index pages.
     */
    function _render_publication_index_page($handler_id, &$data)
    {
        midcom_show_style('welcome-start');

        if ($this->_publications)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach ($this->_publications as $publication)
            {
                if (!$this->_datamanager->autoset_storage($publication))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for publication {$publication->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $publication);
                    debug_pop();
                    continue;
                }

                $data['publication'] =& $publication;
                $data['view_url'] = "{$prefix}view/{$publication->guid}.html";

                midcom_show_style('welcome-item');
            }
        }
        else
        {
            midcom_show_style('welcome-empty');
        }

        midcom_show_style('welcome-end');

        // If we have a defined page, we show the page navigation element
        if ($this->_page)
        {
            midcom_show_style('welcome-pagenav');
        }

        return true;
    }

    /**
     * Shows the complete category index.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_categories($handler_id, $args, &$data)
    {
        $this->_categories = Array();

        foreach ($this->_config->get('categories') as $group => $config)
        {
            if (   array_key_exists('internal', $config)
                && $config['internal'])
            {
                // skip, this is an internal-use-only category
                continue;
            }
            $lister = new net_nehmer_publications_callbacks_categorylister($group, true);
            $this->_categories[$group] = $lister->list_all();
        }

        $this->_title = $this->_topic->extra;

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(net_nehmer_publications_viewer::get_last_modified($this->_topic, $this->_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Shows the complete category index.
     */
    function _show_categories($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $config = $this->_config->get('categories');

        midcom_show_style('welcome-categories-start');
        foreach ($this->_categories as $id => $listing)
        {
            $title = $config[$id]['title'];
            if (! $title)
            {
                $title = sprintf($this->_l10n->get('category %d'), $id);
            }
            $data['title'] = $title;
            $data['id'] = $id;
            midcom_show_style('welcome-categories-category-start');

            foreach ($listing as $key => $description)
            {
                $data['key'] = $key;
                $data['description'] = $description;
                $data['category_url'] = "{$prefix}category/{$key}.html";
                midcom_show_style('welcome-categories-category-item');
            }

            midcom_show_style('welcome-categories-category-end');
        }
        midcom_show_style('welcome-categories-end');
    }

}
?>