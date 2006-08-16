<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class org_openpsa_sales_salesproject_deliverable extends __org_openpsa_sales_salesproject_deliverable
{
    function org_openpsa_sales_salesproject_deliverable($id = null)
    {
        return parent::__org_openpsa_sales_salesproject_deliverable($id);
    }
    
    function get_parent_guid_uncached()
    {
        if ($this->up != 0)
        {
            $parent = new org_openpsa_sales_salesproject_deliverable($this->up);            
            return $parent;
        }
        elseif ($this->salesproject != 0)
        {
            $parent = new org_openpsa_sales_salesproject($this->salesproject);            
            return $parent;
        }
        else
        {
            return null;
        }
    }

    function _on_creating()
    {
        $this->calculate_price(false);
        return parent::_on_creating();
    }
    
    function _on_updating()
    {
        $this->calculate_price();
        return parent::_on_updating();
    }
    
    function _on_deleted()
    {
        $parent = $this->get_parent_guid_uncached();
        if (is_object($parent))
        {
            $parent->calculate_price();
        }
    }
    
    function calculate_price($update = true)
    {
        if ($this->id)
        {
            $pricePerUnit = 0;
            $costPerUnit = 0;
            
            // Check if we have subcomponents
            $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
            $deliverable_qb->add_constraint('salesproject', '=', $this->salesproject);
            $deliverable_qb->add_constraint('up', '=', $this->id);
            $deliverables = $deliverable_qb->execute();
            
            if (count($deliverables) > 0)
            {
                // If subcomponents exist, the price and cost per unit default to the
                // sum of price and cost of all subcomponents
                foreach ($deliverables as $deliverable)
                {
                    $pricePerUnit = $pricePerUnit + $deliverable->price;
                    $costPerUnit = $costPerUnit + $deliverable->cost;
                }
                
                $this->pricePerUnit = $pricePerUnit;
                $this->costPerUnit = $costPerUnit;
            }
        }
    
        if (   $this->invoiceByActualUnits
            || $this->plannedUnits == 0)
        {
            // In most cases we calculate the price based on the actual units entered
            $price = $this->units * $this->pricePerUnit;
        }
        else
        {
            // But in some deals we use the planned units instead
            $price = $this->plannedUnits * $this->pricePerUnit;
        }
        
        // Count cost based on the cost type
        switch ($this->costType)
        {
            case '%':
                // The cost is a percentage of the price
                $cost = $price / 100 * $this->costPerUnit;
                break;
            default:
            case 'm':
                // The cost is a fixed sum per unit
                $cost = $this->units * $this->costPerUnit;
                break;
        }
        
        if (   $price != $this->price
            || $cost != $this->cost)
        {
            $this->price = $price;
            $this->cost = $cost;
            
            if ($update)
            {
                $this->update();
                $parent = $this->get_parent_guid_uncached();
                if (is_object($parent))
                {
                    $parent->calculate_price();
                }
            }
        }
    }
    
    /**
     * Send an invoice from the deliverable. Creates a new, unsent org.openpsa.invoices item
     * and adds a relation between it and the deliverable.
     */
    function _send_invoice($sum, $description)
    {        
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        
        // Send invoice
        $invoice = new org_openpsa_invoices_invoice();
        $invoice->customer = $salesproject->customer;
        $invoice->invoiceNumber = org_openpsa_invoices_invoice::generate_invoice_number();
        $invoice->owner = $salesproject->owner;
        // TODO: Get from invoices configuration
        $invoice->due = 14 * 3600 * 24 + time();
        $invoice->description = $description;
        $invoice->sum = $sum;
        
        if ($invoice->create())
        {
            // TODO: Create invoicing task if assignee is defined
        
            $relation_deliverable = org_openpsa_relatedto_handler::create_relatedto($invoice, 'org.openpsa.invoices', $this, 'org.openpsa.sales');
        }
    }
    
    /**
     * Initiates a new subscription cycle and registers a midcom.services.at call for the next cycle.
     *
     * The subscription cycles rely on midcom.services.at. I'm not sure if it is wise to rely on it for such
     * a totally mission critical part of OpenPsa. Some safeguards might be wise to add.
     */
    function new_subscription_cycle($cycle_number, $send_invoice = true)
    {
        $this_cycle = time();
        $this_cycle_identifier = $cycle_number;
        $next_cycle = $this->_calculate_cycle_next($this_cycle);

        // Recalculate price to catch possible unit changes
        $this->calculate_price();

        $this_cycle_amount = $this->price;

        // TODO: Should we use a more meaninful label for invoices and tasks than just the cycle number?
        
        $product = new org_openpsa_products_product_dba($this->product);
        
        if ($send_invoice)
        {
            $this->_send_invoice($this_cycle_amount, sprintf('%s %s', $this->title, $this_cycle_identifier));
        }
        
        switch ($product->orgOpenpsaObtype)
        {
            case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE:
                // Close previous task(s)
                $task_qb = org_openpsa_projects_task::new_query_builder();
                $task_qb->add_constraint('agreement', '=', $this->id);
                $task_qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_CLOSED);
                $tasks = $task_qb->execute();
                foreach ($tasks as $task)
                {
                    $task->close(sprintf($_MIDCOM->i18n->get_string('completed by subscription %s', 'org.openpsa.sales'), $this_cycle_identifier));
                }
                
                // Create task for the duration of this cycle
                $this->_create_task($this_cycle, $next_cycle - 1, sprintf('%s %s', $this->title, $this_cycle_identifier));
                break;
                
            case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS:
                // TODO: Warehouse management: create new order
            default:
                break;
        }
        
        if ($this->state < ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED)
        {
            $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED;
        }
        
        $this->invoiced = $this->invoiced + $this_cycle_amount;
        $this->update();
    
        if ($this->end < $next_cycle)
        {
            // Do not generate next cycle, the contract ends before
            return false;
        }
        
        // Register next cycle with midcom.services.at
        $args = array(
            'deliverable' => $this->guid,
            'cycle'       => $this_cycle_identifier + 1,
        );
        $atstat = midcom_services_at_interface::register($next_cycle, 'org.openpsa.sales', 'new_subscription_cycle', $args);
        return $atstat;
    }
    
    function _calculate_cycle_next($time)
    {
        switch ($this->unit)
        {
            case 'd':
                // Daily recurring subscription
                require_once 'Calendar/Day.php';
                $this_day =& new Calendar_Day(date('Y', $time), date('m', $time), date('d', $time));
                $next_day = $this_day->nextDay('object');
                return $next_day->getTimestamp();
            case 'm':
                // Monthly recurring subscription
                require_once 'Calendar/Month.php';
                $this_month =& new Calendar_Month(date('Y', $time), date('m', $time));
                $next_month = $this_month->nextMonth('object');
                return $next_month->getTimestamp();
            case 'q':
                // Quarterly recurring subscription
                require_once 'Calendar/Month.php';
                $year = date('Y', $time);
                switch (date('m', $time))
                {
                    case 1:
                    case 2:
                    case 3:
                        $next_month =& new Calendar_Month($year, 4);
                        break;
                    case 4:
                    case 5:
                    case 6:
                        $next_month =& new Calendar_Month($year, 7);
                        break;
                    case 7:
                    case 8:
                    case 9:
                        $next_month =& new Calendar_Month($year, 10);
                        break;
                    case 10:
                    case 11:
                    case 12:
                        $next_month =& new Calendar_Month($year + 1, 1);
                        break;
                }
                return $next_month->getTimestamp();
            case 'y':
                // Monthly recurring subscription
                require_once 'Calendar/Year.php';
                $this_year =& new Calendar_Year(date('Y', $time));
                $next_year = $this_month->nextYear('object');
                return $next_year->getTimestamp();
            default:
                return false;
        }
    }
    
    /**
     * Find out if there already is a project for this sales project. If not, create one.
     * @return org_openpsa_projects_project $project
     */
    function _probe_project()
    {
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        $relation_qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $relation_qb->add_constraint('toGuid', '=', $salesproject->guid);
        $relation_qb->add_constraint('fromComponent', '=', 'org.openpsa.projects');
        $relation_qb->add_constraint('fromClass', '=', 'org_openpsa_projects_project');        
        $relations = $relation_qb->execute();
        if (count($relations) > 0)
        {
            // Just pick the first
            $project = new org_openpsa_projects_project($relations[0]->fromGuid);
            return $project;
        }
        
        // No project yet, try to create
        $project = new org_openpsa_projects_project();
        $project->customer = $salesproject->customer;
        $project->title = $salesproject->title;
        
        $schedule_object = $this;
        if ($this->up != 0)
        {
            $schedule_object = $this->get_parent();
        }
        $project->start = $schedule_object->start;
        $project->end = $schedule_object->end;
        
        $project->manager = $salesproject->owner;
        $project->contacts = $salesproject->contacts;
        // TODO: Figure out if we really want to keep this
        $project->invoiceable_default = true;
        if ($project->create())
        {
            $relation = org_openpsa_relatedto_handler::create_relatedto($project, 'org.openpsa.projects', $salesproject, 'org.openpsa.sales');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'), sprintf($_MIDCOM->i18n->get_string('created project "%s"', 'org.openpsa.projects'), $project->title), 'ok');
            return $project;
        }
        return false;
    }
    
    function _create_task($start, $end, $title)
    {
        $project = false;
        $task = false;
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        $product = new org_openpsa_products_product_dba($this->product);
        
        // TODO: Check if we already have an open task for this delivery?
        
        // Check if we already have a project for the sales project
        $project = $this->_probe_project();
        
        // Create the task
        $task = new org_openpsa_projects_task();
        $task->agreement = $this->id;
        $task->customer = $salesproject->customer;
        $task->title = $title;
        $task->description = $this->description;
        $task->start = $start;
        $task->end = $end;
        $task->plannedHours = $this->plannedUnits;

        $task->manager = $salesproject->owner;
        $task->contacts = $salesproject->contacts;
        $task->resources[$salesproject->owner] = true;

        // TODO: Initiate automated resourcing seek when project broker is done
        
        if ($project)
        {
            $task->up = $project->id;
        }
        
        // TODO: Figure out if we really want to keep this
        $task->hoursInvoiceableDefault = true;
        
        if ($task->create())
        {
            $relation_product = org_openpsa_relatedto_handler::create_relatedto($task, 'org.openpsa.projects', $product, 'org.openpsa.products');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'), sprintf($_MIDCOM->i18n->get_string('created task "%s"', 'org.openpsa.projects'), $task->title), 'ok');
            return true;
        }
        return false;
    }

    function decline()
    {
        if ($this->state >= ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED)
        {
            return false;
        }
        
        // TODO: Check if salesproject has other open deliverables. If not, mark
        // as lost
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED;
        return $this->update();
    }

    function order()
    {
        if ($this->state >= ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED)
        {
            return false;
        }
        
        // Cache the original price and cost values intended
        $this->plannedUnits = $this->units;
        $this->plannedCost = $this->cost;        
        
        // Check what kind of order this is
        $product = new org_openpsa_products_product_dba($this->product);

        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            // This is a new subscription, initiate the cycle
            $this->new_subscription_cycle(1);
        }
        else
        {
            // Check if we need to create task or ship goods
            switch ($product->orgOpenpsaObtype)
            {
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE:
                    $this->_create_task($this->start, $this->end, $this->title);
                    break;
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS:
                    // TODO: Warehouse management: create new order
                default:
                    break;
            }
        }
        
        // TODO: Check if salesproject has other non-ordered deliverables. If not, mark
        // as won
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED;
        return $this->update();
    }
    
    function deliver($update_deliveries = true)
    {
        if ($this->state > ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED)
        {
            return false;
        }
        
        $product = new org_openpsa_products_product_dba($this->product);
        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            // Subscriptions are ongoing, not one delivery
            return false;
        }

        // Check if we need to create task or ship goods
        if ($update_deliveries)
        {
            switch ($product->orgOpenpsaObtype)
            {
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE:
                    // Change status of tasks connected to the deliverable
                    $task_qb = org_openpsa_projects_task::new_query_builder();
                    $task_qb->add_constraint('agreement', '=', $this->id);
                    $task_qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_CLOSED);
                    $tasks = $task_qb->execute();
                    foreach ($tasks as $task)
                    {
                        $task->close(sprintf($_MIDCOM->i18n->get_string('completed from deliverable %s', 'org.openpsa.sales'), $this->title));
                    }
                    break;
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS:
                    // TODO: Warehouse management: mark product as shipped
                default:
                    break;
            }
        }
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED;
        $this->end = time();
        if ($this->update())
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'), sprintf($_MIDCOM->i18n->get_string('marked deliverable "%s" delivered', 'org.openpsa.sales'), $agreement->title), 'ok');
            return true;
        }
        return false;
    }
    
    function invoice($sum)
    {
        if ($this->state > ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED)
        {
            return false;
        }
        
        $product = new org_openpsa_products_product_dba($this->product);
        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            // Subscriptions are invoiced by new_subscription_cycle method
            return false;
        }
        
        $open_amount = $this->price - $this->invoiced;
        if ($sum > $open_amount)
        {
            return false;
        }
        
        // Generate org.openpsa.invoices invoice
        $this->_send_invoice($sum, $this->title);
        
        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED;
        $this->invoiced = $this->invoiced + $sum;
        return $this->update();
    }
}
?>