<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4840 2006-12-29 06:25:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Admin page handler
 *
 * @package net.nemein.organizations
 */

class net_nemein_organizations_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The schema database in use, available only while a controller is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The group to operate on
     *
     * @var midcom_db_group
     * @access private
     */
    var $_group = null;

    /**
     * The Controller of the article used for editing and (in frozen mode) for
     * delete preview.
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['group'] =& $this->_group;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_group)
        {
            if ($this->_group->can_do('midgard:update'))
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "admin/edit/{$this->_group->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                ));
            }
            if ($this->_group->can_do('midgard:delete'))
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "admin/delete/{$this->_group->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                ));
            }
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_organizations_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_group, $this->_config->get('schema'));
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for group {$this->_group->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_create_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_config->get('schema');
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to initialize a DM2 create controller.');
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller to delete the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_delete_controller()
    {
        $this->_load_schemadb();
        $this->_schemadb[$this->_config->get('schema')]->operations['save'] = $this->_l10n_midcom->get('delete');
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_group, $this->_config->get('schema'));
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for group {$this->_group->id}.");
            // This will exit.
        }
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_group = new midcom_db_group($args[0]);
        if (! $this->_group)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The group {$args[0]} was not found.");
            // This will exit.
        }
        $this->_group->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_organizations_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate(net_nemein_organizations_viewer::get_url($this->_group));
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_controller->datamanager->schema->name);

        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $_MIDCOM->set_26_request_metadata($this->_group->metadata->revised, $this->_group->guid);
        }

        $this->_view_toolbar->bind_to($this->_group);
        $this->_component_data['active_leaf'] = $this->_group->id;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->official}");

        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $alpha_filter = $this->_group->official[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$alpha_filter}.html",
                MIDCOM_NAV_NAME => $alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => net_nemein_organizations_viewer::get_url($this->_group),
            MIDCOM_NAV_NAME => $this->_group->official,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "admin/edit/{$this->_group->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }


    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin-edit');
    }

    /**
     * Displays an article delete confirmation view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_group = new midcom_db_group($args[0]);
        if (! $this->_group)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        $this->_group->require_do('midgard:delete');

        $this->_load_delete_controller();
        $this->_controller->formmanager->freeze();

        // Don't process the form, it'll try to validate it (which we don't need).
        switch ($this->_controller->formmanager->get_clicked_button())
        {
            case 'save':
                // Deletion confirmed.
                if (! $this->_group->delete())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete group {$args[0]}, last Midgard error was: " . mgd_errstr());
                    // This will exit.
                }

                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_group->guid);

                // Delete ok, relocating to welcome.
                $_MIDCOM->relocate('');
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate(net_nemein_organizations_viewer::get_url($this->_group));
                // This will exit.
        }

        $this->_prepare_request_data();

        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $_MIDCOM->set_26_request_metadata($this->_group->metadata->revised, $this->_group->guid);
        }

        $this->_view_toolbar->bind_to($this->_group);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->official}");
        $this->_component_data['active_leaf'] = $this->_group->id;

        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $alpha_filter = $this->_group->official[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$alpha_filter}.html",
                MIDCOM_NAV_NAME => $alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => $url = net_nemein_organizations_viewer::get_url($this->_group),
            MIDCOM_NAV_NAME => $this->_group->official,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "admin/delete/{$this->_group->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }


    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin-delete');
    }

    /**
     * DM2 creation callback, creates a new group and binds it to the selected group.
     *
     * Assumes Admin Privileges.
     */
    function & dm2_create_callback (&$controller)
    {
        $group = new midcom_db_group($this->_config->get('group'));
        if (! $group)
        {
            $guid = $this->_config->get('group');
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the configured group '{$group}', cannot continue. Last Midgard error was: ". mgd_errstr());
            // This will exit.
        }

        $this->_group = new midcom_db_group();
        $this->_group->owner = $group->id;
        if (! $this->_group->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_group);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new group, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_group;
    }

    /**
     * Displays the create view and processes the controller events.
     *
     * Requires Admin Privileges.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // FIXME: Check the real ACL instead of this
        $_MIDCOM->auth->require_admin_user();

        $this->_load_create_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_organizations_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // Generate URL name
                if ($this->_group->name == '')
                {
                    $this->_group->name = midcom_generate_urlname_from_string($this->_group->official);
                    $tries = 0;
                    $maxtries = 999;
                    while(   !$this->_group->update()
                          && $tries < $maxtries)
                    {
                        $this->_group->name = midcom_generate_urlname_from_string($this->_group->official);
                        if ($tries > 0)
                        {
                            // Append an integer if articles with same name exist
                            $this->_group->name .= sprintf("-%03d", $tries);
                        }
                        $tries++;
                    }
                }

                $_MIDCOM->relocate(net_nemein_organizations_viewer::get_url($this->_group));
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('create group'));

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'admin/create.html',
            MIDCOM_NAV_NAME => $this->_l10n->get('create group'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create');
    }


}

?>