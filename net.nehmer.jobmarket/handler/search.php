<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market generic search page handler.
 *
 * The handler uses PHP sessions to keep all relevant information about the search
 * persistant over the requests.
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_handler_search extends midcom_baseclasses_components_handler
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
     * The type we're currently limited to. This is left to null if there is
     * no type limit induced by the URL of the request.
     *
     * @var string
     * @access private
     */
    var $_type = null;

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

    /**
     * The search form data, made transient using sessioning, stays null in case of first
     * calls to the system
     *
     * Members:
     *
     * - TODO
     *
     * @var Array
     * @access private
     */
    var $_search_data = null;

    function net_nehmer_jobmarket_handler_search()
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
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['page'] =& $this->_page;
        $this->_request_data['total_count'] =& $this->_total_count;
        $this->_request_data['total_pages'] =& $this->_total_pages;
        $this->_request_data['search_data'] =& $this->_search_data;

        // Compute page numbers and urls
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_request_data['result_url'] = "{$prefix}search/result/1.html";
        if ($this->_page !== null)
        {
            if ($this->_page < $this->_total_pages)
            {
                $this->_request_data['next_page'] = $this->_page + 1;
                $this->_request_data['next_page_url'] = "{$prefix}search/result/{$this->_request_data['next_page']}.html";
            }
            else
            {
                $this->_request_data['next_page'] = null;
                $this->_request_data['next_page_url'] = null;
            }

            if ($this->_page > 1)
            {
                $this->_request_data['previous_page'] = $this->_page - 1;
                $this->_request_data['previous_page_url'] = "{$prefix}search/result/{$this->_request_data['previous_page']}.html";
            }
            else
            {
                $this->_request_data['previous_page'] = null;
                $this->_request_data['previous_page_url'] = null;
            }

        }
        else
        {
            $this->_request_data['next_page'] = null;
            $this->_request_data['next_page_url'] = null;
            $this->_request_data['previous_page'] = null;
            $this->_request_data['previous_page_url'] = null;
        }
    }

    /**
     * Validates the ticker arguments.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool True if the request can be handled, false otherwise.
     */
    function _can_handle_search($handler_id, $args, &$data)
    {
        if (   $args[0] != 'offer'
            && $args[0] != 'application')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * This code prepares a search form for viewing. It does not pick up any leftover
     * session information regarding search parameters. This will force-start a new
     * search session unconditionally, as the result handler will priorize form submissions
     * over session data. It will however try to pick up the selections of the last search
     * session as far as possible; current URL settings are not overridden however.
     *
     * You can override this behavior by setting the HTTP GET parameter reset_search_session,
     * which forces the component to display a clean search form again.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_search($handler_id, $args, &$data)
    {
        $this->_type_list = $this->_config->get('type_config');
        $this->_mode = $args[0];
        if ($handler_id == 'search_type')
        {
            if (! array_key_exists($args[1], $this->_type_list))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The type {$args[1]} is unknown.");
                // This will exit.
            }
            if (! $this->_type_list[$args[1]]["{$this->_mode}_schema"])
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The type {$args[1]} is not configured for mode {$this->_mode}.");
                // This will exit.
            }
            $this->_type = $args[1];
        }

        $session = new midcom_service_session();

        if (   $session->exists('search_data')
            && ! array_key_exists('reset_search_session', $_REQUEST))
        {
            $this->_search_data = $session->get('search_data');
        }
        else
        {
            // Initialize search data.
            $this->_search_data = Array
            (
                'mode' => '',
                'locations' => Array(),
                'sectors' => Array(),
                'type' => null,
                'types' => Array(),
                'type_mode' => false,
                'last_page' => 1,
                'search_all_types' => true,
            );
        }
        $this->_search_data['mode'] = $this->_mode;
        if ($this->_type)
        {
            $this->_search_data['type_mode'] = true;
            $this->_search_data['type'] = Array($this->_type);
        }
        else
        {
            $this->_search_data['type_mode'] = false;
            if (! $this->_search_data['types'])
            {
                $this->_search_data['search_all_types'] = true;
            }
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("search {$this->_mode}s"));
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_SEARCH_OFFERS : NET_NEHMER_JOBMARKET_LEAFID_SEARCH_APPLICATIONS;

        if ($handler_id == 'search_type')
        {
            $tmp = Array
            (
                Array
                (
                    MIDCOM_NAV_URL => "search/{$this->_mode}/{$this->_type}.html",
                    MIDCOM_NAV_NAME => $this->_type_list[$this->_type]['title'],
                ),
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        }

        return true;
    }

    /**
     * Displays the search form. Accomodates the logic required to dynamically activate parts
     * of the form. The form is constructed manually, without QuickForm.
     */
    function _show_search($handler_id, &$data)
    {
        midcom_show_style('search-form-start');

        // Add hidden fields where required
        echo "<input type='hidden' name='mode' value='{$this->_mode}'/>\n";
        if ($this->_type)
        {
            echo "<input type='hidden' name='type_mode' value='1'/>\n";
            echo "<input type='hidden' name='type' value='{$this->_type}'/>\n";
            if ($this->_type)
            {
                if ($this->_type_list[$this->_type]['search_by_sectors'])
                {
                    midcom_show_style('search-form-sectors');
                }
                if ($this->_type_list[$this->_type]['search_by_locations'])
                {
                    midcom_show_style('search-form-locations');
                }
            }
        }
        else
        {
            echo "<input type='hidden' name='type_mode' value='0'/>\n";
            midcom_show_style('search-form-typelist');
            midcom_show_style('search-form-sectors');
            midcom_show_style('search-form-locations');
        }

        midcom_show_style('search-form-end');
    }

    /**
     * Prepares the search result for displaying. Submitted form data takes priority over
     * HTTP session information. Search results are available as long as the session lasts
     * therefore.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_result($handler_id, $args, &$data)
    {
        $this->_type_list = $this->_config->get('type_config');

        // First, we determine startup mode: either based on a form result or from the PHP session
        $session = new midcom_service_session();
        if (array_key_exists('net_nehmer_jobmarket_search_submit', $_REQUEST))
        {
            $this->_search_data = Array
            (
                'mode' => '',
                'locations' => Array(),
                'sectors' => Array(),
                'type' => null,
                'types' => Array(),
                'type_mode' => false,
                'last_page' => 1,
                'search_all_types' => true,
            );
            $this->_process_form();
        }
        else if ($session->exists('search_data'))
        {
            $this->_search_data = $session->get('search_data');
            $this->_process_session();
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Neither form nor session data present, cannot display a resultset.');
            // This will exit.
        }
        $session->set('search_data', $this->_search_data);

        $this->_page = (int) $args[0];
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

        // Update search data session with last viewed page (useful for back-links)
        $this->_search_data['last_page'] = $this->_page;
        $session->set('search_data', $this->_search_data);

        // Prepare object state for listing.
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        // Set request data, construct a back-to-search-result link as well.
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_prepare_request_data();
        if ($this->_type)
        {
            $this->_request_data['new_search_url'] = "{$prefix}search/{$this->_mode}/{$this->_type}.html?reset_search_session";
            $this->_request_data['last_search_url'] = "{$prefix}search/{$this->_mode}/{$this->_type}.html";
        }
        else
        {
            $this->_request_data['new_search_url'] = "{$prefix}search/{$this->_mode}.html?reset_search_session";
            $this->_request_data['last_search_url'] = "{$prefix}search/{$this->_mode}.html";
        }

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("search {$this->_mode}s"));
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_SEARCH_OFFERS : NET_NEHMER_JOBMARKET_LEAFID_SEARCH_APPLICATIONS;

        $tmp = Array();
        if ($this->_search_data['type_mode'])
        {
            $tmp = Array
            (
                Array
                (
                    MIDCOM_NAV_URL => "search/{$this->_mode}/{$this->_type}.html",
                    MIDCOM_NAV_NAME => $this->_type_list[$this->_type]['title'],
                ),
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "search/result/{$this->_page}.html",
            MIDCOM_NAV_NAME => $this->_l10n->get('search result'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

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
        // WARNING: This must be kept in sync with entry.php !

        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('offer', '=', ($this->_mode == 'offer'));

        if (count($this->_search_data['locations'] > 0))
        {
            $qb->begin_group('OR');
            foreach ($this->_search_data['locations'] as $location)
            {
                $qb->add_constraint('location', '=', $location);
            }
            $qb->end_group('OR');
        }

        if (count($this->_search_data['sectors'] > 0))
        {
            $qb->begin_group('OR');
            foreach ($this->_search_data['sectors'] as $sector)
            {
                $qb->add_constraint('sector', '=', $sector);
            }
            $qb->end_group('OR');
        }

        if ($this->_search_data['type_mode'])
        {
            $qb->add_constraint('type', '=', $this->_search_data['type']);
        }
        else
        {
            $qb->begin_group('OR');
            foreach ($this->_search_data['types'] as $type)
            {
                $qb->add_constraint('type', '=', $type);
            }
            $qb->end_group('OR');
        }

        return $qb;

        // WARNING: This must be kept in sync with entry.php !
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
     * Displays search result.
     */
    function _show_result($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('search-result-begin');
        if ($this->_entries)
        {
            foreach ($this->_entries as $key => $entry)
            {
                $data['entry'] = $entry;
                $data['view_url'] = "{$prefix}entry/view/{$entry->guid}.html";
                $param_name = ($entry->offer ? 'offer_schema' : 'application_schema');
                $this->_datamanager->set_schema($this->_type_list[$entry->type][$param_name]);
                $this->_datamanager->set_storage($entry);
                midcom_show_style('search-result-item');
            }
        }
        midcom_show_style('search-result-end');
    }

    /**
     * Processes a search form submission. Tries to be as lax as possible when evaluating
     * the request data, only missing required information such as 'mode' lead to critical
     * errors. All information is fully revalidated, to prevent request tampering.
     */
    function _process_form()
    {
        // Search mode goes first, this is mandatory
        if (! array_key_exists('mode', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Incomplete request, the key "mode" is missing.');
            // This will exit.
        }
        $this->_mode = $_REQUEST['mode'];
        if (   $this->_mode != 'offer'
            && $this->_mode != 'application')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Invalid search mode, got {$this->_mode}, expected one of offer, application.");
            // This will exit.
        }
        $this->_search_data['mode'] = $this->_mode;

        // Now we go for type mode, which is optional, but if set, type itself must be set as well
        // Type declarations must then be valid, of course.
        //
        // Otherwise, we look out for a type array, which contains further restrictions. They
        // are validated against the permissions then. The types_all key is always ignored, as
        // all search is only done if no other restriction has been made. A JS should ensure
        // sanity of the checkboxes. Invalid type array entries are dropped silently.
        //
        // Finally, if we don't have a types array, we are in list all mode. In that case we
        // need to go over all types nevertheless and add those which are readable in the
        // current context.
        //
        if (   array_key_exists('type_mode', $_REQUEST)
            && $_REQUEST['type_mode'])
        {
            $this->_search_data['type_mode'] = true;
            $this->_search_data['search_all_types'] = false;
            if (! array_key_exists('type', $_REQUEST))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Incomplete request, the key "type" is missing.');
                // This will exit.
            }
            $this->_type = $_REQUEST['type'];
            if (! array_key_exists($this->_type, $this->_type_list))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Invalid type specification, got {$this->_type}, which is unknown.");
                // This will exit.
            }
            if (! $this->_type_list[$this->_type]["{$this->_mode}_schema"])
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Invalid type specification, got {$this->_type}, which is not configured for mode {$this->_mode}.");
                // This will exit.
            }
            $this->_search_data['type'] = $this->_type;
            if (! $this->_type_list[$this->_type]["{$this->_mode}_anonymous_read"])
            {
                $_MIDCOM->auth->require_valid_user();
            }
        }
        else if (array_key_exists('types', $_REQUEST))
        {
            $this->_search_data['search_all_types'] = false;
            if (! is_array($_REQUEST['types']))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Invalid types request data, expected an array, got something else.');
                // This will exit.
            }
            foreach ($_REQUEST['types'] as $type)
            {
                if (   array_key_exists($type, $this->_type_list)
                    && $this->_type_list[$type]["{$this->_mode}_schema"]
                    && (   $_MIDCOM->auth->user
                        || $this->_type_list[$type]["{$this->_mode}_anonymous_read"]))
                {
                    $this->_search_data['types'][] = $type;
                }
                else
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Encountered unknown or restricted type {$type}, skipping...");
                    debug_pop();
                }
            }
        }
        else
        {
            $this->_search_data['search_all_types'] = true;
            foreach ($this->_type_list as $type => $config)
            {
                if (   $this->_type_list[$type]["{$this->_mode}_schema"]
                    && $this->_type_list[$type]['show_in_search_all']
                    && (   $_MIDCOM->auth->user
                        || $this->_type_list[$type]["{$this->_mode}_anonymous_read"]))
                {
                    $this->_search_data['types'][] = $type;
                }
            }
        }

        // Now we go for sectors, again dropping unknown keys.
        $sectors = $this->_config->get('sector_list');
        if (array_key_exists('sectors', $_REQUEST))
        {
            if (! is_array($_REQUEST['sectors']))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Invalid sectors request data, expected an array, got something else.');
                // This will exit.
            }
            foreach ($_REQUEST['sectors'] as $sector)
            {
                if (array_key_exists($sector, $sectors))
                {
                    $this->_search_data['sectors'][] = $sector;
                }
                else
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Encountered unknown or restricted location {$sector}, skipping...");
                    debug_pop();
                }
            }
        }

        // Now we go for locations, again dropping unknown keys.
        $locations = $this->_config->get('location_list');
        if (array_key_exists('locations', $_REQUEST))
        {
            if (! is_array($_REQUEST['locations']))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Invalid locations request data, expected an array, got something else.');
                // This will exit.
            }
            foreach ($_REQUEST['locations'] as $location)
            {
                if (array_key_exists($location, $locations))
                {
                    $this->_search_data['locations'][] = $location;
                }
                else
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Encountered unknown or restricted location {$location}, skipping...");
                    debug_pop();
                }
            }
        }
    }

    /**
     * This function loads all data from the search_data session driven storage
     * and updates the local instance accordingly. Only rudimentary checks are done
     * here.
     */
    function _process_session()
    {
        $this->_mode = $this->_search_data['mode'];
        if ($this->_search_data['type_mode'])
        {
            $this->_type = $this->_search_data['type'];
            if (! array_key_exists($this->_type, $this->_type_list))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Invalid type specification, got {$this->_type}, which is unknown.");
                // This will exit.
            }
        }
    }



}

?>
