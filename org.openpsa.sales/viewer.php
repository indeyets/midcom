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

    function org_openpsa_sales_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->_toolbars =& midcom_helper_toolbars::get_instance();
        $this->_request_data['toolbars'] =& $this->_toolbars;

        // Match /list/salesprojects/<status>
        $this->_request_switch[] = array(
            'fixed_args' => array('list', 'salesprojects'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_sales_handler_list', 'salesprojects'),
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

        // Match /
        $this->_request_switch[] = array(
            'handler' => Array('org_openpsa_sales_handler_list', 'active'),
        );
        
        //Add common relatedto request switches
        org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.sales');
        //If you need any custom switches add them here

        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.sales/sales.css",
            )
        );   
        
        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.invoices/invoices.css",
            )
        );
    }
}

?>