<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Invoices reporting
 *
 * @package org.openpsa.projects
 */
class org_openpsa_reports_handler_invoices_report extends org_openpsa_reports_handler_reports_base
{
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        // We need sales classes etc here
        $_MIDCOM->componentloader->load('org.openpsa.invoices');
        $this->module = 'invoices';
        $this->_initialize_datamanager1($this->module, $this->_config->get('schemadb_queryform_'. $this->module));
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_generator($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (!$this->_generator_load_redirect($args))
        {
            return false;
        }
        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_invoices";
        $this->_handler_generator_style();


        /*** Copied from sales/handler/deliverable/report.php ***/
        $data['invoices'] = Array();

        // Calculate time range
        /*
        // TODO: Make more configurable
        $time = time();
        require_once 'Calendar/Month.php';
        $this_month =& new Calendar_Month(date('Y', $time), date('m', $time));
        $next_month = $this_month->nextMonth('object');

        $data['start'] = $this_month->getTimestamp();
        $data['end'] = $next_month->getTimestamp();
        */
        $data['start'] = $this->_request_data['query_data']['start']['timestamp'];
        $data['end'] = $this->_request_data['query_data']['end']['timestamp'];

        // List invoices
        $invoice_qb = org_openpsa_invoices_invoice::new_query_builder();
        $invoice_qb->begin_group('AND');
            $invoice_qb->add_constraint('due', '>=', $data['start']);
            $invoice_qb->add_constraint('due', '<', $data['end']);
        $invoice_qb->end_group();
        if ($this->_request_data['query_data']['resource'] != 'all')
        {
            $this->_request_data['query_data']['resource_expanded'] = $this->_expand_resource($this->_request_data['query_data']['resource']);
            $invoice_qb->begin_group('OR');
            foreach ($this->_request_data['query_data']['resource_expanded'] as $pid)
            {
                $invoice_qb->add_constraint('owner', '=', $pid);
            }
            $invoice_qb->end_group();
        }
        $invoice_qb->add_order('id', 'DESC');

        $data['invoices'] = $invoice_qb->execute();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_generator($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        midcom_show_style('invoices_report-start');

        /*** Copied from sales/handler/deliverable/report.php ***/
        midcom_show_style('invoices_report-header');

        $invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');

        $sums_per_person = Array();
        $sums_all = Array
        (
            'price'  => 0,
        );
        $this->_request_data['contacts_node'] = midcom_helper_find_node_by_component('org.openpsa.contacts');
        $odd = true;
        foreach ($data['invoices'] as $invoice)
        {

            $customer = new midcom_db_group($invoice->customer);

            if (!array_key_exists($invoice->owner, $sums_per_person))
            {
                $sums_per_person[$invoice->owner] = Array
                (
                    'price'  => 0,
                );
            }

            if ($odd)
            {
                $data['row_class'] = '';
                $odd = false;
            }
            else
            {
                $data['row_class'] = ' class="even"';
                $odd = true;
            }

            // Calculate the price and cost from invoices
            $data['invoice_string'] = '';
            $invoice_cycle_numbers = Array();
            $invoice_class = $invoice->get_invoice_class();

            if ($invoices_node)
            {
                $invoice_label = "<a target=\"_blank\" class=\"{$invoice_class}\" href=\"{$invoices_node[MIDCOM_NAV_FULLURL]}invoice/{$invoice->guid}/\">{$invoice->invoiceNumber}</a>";
            }
            else
            {
                $invoice_label = $invoice->invoiceNumber;
            }

            $data['invoice_string'] = $invoice_label;

            $data['customer'] = $customer;

            $sums_per_person[$invoice->owner]['price'] += $invoice->sum;
            $sums_all['price'] += $invoice->sum;

            $data['invoice'] = $invoice;

            midcom_show_style('invoices_report-item');
        }

        $data['sums_per_person'] = $sums_per_person;
        $data['sums_all'] = $sums_all;
        midcom_show_style('invoices_report-footer');
        midcom_show_style('invoices_report-end');

        debug_pop();
    }

}
?>