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
        $this->_request_switch['list_status'] = array
        (
            'handler' => array('org_openpsa_sales_handler_list', 'list'),
            'fixed_args' => array('list'),
            'variable_args' => 1,
        );

        // Match /salesproject/edit/<salesproject>
        $this->_request_switch[] = array
        (
            'handler' => array('org_openpsa_sales_handler_edit', 'edit'),
            'fixed_args' => array('salesproject', 'edit'),
            'variable_args' => 1,
        );

        // Match /salesproject/new
        $this->_request_switch[] = array
        (
            'handler' => array('org_openpsa_sales_handler_edit', 'new'),
            'fixed_args' => array('salesproject', 'new'),
        );

        // Match /salesproject/<salesproject>
        $this->_request_switch[] = array
        (
            'handler' => array('org_openpsa_sales_handler_view', 'view'),
            'fixed_args' => array('salesproject'),
            'variable_args' => 1,
        );

        // Match /deliverable/add/<salesproject>/
        $this->_request_switch[] = array
        (
            'handler' => array('org_openpsa_sales_handler_deliverable_add', 'add'),
            'fixed_args' => array('deliverable', 'add'),
            'variable_args' => 1,
        );

        // Match /deliverable/process/<deliverable>/
        $this->_request_switch[] = array
        (
            'handler' => array('org_openpsa_sales_handler_deliverable_process', 'process'),
            'fixed_args' => array('deliverable', 'process'),
            'variable_args' => 1,
        );

        // Match /deliverable/edit/<deliverable>
        $this->_request_switch['deliverable_edit'] = array
        (
            'handler' => array('org_openpsa_sales_handler_deliverable_admin', 'edit'),
            'fixed_args' => array('deliverable', 'edit'),
            'variable_args' => 1,
        );

        // Match /deliverable/<deliverable>
        $this->_request_switch['deliverable_view'] = array
        (
            'handler' => array('org_openpsa_sales_handler_deliverable_view', 'view'),
            'fixed_args' => array('deliverable'),
            'variable_args' => 1,
        );

        // Match /
        $this->_request_switch['list_active'] = array
        (
            'handler' => array('org_openpsa_sales_handler_list', 'list'),
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
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.core/ui-elements.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.projects/projects.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.invoices/invoices.css",
            )
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.sales/sales.css",
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
        $_MIDCOM->load_library('org.openpsa.contactwidget');

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
        $tmp = array();

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