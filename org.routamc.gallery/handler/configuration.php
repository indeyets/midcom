<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Component configuration screen.
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_handler_configuration extends midcom_baseclasses_components_handler
{
    /**
     * The Controller of the gallery used for editing
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

    function org_routamc_gallery_handler_configuration()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['node'] =& $this->_topic;
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Internal helper, loads the controller for the current photo. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_config'));

        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_topic);
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
            MIDCOM_NAV_URL => "config.html",
            MIDCOM_NAV_NAME => $this->_l10n->get('gallery settings'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Displays a config edit view.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_config($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Refresh gallery subscriptions
                // TODO: Schedule using midcom.services.at instead
                $synchronizer = new org_routamc_gallery_helper($this->_topic);
                $synchronizer->sync();

                // Fall-through to relocate

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('gallery settings'));
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * Shows the loaded photo.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_config($handler_id, &$data)
    {
        midcom_show_style('admin_config');
    }
}
?>