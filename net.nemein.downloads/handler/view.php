<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4153 2006-09-20 18:28:00Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager view handler
 *
 * @package net.nemein.downloads
 */
class net_nemein_downloads_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The downloadpage to display
     *
     * @var midcom_db_article
     * @access private
     */
    var $_downloadpage = null;

    /**
     * The Datamanager of the downloadpage to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_downloads_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['downloadpage'] =& $this->_downloadpage;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        if ($this->_downloadpage->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "edit/{$this->_downloadpage->guid}.html",
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
                    MIDCOM_TOOLBAR_URL => "delete/{$this->_downloadpage->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                )
            );
        }
    }

    /**
     * Can-Handle check against the downloadpage name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     *
     * Not applicable for the "index" handler, where the downloadpage name is fixed (see handle).
     */
    function _can_handle_view($handler_id, $args, &$data)
    {
        if ($handler_id == 'view_current')
        {
            return true;
        }

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $args[0]);
        $qb->add_constraint('up', '=', 0);
        $result = $qb->execute();

        if ($result)
        {
            $this->_downloadpage = $result[0];
            return true;
        }

        return false;
    }


    /**
     * Looks up a downloadpage to display. If the handler_id is 'index', the index downloadpage is tried to be
     * looked up, otherwise the downloadpage name is taken from args[0]. Triggered error messages are
     * generated accordingly. A missing index will trigger a forbidden error, a missing regular
     * downloadpage a 404 (from can_handle).
     *
     * Note, that the downloadpage for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation downloadpage
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        if ($handler_id == 'view_current')
        {
            $this->_downloadpage = new midcom_db_article($this->_config->get('current_release'));
            if (!$this->_downloadpage)
            {
                return false;
            }
        }

        $this->_load_datamanager();

        $this->_datamanager->autoset_storage($this->_downloadpage);

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_downloadpage->revised, $this->_downloadpage->guid);
        $_MIDCOM->bind_view_to_object($this->_downloadpage, $this->_datamanager->schema->name);

        $this->_component_data['active_leaf'] = $this->_downloadpage->id;

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_downloadpage->title}");

        $tmp = array();
        $arg = $this->_downloadpage->name ? $this->_downloadpage->name : $this->_downloadpage->guid;
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "{$arg}.html",
            MIDCOM_NAV_NAME => $this->_downloadpage->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current downloadpage. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for downloadpage {$this->_downloadpage->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded downloadpage.
     */
    function _show_view($handler_id, &$data)
    {
        $data['view_downloadpage'] = $data['datamanager']->get_content_html();

        midcom_show_style('view-release');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_index($handler_id, $args, &$data)
    {
        // List releases
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('title', 'DESC');
        $data['releases'] = $qb->execute();

        $this->_load_datamanager();
        $this->_request_data['datamanager'] =& $this->_datamanager;

        return true;
    }

    /**
     * Shows the loaded downloadpage.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('view-index-header');

        foreach ($data['releases'] as $release)
        {
            $data['datamanager']->autoset_storage($release);
            $data['downloadpage'] = $release;
            $data['view_downloadpage'] = $data['datamanager']->get_content_html();

            midcom_show_style('view-index-item');
        }
        midcom_show_style('view-index-footer');
    }
}
?>