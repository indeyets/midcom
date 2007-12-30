<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Projects edit/delete deliverable handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_deliverable_admin extends midcom_baseclasses_components_handler
{
    /**
     * The deliverable to operate on
     *
     * @var org_openpsa_sales_salesproject_deliverable
     * @access private
     */
    var $_deliverable = null;

    /**
     * The Datamanager of the deliverable to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the deliverable used for editing
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
     * Schema to use for deliverable display
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_sales_handler_deliverable_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['deliverable'] =& $this->_deliverable;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "deliverable/edit/{$this->_deliverable->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_deliverable->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );

        /*$this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "deliverable/delete/{$this->_deliverable->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_deliverable->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );*/

        switch ($handler_id)
        {
            case 'deliverable_edit':
                $this->_view_toolbar->disable_item("deliverable/edit/{$this->_deliverable->guid}.html");
                break;
            case 'deliverable_delete':
                $this->_view_toolbar->disable_item("deliverable/delete/{$this->_deliverable->guid}.html");
                break;
        }
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/legacy.css",
            )
        );
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb_salesproject_deliverable'];
    }

    /**
     * Internal helper, loads the datamanager for the current deliverable. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_deliverable))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for deliverable {$this->_deliverable->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current deliverable. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_deliverable, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for deliverable {$this->_deliverable->id}.");
            // This will exit.
        }
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param string $handler_id
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = org_openpsa_sales_viewer::update_breadcrumb_line($this->_request_data['deliverable']);

        switch ($handler_id)
        {
            case 'deliverable_edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "deliverable/edit/{$this->_deliverable->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'deliverable_delete':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "deliverable/delete/{$this->_deliverable->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays a deliverable edit view.
     *
     * Note, that the deliverable for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation deliverable
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_deliverable = new org_openpsa_sales_salesproject_deliverable($args[0]);
        if (! $this->_deliverable)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The deliverable {$args[0]} was not found.");
            // This will exit.
        }
        $this->_deliverable->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the deliverable
                //$indexer =& $_MIDCOM->get_service('indexer');
                //org_openpsa_sales_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("deliverable/{$this->_deliverable->guid}/");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_deliverable->title}");
        $_MIDCOM->bind_view_to_object($this->_deliverable, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded deliverable.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('show-deliverable-edit');
    }

    /**
     * Displays a deliverable delete confirmation view.
     *
     * Note, that the deliverable for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation deliverable
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_deliverable = new org_openpsa_sales_salesproject_deliverable($args[0]);
        if (! $this->_deliverable)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The deliverable {$args[0]} was not found.");
            // This will exit.
        }
        $this->_deliverable->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('org_openpsa_sales_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_deliverable->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete deliverable {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_deliverable->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_openpsa_sales_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("deliverable/{$this->_deliverable->guid}/");
            // This will exit()
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_deliverable->title}");
        $_MIDCOM->bind_view_to_object($this->_deliverable, $this->_request_data['controller']->datamanager->schema->title);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded deliverable.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['deliverable_view'] = $this->_datamanager->get_content_html();

        midcom_show_style('show-deliverable-delete');
    }
}

?>