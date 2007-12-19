<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications admin page handler
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The publication to operate on
     *
     * @var net_nehmer_publications_entry
     * @access private
     */
    var $_publication = null;

    /**
     * The Datamanager of the publication to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the publication used for editing
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
        $this->_request_data['publication'] =& $this->_publication;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_publication->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_publication->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            ));
        }

        if ($this->_publication->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_publication->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            ));
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nehmer_publications_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Loads and prepares the schema database.
     *
     * All fields present in the topic filter list will be made readonly and not-required.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];

        $hidden_fields_raw = $this->_config->get('hide_fields');
        if ($hidden_fields_raw)
        {
            $hidden_fields = explode(',', $hidden_fields_raw);
            foreach ($this->_schemadb as $schemaname => $copy)
            {
                foreach ($hidden_fields as $fieldname)
                {
                    $this->_schemadb[$schemaname]->fields[$fieldname]['hidden'] = true;
                    $this->_schemadb[$schemaname]->fields[$fieldname]['required'] = false;
                }
            }
        }
    }

    /**
     * Internal helper, loads the datamanager for the current publication. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_publication))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for publication {$this->_publication->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current publication. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_publication);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for publication {$this->_publication->id}.");
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
            MIDCOM_NAV_URL => "view/{$this->_publication->guid}.html",
            MIDCOM_NAV_NAME => $this->_publication->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$handler_id}/{$this->_publication->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_id),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays a publication edit view.
     *
     * Note, that the publication for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation publication,
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_publication = new net_nehmer_publications_entry($args[0]);
        if (! $this->_publication)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The publication {$args[0]} was not found.");
            // This will exit.
        }
        $this->_publication->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the publication
                $indexer =& $_MIDCOM->get_service('indexer');
                $this->_publication->index($this->_controller->datamanager, $indexer, $this->_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("view/{$this->_publication->guid}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_publication->metadata->revised, $this->_publication->guid);
        $this->_view_toolbar->bind_to($this->_publication);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_publication->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded publication.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin-edit');
    }

    /**
     * Displays a publication delete confirmation view.
     *
     * Note, that the publication for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation publication,
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_publication = new net_nehmer_publications_entry($args[0]);
        if (! $this->_publication)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The publication {$args[0]} was not found.");
            // This will exit.
        }
        $this->_publication->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('net_nehmer_publications_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_publication->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete publication {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_publication->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nehmer_publications_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("view/{$this->_publication->guid}.html");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_publication->metadata->revised, $this->_publication->guid);
        $this->_view_toolbar->bind_to($this->_publication);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_publication->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded publication.
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin-delete');
    }



}

?>