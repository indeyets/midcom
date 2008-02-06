<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server viewer class
 *
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_viewer extends midcom_baseclasses_components_request
{
    function cc_kaktus_pearserver_viewer($object, $config)
    {
        parent::midcom_baseclasses_components_request($object, $config);
        $this->_current_node = null;
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // List latest releases and show a general welcome page
        $this->_request_switch['welcome'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_welcome', 'welcome'),
        );

        // Configuration screen
        $this->_request_switch['config'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_config', 'config'),
            'fixed_args' => array ('config'),
        );

        // Upload a release
        // Match /upload/
        $this->_request_switch['upload'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_upload', 'upload'),
            'fixed_args' => array ('upload'),
        );

        // Process an uploaded release
        // Match /process/<release guid>/
        $this->_request_switch['process'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_process', 'process'),
            'fixed_args' => array ('process'),
            'variable_args' => 1,
        );

        // List categories
        // Match /c/
        $this->_request_switch['xml_channel_list'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_channel', 'list'),
            'fixed_args' => array ('c'),
        );

        // List all the categories
        // Match /c/categories.xml
        $this->_request_switch['xml_channel_list_xml'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_channel', 'categories_xml'),
            'fixed_args' => array ('c', 'categories.xml'),
        );

        // Show channel details
        // Match /c/<channel name>/
        $this->_request_switch['xml_channel_type_list'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_channel', 'channel'),
            'fixed_args' => array('c'),
            'variable_args' => 1,
        );

        // Show channel details in XML format
        // Match /c/<channel name/<xml file>/
        $this->_request_switch['xml_channel_file'] = array
        (
            'handler' => array ('cc_kaktus_pearserver_handler_channel', 'xml'),
            'fixed_args' => array('c'),
            'variable_args' => 1,
        );
    }

    /**
     * Populate the toolbar with common items
     *
     * @access private
     */
    function _populate_node_toolbar()
    {
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            )
        );

        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'upload/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('upload a release'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            )
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_request_data['root_group'] = new org_openpsa_products_product_group_dba($this->_config->get('root_group'));

        if (   !$this->_request_data['root_group']
            || !isset($this->_request_data['root_group']->guid)
            || !$this->_request_data['root_group']->guid)
        {
            $this->_generate_root_group();
        }

        $this->_populate_node_toolbar();

        return true;
    }

    /**
     * Generate a root group for products
     *
     * @access private
     * @return boolean Indicating success
     */
    function _generate_root_group()
    {
        $root_group = new org_openpsa_products_product_group_dba();
        $root_group->title = 'PEAR channel packages';
        $root_group->description = 'Packages for PEAR channel';

        if (!$root_group->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create the root group for PEAR packages, last mgd_errstr() was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('Object data', $root_group, MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create the root group object, see error level log for details');
        }

        $this->_request_data['root_group'] =& $root_group;
        $this->_topic->set_parameter('cc.kaktus.pearserver', 'root_group', $root_group->guid);
        return true;
    }
}
?>