<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php,v 1.4 2006/06/14 15:01:43 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Invoice interface class.
 * 
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_viewer extends midcom_baseclasses_components_request
{
    function org_openpsa_invoices_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Match /
        $this->_request_switch['list_open'] = array(
            'handler' => Array('org_openpsa_invoices_handler_list', 'dashboard'),
        );

        /*
        // Match /projects/
        $this->_request_switch['list_projects_uninvoiced'] = array(
            'fixed_args' => array('projects'),     
            'handler' => Array('org_openpsa_invoices_handler_projects', 'uninvoiced'),
        );
        */

        // Match /list/customer/<company guid>
        $this->_request_switch['list_customer_open'] = array(
            'fixed_args' => array('list', 'customer'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_invoices_handler_list', 'customer'),
        );

        // Match /list/customer/all/<company guid>
        $this->_request_switch['list_customer_all'] = array(
            'fixed_args' => array('list', 'customer', 'all'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_invoices_handler_list', 'customer'),
        );
        
        // Match /list/deliverable/<deliverable guid>
        $this->_request_switch['list_deliverable_all'] = array(
            'fixed_args' => array('list', 'deliverable'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_invoices_handler_list', 'deliverable'),
        );
        
        // Match /invoice/new/
        $this->_request_switch['invoice_new_nocustomer'] = array(
            'fixed_args' => array('invoice', 'new'),
            'handler' => Array('org_openpsa_invoices_handler_edit', 'new'),
        ); 

        // Match /invoice/new/<company guid>
        $this->_request_switch['invoice_new'] = array(
            'fixed_args' => array('invoice', 'new'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_invoices_handler_edit', 'new'),
        ); 

        // Match /invoice/edit/<guid>
        $this->_request_switch['invoice_edit'] = array(
            'fixed_args' => array('invoice', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_invoices_handler_edit', 'edit'),
        );

        // Match /invoice/mark_sent/<guid>
        $this->_request_switch['invoice_mark_sent'] = array(
            'fixed_args' => array('invoice', 'mark_sent'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_invoices_handler_edit', 'mark_sent'),
        );
        
        // Match /invoice/mark_paid/<guid>
        $this->_request_switch['invoice_mark_paid'] = array(
            'fixed_args' => array('invoice', 'mark_paid'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_invoices_handler_edit', 'mark_paid'),
        );        
        
        // Match /invoice/<guid>
        $this->_request_switch['invoice'] = array(
            'fixed_args' => array('invoice'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_invoices_handler_edit', 'view'),
        ); 
        
        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/openpsa/invoices/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
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