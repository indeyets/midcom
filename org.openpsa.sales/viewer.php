<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php,v 1.3 2006/05/10 16:31:05 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sales viewer interface class.
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_viewer extends midcom_baseclasses_components_request
{
    var $_datamanagers = array();
    var $_toolbars;

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);

        $this->_toolbars =& midcom_helper_toolbars::get_instance();
        $this->_request_data['toolbars'] =& $this->_toolbars;

        // Match /list/<status>
        $this->_request_switch['list_status'] = array(
            'fixed_args' => array('list'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_list', 'list'),
        );

        // Match /salesproject/edit/<salesproject>
        $this->_request_switch[] = array(
            'fixed_args' => array('salesproject', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_edit', 'edit_salesproject'),
        );

        // Match /salesproject/new
        $this->_request_switch[] = array(
            'fixed_args' => array('salesproject', 'new'),
            'handler' => Array('org_openpsa_sales_handler_edit', 'new_salesproject'),
        );

        // Match /salesproject/<salesproject>
        $this->_request_switch[] = array(
            'fixed_args' => array('salesproject'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_view', 'view'),
        );

        // Match /deliverable/add/<salesproject>/
        $this->_request_switch[] = array(
            'fixed_args' => array('deliverable', 'add'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_deliverable_add', 'add'),
        );

        // Match /deliverable/process/<deliverable>/
        $this->_request_switch[] = array(
            'fixed_args' => array('deliverable', 'process'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_deliverable_process', 'process'),
        );

        /* Moved to reports
        // Match /deliverable/report/all/
        $this->_request_switch['deliverable_report_all'] = array
        (
            'fixed_args' => array('deliverable', 'report', 'all'),
            'handler' => Array('org_openpsa_sales_handler_deliverable_report', 'report'),
        );

        // Match /deliverable/report/
        $this->_request_switch['deliverable_report'] = array
        (
            'fixed_args' => array('deliverable', 'report'),
            'handler' => Array('org_openpsa_sales_handler_deliverable_report', 'report'),
        );
        */

       // Match /deliverable/edit/<deliverable>
        $this->_request_switch['deliverable_edit'] = array
        (
            'fixed_args' => array('deliverable', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_deliverable_admin', 'edit'),
        );

        // Match /deliverable/<deliverable>
        $this->_request_switch['deliverable_view'] = array
        (
            'fixed_args' => array('deliverable'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_deliverable_view', 'view'),
        );

        // Match /
        $this->_request_switch['list_active'] = array(
            'handler' => Array('org_openpsa_sales_handler_list', 'list'),
        );

        //Add common relatedto request switches
        org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.sales');
        //If you need any custom switches add them here

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.core/ui-elements.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.projects/projects.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.invoices/invoices.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.sales/sales.css",
            )
        );

        $_MIDCOM->auth->require_valid_user();
    }

    /**
     * Generic request startup work:
     *
     * - Load the Schema Database
     * - Add the LINK HTML HEAD elements
     * - Populate the Node Toolbar
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb_salesproject_dm2'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_salesproject_dm2'));
        $this->_request_data['schemadb_salesproject_deliverable'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_deliverable'));

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     * @param org_openpsa_projects_object
     */
    function update_breadcrumb_line($object)
    {
        $tmp = Array();

        while ($object)
        {
            if (is_a($object, 'org_openpsa_sales_salesproject_deliverable_dba'))
            {
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "deliverable/{$object->guid}/",
                    MIDCOM_NAV_NAME => $object->title,
                );
            }
            else
            {
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "salesproject/{$object->guid}/",
                    MIDCOM_NAV_NAME => $object->title,
                );
            }
            $object = $object->get_parent();
        }
        $tmp = array_reverse($tmp);
        return $tmp;
    }
}

?>