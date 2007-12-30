<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace category listing code.
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_handler_category extends midcom_baseclasses_components_handler
{
    /**
     * The category key (!) we're currently limited to.
     *
     * @var string
     * @access private
     */
    var $_category = null;

    /**
     * Full category listing, with parsed names.
     *
     * @var Array
     * @access private
     */
    var $_category_list = null;

    /**
     * The clear text name of the category we're currently limited to.
     *
     * @var string
     * @access private
     */
    var $_category_name = null;

    /**
     * Current display mode, one of ask, bid.
     *
     * @var string
     * @access private
     */
    var $_mode = null;

    /**
     * The matching entries for self mode, this is already paged.
     *
     * @var array
     * @access private
     */
    var $_entries = null;

    /**
     * The datamanager encapsulating the currently displayed entry.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The page currently displayed, this is a one-based index.
     *
     * @var int
     * @access private
     */
    var $_page = null;

    /**
     * The total number of entries matching the current mode and access restrictions.
     * This does not take MidCOM ACL into account.
     *
     * @var int
     * @access private
     */
    var $_total_count = null;

    /**
     * The total number of pages.
     * This does not take MidCOM ACL into account.
     *
     * @var int
     * @access private
     */
    var $_total_pages = null;

    function net_nehmer_marketplace_handler_category()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Initialize the category listing.
     */
    function _on_initialize()
    {
        $this->_category_list = $this->_config->get('categories');
        foreach ($this->_category_list as $key => $copy)
        {
            $this->_category_list[$key] = str_replace('|', ': ', $copy);
        }
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['category'] =& $this->_category;
        $this->_request_data['category_name'] =& $this->_category_name;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['page'] =& $this->_page;
        $this->_request_data['total_count'] =& $this->_total_count;
        $this->_request_data['total_pages'] =& $this->_total_pages;

        // Compute page numbers and urls
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($this->_page < $this->_total_pages)
        {
            $this->_request_data['next_page'] = $this->_page + 1;
            $this->_request_data['next_page_url'] = "{$prefix}list/{$this->_category}/{$this->_mode}/{$this->_request_data['next_page']}.html";
        }
        else
        {
            $this->_request_data['next_page'] = null;
            $this->_request_data['next_page_url'] = null;
        }

        if ($this->_page > 1)
        {
            $this->_request_data['previous_page'] = $this->_page - 1;
            $this->_request_data['previous_page_url'] = "{$prefix}list/{$this->_category}/{$this->_mode}/{$this->_request_data['previous_page']}.html";
        }
        else
        {
            $this->_request_data['previous_page'] = null;
            $this->_request_data['previous_page_url'] = null;
        }
    }

    /**
     * Validates the browsing arguments.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool True if the request can be handled, false otherwise.
     */
    function _can_handle_browse($handler_id, $args, &$data)
    {
        if (   $args[1] != 'ask'
            && $args[1] != 'bid')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'ask' or 'bid' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (! array_key_exists($args[0], $this->_category_list))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Unknown category, got '{$args[1]}'", MIDCOM_LOG_INFO);
            debug_print_r('Got these categories:', $this->_category_list);
            debug_pop();
            return false;
        }

        if (   ! is_numeric($args[2])
            || $args[2] < 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Invalid page number, got '{$args[2]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "list/{$this->_category}/{$this->_mode}/1.html",
                MIDCOM_NAV_NAME => $this->_category_name,
            ),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * This code loads the currently displayed data set based on the (already validated)
     * argument list.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_browse($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_category = $args[0];
        $this->_mode = $args[1];
        $this->_page = (int) $args[2];
        $this->_category_name = $this->_category_list[$this->_category];

        $this->_update_breadcrumb_line();

        $this->_load_total_count();
        $this->_total_pages = ceil($this->_total_count / $this->_config->get('page_size'));

        // Validate page numbers and load corresponding resultset
        if ($this->_total_count > 0)
        {
            if ($this->_page > $this->_total_pages)
            {
                $this->_relocate("list/{$this->_category}/{$this->_mode}/{$this->_total_pages}.html");
                // This will exit.
            }
            $this->_load_results();
        }
        else
        {
            // Special treatment for empty resultsets.
            if ($this->_page != 1)
            {
                $this->_relocate("list/{$this->_category}/{$this->_mode}/1.html");
                // This will exit.
            }
            $this->_total_pages = 1;
        }

        // Prepare object state for listing.
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_datamanager->set_schema($this->_config->get("{$this->_mode}_schema"));

        // Set request data, construct a back-to-search-result link as well.
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get($this->_mode));
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_BIDS;

        return true;
    }

    /**
     * Internal helper function, prepares a query builder which lists all results applicable
     * in the current state. Further constraints like
     * ordering and offset/count limits must be applied by the callee.
     *
     * @return midcom_core_querybuilder The constructed QB.
     */
    function _prepare_result_qb()
    {
        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('ask', '=', ($this->_mode == 'ask'));
        $qb->add_constraint('category', '=', $this->_category);
        return $qb;
    }

    /**
     * Loads the total result count applicable in the active mode. This query
     * bypasses ACL restrictions to keep the speed up.
     */
    function _load_total_count()
    {
        $qb = $this->_prepare_result_qb();
        $this->_total_count = $qb->count_unchecked();
    }

    /**
     * Loads the results applicable to the current page, limited by the current page size
     * setting. Be aware that the actual page size returned might be smaller then the one set
     * if either a) the last page is displayed (obviously) or b) some market entries are
     * ACL-hidden for any reason (shouldn't happen component-side).
     */
    function _load_results()
    {
        $page_size = $this->_config->get('page_size');
        $qb = $this->_prepare_result_qb();
        $qb->set_limit($page_size);
        $qb->set_offset(($this->_page - 1) * $page_size);
        $qb->add_order('published', 'DESC');
        $qb->add_order('id');
        $this->_entries = $qb->execute_unchecked();
    }

    /**
     * Displays the loaded results
     */
    function _show_browse($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('browse-begin');
        if ($this->_entries)
        {
            foreach ($this->_entries as $key => $entry)
            {
                $data['entry'] = $entry;
                $data['view_url'] = "{$prefix}entry/view/{$entry->guid}.html";
                $this->_datamanager->set_storage($entry);
                midcom_show_style('browse-item');
            }
        }
        midcom_show_style('browse-end');
    }


}

?>