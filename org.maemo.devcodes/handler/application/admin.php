<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * application edit/delete handler
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_application_admin extends midcom_baseclasses_components_handler
{
    /**
     * The application to operate on
     *
     * @var midcom_db_application
     * @access private
     */
    var $_application = null;

    /**
     * The Datamanager of the application to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the application used for editing
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
        $this->_request_data['application'] =& $this->_application;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        /* On a second thought, we don't need these here
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "application/edit/{$this->_application->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_application->can_do('midgard:update'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "application/delete/{$this->_application->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_application->can_do('midgard:delete'),
        ));
        */
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_application_admin()
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
     * Special treatment is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_application'));
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, loads the datamanager for the current application. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_application))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for application {$this->_application->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current application. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        if (!$this->_application->can_do('org.maemo.devcodes:manage'))
        {
            $schema = 'application-user';
        }
        else
        {
            $schema = 'application';
        }
        $this->_controller->set_storage($this->_application, $schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for application {$this->_application->id}.");
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
            MIDCOM_NAV_URL => "application/{$this->_application->guid}.html",
            MIDCOM_NAV_NAME => $this->_application->title,
        );
        list ($handler_op, $handler_type) = explode('-', $handler_id, 2);
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$handler_type}/{$handler_op}/{$this->_application->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_op),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays an application edit view.
     *
     * Note, that the application for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation application
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_application = new org_maemo_devcodes_application_dba($args[0]);
        if (  !$this->_application
            || empty($this->_application->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The application '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_application->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                // Reindex the application
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nehmer_blog_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);
                */

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("application/{$this->_application->guid}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_application);
        $_MIDCOM->set_26_request_metadata($this->_application->metadata->revised, $this->_application->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_application->title}");
        $this->_request_data['title'] = sprintf($this->_l10n_midcom->get('edit %s'), $this->_application->title);
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded application.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin-edit-application');
    }

    /**
     * Displays an application delete confirmation view.
     *
     * Note, that the application for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation application
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_application = new org_maemo_devcodes_application_dba($args[0]);
        if (  !$this->_application
            || empty($this->_application->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The application {$args[0]} was not found.");
            // This will exit.
        }
        $this->_application->require_do('midgard:delete');
        $data['show_form'] =  true;
        $data['message'] = '<p>' . $this->_l10n->get('are you sure you want to delete this application') . '</p>';
        if ($this->_application->has_dependencies())
        {
            $data['show_form'] =  false;
            $data['message'] = '<p>' . $this->_l10n->get('object has dependencies') . '</p>';
        }

        //$this->_load_datamanager();

        if (array_key_exists('org_maemo_devcodes_delete_confirm', $_REQUEST))
        {
            // Deletion confirmed.
            if (!$this->_application->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete application {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_maemo_devcodes_delete_cancel', $_REQUEST))
        {
            $_MIDCOM->relocate("application/{$this->_application->guid}.html");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_application->metadata->revised, $this->_application->guid);
        $this->_view_toolbar->bind_to($this->_application);
        $this->_request_data['title'] = sprintf($this->_l10n_midcom->get('delete %s'), $this->_application->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_application->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded application.
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin-delete-application');
    }



}

?>