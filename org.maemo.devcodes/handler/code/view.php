<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the code object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_code_view extends midcom_baseclasses_components_handler
{
    /**
     * The code to display
     *
     * @var midcom_db_code
     * @access private
     */
    var $_code = null;

    var $_device = null;

    /**
     * The Datamanager of the code to display.
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
        $this->_request_data['code'] =& $this->_code;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "code/edit/{$this->_code->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            MIDCOM_TOOLBAR_ENABLED => $this->_code->can_do('midgard:update'),
        ));

        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "code/delete/{$this->_code->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            MIDCOM_TOOLBAR_ENABLED => $this->_code->can_do('midgard:delete'),
        ));
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_code_view()
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
     * Handle actual code display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_code = new org_maemo_devcodes_code_dba($args[0]);
        if (  !$this->_code
            || empty($this->_code->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The code '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_load_datamanager();

        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_code);
            $this->_request_data['controller']->process_ajax();
        }
        */

        if (!empty($this->_code->device))
        {
            $this->_device =& org_maemo_devcodes_device_dba::get_cached($this->_code->device);
            if (   !$this->_device
                || empty($this->_device->guid))
            {
                $this->_device = null;
            }
        }

        $data['title'] = $this->_code->title;
        $tmp = Array();
        if ($this->_device)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "device/{$this->_device->guid}",
                MIDCOM_NAV_NAME => $this->_device->title,
            );
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "code/list/{$this->_device->guid}",
                MIDCOM_NAV_NAME => $data['title'],
            );
        }
        else
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "code/{$this->_code->guid}",
                MIDCOM_NAV_NAME =>  $data['title'],
            );
        }
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_code, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_code->metadata->revised, $this->_code->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    function _load_schemadb()
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_code'));
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, loads the datamanager for the current code. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_code))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for code {$this->_code->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded code.
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
            $this->_request_data['view_code'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_code'] = $this->_datamanager->get_content_html();
        }
        */
        $this->_request_data['view_code'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-code');
    }
}

?>