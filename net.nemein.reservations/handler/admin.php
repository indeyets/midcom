<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Reservations edit/delete resource handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The resource to operate on
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * The Datamanager of the resource to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the resource used for editing
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
     * Schema to use for resource display
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_reservations_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['resource'] =& $this->_resource;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_resource->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_resource->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_resource->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_resource->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        switch ($handler_id)
        {
            case 'edit_resource':
                $this->_view_toolbar->disable_item("edit/{$this->_resource->guid}.html");
                break;
            case 'delete_resource':
                $this->_view_toolbar->disable_item("delete/{$this->_resource->guid}.html");
                break;
        }
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
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb_resource'];
    }

    /**
     * Internal helper, loads the datamanager for the current resource. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        //$this->_datamanager->schema = $this->_resource->type;
        if (!$this->_datamanager->autoset_storage($this->_resource))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for resource {$this->_resource->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current resource. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_resource, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for resource {$this->_resource->id}.");
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
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );

        switch ($handler_id)
        {
            case 'edit_resource':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "edit/{$this->_resource->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );
                break;
            case 'delete_resource':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "delete/{$this->_resource->guid}.html",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays a resource edit view.
     *
     * Note, that the resource for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation resource
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_resource = new org_openpsa_calendar_resource_dba($args[0]);
        if (! $this->_resource)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The resource {$args[0]} was not found.");
            // This will exit.
        }

        if (!array_key_exists($this->_resource->type, $this->_request_data['schemadb_resource']))
        {
            // This resource type isn't available for our schema, return error
            return false;
        }

        $this->_resource->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the resource
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_reservations_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("view/{$this->_resource->name}/");
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_resource->title}");
        $_MIDCOM->bind_view_to_object($this->_resource, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded resource.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('view-resource-edit');
    }

    /**
     * Displays a resource delete confirmation view.
     *
     * Note, that the resource for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation resource
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_resource = new org_openpsa_calendar_resource_dba($args[0]);
        if (! $this->_resource)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The resource {$args[0]} was not found.");
            // This will exit.
        }

        if (!array_key_exists($this->_resource->type, $this->_request_data['schemadb_resource']))
        {
            // This resource type isn't available for our schema, return error
            return false;
        }

        $this->_resource->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('net_nemein_reservations_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_resource->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete resource {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_resource->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nemein_reservations_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("view/{$this->_resource->name}/");
            // This will exit()
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_resource->title}");
        $_MIDCOM->bind_view_to_object($this->_resource, $this->_datamanager->schema->name);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded resource.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['view_resource'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-resource-delete');
    }
}

?>