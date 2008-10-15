<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Deliverable reports
 *
 * @package org.openpsa.projects
 */
class org_openpsa_reports_handler_sales_report extends org_openpsa_reports_handler_base
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
        $this->module = 'sales';
        $this->_initialize_datamanager1($this->module, $this->_config->get('schemadb_queryform_'. $this->module));
        return true;
    }

/*
    function _handler_xxx($handler_id, $args, &$data)
    {

        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_sales";

        return true;
    }

    function _show_xxx($handler_id, &$data)
    {
    }
*/

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
        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_sales";
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

        // List sales projects
        $salesproject_qb = org_openpsa_sales_salesproject_dba::new_query_builder();
        $salesproject_qb->add_constraint('status', '<>', ORG_OPENPSA_SALESPROJECTSTATUS_LOST);
        /*
        if ($handler_id == 'deliverable_report')
        {
            // List only from current user
            $salesproject_qb->add_constraint('owner', '=', $_MIDGARD['user']);
        }
        */
        if ($this->_request_data['query_data']['resource'] != 'all')
        {
            $this->_request_data['query_data']['resource_expanded'] = $this->_expand_resource($this->_request_data['query_data']['resource']);
            $salesproject_qb->begin_group('OR');
            foreach ($this->_request_data['query_data']['resource_expanded'] as $pid)
            {
                $salesproject_qb->add_constraint('owner', '=', $pid);
            }
            $salesproject_qb->end_group();
        }
        $salesprojects = $salesproject_qb->execute();

        // List deliverables related to the sales projects
        $deliverable_qb = org_openpsa_sales_salesproject_deliverable_dba::new_query_builder();
        $deliverable_qb->add_constraint('state', '<>', 'ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED');
        $deliverable_qb->begin_group('OR');
        foreach ($salesprojects as $salesproject)
        {
            $deliverable_qb->add_constraint('salesproject', '=', $salesproject->id);
        }
        $deliverable_qb->end_group();
        $deliverables = $deliverable_qb->execute();

        // List relations of invoices to the deliverables we have
        $relation_qb = org_openpsa_relatedto_relatedto_dba::new_query_builder();
        $relation_qb->add_constraint('fromComponent', '=', 'org.openpsa.invoices');
        $relation_qb->add_constraint('fromClass', '=', 'org_openpsa_invoices_invoice_dba');
        $relation_qb->begin_group('OR');
        foreach ($deliverables as $deliverable)
        {
            $relation_qb->add_constraint('toGuid', '=', $deliverable->guid);
            $data['invoices'][$deliverable->guid] = Array();
        }
        $relation_qb->end_group();
        $relations = $relation_qb->execute();

        // Get invoices our deliverables are related to

        foreach ($relations as $relation)
        {
            $invoice = new org_openpsa_invoices_invoice_dba($relation->fromGuid);
            if (   $invoice->created >= $data['start']
                && $invoice->created < $data['end'])
            {
                $data['invoices'][$relation->toGuid][] = $invoice;
            }
        }
        /*
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "salesproject/{$this->_salesproject->guid}/",
            MIDCOM_NAV_NAME => $this->_salesproject->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_salesproject->title}");
        */
        /*** /Copied from sales/handler/deliverable/report.php ***/

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
        midcom_show_style('sales_report-deliverable-start');

        // Quick workaround to Bergies lazy determination of whether this is user's or everyone's report...
        if ($this->_request_data['query_data']['resource'] == 'user:' . $_MIDCOM->auth->user->guid)
        {
            // My report
            $data['handler_id'] = 'deliverable_report';
        }
        else
        {
            // Generic report
            $data['handler_id'] = 'sales_report';
        }
        /*** Copied from sales/handler/deliverable/report.php ***/
        midcom_show_style('sales_report-deliverable-header');

        $invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');

        $sums_per_person = Array();
        $sums_all = Array
        (
            'price'  => 0,
            'cost'   => 0,
            'profit' => 0,
        );
        $odd = true;
        foreach ($data['invoices'] as $deliverable_guid => $invoices)
        {
            if (count($invoices) == 0)
            {
                // No invoices sent in this project, skip
                continue;
            }

            $deliverable = new org_openpsa_sales_salesproject_deliverable_dba($deliverable_guid);
            $salesproject = new org_openpsa_sales_salesproject_dba($deliverable->salesproject);
            $customer = new midcom_db_group($salesproject->customer);

            if (!array_key_exists($salesproject->owner, $sums_per_person))
            {
                $sums_per_person[$salesproject->owner] = Array
                (
                    'price'  => 0,
                    'cost'   => 0,
                    'profit' => 0,
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
            $invoice_price = 0;
            $data['invoice_string'] = '';
            $invoice_cycle_numbers = Array();
            foreach ($invoices as $invoice)
            {
                $invoice_price += $invoice->sum;
                $invoice_class = $invoice->get_invoice_class();

                if ($invoices_node)
                {
                    $invoice_label = "<a class=\"{$invoice_class}\" href=\"{$invoices_node[MIDCOM_NAV_FULLURL]}invoice/{$invoice->guid}/\">{$invoice->invoiceNumber}</a>";
                }
                else
                {
                    $invoice_label = $invoice->invoiceNumber;
                }

                if ($deliverable->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
                {
                    $invoice_cycle_numbers[] = $invoice->parameter('org.openpsa.sales', 'cycle_number');
                }

                $data['invoice_string'] .= "<li class=\"{$invoice_class}\">{$invoice_label}</li>\n";
            }

            if ($deliverable->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
            {
                // This is a subscription, it should be shown only if it is the first invoice
                if (!in_array(1, $invoice_cycle_numbers))
                {
                    continue;
                    // This will skip to next deliverable
                }

                if ($deliverable->end == 0)
                {
                    // Subscription doesn't have an end date, use specified amounth of months for calculation
                    $cycles = $deliverable->calculate_cycles($this->_config->get('subscription_profit_months'));
                    $data['calculation_basis'] = sprintf($data['l10n']->get('%s cycles in %s months'), $cycles, $this->_config->get('subscription_profit_months'));
                }
                else
                {
                    $cycles = $deliverable->calculate_cycles();
                    $data['calculation_basis'] = sprintf($data['l10n']->get('%s cycles, %s - %s'), $cycles, strftime('%x', $deliverable->start), strftime('%x', $deliverable->end));
                }

                $price = $deliverable->price * $cycles;
                $cost = $deliverable->cost * $cycles;
            }
            else
            {
                // This is a single delivery, calculate cost as percentage as it may be invoiced in pieces
                if ($deliverable->price)
                {
                    $cost_percentage = 100 / $deliverable->price * $invoice_price;
                    $cost = $deliverable->cost / 100 * $cost_percentage;
                }
                else
                {
                    $cost_percentage = 100;
                    $cost = $deliverable->cost;
                }
                $price = $invoice_price;
                $data['calculation_basis'] = sprintf($data['l10n']->get('%s%% of %s'), round($cost_percentage), $deliverable->price);
            }

            // And now just count the profit
            $profit = $price - $cost;
            $data['customer'] = $customer;
            $data['salesproject'] = $salesproject;
            $data['deliverable'] = $deliverable;

            $data['price'] = $price;
            $sums_per_person[$salesproject->owner]['price'] += $price;
            $sums_all['price'] += $price;

            $data['cost'] = $cost;
            $sums_per_person[$salesproject->owner]['cost'] += $cost;
            $sums_all['cost'] += $cost;

            $data['profit'] = $profit;
            $sums_per_person[$salesproject->owner]['profit'] += $profit;
            $sums_all['profit'] += $profit;

            midcom_show_style('sales_report-deliverable-item');
        }

        $data['sums_per_person'] = $sums_per_person;
        $data['sums_all'] = $sums_all;
        midcom_show_style('sales_report-deliverable-footer');
        /*** /Copied from sales/handler/deliverable/report.php ***/
        midcom_show_style('sales_report-deliverable-end');

        debug_pop();
    }


}
?>