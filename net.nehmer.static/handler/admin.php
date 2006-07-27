<?php
/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer admin page handler
 *
 * @package net.nehmer.static
 */

class net_nehmer_static_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The article to operate on
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

    /**
     * The Datamanager of the article to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the article used for editing
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
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['controller'] =& $this->_controller;

        // Populate the toolbar
        if ($this->_article->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            ));
        }
        if ($this->_article->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "delete/{$this->_article->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            ));
        }
    }


    /**
     * Simple default constructor.
     */
    function net_nehmer_static_handler_admin()
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
     * Special treatement is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
        if (   $this->_config->get('simple_name_handling')
            && ! $_MIDCOM->auth->admin)
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['name']['readonly'] = true;
            }
        }
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_article))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
            // This will exit.
        }
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
        $this->_controller->set_storage($this->_article);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
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
            MIDCOM_NAV_URL => "{$handler_id}/{$this->_article->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get($handler_id),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
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
        $this->_article = new midcom_db_article($args[0]);
        if (! $this->_article)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        $this->_article->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nehmer_static_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("{$this->_article->name}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_controller->datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_article->revised, $this->_article->guid);
        $this->_view_toolbar->bind_to($this->_article);
        $this->_component_data['active_leaf'] = $this->_article->id;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_article->title}");
        $this->_update_breadcrumb_line($handler_id);

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
        $this->_article = new midcom_db_article($args[0]);
        if (! $this->_article)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        $this->_article->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('net_nehmer_static_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_article->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete article {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_article->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nehmer_static_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("{$this->_article->name}.html");
            // This will exit()
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_article->revised, $this->_article->guid);
        $this->_view_toolbar->bind_to($this->_article);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_article->title}");
        $this->_component_data['active_leaf'] = $this->_article->id;
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_delete ($handler_id, &$data)
    {
        $this->_request_data['view_article'] = $this->_datamanager->get_content_html();
        midcom_show_style('admin-delete');
    }



}

?>