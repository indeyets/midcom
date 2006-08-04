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
            'handler' => Array('org_openpsa_sales_handler_edit', 'view_salesproject'),
        );


        // Match /debug
        $this->_request_switch[] = array(
            'fixed_args' => 'debug',
            'handler' => 'debug'
        );
        
        //Add common relatedto request switches
        org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.sales');
        //If you need any custom switches add them here
        
        // Match /
        $this->_request_switch[] = array(
            'handler' => Array('org_openpsa_sales_handler_list', 'active'),
        );
    }

    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['config'] =& $this->_config;
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
        return true;
    }
    
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }
}

?>