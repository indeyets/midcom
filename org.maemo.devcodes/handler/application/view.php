<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the application object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_application_view extends midcom_baseclasses_components_handler
{
    /**
     * The application to display
     *
     * @var midcom_db_application
     * @access private
     */
    var $_application = null;

    /**
     * The Datamanager of the application to display.
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
        $this->_request_data['application'] =& $this->_application;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
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
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_application_view()
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
     * Handle actual application display
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_application = new org_maemo_devcodes_application_dba($args[0]);
        if (   !$this->_application
            || empty($this->_application->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The application '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_load_datamanager();

        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_application);
            $this->_request_data['controller']->process_ajax();
        }
        */

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "application/{$this->_application->guid}",
            MIDCOM_NAV_NAME => $this->_application->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_application, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_application->metadata->revised, $this->_application->guid);
        $data['title'] = $this->_application->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current application. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_application))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for application {$this->_application->id}.");
            // This will exit.
        }
    }

    /**
     * Shows the loaded application.
     */
    function _show_view ($handler_id, &$data)
    {
        /*
        if ($this->_config->get('enable_ajax_editing'))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_application'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_application'] = $this->_datamanager->get_content_html();
        }
        */
        $this->_request_data['view_application'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-application');
    }
}

?>