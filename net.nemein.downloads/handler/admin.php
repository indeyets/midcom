<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager page handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package net.nemein.downloads
 */
class net_nemein_downloads_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The downloadpage to operate on
     *
     * @var midcom_db_article
     * @access private
     */
    var $_downloadpage = null;

    /**
     * The Datamanager of the downloadpage to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the downloadpage used for editing
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
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['downloadpage'] =& $this->_downloadpage;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_downloadpage->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "edit/{$this->_downloadpage->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                )
            );
        }

        if ($this->_downloadpage->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/{$this->_downloadpage->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                )
            );
        }
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-admins
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
     * Internal helper, loads the datamanager for the current downloadpage. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_downloadpage))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for downloadpage {$this->_downloadpage->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current downloadpage. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_downloadpage);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for downloadpage {$this->_downloadpage->id}.");
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
            MIDCOM_NAV_URL => "{$this->_downloadpage->name}/",
            MIDCOM_NAV_NAME => $this->_downloadpage->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$handler_id}/{$this->_downloadpage->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_id),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays a downloadpage edit view.
     *
     * Note, that the downloadpage for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation downloadpage
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_downloadpage = new midcom_db_article($args[0]);
        if (! $this->_downloadpage)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The downloadpage {$args[0]} was not found.");
            // This will exit.
        }
        $this->_downloadpage->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the downloadpage
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_downloads_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("{$this->_downloadpage->name}/");
                // This will exit.
        }

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_downloadpage, $this->_controller->datamanager->schema->name);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_downloadpage->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded downloadpage.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit ($handler_id, &$data)
    {
        $data['view_downloadpage'] = $this->_controller->datamanager->get_content_html();

        midcom_show_style('admin-edit');
    }

    /**
     * Displays a downloadpage delete confirmation view.
     *
     * Note, that the downloadpage for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation downloadpage
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_downloadpage = new midcom_db_article($args[0]);
        if (! $this->_downloadpage)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The downloadpage {$args[0]} was not found.");
            // This will exit.
        }
        $this->_downloadpage->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('net_nemein_downloads_deleteok', $_REQUEST))
        {
            // Deletion confirmed.

            if ($this->_config->get('current_release') == $this->_downloadpage->guid)
            {
                $this->_topic->parameter('net.nemein.downloads', 'current_release', '');
            }

            if (!$this->_downloadpage->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete downloadpage {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_downloadpage->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nemein_downloads_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("{$this->_downloadpage->name}/");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_downloadpage->revised, $this->_downloadpage->guid);
        $this->_view_toolbar->bind_to($this->_downloadpage);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_downloadpage->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded downloadpage.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['view_downloadpage'] = $this->_datamanager->get_content_html();

        midcom_show_style('admin-delete');
    }
}
?>