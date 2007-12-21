<?php
/**
 * @package org.openpsa.sales
 * @author Nemein Oy, http://www.nemein.com/
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 */

class org_openpsa_sales_salesproject_deliverable extends __org_openpsa_sales_salesproject_deliverable
{
    /**
     * Combination property containing HTML depiction of the deliverable
     */
    var $deliverable_html = '';

    var $_salesproject = null;

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

    function list_task_agreements($task)
    {
        $ret = Array(
            0 => 'no agreement',
        );

        if (count($task->contacts) == 0)
        {
            return $ret;
        }

        $companies = Array();

        $member_qb = org_openpsa_sales_salesproject_member::new_query_builder();
        $member_qb->begin_group('OR');
        foreach ($task->contacts as $contact_id => $active)
        {
            $member_qb->add_constraint('person', '=', $contact_id);
        }
        $member_qb->end_group();

        $members = $member_qb->execute();

        $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
        $deliverable_qb->add_constraint('state', '>=', ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED);
        $deliverable_qb->add_constraint('state', '<', ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED);
        $deliverable_qb->begin_group('OR');
        foreach ($members as $member)
        {
            $deliverable_qb->add_constraint('salesproject', '=', $member->salesproject);
        }
        $deliverable_qb->end_group();
        $deliverables = $deliverable_qb->execute();

        foreach ($deliverables as $deliverable)
        {
            $salesproject = new org_openpsa_sales_salesproject($deliverable->salesproject);
            $customer = new midcom_db_group($salesproject->customer);
            $ret[$deliverable->id] = "{$deliverable->title} ({$customer->official}: {$salesproject->title})";
        }
        return $ret;
    }

    function _on_creating()
    {
        $this->calculate_price(false);
        return parent::_on_creating();
    }

