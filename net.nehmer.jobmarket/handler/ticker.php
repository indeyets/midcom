<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market job ticker page handler
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_handler_ticker extends midcom_baseclasses_components_handler
{

    /**
     * This is an array holding the computed type list.
     *
     * The elements are indexed by type name and contain the following keys:
     *
     * - all keys from the configuration array
     * - string offer_search_url
     * - string application_search_url
     *
     * @var Array
     * @access private
     */
    var $_type_list = null;

    /**
     * The mode in use for self mode (offers or applications)
     *
     * @var string
     * @access private
     */
    var $_mode = null;

    /**
     * The matching entries for self mode.
     *
     * @var array
     * @access private
     */
    var $_entries = null;

    /**
     * The datamanager encaspulating the current resultset.
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
     * This does not take MidCOM ACL into account, just the component side read-restrictions.
     *
     * @var int
     * @access private
     */
    var $_total_count = null;

    /**
     * The total number of pages.
     * This does not take MidCOM ACL into account, just the component side read-restrictions.
     *
     * @var int
     * @access private
     */
    var $_total_pages = null;

    function net_nehmer_jobmarket_handler_ticker()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['type_list'] =& $this->_type_list;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['page'] =& $this->_page;
        $this->_request_data['total_count'] =& $this->_total_count;
        $this->_request_data['total_pages'] =& $this->_total_pages;

        // Compute page numbers
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($this->_page < $this->_total_pages)
        {
            $this->_request_data['next_page'] = $this->_page + 1;
            $this->_request_data['next_page_url'] = "{$prefix}ticker/{$this->_mode}/{$this->_request_data['next_page']}.html";
        }
        else
        {
            $this->_request_data['next_page'] = null;
            $this->_request_data['next_page_url'] = null;
        }

        if ($this->_page > 1)
        {
            $this->_request_data['previous_page'] = $this->_page - 1;
            $this->_request_data['previous_page_url'] = "{$prefix}ticker/{$this->_mode}/{$this->_request_data['previous_page']}.html";
        }
        else
        {
            $this->_request_data['previous_page'] = null;
            $this->_request_data['previous_page_url'] = null;
        }
    }


    /**
     * Validates the ticker arguments.
     */
    function _can_handle_ticker($handler_id, $args, &$data)
    {
        if (   $args[0] != 'offer'
            && $args[0] != 'application')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offfer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (! is_numeric($args[1]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The page number must be a number, got '{$args[1]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if ($args[1] < 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The page number must not be less then 1, got '{$args[1]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Starts up the job ticker, bound to either the offers or the applications sections. Visibility
     * of the resultset is limited according to the components' configuration. Full result listings
     * are not directly supported, the component will always page the job ticker, to avoid huge
     * listings.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_ticker($handler_id, $args, &$data)
    {
        $this->_mode = $args[0];
        $this->_page = (int) $args[1];
        $this->_type_list = $this->_config->get('type_config');

        $this->_load_total_count();
        $this->_total_pages = ceil($this->_total_count / $this->_config->get('result_list_page_size'));

        // Validate page numbers and load corresponding resultset
        if ($this->_total_count > 0)
        {
            if ($this->_page > $this->_total_pages)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'The given page does not exist.');
                // This will exit.
            }
            $this->_load_results();
        }
        else
        {
            // Special treatment for empty resultsets.
            if ($this->_page != 1)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'The given page does not exist.');
                // This will exit.
            }
            $this->_total_pages = 1;
        }

        // Prepare object state for listing.
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("jobticker: {$this->_mode}s"));
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_TICKER_OFFERS : NET_NEHMER_JOBMARKET_LEAFID_TICKER_APPLICATIONS;

        return true;
    }

    /**
     * Internal helper function, prepares a query builder which lists all results applicable
     * in the current authentication state and display mode. Further constraints like
     * ordering and offset/count limits must be applied by the callee.
     *
     * @return midcom_core_querybuilder The constructed QB.
     */
    function _prepare_result_qb()
    {
        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('offer', '=', ($this->_mode == 'offer'));
        // Check privileges
        if (! $_MIDCOM->auth->user)
        {
            $keyname = "{$this->_mode}_anonymous_read";
            foreach ($this->_type_list as $name => $config)
            {
                if (! $config[$keyname])
                {
                    $qb->add_constraint('type', '<>', $name);
                }
            }
        }
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
        $page_size = $this->_config->get('result_list_page_size');
        $qb = $this->_prepare_result_qb();
        $qb->set_limit($page_size);
        $qb->set_offset(($this->_page - 1) * $page_size);
        $qb->add_order('published', 'DESC');
        $this->_entries = $qb->execute_unchecked();
    }

    /**
     * Jobticker display loop.
     */
    function _show_ticker($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('ticker-begin');
        foreach ($this->_entries as $key => $entry)
        {
            $data['entry'] = $entry;
            $data['view_url'] = "{$prefix}entry/view/{$entry->guid}.html";
            $param_name = ($entry->offer ? 'offer_schema' : 'application_schema');
            $this->_datamanager->set_schema($this->_type_list[$entry->type][$param_name]);
            $this->_datamanager->set_storage($entry);
            midcom_show_style('ticker-item');
        }
        midcom_show_style('ticker-end');
    }


}

?>
