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
    
    function calculate_price($update = true)
    {
        if ($this->id)
        {
            $pricePerUnit = 0;
            $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
            $deliverable_qb->add_constraint('salesproject', '=', $this->salesproject);
            $deliverable_qb->add_constraint('up', '=', $this->id);
            $deliverables = $deliverable_qb->execute();
                        
            foreach ($deliverables as $deliverable)
            {
                $pricePerUnit = $pricePerUnit + $deliverable->price;
            }
            
            if (count($deliverables) > 0)
            {
                $this->pricePerUnit = $pricePerUnit;
            }
        }
    
        $price = $this->units * $this->pricePerUnit;
        
        if ($price != $this->price)
        {
            $this->price = $price;
            
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
    
    function _send_invoice($sum, $description)
    {
        // Recalculate price, just to be sure
        $this->calculate_price();
        
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        
        // Send invoice
        $invoice = new org_openpsa_invoices_invoice();
        $invoice->customer = $salesproject->customer;
        $invoice->invoiceNumber = org_openpsa_invoices_invoice::generate_invoice_number();
        $invoice->owner = $this->owner;
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
     */
    function new_subscription_cycle($send_invoice = true)
    {
        $this_cycle = time();
        $this_cycle_identifier = $this->_get_cycle_identifier();
        $this_cycle_amount = $this->price;
        $next_cycle = $this->_calculate_cycle_next($this_cycle);
        
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
                $this->_create_task($this_cycle, $next_cycle - 1);
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
        
        // TODO: Register midcom.services.at event
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
    
    function _create_task($start, $end)
    {
        $project = false;
        $task = false;
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        
        // TODO: Check if we already have an open task for this delivery?
        
        // Check if we already have a project for the sales project
        $project = $this->_probe_project();
        
        // Create the task
        $task = new org_openpsa_projects_task();
        $task->agreement = $this->id;
        $task->customer = $salesproject->customer;
        $task->title = $this->title;
        $task->description = $this->description;
        $task->start = $start;
        $task->end = $end;

        $task->manager = $salesproject->owner;
        $task->contacts = $salesproject->contacts;
        // TODO: Initiate automated resourcing seek when project broker is done
        
        if ($project)
        {
            $task->up = $project->id;
        }
        
        // TODO: Figure out if we really want to keep this
        $task->invoiceable_default = true;
        
        if ($task->create())
        {
            $relation_product = org_openpsa_relatedto_handler::create_relatedto($task, 'org.openpsa.projects', $product, 'org.openpsa.products');
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'), sprintf($_MIDCOM->i18n->get_string('created task "%s"', 'org.openpsa.projects'), $task->title), 'ok');
            return true;
        }
        return false;
    }
    
    function order()
    {
        if ($this->state >= ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED)
        {
            return false;
        }
        
        // Check what kind of order this is
        $product = new org_openpsa_products_product_dba($this->product);

        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            // This is a new subscription, initiate the cycle
            $this->new_subscription_cycle();
        }
        else
        {
            // Check if we need to create task or ship goods
            switch ($product->orgOpenpsaObtype)
            {
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE:
                    $this->_create_task($this->start, $this->end);
                    break;
                case ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS:
                    // TODO: Warehouse management: create new order
                default:
                    break;
            }
        }
        
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