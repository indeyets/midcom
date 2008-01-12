<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the host object view page
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_handler_host_view extends midcom_baseclasses_components_handler
{
    /**
     * The host to display
     *
     * @var midcom_db_host
     * @access private
     */
    var $_host = null;

    /**
     * The Datamanager of the host to display.
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
        $this->_request_data['host'] =& $this->_host;
        $this->_request_data['l10n'] =& $this->_l10n;
        $this->_request_data['l10n_midcom'] =& $this->_l10n_midcom;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "host/edit/{$this->_host->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_host->can_do('midgard:update'),
        ));
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "create/host/{$this->_host->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new child host'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "host/delete/{$this->_host->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_host->can_do('midgard:delete'),
        ));
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_netmon_handler_host_view()
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
     * Handle actual host display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_host = new net_nemein_netmon_host_dba($args[0]);
        if (! $this->_host)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The host '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_load_datamanager();

        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_host);
            $this->_request_data['controller']->process_ajax();
        }
        */

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "host/{$this->_host->guid}",
            MIDCOM_NAV_NAME => $this->_host->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_host, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_host->metadata->revised, $this->_host->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_host->title}");

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current host. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_host))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for host {$this->_host->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded host.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view ($handler_id, &$data)
    {
        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_host'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_host'] = $this->_datamanager->get_content_html();
        }
        */
        $this->_request_data['view_host'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-host');
    }
}

?>