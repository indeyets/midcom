<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Deliverable reports
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_deliverable_report extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function org_openpsa_sales_handler_deliverable_report()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Lists salesprojects for either current user or all
     */
    function _handler_report($handler_id, $args, &$data)
    {
        if ($handler_id == 'deliverable_report')
        {
            $_MIDCOM->auth->require_valid_user();
        }
        else
        {
            $_MIDCOM->auth->require_admin_user();
        }
        
        $data['invoices'] = Array();

        // Calculate time range
        // TODO: Make more configurable
        $time = time();
        require_once 'Calendar/Month.php';
        $this_month =& new Calendar_Month(date('Y', $time), date('m', $time));
        $next_month = $this_month->nextMonth('object');

        $data['start'] = $this_month->getTimestamp();
        $data['end'] = $next_month->getTimestamp();
        
        // List sales projects
        $salesproject_qb = org_openpsa_sales_salesproject::new_query_builder();
        $salesproject_qb->add_constraint('status', '<>', ORG_OPENPSA_SALESPROJECTSTATUS_LOST);
        if ($handler_id == 'deliverable_report')
        {
            // List only from current user
            $salesproject_qb->add_constraint('owner', '=', $_MIDGARD['user']);
        }
        $salesprojects = $salesproject_qb->execute();
        
        // List deliverables related to the sales projects
        $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
        $deliverable_qb->add_constraint('state', '<>', 'ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED');
        $deliverable_qb->begin_group('OR');
        foreach ($salesprojects as $salesproject)
        {
            $deliverable_qb->add_constraint('salesproject', '=', $salesproject->id);
        }
        $deliverable_qb->end_group();
        $deliverables = $deliverable_qb->execute();
        
        // List relations of invoices to the deliverables we have
        $relation_qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $relation_qb->add_constraint('fromComponent', '=', 'org.openpsa.invoices');
        $relation_qb->add_constraint('fromClass', '=', 'org_openpsa_invoices_invoice');        
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
            $invoice = new org_openpsa_invoices_invoice($relation->fromGuid);
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
            MIDCOM_NAV_URL => "salesproject/{$this->_salesproject->guid}.html",
            MIDCOM_NAV_NAME => $this->_salesproject->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_salesproject->title}");
        */
        return true;
    }

    /**
     * Shows the report
     */
    function _show_report($handler_id, &$data)
    {   
        $data['handler_id' ] = $handler_id;
        midcom_show_style('show-deliverable-report-header');
        
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
        
            $deliverable = new org_openpsa_sales_salesproject_deliverable($deliverable_guid);
            $salesproject = new org_openpsa_sales_salesproject($deliverable->salesproject);
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

            midcom_show_style('show-deliverable-report-item');
        }
        
        $data['sums_per_person'] = $sums_per_person;
        $data['sums_all'] = $sums_all;
        midcom_show_style('show-deliverable-report-footer');
    }
}
?>