<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * result edit/delete handler
 *
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_handler_result_admin extends midcom_baseclasses_components_handler
{
    /**
     * The result to operate on
     *
     * @var midcom_db_result
     * @access private
     */
    var $_result = null;

    /**
     * The dog this result belongs to
     *
     * @var midcom_db_result
     * @access private
     */
    var $_dog = null;

    /**
     * The Datamanager of the result to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the result used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['result'] =& $this->_result;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        /* On a second though, I don't think we need these here
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "result/edit/{$this->_result->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_result->can_do('midgard:update'),
        ));
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "result/delete/{$this->_result->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_result->can_do('midgard:delete'),
        ));
        */
    }


    /**
     * Simple default constructor.
     */
    function net_fernmark_pedigree_handler_result_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, loads the datamanager for the current result. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_result))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for result {$this->_result->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current result. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_result);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for result {$this->_result->id}.");
            // This will exit.
        }
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "dog/{$this->_dog->guid}.html",
            MIDCOM_NAV_NAME => $this->_dog->name_with_kennel,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "result/{$this->_result->guid}.html",
            MIDCOM_NAV_NAME => $this->_result->eventname,
        );
        list ($handler_op, $handler_type) = explode('-', $handler_id, 2);
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$handler_type}/{$handler_op}/{$this->_result->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_op),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays an result edit view.
     *
     * Note, that the result for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation result,
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_result = new net_fernmark_pedigree_dog_result_dba($args[0]);
        if (! $this->_result)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The result '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_result->require_do('midgard:update');
        $this->_dog = new net_fernmark_pedigree_dog_dba($this->_result->dog);

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                // Reindex the result
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nehmer_blog_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);
                */

                // *** FALL-THROUGH ***

            case 'cancel':       
                $_MIDCOM->relocate("result/{$this->_result->guid}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_result);
        $_MIDCOM->set_26_request_metadata($this->_result->metadata->revised, $this->_result->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_result->eventname}");
        $this->_request_data['title'] = sprintf($this->_l10n_midcom->get('edit %s for %s'), $this->_result->eventname, $this->_dog->name_with_kennel);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded result.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin-edit-result');
    }

    /**
     * Displays an result delete confirmation view.
     *
     * Note, that the result for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation result,
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_result = new net_fernmark_pedigree_dog_result_dba($args[0]);
        if (! $this->_result)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The result {$args[0]} was not found.");
            // This will exit.
        }
        $this->_result->require_do('midgard:delete');

        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Not implemented');
        // This will exit
        /*
        $this->_load_datamanager();

        if (array_key_exists('net_nehmer_blog_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_result->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete result {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_result->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nehmer_blog_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            if ($this->_config->get('view_in_url'))
            {
                $_MIDCOM->relocate("view/{$this->_result->eventname}.html");
            }
            else
            {
                $_MIDCOM->relocate("{$this->_result->eventname}.html");
            }
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_result->metadata->revised, $this->_result->guid);
        $this->_view_toolbar->bind_to($this->_result);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_result->eventname}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
        */
    }

    /**
     * Shows the delete form
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin-delete-result');
    }
}

?>