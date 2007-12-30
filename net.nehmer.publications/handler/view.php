<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications index page handler
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The publication to display
     *
     * @var net_nehmer_publications_entry
     * @access private
     */
    var $_publication = null;

    /**
     * The Datamanager of the publication to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['publication'] =& $this->_publication;
        $this->_request_data['datamanager'] =& $this->_datamanager;

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
    function net_nehmer_publications_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Looks up a publication to display. If the handler_id is 'index', the index publication is tried to be
     * looked up, otherwise the publication name is taken from args[0]. Triggered error messages are
     * generated accordingly. A missing index will trigger a forbidden error, a missing regular
     * publication a 404 (from can_handle).
     *
     * Note, that the publication for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation publication
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_publication = new net_nehmer_publications_entry($args[0]);
        $this->_load_datamanager();

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_publication->guid}.html",
            MIDCOM_NAV_NAME => $this->_publication->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->substyle_append($this->_datamanager->schema->name);
        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_publication->metadata->revised, $this->_publication->guid);
        $this->_view_toolbar->bind_to($this->_publication);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_publication->title}");

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current publication. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_publication))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for publication {$this->_publication->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded publication.
     */
    function _show_view ($handler_id, &$data)
    {
        midcom_show_style('view');
    }



}

?>