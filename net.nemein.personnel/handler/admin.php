<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Admin page handler
 *
 * @package net.nemein.personnel
 */

class net_nemein_personnel_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The schema database in use, available only while a controller is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The person to operate on
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person = null;

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
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_person)
        {
            if ($this->_person->can_do('midgard:update'))
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "admin/edit/{$this->_person->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                ));
            }
            if ($this->_person->can_do('midgard:delete'))
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "admin/delete/{$this->_person->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                ));
            }
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_personnel_handler_admin()
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
        $this->_controller->set_storage($this->_person, $this->_config->get('schema'));
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for person {$this->_person->id}.");
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
        $this->_controller->set_storage($this->_person, $this->_config->get('schema'));
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for person {$this->_person->id}.");
            // This will exit.
        }
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_person = new midcom_db_person($args[0]);
        if (! $this->_person)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The person {$args[0]} was not found.");
            // This will exit.
        }
        $this->_person->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_personnel_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);
                */

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate(net_nemein_personnel_viewer::get_url($this->_person));
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_controller->datamanager->schema->name);

        if (version_compare(mgd_version(), '1.7', '>'))
        {
            $_MIDCOM->set_26_request_metadata($this->_person->metadata->revised, $this->_person->guid);
        }
        
        $this->_view_toolbar->bind_to($this->_person);
        $this->_component_data['active_leaf'] = $this->_person->id;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_person->name}");

        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $alpha_filter = $this->_person->lastname[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$alpha_filter}.html",
                MIDCOM_NAV_NAME => $alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => net_nemein_personnel_viewer::get_url($this->_person),
            MIDCOM_NAV_NAME => $this->_person->name,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "admin/edit/{$this->_person->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }


    /**
     * Shows the loaded article.
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
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_person = new midcom_db_person($args[0]);
        if (! $this->_person)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        $this->_person->require_do('midgard:delete');

        $this->_load_delete_controller();
        $this->_controller->formmanager->freeze();

        // Don't process the form, it'll try to validate it (which we don't need).
        switch ($this->_controller->formmanager->get_clicked_button())
        {
            case 'save':
                // Deletion confirmed.
                if (! $this->_person->delete())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete person {$args[0]}, last Midgard error was: " . mgd_errstr());
                    // This will exit.
                }

                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_person->guid);
                */

                // Delete ok, relocating to welcome.
                $_MIDCOM->relocate('');
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate(net_nemein_personnel_viewer::get_url($this->_person));
                // This will exit.
        }

        $this->_prepare_request_data();

        if (version_compare(mgd_version(), '1.7', '>'))
        {        
            $_MIDCOM->set_26_request_metadata($this->_person->metadata->revised, $this->_person->guid);
        }
        
        $this->_view_toolbar->bind_to($this->_person);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_person->name}");
        $this->_component_data['active_leaf'] = $this->_person->id;

        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $alpha_filter = $this->_person->lastname[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$alpha_filter}.html",
                MIDCOM_NAV_NAME => $alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => $url = net_nemein_personnel_viewer::get_url($this->_person),
            MIDCOM_NAV_NAME => $this->_person->name,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "admin/delete/{$this->_person->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('delete'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin-delete');
    }

    /**
     * DM2 creation callback, creates a new person and binds it to the selected group.
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

        $this->_person = new midcom_db_person();
        if (! $this->_person->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_person);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new person, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        $member = new midcom_db_member();
        $member->uid = $this->_person->id;
        $member->gid = $group->id;
        if (! $member->create())
        {
            $this->_person->delete();
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $member);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new member record, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_person;
    }

    /**
     * Displays the create view and processes the controller events.
     *
     * Requires Admin Privileges.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $this->_load_create_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nehmer_static_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);
                */

                $_MIDCOM->relocate(net_nemein_personnel_viewer::get_url($this->_person));
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('create person'));

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'admin/create.html',
            MIDCOM_NAV_NAME => $this->_l10n->get('create person'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create');
    }


}

?>