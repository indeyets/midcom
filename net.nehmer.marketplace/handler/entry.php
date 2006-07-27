<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace entry management handler class.
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_handler_entry extends midcom_baseclasses_components_handler
{
    /**
     * The entry which is currently being operated on.
     *
     * @var net_nehmer_marketplace_entry
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
     * One of 'ask' or 'bid'.
     *
     * @var string
     * @access private
     */
    var $_mode = true;

    /**
     * Full category listing. Be aware, that to the contrary of the category.php
     * handler this list is not yet normalized towards display, as it is only required for
     * the breadcrumb navigation. You need to str_replace('|', ': ', ...) yourself.
     *
     * @var Array
     * @access private
     */
    var $_category_list = null;


    function net_nehmer_marketplace_handler_entry()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Initialize the category listing. Be aware, that to the contrary of the category.php
     * handler this list is not yet normalized towards display, as it is only required for
     * the breadcrumb navigation. You need to str_replace('|', ': ', ...) yourself.
     */
    function _on_initialize()
    {
        $this->_category_list = $this->_config->get('categories');
    }


    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['entry'] =& $this->_entry;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Little helper, populates the $_datamanager member accordingly.
     */
    function _prepare_datamanager()
    {
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_datamanager->set_schema($this->_config->get("{$this->_mode}_schema"));
        $this->_datamanager->set_storage($this->_entry);
    }

    /**
     * Little helper, populates the $_controller member accordingly.
     */
    function _prepare_controller()
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->load_schemadb($this->_config->get('schemadb'));
        $this->_controller->set_storage($this->_entry, $this->_config->get("{$this->_mode}_schema"));
        $this->_controller->initialize();
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param string $view Mode in which we are operating, one of 'view', 'edit' and 'delete'.
     */
    function _update_breadcrumb_line($view = 'view')
    {
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "list/{$this->_entry->category}/{$this->_mode}/1.html",
                MIDCOM_NAV_NAME => str_replace('|', ': ', $this->_category_list[$this->_entry->category]),
            ),
            Array
            (
                MIDCOM_NAV_URL => "entry/view/{$this->_entry->guid}.html",
                MIDCOM_NAV_NAME => $this->_entry->title,
            ),
        );

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
     * Loads everything needed to display an entry, no dark magic here.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $this->_entry = new net_nehmer_marketplace_entry($args[0]);
        if (! $this->_entry)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The entry {$args[0]} is unknown.";
            return false;
        }
        $this->_mode = ($this->_entry->ask ? 'ask' : 'bid');

        // First, update the status information, as DM startups can alredy fire up NAP.
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_BIDS;
        $this->_update_breadcrumb_line();

        $this->_prepare_datamanager();

        $this->_prepare_request_data();
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_entry))
        {
            $data['edit_url'] = "{$prefix}entry/edit/{$this->_entry->guid}.html";
        }
        else
        {
            $data['edit_url'] = null;
        }
        if ($_MIDCOM->auth->can_do('midgard:delete', $this->_entry))
        {
            $data['delete_url'] = "{$prefix}entry/delete/{$this->_entry->guid}.html";
        }
        else
        {
            $data['delete_url'] = null;
        }

        $data['next'] = $this->_entry->get_next();
        if ($data['next'])
        {
            $data['next_url'] = "{$prefix}entry/view/{$data['next']->guid}.html";
        }
        else
        {
            $data['next_url'] = null;
        }
        $data['previous'] = $this->_entry->get_previous();
        if ($data['previous'])
        {
            $data['previous_url'] = "{$prefix}entry/view/{$data['previous']->guid}.html";
        }
        else
        {
            $data['previous_url'] = null;
        }
        $data['category_url'] = "{$prefix}list/{$this->_entry->category}/{$this->_mode}/1.html";

        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $_MIDCOM->substyle_append($this->_config->get("{$this->_mode}_schema"));

        return true;
    }

    /**
     * Displays an entry.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('entry-view');
    }

    /**
     * Loads everything needed to display an entry, no dark magic here.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_entry = new net_nehmer_marketplace_entry($args[0]);
        if (! $this->_entry)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The entry {$args[0]} is unknown.";
            return false;
        }
        $_MIDCOM->auth->require_do('midgard:update', $this->_entry);

        $this->_mode = ($this->_entry->ask ? 'ask' : 'bid');

        // First, update the status information, as DM startups can alredy fire up NAP.
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_BIDS;
        $this->_update_breadcrumb_line('edit');

        $this->_prepare_controller();
        $this->_process_controller();

        $this->_prepare_request_data();
        $data['view_url'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "entry/view/{$this->_entry->guid}.html";

        $_MIDCOM->set_26_request_metadata(time(), $this->_entry->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $_MIDCOM->substyle_append($this->_config->get("{$this->_mode}_schema"));

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
                net_nehmer_marketplace_entry::index($this->_controller->datamanager, $indexer, $topic);

                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("entry/view/{$this->_entry->guid}.html");
                // This will exit.
        }
        // Do nothing while editing.
    }

    /**
     * Displays an entry edit form.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('entry-edit');
    }

    /**
     * Loads everything needed to display an entry, no dark magic here.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $this->_entry = new net_nehmer_marketplace_entry($args[0]);
        if (! $this->_entry)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The entry {$args[0]} is unknown.";
            return false;
        }

        // First, update the status information, as DM startups can alredy fire up NAP.
        $this->_mode = ($this->_entry->ask ? 'ask' : 'bid');
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_BIDS;
        $this->_update_breadcrumb_line('delete');

        $_MIDCOM->auth->require_do('midgard:delete', $this->_entry);
        if (array_key_exists('net_nehmer_marketplace_deleteok', $_REQUEST))
        {
            // Delete entry
            if (! $this->_entry->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to delete entry: ' . mgd_errstr());
                // This will exit.
            }

            // Update the index
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
        $_MIDCOM->substyle_append($this->_config->get("{$this->_mode}_schema"));

        return true;
    }

    /**
     * Displays an entry.
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('entry-delete');
    }

}

?>
