<?php
/**
 * @package midcom.helper.dm2config
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 11095 2007-07-04 16:31:49Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * midcom.helper.datamanager2 based configuration
 *
 * Usage:
 *
 * 1. Write a midcom_helper_datamanager2_schema compatible configuration
 *    schema and place it among your component files
 * 2. Point a configuration key 'schemadb_config' to it within your
 *    component configuration (_config/config.inc_)
 * 3. Refer to DM2 component configuration helper with a request handler,
 *    e.g.
 *    <code>
 *     $this->_request_handler['config'] = array
 *     (
 *         'handler' => array ('midcom_helper_dm2config_config', 'config'),
 *         'fixed_args' => array ('config'),
 *     );
 *    </code>
 * 4. Remember to include midcom.helper.dm2config as a requirement in
 *    _config/manifest.inc_ and to set it in $this->_autoload_libraries in
 *    _midcom/interfaces.php_
 *
 * @package midcom.helper.dm2config
 */
class midcom_helper_dm2config_config extends midcom_baseclasses_components_handler
{
    /**
     * DM2 controller instance
     *
     * @access private
     * @var midcom_helper_datamanager2_controller $_controller
     */
    var $_controller;

    /**
     * DM2 configuration schema
     *
     * @access private
     * @var midcom_helper_datamanager2_schema $_schemadb
     */
    var $_schemadb;

    /**
     * Constructor. Connect to the parent class constructor, but do nothing else
     *
     * @access public
     */
    function midcom_helper_dm2config_config()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Load midcom_helper_datamanager2_controller instance or output an error on any error
     *
     * @access private
     * @return boolean Indicating success
     */
    function _load_controller()
    {
        debug_add(__CLASS__, __FUNCTION__);

        if (!$this->_config->get('schemadb_config'))
        {
            debug_add('No "schemadb_config" defined in the configuration', MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "You must define 'schemadb_config' for displaying configuration interface");
            // This will exit
        }

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_config'));

        if (empty($this->_schemadb))
        {
            debug_add('Failed to load the schemadb', MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to load configuration schemadb');
            // This will exit
        }

        // Create a 'simple' controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_topic);

        if (! $this->_controller->initialize())
        {
            debug_add('Failed to initialize the configuration controller');
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for photo {$this->_photo->id}.");
            // This will exit.
        }

        return true;
    }

    /**
     * Generic handler for all the DM2 based configuration requests
     *
     * @access public
     * @param string $handler_id    Name of the handler
     * @param Array  $args          Variable arguments
     * @param Array  $data          Miscellaneous output data
     * @return boolean              Indicating success
     */
    function _handler_config($handler_id, $args, &$data)
    {
        // Prepend the style directory to show configuration style elements
        $_MIDCOM->style->prepend_component_styledir('midcom.helper.dm2config');

        // Require corresponding ACL's
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midgard:config');

        // Add DM2 link head
        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel' => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/legacy.css',
                'media' => 'all',
            )
        );

        // Load the midcom_helper_datamanager2_controller for form processing
        $this->_load_controller();

        // Process the form
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->uimessages->add($this->_l10n_midcom->get('component configuration'), $_MIDCOM->i18n->get_string('configuration saved', 'midcom.helper.dm2config'));
                $_MIDCOM->relocate('');
                // This will exit
                break;

            case 'cancel':
                $_MIDCOM->uimessages->add($this->_l10n_midcom->get('component configuration'), $_MIDCOM->i18n->get_string('cancelled', 'midcom.helper.dm2config'));
                $_MIDCOM->relocate('');
                // This will exit
                break;

        }

        // Add back button to view toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/back.png',
            )
        );

        // Update the breadcrumb and page title
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "config.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('component configuration'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle(sprintf($_MIDCOM->i18n->get_string('component configuration for folder %s', 'midcom.helper.dm2config'), $this->_topic->extra));

        debug_add('Schemadb loaded for DM2 configuration');
        debug_pop();

        return true;
    }

    /**
     * Show the configuration screen
     *
     * @access public
     * @param string $handler_id    Name of the handler
     * @param Array  $data          Miscellaneous output data
     */
    function _show_config($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        midcom_show_style('dm2_config');
    }
}
?>