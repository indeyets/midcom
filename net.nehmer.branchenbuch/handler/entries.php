<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch Category Management class.
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_handler_entries extends midcom_baseclasses_components_handler
{
    /**
     * The category record encapsulating the root (type) category.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_type = null;

    /**
     * The list of entries found.
     *
     * @var array
     * @access private
     */
    var $_entries = null;

    /**
     * The entry currently being displayed.
     *
     * @var array
     * @access private
     */
    var $_entry = null;

    /**
     * A DM created on the current entry.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_entry_dm = null;

    /**
     * The controller used when editing entries.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_entry_controller = null;

    /**
     * The category we are currently displaying.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_branche = null;

    /**
     * The total number of results.
     *
     * @var int
     * @access private
     */
    var $_total = 0;

    /**
     * The currently displayed page number, a one-based index number.
     *
     * @var int
     * @access private
     */
    var $_page = null;

    /**
     * The last valid page, a one-based index number.
     *
     * @var int
     * @access private
     */
    var $_last_page = null;

    /**
     * The base URL used within the list handlers' paging code. Cached for better code readability
     * mainly. This is a full prefix replacing MIDCOM_CONTEXT_ANCHORPREFIX.
     *
     * @var string
     * @access private
     */
    var $_list_url_base = null;

    /**
     * The schema manager class encapsulating all schema operations referencing
     * account schemas.
     *
     * @var net_nehmer_branchenbuch_schemamgr
     * @access private
     */
    var $_schemamgr = null;

    /**
     * The handler class responsible for the custom search forms.
     *
     * @var net_nehmer_branchenbuch_callbacks_searchbase
     * @access protected
     */
    var $_customsearch = null;

    /**
     * The processing message for the image upload tool.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';

    function net_nehmer_branchenbuch_handler_entries()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal Helper encapsulating the index call.
     *
     * @param midcom_helper_datamanager2_datamanager &$datamanager The DM2 instance to index.
     */
    function _index(&$datamanager)
    {
        $topic = $this->_config->get('index_to');
        if (! $topic)
        {
            $topic = $this->_topic;
        }

        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_branchenbuch_entry::index($datamanager, $indexer, $topic);
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['branche'] =& $this->_branche;
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['entry'] =& $this->_entry;
        $this->_request_data['entry_dm'] =& $this->_entry_dm;
        $this->_request_data['entry_controller'] =& $this->_entry_controller;
        $this->_request_data['total'] =& $this->_total;
        $this->_request_data['page'] =& $this->_page;
        $this->_request_data['last_page'] =& $this->_last_page;
        $this->_request_data['processing_msg'] =& $this->_processing_msg;
    }

    /**
     * This is the basic list handler which provides you with a flat, full listing of all
     * categories. As outlined in the components' main interface class, this code is optimized
     * for a two level hierarchy below the root category both to ease implementation and to keep
     * up the performance.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // Initialize Page number
        if (array_key_exists('page', $_REQUEST))
        {
            $this->_page = $_REQUEST['page'];
        }
        else
        {
            $this->_page = 1;
        }

        // Initialize the class depending on the currently selected handler
        // The functions called here populate the members $_total, $_type,
        // $_branche (optional), $_list_url_base and the request data key
        // return_url.

        if ($handler_id == 'entry_list_customsearch')
        {
            $this->_handler_list_init_from_customsearch($args[0]);
        }
        else
        {
            $this->_handler_list_init_from_branche($handler_id, $args[0]);
        }

        // Validate page numbers.
        $this->_last_page = max
        (
            1,
            ceil($this->_total / $this->_config->get('category_list_page_size'))
        );
        $this->_handler_list_validate_page_number();

        // Get result, switch again depending on handler.
        if ($handler_id == 'entry_list_customsearch')
        {
            $this->_entries = $this->_customsearch->list_entries($this->_page, $this->_config->get('category_list_page_size'));
        }
        else
        {
            $this->_entries = $this->_branche->list_entries($this->_page, $this->_config->get('category_list_page_size'));
        }

        $this->_prepare_entry_dm();

        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_branche->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_branche->get_full_name());
        $this->_component_data['active_leaf'] = $this->_type->guid;
        $this->_update_breadcrumb_line();

        $this->_prepare_request_data();
        $this->_handler_list_compute_urls($handler_id);

        return true;
    }

    /**
     * Checks the selected page number against the boundaries of the total resultset.
     *
     * It will relocate to the "nearest" valid page.
     *
     * @access private
     */
    function _handler_list_validate_page_number()
    {
        if ($this->_total == 0)
        {
            if ($this->_page > 1)
            {
                $_MIDCOM->relocate($this->_list_url_base);
                // This will exit.
            }
        }
        else if ($this->_page > $this->_last_page)
        {
            // In case we have a page number which is too large, we relocate to the last known
            // good page.

            if ($this->_last_page == 1)
            {
                $_MIDCOM->relocate($this->_list_url_base);
                // This will exit.
            }
            else
            {
                $_MIDCOM->relocate("{$this->_list_url_base}?page={$this->_last_page}");
                // This will exit.
            }
        }
    }

    /**
     * Initializes the list handler from a category. Calls generate_error an any problem.
     *
     * @param mixed $id The ID or GUID of the category to load.
     * @param mixed $handler_id The ID of the handler.
     * @access private
     */
    function _handler_list_init_from_branche($handler_id, $id)
    {
        $this->_branche = new net_nehmer_branchenbuch_branche($id);
        if (! $this->_branche)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The Branche {$args[0]} is unknown.");
            // This will exit.
        }
        $this->_type = $this->_branche->get_root_category();
        $this->_total = $this->_branche->get_live_entry_count();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if ($handler_id == 'entry_list_alpha')
        {
            // If we don't have a top-level category, traverse upwards until we find one.
            // Otherwise, we'd have as wrong filter spec.
            $branche = $this->_branche;
            while ($branche->parent != $this->_type->guid)
            {
                $branche = $branche->get_parent_branche();
            }
            $filter = $branche->name{0};
            $this->_request_data['return_url'] = "{$prefix}category/list/alpha/{$this->_type->guid}/{$filter}.html";
            $this->_list_url_base = "{$prefix}entry/list/alpha/{$this->_branche->guid}.html";
        }
        else
        {
            $this->_request_data['return_url'] = "{$prefix}category/list/{$this->_type->guid}.html";
            $this->_list_url_base = "{$prefix}entry/list/{$this->_branche->guid}.html";
        }
    }

    /**
     * Initializes the list handler for custom searching.
     * Calls generate_error an any problem.
     *
     * @param mixed $id The ID or GUID of the root category to load.
     * @access private
     */
    function _handler_list_init_from_customsearch($id)
    {
        $this->_type = new net_nehmer_branchenbuch_branche($id);
        if (! $this->_type)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The root category {$id} is unknown.");
            // This will exit.
        }
        $this->_branche = $this->_type;

        // Create a callback instance and fire us up
        $this->_handler_list_load_searchhandler();
        $this->_customsearch->prepare_query();
        $this->_total = $this->_customsearch->get_total();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_list_url_base = "{$prefix}entry/list/customsearch/{$this->_branche->guid}.html";
        $this->_request_data['return_url'] = "{$prefix}category/customsearch/{$this->_type->guid}.html";
    }

    /**
     * Creates and returns an instance of the custom search handler.
     *
     * Any error calls generate_error.
     */
    function _handler_list_load_searchhandler()
    {
        // Ensure that the base class is there (for the static callback)
        require_once(MIDCOM_ROOT . '/net/nehmer/branchenbuch/callbacks/searchbase.php');

        $type_config = $this->_config->get('type_config');
        $config = $type_config[$this->_type->type]['customsearch'];
        $this->_customsearch =&
            net_nehmer_branchenbuch_callbacks_searchbase::create_instance($this, $config);
    }

    /**
     * Computes the paging and return URLs for the List handler. This is dependant from the
     * currently active handler, which decides where to return to.
     *
     * @param string $handler_id The currently active handler.
     * @access private
     */
    function _handler_list_compute_urls($handler_id)
    {
        if ($this->_page < $this->_last_page)
        {
            $this->_request_data['next_page'] = $this->_page + 1;
            $this->_request_data['next_page_url'] = "{$this->_list_url_base}?page={$this->_request_data['next_page']}";
        }
        else
        {
            $this->_request_data['next_page'] = null;
            $this->_request_data['next_page_url'] = null;
        }

        if ($this->_page > 1)
        {
            $this->_request_data['previous_page'] = $this->_page - 1;
            $this->_request_data['previous_page_url'] = $this->_list_url_base;
            if ($this->_request_data['previous_page'] != 1)
            {
                $this->_request_data['previous_page_url'] .= "?page={$this->_request_data['previous_page']}";
            }
        }
        else
        {
            $this->_request_data['previous_page'] = null;
            $this->_request_data['previous_page_url'] = null;
        }

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param string $mode The view mode of a single entry, one of 'edit', 'delete'.
     *     Omit this (or set it to null) for view or category listing modes.
     */
    function _update_breadcrumb_line($view = null)
    {
        $tmp = Array();
        if ($this->_branche->id != $this->_type->id)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "entry/list/{$this->_branche->guid}.html",
                MIDCOM_NAV_NAME => $this->_branche->get_full_name(),
            );
        }
        if ($this->_entry)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "entry/view/{$this->_entry->guid}.html",
                MIDCOM_NAV_NAME => "{$this->_entry->firstname} {$this->_entry->lastname}",
            );
        }
        if (   $view == 'edit'
            || $view == 'delete')
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "entry/{$view}/{$this->_entry->guid}.html",
                MIDCOM_NAV_NAME => $this->_l10n_midcom->get($view),
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Prepares an entry datamanager, based off the type we're currently viewing.
     * The DM will not be initialized to any storage object, this has to be done
     * by the viewer code iterating over the entries to show.
     *
     * @param boolean $load_all_schemas Set this to true to load the schemas for all account types,
     *     mainly used for self account listings.
     */
    function _prepare_entry_dm($load_all_schemas = false)
    {
        $this->_schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);
        if ($load_all_schemas)
        {
            $schemadb = Array();
            foreach ($this->_schemamgr->remote->list_account_types() as $name => $description)
            {
                $schemadb[$name] = $this->_schemamgr->get_account_schema($name);
            }
        }
        else
        {
            $schemadb = Array($this->_type->type => $this->_schemamgr->get_account_schema($this->_type->type));
        }
        $this->_entry_dm = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_entry_dm->set_schema($this->_type->type);
    }


    /**
     * Shows all entries in the category.
     *
     * The current entry will be available in the <i>entry</i> key. An initialized datamamanger
     * can be found in <i>entry_dm</i>.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        if ($handler_id == 'entry_list_alpha')
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/view/alpha/{$this->_page}";
        }
        else if ($handler_id == 'entry_list_customsearch')
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/view/customsearch/{$this->_page}";
        }
        else
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/view/{$this->_page}";
        }

        midcom_show_style('entries-list-begin');
        foreach ($this->_entries as $guid => $entry)
        {
            $this->_entry =& $this->_entries[$guid];
            $this->_entry_dm->set_storage($this->_entry);
            $data['detail_url'] = "{$prefix}/{$this->_entry->guid}.html";
            midcom_show_style('entries-list-item');
        }
        midcom_show_style('entries-list-end');
    }

    /**
     * Shows an entry.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_entry($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if ($handler_id == 'entry_view')
        {
            $this->_page = null;
            $id = $args[0];
        }
        else
        {
            $this->_page = $args[0];
            $id = $args[1];
        }
        $this->_entry = new net_nehmer_branchenbuch_entry($id);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The Entry {$id} is unknown.");
        }
        $this->_branche = $this->_entry->get_branche();
        $this->_type = $this->_branche->get_root_category();
        $this->_prepare_entry_dm();
        $this->_entry_dm->set_storage($this->_entry);

        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_entry->firstname} {$this->_entry->lastname}");
        $this->_component_data['active_leaf'] = $this->_type->guid;
        $this->_update_breadcrumb_line();

        $this->_prepare_request_data();
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Compute a few base urls
        switch ($handler_id)
        {
            case 'entry_view_alpha':
                $data['return_url'] = "{$prefix}entry/list/alpha/{$this->_branche->guid}.html";
                $data['next_entry'] = $this->_entry->get_next();
                $data['previous_entry'] = $this->_entry->get_previous();
                $entry_baseurl = "{$prefix}entry/view/alpha/{$this->_page}/";
                break;

            case 'entry_view_customsearch':
                $this->_handler_list_load_searchhandler();
                $this->_customsearch->prepare_query();
                $data['return_url'] = "{$prefix}entry/list/customsearch/{$this->_type->guid}.html";
                $data['next_entry'] = $this->_customsearch->get_next($this->_entry->guid);
                $data['previous_entry'] = $this->_customsearch->get_previous($this->_entry->guid);
                $entry_baseurl = "{$prefix}entry/view/customsearch/{$this->_page}/";
                break;

            case 'entry_view_list':
                $data['return_url'] = "{$prefix}entry/list/{$this->_branche->guid}.html";
                $data['next_entry'] = $this->_entry->get_next();
                $data['previous_entry'] = $this->_entry->get_previous();
                $entry_baseurl = "{$prefix}entry/view/{$this->_page}/";
                break;

            default:
                $data['return_url'] = "{$prefix}entry/list/{$this->_branche->guid}.html";
                $data['next_entry'] = $this->_entry->get_next();
                $data['previous_entry'] = $this->_entry->get_previous();
                $entry_baseurl = "{$prefix}entry/view/";
                break;
        }

        if ($this->_page > 1)
        {
            $data['return_url'] .= "?page={$this->_page}";
        }


        // Get next, previous and admin URLs
        $data['next_entry_url'] = ($data['next_entry']) ?
            "{$entry_baseurl}{$data['next_entry']->guid}.html" : null;
        $data['previous_entry_url'] = ($data['previous_entry']) ?
            "{$entry_baseurl}{$data['previous_entry']->guid}.html" : null;

        $data['update_url'] = $_MIDCOM->auth->can_do('midgard:update', $this->_entry) ?
            "{$prefix}entry/edit/{$this->_entry->guid}.html" : null;
        $data['delete_url'] = $_MIDCOM->auth->can_do('midgard:delete', $this->_entry) ?
            "{$prefix}entry/delete/{$this->_entry->guid}.html" : null;


        return true;
    }

    /**
     * Shows an entry.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_entry($handler_id, &$data)
    {
        midcom_show_style('entry-show');
    }

    /**
     * This call allows you to edit an entry. Only applicable for entries you've
     * write privileges on.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_entry = new net_nehmer_branchenbuch_entry($args[0]);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The Entry {$args[0]} is unknown.");
        }
        $this->_branche = $this->_entry->get_branche();
        $this->_type = $this->_branche->get_root_category();

        $_MIDCOM->auth->require_do('midgard:update', $this->_entry);
        $this->_prepare_entry_controller();
        $this->_process_entry_controller();

        $this->_prepare_request_data();
        $data['return_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "entry/view/{$this->_entry->guid}.html";

        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_entry->firstname} {$this->_entry->lastname}");
        $this->_component_data['active_leaf'] = $this->_type->guid;
        $this->_update_breadcrumb_line('edit');

        return true;
    }

    /**
     * Prepares a DM2 controller instance useable to edit existing entries.
     */
    function _prepare_entry_controller()
    {
        $this->_schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);
        $schemadb = Array($this->_type->type => $this->_schemamgr->get_account_schema($this->_type->type));

        $this->_entry_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_entry_controller->schemadb = $schemadb;
        $this->_entry_controller->set_storage($this->_entry, $this->_type->type);
        $this->_entry_controller->initialize();
    }

    /**
     * Processes the entry controller form results, this will redirect on form
     * submissions.
     */
    function _process_entry_controller()
    {
        switch($this->_entry_controller->process_form())
        {
            case 'save':
                $this->_entry->delete_parameter('net.nehmer.branchenbuch', 'autocreated_entry');
                $this->_index($this->_entry_controller->datamanager);
                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("entry/view/{$this->_entry->guid}.html");
                // This will exit.
        }
        // Do nothing while editing.
    }

    /**
     * Shows the entry edit form.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('entry-edit');
    }

    /**
     * This list handler shows all entries currently associated to the active user.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list_self($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_entries = net_nehmer_branchenbuch_entry::list_by_user();
        $this->_total = count($this->_entries);
        $this->_prepare_entry_dm(true);

        $_MIDCOM->componentloader->load('net.nehmer.account');
        $interface =& $_MIDCOM->componentloader->get_interface_class('net.nehmer.account');
        $remote = $interface->create_remote_controller($this->_config->get('account_topic'));
        $this->_type = net_nehmer_branchenbuch_branche::get_root_category_by_type($remote->get_account_type());

        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('your entries'));
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_LISTSELF;

        $this->_prepare_request_data();
        return true;
    }

    /**
     * Shows all list of all entries owned by the current user.
     *
     * The current entry will be available in the <i>entry</i> key. An initialized datamamanger
     * can be found in <i>entry_dm</i>.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list_self($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        midcom_show_style('entries-list-self-begin');
        foreach ($this->_entries as $guid => $entry)
        {
            $this->_entry =& $this->_entries[$guid];
            $this->_entry_dm->set_schema($this->_entry->type);
            $this->_entry_dm->set_storage($this->_entry);
            $this->_branche = $this->_entry->get_branche();
            $this->_type = $this->_branche->get_root_category();
            $data['detail_url'] = "{$prefix}entry/view/{$this->_entry->guid}.html";
            if ($_MIDCOM->auth->can_do('midgard:update', $this->_entry))
            {
                $this->_request_data['update_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "entry/edit/{$this->_entry->guid}.html";
            }
            else
            {
                $this->_request_data['update_url'] = null;
            }
            if ($_MIDCOM->auth->can_do('midgard:delete', $this->_entry))
            {
                $this->_request_data['delete_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "entry/delete/{$this->_entry->guid}.html";
            }
            else
            {
                $this->_request_data['delete_url'] = null;
            }
            $data['branche_url'] = "{$prefix}entry/list/{$this->_branche->guid}.html";
            midcom_show_style('entries-list-self-item');
        }
        midcom_show_style('entries-list-self-end');
    }

    /**
     * Deletes an entry.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_entry = new net_nehmer_branchenbuch_entry($args[0]);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The Entry {$args[0]} is unknown.");
        }
        $this->_branche = $this->_entry->get_branche();
        $this->_type = $this->_branche->get_root_category();
        $this->_prepare_entry_dm();
        $this->_entry_dm->set_storage($this->_entry);

        $_MIDCOM->auth->require_do('midgard:delete', $this->_entry);
        if (array_key_exists('net_nehmer_branchenbuch_deleteok', $_REQUEST))
        {
            // Delete entry
            if (! $this->_entry->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to delete entry: ' . mgd_errstr());
                // This will exit.
            }

            // Drop the index entry
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_entry->guid);

            $_MIDCOM->relocate('entry/list/self.html');
        }

        $_MIDCOM->substyle_append($this->_type->type);
        // $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        // $_MIDCOM->set_pagetitle("{$this->_account->name} ({$this->_datamanager->schema->description})");

        $data['return_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "entry/view/{$this->_entry->guid}.html";
        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_entry->firstname} {$this->_entry->lastname}");
        $this->_component_data['active_leaf'] = $this->_type->guid;
        $this->_update_breadcrumb_line('delete');

        return true;
    }

    /**
     * Shows the delete confirmation.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('entry-delete');
    }

}

?>