    function _on_updating()
    {
        $this->calculate_price();

        if (   $this->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION
            && $this->continuous == true
            && $this->end > 0)
        {
            $this->end = 0;
        }
        elseif ($this->end < $this->start)
        {
            $this->end = $this->start + 1;
        }

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

    function _generate_html()
    {
        $this->_salesproject = new org_openpsa_sales_salesproject($this->salesproject);

        $this->deliverable_html  = "<div class=\"org_openpsa_sales_salesproject_deliverable\">\n";
        $this->deliverable_html .= "    <span class=\"title\">{$this->title}</span>\n";
        $this->deliverable_html .= "    (<span class=\"salesproject\">{$this->_salesproject->title}</span>)\n";
        $this->deliverable_html .= "</div>\n";
    }

    function get_status()
    {
        switch ($this->state)
        {
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_NEW:
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_PROPOSED:
                return 'proposed';
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED:
                return 'declined';
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED:
                return 'ordered';
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED:
                return 'started';
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED:
                return 'delivered';
            case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED:
                return 'invoiced';
        }
        return '';
    }

    function _on_loaded()
    {
        $this->_generate_html();

        return parent::_on_loaded();
    }

    /**
     * List subcomponents of this deliverable
     * @return Array
     */
    function _get_components()
    {
        $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
        $deliverable_qb->add_constraint('salesproject', '=', $this->salesproject);
        $deliverable_qb->add_constraint('up', '=', $this->id);
        $deliverables = $deliverable_qb->execute();
        return $deliverables;
    }

    function calculate_price($update = true)
    {
        $has_components = false;

        if ($this->id)
        {
            $pricePerUnit = 0;
            $costPerUnit = 0;

            // Check if we have subcomponents
            $deliverables = $this->_get_components();
            if (count($deliverables) > 0)
            {
                // If subcomponents exist, the price and cost per unit default to the
                // sum of price and cost of all subcomponents
                $has_components = true;

                foreach ($deliverables as $deliverable)
                {
                    $pricePerUnit = $pricePerUnit + $deliverable->price;
                    $costPerUnit = $costPerUnit + $deliverable->cost;
                }

                $this->pricePerUnit = $pricePerUnit;
                $this->costPerUnit = $costPerUnit;
            }
        }

        if ($has_components)
        {
            // We can't have percentage-based cost type if the agreement
            // has subcomponents
            $this->costType = 'm';
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
    function _send_invoice($sum, $description, $cycle_number = null)
    {
        if ($sum == 0)
        {
            return;
        }

        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);

        // Send invoice
        $invoice = new org_openpsa_invoices_invoice();
        $invoice->customer = $salesproject->customer;
        $invoice->invoiceNumber = org_openpsa_invoices_invoice::generate_invoice_number();
        $invoice->owner = $salesproject->owner;

        // TODO: Get from invoices configuration or make the invoice class handle due dates itself
        $invoice->due = 14 * 3600 * 24 + time();

        $invoice->description = $description;
        $invoice->sum = $sum;

        if ($invoice->create())
        {
            // TODO: Create invoicing task if assignee is defined

            // Mark the tasks (and hour reports) related to this agreement as invoiced
            $task_qb = org_openpsa_projects_task::new_query_builder();
            $task_qb->add_constraint('agreement', '=', $this->id);
            $tasks = $task_qb->execute();
            foreach ($tasks as $task)
            {
                $task->mark_invoiced($invoice);
            }

            // Register relation between the invoice and this agreement
            $relation_deliverable = org_openpsa_relatedto_handler::create_relatedto($invoice, 'org.openpsa.invoices', $this, 'org.openpsa.sales');

            // Register the cycle number for reporting purposes
            if (!is_null($cycle_number))
            {
                $invoice->parameter('org.openpsa.sales', 'cycle_number', $cycle_number);
            }
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
        if (time() < $this->start)
        {
            // Subscription hasn't started yet, register the start-up event to $start
            $args = array(
                'deliverable' => $this->guid,
                'cycle'       => $cycle_number,
            );
            $at_entry = new midcom_services_at_entry();
            $at_entry->start = $this->start;
            $at_entry->component = 'org.openpsa.sales';
            $at_entry->method = 'new_subscription_cycle';
            $at_entry->arguments = $args;

            if ($at_entry->create())
            {
                $relation = org_openpsa_relatedto_handler::create_relatedto($at_entry, 'midcom.services.at', $this, 'org.openpsa.sales');
                return true;
            }
            else
            {
                return false;
            }
        }

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
            $this->_send_invoice($this_cycle_amount, sprintf('%s %s', $this->title, $this_cycle_identifier) . "\n\n{$this->description}", $cycle_number);
        }

        $tasks_completed = array();
        $tasks_not_completed = array();
        $new_task = null;

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
                    $stat = $task->complete(sprintf($_MIDCOM->i18n->get_string('completed by subscription %s', 'org.openpsa.sales'), $this_cycle_identifier));
                    if ($stat)
                    {
                        $tasks_completed[] = $task;
                    }
                    else
                    {
                        $tasks_not_completed[] = $task;
                    }
                }

                // Create task for the duration of this cycle
                $new_task = $this->_create_task($this_cycle, $next_cycle - 1, sprintf('%s %s', $this->title, $this_cycle_identifier));
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

        if (   $this->end < $next_cycle
            && $this->end != 0)
        {
            // Do not register next cycle, the contract ends before
            return true;
        }

        // Register next cycle with midcom.services.at
        $args = array(
            'deliverable' => $this->guid,
            'cycle'       => $this_cycle_identifier + 1,
        );
        $atstat = midcom_services_at_interface::register($next_cycle, 'org.openpsa.sales', 'new_subscription_cycle', $args);
        $at_entry = new midcom_services_at_entry();
        $at_entry->start = $next_cycle;
        $at_entry->component = 'org.openpsa.sales';
        $at_entry->method = 'new_subscription_cycle';
        $at_entry->arguments = $args;

        if ($at_entry->create())
        {
            $relation = org_openpsa_relatedto_handler::create_relatedto($at_entry, 'midcom.services.at', $this, 'org.openpsa.sales');

            $this->_notify_owner($this_cycle_identifier, $next_cycle, $this_cycle_amount, $tasks_completed, $tasks_not_completed);

            return true;
        }
        else
        {
            // TODO: What to do? At registration failed
            return false;
        }
    }

    function _notify_owner($cycle_number, $next_run, $invoiced_sum, $tasks_completed, $tasks_not_completed, $new_task = null)
    {
        // Prepare notification to sales project owner
        $message = array();
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        $owner = new midcom_db_person($salesproject->owner);
        $customer = new midcom_db_group($salesproject->customer);

        if (is_null($next_run))
        {
            $next_run_label = $_MIDCOM->i18n->get_string('no more cycles', 'org.openpsa.sales');
        }
        else
        {
            $next_run_label = strftime('%x %X', $next_run);
        }

        // Title for long notifications
        $message['title'] = sprintf($_MIDCOM->i18n->get_string('subscription cycle %d closed for agreement %s (%s)', 'org.openpsa.sales'), $cycle_number, $this->title, $customer->official);

        // Content for long notifications
        $message['content']  = "{$message['title']}\n\n";

        $message['content'] .= $_MIDCOM->i18n->get_string('invoiced', 'org.openpsa.sales') . ": {$invoiced_sum}\n\n";

        if (count($tasks_completed) > 0)
        {
            $message['content'] .= "\n" . $_MIDCOM->i18n->get_string('tasks completed', 'org.openpsa.sales') . ":\n";

            foreach ($tasks_completed as $task)
            {
                $message['content'] .= "{$task->title}: {$task->hourCache}h\n";
            }
        }

        if (count($tasks_not_completed) > 0)
        {
            $message['content'] .= "\n" . $_MIDCOM->i18n->get_string('tasks not completed', 'org.openpsa.sales') . ":\n";

            foreach ($tasks_not_completed as $task)
            {
                $message['content'] .= "{$task->title}: {$task->hourCache}h\n";
            }
        }

        if ($new_task)
        {
            $message['content'] .= "\n" . $_MIDCOM->i18n->get_string('created new task', 'org.openpsa.sales') . ":\n";
            $message['content'] .= "{$task->title}\n";
        }

        $message['content'] .= "\n" . $_MIDCOM->i18n->get_string('next run', 'org.openpsa.sales') . ": {$next_run_label}\n\n";

        $message['content'] .= $_MIDCOM->i18n->get_string('salesproject', 'org.openpsa.sales') . ":\n";
        $message['content'] .= $_MIDCOM->permalinks->create_permalink($salesproject->guid);

        // Content for short notifications
        $message['abstract'] = sprintf($_MIDCOM->i18n->get_string('%s: closed subscription cycle %d for agreement %s. invoiced %d. next cycle %s', 'org.openpsa.sales'), $customer->official, $cycle_number, $this->title, $invoiced_sum, $next_run_label);

        // Send the message out
        org_openpsa_notifications::notify('org.openpsa.sales:new_subscription_cycle', $owner, $message);
    }

    function calculate_cycles($months = null)
    {
        $cycle_time = $this->start;
        $end_time = $this->end;

        if (!is_null($months))
        {
            // We calculate how many cycles fit into the number of months, figure out the end of time
            $end_time = mktime(date('H', $cycle_time), date('m', $cycle_time), date('i', $cycle_time), date('m', $cycle_time) + $months, date('d', $cycle_time), date('Y', $cycle_time));
        }

        // Calculate from begininning to the end
        $cycles = 0;
        while (   $cycle_time < $end_time
               && $cycle_time != false)
        {
            $cycle_time = $this->_calculate_cycle_next($cycle_time);

            if ($cycle_time <= $end_time)
            {
                $cycles++;
            }
        }
        return $cycles;
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

    function get_at_entries()
    {
        $at_entries = array();
        $relation_qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $relation_qb->add_constraint('toGuid', '=', $this->guid);
        $relation_qb->add_constraint('fromComponent', '=', 'midcom.services.at');
        $relation_qb->add_constraint('fromClass', '=', 'midcom_services_at_entry');
        $relations = $relation_qb->execute();
        foreach ($relations as $relation)
        {
            $at_entries[] = new midcom_services_at_entry($relation->fromGuid);
        }
        return $at_entries;
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

        // TODO: If deliverable has a supplier specified, add the supplier
        // organization members as potential resources here
        $project->resources[$salesproject->owner] = true;

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

        if ($project)
        {
            $task->up = $project->id;
        }

        // TODO: Figure out if we really want to keep this
        $task->hoursInvoiceableDefault = true;

        if ($task->create())
        {
            $task = new org_openpsa_projects_task($task->id);
            $relation_product = org_openpsa_relatedto_handler::create_relatedto($task, 'org.openpsa.projects', $product, 'org.openpsa.products');

            // Copy tags from deliverable so we can seek resources
            $tagger = new net_nemein_tag_handler();
            $tagger->copy_tags($this, $task);

            // Initiate automated resourcing seek from local OpenPsa
            $task->resource_seek_type = 'openpsa';
            $task->update();

            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'), sprintf($_MIDCOM->i18n->get_string('created task "%s"', 'org.openpsa.projects'), $task->title), 'ok');
            return $task;
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

        if ($this->update())
        {
            // Mark subcomponents as declined also
            $deliverables = $this->_get_components();
            if (count($deliverables) > 0)
            {
                foreach ($deliverables as $deliverable)
                {
                    $deliverable->decline();
                }
            }

            // Update sales project if it doesn't have any open deliverables
            $qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
            $qb->add_constraint('salesproject', '=', $this->salesproject);
            $qb->add_constraint('state', '<>', ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED);
            if ($qb->count() == 0)
            {
                // No proposals that are not declined
                $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
                $salesproject->status = ORG_OPENPSA_SALESPROJECTSTATUS_LOST;
                $salesproject->update();
            }

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

        // Cache the original price and cost values intended
        $this->plannedUnits = $this->units;
        $this->plannedCost = $this->cost;

        // Check what kind of order this is
        $product = new org_openpsa_products_product_dba($this->product);

        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            // This is a new subscription, initiate the cycle but don't send invoice
            $this->new_subscription_cycle(1, false);
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

        if ($this->update())
        {
            // Mark subcomponents as ordered also
            $deliverables = $this->_get_components();
            if (count($deliverables) > 0)
            {
                foreach ($deliverables as $deliverable)
                {
                    $deliverable->order();
                }
            }

            // Update sales project and mark as won
            $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
            if ($salesproject->status < ORG_OPENPSA_SALESPROJECTSTATUS_WON)
            {
                $salesproject->status = ORG_OPENPSA_SALESPROJECTSTATUS_WON;
                $salesproject->update();
            }

            return true;
        }

        return false;
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
            // Mark subcomponents as delivered also
            $deliverables = $this->_get_components();
            if (count($deliverables) > 0)
            {
                foreach ($deliverables as $deliverable)
                {
                    $deliverable->deliver($update_deliveries);
                }
            }

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
            // TODO: This should only raise UImessage instead of critical error
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The amount you're trying to invoice ({$sum}) exceeds the open amount of the deliverable ({$open_amount}). Please edit deliverable.");
            // This will exit.
        }

        // Generate org.openpsa.invoices invoice
        $this->_send_invoice($sum, "{$this->title}\n\n{$this->description}");

        $this->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED;
        $this->invoiced = $this->invoiced + $sum;
        return $this->update();
    }
}
?>