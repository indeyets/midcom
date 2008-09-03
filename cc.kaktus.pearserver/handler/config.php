<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server handler class for setting the configuration options
 *
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_handler_config extends midcom_baseclasses_components_handler
{
    /**
     * Constructor. Ties to the parent class constructor.
     *
     * @access public
     */
    function cc_kaktus_pearserver_handler_config()
    {
        parent::__construct();
        $this->_root_group =& $this->_request_data['root_group'];
    }

    /**
     * Load the DM2 controller instance
     *
     * @access private
     */
    function _load_controller()
    {
        // Get the configuration schema
        $this->_schemadb_config = midcom_helper_datamanager2_schema::load_database('file:/cc/kaktus/pearserver/config/schemadb_config.inc');

        // Create the controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb_config;
        $this->_controller->set_storage($this->_topic);

        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_event->id}.");
            // This will exit.
        }
    }

    /**
     * Require the correct ACL's for configuring
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_config($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midgard:config');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->uimessages->add('cc.kaktus.pearserver', $this->_l10n->get('configuration saved'));
                // Fall through

            case 'cancel':
                $_MIDCOM->relocate('');
                break;
        }

        // Set the breadcrumb
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "config/",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('component configuration'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Show the configuration screen
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_config($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;

        midcom_show_style('pearserver-configuration');
    }
}
?>