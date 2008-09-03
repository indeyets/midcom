<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market entry management handler class.
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_handler_entry extends midcom_baseclasses_components_handler
{

    /**
     * This is an array holding the type list.
     *
     * @var Array
     * @access private
     */
    var $_type_list = null;

    /**
     * The entry which is currently being operated on.
     *
     * @var net_nehmer_jobmarket_entry
     * @access private
     */
    var $_entry = null;

    /**
     * The DM controller used for editing operations.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The DM instance used for displaying the data.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * A shortcut to $_type_list[$_type].
     *
     * @var Array
     * @access private
     */
    var $_type_config = null;

    /**
     * The name of the type we have to create.
     *
     * @var string
     * @access private
     */
    var $_type = null;

    /**
     * One of 'offer' or 'application'.
     *
     * @var string
     * @access private
     */
    var $_mode = true;

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

    function net_nehmer_jobmarket_handler_entry()
    {
        parent::__construct();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['type_list'] =& $this->_type_list;
        $this->_request_data['type_config'] =& $this->_type_config;
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['entry'] =& $this->_entry;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['search_data'] =& $this->_search_data;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param string $view Mode in which we are operating, one of 'view', 'edit', 'delete'.
     *     Defaults to 'view'.
     */
    function _update_breadcrumb_line($view = 'view')
    {
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "entry/view/{$this->_entry->guid}.html",
                MIDCOM_NAV_NAME => $this->_entry->title,
            ),
        );
        switch ($view)
        {
            // These to can be treated uniformly.
            case 'edit':
            case 'delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "entry/{$view}/{$this->_entry->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get($view),
                );
                break;

        }
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    /**
     * This is a helper interfacing with the search session. It will make a few
     * URLs available like back to search result set, next/previous entry etc.
     */
    function _add_search_request_data()
    {
        $session = new midcom_service_session();
        if ($session->exists('search_data'))
        {
            $this->_search_data = $session->get('search_data');
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $this->_request_data['search_result_url'] = "{$prefix}search/result/{$this->_search_data['last_page']}.html";

            $qb = $this->_prepare_result_qb();
            $qb->add_constraint('published', '<', $this->_entry->published);
            $qb->add_order('published', 'DESC');
            $qb->set_limit(1);
            $tmp = $qb->execute_unchecked();
            if ($tmp)
            {
                $this->_request_data['next_search_result'] = $tmp[0];
                $this->_request_data['next_search_result_url'] = "{$prefix}entry/view/{$this->_request_data['next_search_result']->guid}.html";
            }
            else
            {
                $this->_request_data['next_search_result'] = null;
                $this->_request_data['next_search_result_url'] = null;
            }

            $qb = $this->_prepare_result_qb();
            $qb->add_constraint('published', '>', $this->_entry->published);
            $qb->add_order('published', 'ASC');
            $qb->set_limit(1);
            $tmp = $qb->execute_unchecked();
            if ($tmp)
            {
                $this->_request_data['prev_search_result'] = $tmp[0];
                $this->_request_data['prev_search_result_url'] = "{$prefix}entry/view/{$this->_request_data['prev_search_result']->guid}.html";
            }
            else
            {
                $this->_request_data['prev_search_result'] = null;
                $this->_request_data['prev_search_result_url'] = null;
            }
        }
        else
        {
            $this->_request_data['search_result'] = null;
            $this->_request_data['search_result_url'] = null;
            $this->_request_data['next_search_result'] = null;
            $this->_request_data['next_search_result_url'] = null;
            $this->_request_data['prev_search_result'] = null;
            $this->_request_data['prev_search_result_url'] = null;
        }
    }

    /**
     * Internal helper function, prepares a query builder which lists all search results applicable
     * in the current authentication state and display mode. Further constraints like
     * ordering and offset/count limits must be applied by the callee.
     *
     * @return midcom_core_querybuilder The constructed QB.
     */
    function _prepare_result_qb()
    {
        // WARNING: Keep this function in-sync with its pendant in search.php

        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('offer', '=', ($this->_mode == 'offer'));

        // If applicable, limit down to the selected locations and sectors
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

        // WARNING: Keep this function in-sync with its pendant in search.php
    }

    /**
     * Little helper, populates the $_datamanager member accordingly.
     */
    function _prepare_datamanager()
    {
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_datamanager->set_schema($this->_type_config["{$this->_mode}_schema"]);
        $this->_datamanager->set_storage($this->_entry);
    }

    /**
     * Little helper, populates the $_controller member accordingly.
     */
    function _prepare_controller()
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->load_schemadb($this->_config->get('schemadb'));
        $this->_controller->set_storage($this->_entry, $this->_type_config["{$this->_mode}_schema"]);
        $this->_controller->initialize();
    }

    /**
     * Loads everything needed to display an entry, no dark magic here.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_type_list = $this->_config->get('type_config');

        $this->_entry = new net_nehmer_jobmarket_entry($args[0]);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'This entry is unknown.');
            // This will exit.
        }

        $this->_mode = ($this->_entry->offer ? 'offer' : 'application');
        $this->_type = $this->_entry->type;
        $this->_type_config = $this->_type_list[$this->_type];

        if (! $this->_type_config["{$this->_mode}_anonymous_read"])
        {
            $_MIDCOM->auth->require_valid_user();
        }

        $this->_prepare_datamanager();

        $this->_prepare_request_data();
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_entry))
        {
            $data['edit_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/edit/{$this->_entry->guid}.html";
        }
        else
        {
            $data['edit_url'] = null;
        }
        if ($_MIDCOM->auth->can_do('midgard:delete', $this->_entry))
        {
            $data['delete_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/delete/{$this->_entry->guid}.html";
        }
        else
        {
            $data['delete_url'] = null;
        }
        $this->_add_search_request_data();

        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $_MIDCOM->substyle_append($this->_type_config["{$this->_mode}_schema"]);
        $this->_component_data['active_leaf'] = NET_NEHMER_JOBMARKET_LEAFID_OTHER;
        $this->_update_breadcrumb_line();

        return true;
    }

    /**
     * Displays an entry.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('entry-view');
    }

    /**
     * Loads everything needed to display an entry, no dark magic here.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_type_list = $this->_config->get('type_config');

        $this->_entry = new net_nehmer_jobmarket_entry($args[0]);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'This entry is unknown.');
            // This will exit.
        }
        $_MIDCOM->auth->require_do('midgard:update', $this->_entry);

        $this->_mode = ($this->_entry->offer ? 'offer' : 'application');
        $this->_type = $this->_entry->type;
        $this->_type_config = $this->_type_list[$this->_type];

        $this->_prepare_controller();
        $this->_process_controller();

        $this->_prepare_request_data();
        $data['view_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "entry/view/{$this->_entry->guid}.html";

        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $_MIDCOM->substyle_append($this->_type_config["{$this->_mode}_schema"]);
        $this->_component_data['active_leaf'] = NET_NEHMER_JOBMARKET_LEAFID_OTHER;
        $this->_update_breadcrumb_line('edit');

        return true;
    }

    /**
     * Processes the datamanager, and redirects to the view on save/cancel events.
     */
    function _process_controller()
    {
        switch($this->_controller->process_form())
        {
            case 'save':
                $topic = $this->_config->get('index_to');
                if (! $topic)
                {
                    $topic = $this->_topic;
                }

                $indexer =& $_MIDCOM->get_service('indexer');
                net_nehmer_jobmarket_entry::index($this->_controller->datamanager, $indexer,
                    $topic, $this->_type_config["{$this->_mode}_anonymous_read"]);

                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("entry/view/{$this->_entry->guid}.html");
                // This will exit.
        }
        // Do nothing while editing.
    }

    /**
     * Displays an entry edit form.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('entry-edit');
    }

    /**
     * Loads everything needed to display an entry, no dark magic here.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_type_list = $this->_config->get('type_config');

        $this->_entry = new net_nehmer_jobmarket_entry($args[0]);
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'This entry is unknown.');
            // This will exit.
        }

        $this->_mode = ($this->_entry->offer ? 'offer' : 'application');
        $this->_type = $this->_entry->type;
        $this->_type_config = $this->_type_list[$this->_type];

        $_MIDCOM->auth->require_do('midgard:delete', $this->_entry);
        if (array_key_exists('net_nehmer_jobmarket_deleteok', $_REQUEST))
        {
            // Delete entry
            if (! $this->_entry->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to delete entry: ' . mgd_errstr());
                // This will exit.
            }
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_entry->guid);

            $_MIDCOM->relocate("self/{$this->_mode}.html");
        }

        $this->_prepare_datamanager();

        $this->_prepare_request_data();
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_entry))
        {
            $data['edit_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "entry/edit/{$this->_entry->guid}.html";
        }
        else
        {
            $data['edit_url'] = null;
        }
        $data['view_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "entry/view/{$this->_entry->guid}.html";

        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $_MIDCOM->substyle_append($this->_type_config["{$this->_mode}_schema"]);
        $this->_component_data['active_leaf'] = NET_NEHMER_JOBMARKET_LEAFID_OTHER;
        $this->_update_breadcrumb_line('delete');

        return true;
    }

    /**
     * Displays an entry.
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