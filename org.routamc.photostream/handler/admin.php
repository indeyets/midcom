<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photostream edit/delete photos handler
 *
 * Originally copied from net.nehmer.blog
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The photo to operate on
     *
     * @var org_routamc_photostream_photo_dba
     * @access private
     */
    var $_photo = null;

    /**
     * The Datamanager of the photo to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the photo used for editing
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
        $this->_request_data['photo'] =& $this->_photo;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_photo->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_photo->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ));
        }

        if ($this->_photo->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_photo->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            ));
        }
    }


    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
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
     * Internal helper, loads the datamanager for the current photo. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_photo))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for photo {$this->_photo->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current photo. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_photo);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for photo {$this->_photo->id}.");
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
            MIDCOM_NAV_URL => "photo/{$this->_photo->guid}/",
            MIDCOM_NAV_NAME => $this->_photo->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$handler_id}/{$this->_photo->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_id),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }


    /**
     * Displays a photo edit view.
     *
     * Note, that the photo for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation photo,
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_photo = new org_routamc_photostream_photo_dba($args[0]);
        if (! $this->_photo)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The photo {$args[0]} was not found.");
            // This will exit.
        }
        $this->_photo->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the photo
                $indexer =& $_MIDCOM->get_service('indexer');
                org_routamc_photostream_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("photo/{$this->_photo->guid}/");
                // This will exit.
        }

        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_photo);
        $_MIDCOM->set_26_request_metadata($this->_photo->metadata->revised, $this->_photo->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_photo->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded photo.
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin_edit');
    }

    /**
     * Displays a photo delete confirmation view.
     *
     * Note, that the photo for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation photo,
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_photo = new org_routamc_photostream_photo_dba($args[0]);
        if (! $this->_photo)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The photo {$args[0]} was not found.");
            // This will exit.
        }
        $this->_photo->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('org_routamc_photostream_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_photo->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete photo {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            // RIs now contain language identifier
            $indexer->delete($this->_photo->guid . '_' . $_MIDCOM->i18n->get_content_language());

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_routamc_photostream_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("photo/{$this->_photo->guid}/");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_photo->metadata->revised, $this->_photo->guid);
        $this->_view_toolbar->bind_to($this->_photo);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_photo->title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded photo.
     */
    function _show_delete ($handler_id, &$data)
    {
        $data['photo_view'] = $this->_datamanager->get_content_html();

        // Figure out how URLs to photo lists should be constructed
        $data['photographer'] = new midcom_db_person($data['photo']->photographer);
        if ($data['photographer']->username)
        {
            $data['user_url'] = $data['photographer']->username;
        }
        else
        {
            $data['user_url'] = $data['photographer']->guid;
        }

        midcom_show_style('admin_delete');
    }

    function _handler_recreate($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (array_key_exists('org_routamc_photostream_recreatecancel', $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit()
        }
        if (array_key_exists('org_routamc_photostream_recreateok', $_REQUEST))
        {
            //Disable limits
            // TODO: Could this be done more safely somehow
            @ini_set('memory_limit', -1);
            @ini_set('max_execution_time', 0);        
        
            $data['process_photos'] = array();
            $qb = org_routamc_photostream_photo_dba::new_query_builder();
            $qb->add_constraint('node', '=', $this->_topic->id);
            $qb->add_order('taken', 'DESC');
            $photos = $qb->execute();
            if (!is_array($photos))
            {
                // QB error
                return false;
            }
            foreach ($photos as $photo)
            {
                // Check for midgard:update AND midgard:attachments for each photo
                if (   !$photo->can_do('midgard:update')
                    || !$photo->can_do('midgard:attachments'))
                {
                    // No privileges for this photo
                    continue;
                }
                // PHP5-TODO: Must be copy-by-value
                $data['process_photos'][] = $photo;
            }
            unset($photos);
            $_MIDCOM->skip_page_style = true;
        }
        $_MIDCOM->cache->content->enable_live_mode();
        
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "recreate.html",
            MIDCOM_NAV_NAME => $this->_l10n->get('recreate derived images'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        return true;
    }

    function _show_recreate($handler_id, &$data)
    {
        if (!array_key_exists('org_routamc_photostream_recreateok', $_REQUEST))
        {
            midcom_show_style('admin_recreate_confirm');
            return;
        }
        // Get rid of all output buffers
        while(@ob_end_flush());
        midcom_show_style('admin_recreate_start');
        echo "<!-- send a lot of dummy data to make some browsers (*cough*IE*cough*) happier\n";
        for ($i = 1; $i < 1041; $i++)
        {
            echo '.';
            if ( ($i % 80) == 0)
            {
                echo "\n";
            }
        }
        echo "-->\n";
        flush();
        $indexer =& $_MIDCOM->get_service('indexer');
        foreach ($data['process_photos'] as $photo)
        {
            // PHP5-TODO: (probably) Must be copy-by-value
            $this->_photo = $photo;
            $this->_load_datamanager();
            $photo_field = false;
            foreach ($this->_request_data['schemadb']['upload']->fields as $name => $field)
            {
                if ($field['type'] == 'photo')
                {
                    $photo_field = $name;
                }
            }
            if (!$photo_field)
            {
                // Could not figure which field houses the photo type
                continue;
            }
            $stat = $this->_datamanager->types[$photo_field]->recreate_main_image();
            $this->_request_data['photo'] =& $this->_photo;
            if ($stat)
            {
                // reindex with new thumbnail
                org_routamc_photostream_viewer::index($this->_datamanager, $indexer, $this->_topic);
                midcom_show_style('admin_recreate_rowok');
            }
            else
            {
                midcom_show_style('admin_recreate_rowfailed');
            }
            flush();
        }
        $nap = new midcom_helper_nav();
        $data['photostream_node'] = $nap->get_node($this->_topic->id);
        midcom_show_style('admin_recreate_done');
        ob_start();
        // Restart OB to make MidCOM happier
    }

}

?>