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
     * Shows the loaded salesproject.
     */
    function _show_report($handler_id, &$data)
    {   
        $invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');
        echo "<h1>sales report " . strftime('%x', $data['start']) . " - " . strftime('%x', $data['end']) . "</h1>\n";
        echo "<table class=\"sales_report\">\n";
        echo "    <thead>\n";
        echo "        <tr>\n";
        echo "            <th>Invoices</th>\n";
        if ($handler_id != 'deliverable_report')
        {
            echo "            <th>Owner</th>\n";
        }
        echo "            <th>Customer</th>\n";
        echo "            <th>Sales project</th>\n";
        echo "            <th>Product</th>\n";
        echo "            <th>Price</th>\n";
        echo "            <th>Cost</th>\n";
        echo "            <th>Profit</th>\n";
        echo "        </tr>\n";
        echo "    </thead>\n";
        echo "    <tbody>\n";        
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
                $class = '';
                $odd = false;
            }
            else
            {
                $class = ' class="even"';
                $odd = true;
            }
            
            echo "<tr{$class}>\n";

            echo "    <td class=\"invoices\"><ul>\n";            
            // Calculate the price and cost from invoices
            $invoice_price = 0;
            foreach ($invoices as $invoice)
            {
                $invoice_price += $invoice->sum;
                $class = $invoice->get_invoice_class();
                
                if ($invoices_node)
                {
                    $invoice_label = "<a class=\"{$class}\" href=\"{$invoices_node[MIDCOM_NAV_FULLURL]}invoice/{$invoice->guid}/\">{$invoice->invoiceNumber}</a>";
                }
                else
                {
                    $invoice_label = $invoice->invoiceNumber;
                }
                
                echo "<li class=\"{$class}\">{$invoice_label}</li>\n";
            }
            echo "</ul></td>\n";
            
            if ($invoice_price > $deliverable->price)
            {
                // TODO: Do we need other ways note that this is a subscription?
                $price = $invoice_price;
                $cost = $deliverable->cost * count($invoices);
            }
            elseif ($invoice_price <= $deliverable->price)
            {
                // This is a partial invoice, calculate cost as percentage
                $cost_percentage = 100 / $deliverable->price * $invoice_price;
                $cost = $deliverable->cost / 100 * $cost_percentage;
            }
            $price = $invoice_price;
            
            $profit = $price - $cost;
            
            if ($handler_id != 'deliverable_report')
            {
                $owner = new midcom_db_person($salesproject->owner);
                echo "    <td>{$owner->name}</td>\n";
            }
            
            echo "    <td>{$customer->official}</td>\n";
            echo "    <td>{$salesproject->title}</td>\n";
            echo "    <td>{$deliverable->title}</td>\n";

            echo "    <td>{$price}</td>\n";
            $sums_per_person[$salesproject->owner]['price'] += $price;
            $sums_all['price'] += $price;

            echo "    <td>{$cost}</td>\n";
            $sums_per_person[$salesproject->owner]['cost'] += $cost;
            $sums_all['cost'] += $cost;
            
            echo "    <td>{$profit}</td>\n";
            $sums_per_person[$salesproject->owner]['profit'] += $profit;
            $sums_all['profit'] += $profit;
            echo "</tr>\n";        
        }
        echo "    </tbody>\n";
        echo "    <tfoot>\n";
        $colspan = 4;
        if ($handler_id != 'deliverable_report')
        {
            $colspan++;
            foreach ($sums_per_person as $person_id => $sums)
            {
                $owner = new midcom_db_person($person_id);
                echo "        <tr>\n";
                echo "            <td colspan=\"{$colspan}\">{$owner->name}</td>\n";
                echo "            <td>{$sums['price']}</td>\n";
                echo "            <td>{$sums['cost']}</td>\n";
                echo "            <td>{$sums['profit']}</td>\n";
                echo "        </tr>\n";
            }
        }
        echo "        <tr>\n";
        echo "            <td colspan=\"{$colspan}\">Totals</td>\n";
        echo "            <td>{$sums_all['price']}</td>\n";
        echo "            <td>{$sums_all['cost']}</td>\n";
        echo "            <td>{$sums_all['profit']}</td>\n";
        echo "        </tr>\n";

        echo "    </tfoot>\n";
        echo "</table>\n";
    }
}
?>