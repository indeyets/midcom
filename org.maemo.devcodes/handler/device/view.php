<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the device object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_device_view extends midcom_baseclasses_components_handler
{
    /**
     * The device to display
     *
     * @var midcom_db_device
     * @access private
     */
    var $_device = null;

    /**
     * The Datamanager of the device to display.
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
        $this->_request_data['device'] =& $this->_device;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "device/edit/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('midgard:update'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "device/delete/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('midgard:delete'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "code/list/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('list codes'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('midgard:read'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "code/import/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('import codes'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/editpaste.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'i',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('midgard:create'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "application/list/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('list code applications'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('midgard:read'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "code/assign/{$this->_device->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('assign codes to applicants'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
            MIDCOM_TOOLBAR_ENABLED => $this->_device->can_do('org.maemo.devcodes:manage'),
        ));

    }


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
    }

    /**
     * Handle actual device display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($args[0]);
        if (   !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$args[0]}' was not found.");
            // This will exit.
        }
        // These admin views are only for those who have the privileges...
        $this->_device->require_do('org.maemo.devcodes:read');
        $this->_load_datamanager();

        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_device);
            $this->_request_data['controller']->process_ajax();
        }
        */

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_device, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_device->metadata->revised, $this->_device->guid);
        $data['title'] = $this->_device->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    function _load_schemadb()
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_device'));
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, loads the datamanager for the current device. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_device))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for device {$this->_device->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded device.
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
            $this->_request_data['view_device'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_device'] = $this->_datamanager->get_content_html();
        }
        */
        $this->_request_data['view_device'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-device');
    }
}

?>