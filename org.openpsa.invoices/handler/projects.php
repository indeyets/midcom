<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: projects.php,v 1.1 2006/06/01 15:28:20 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * invoice projects invoicing handler
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_handler_projects extends midcom_baseclasses_components_handler
{
    function org_openpsa_invoices_handler_projects()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _generate_invoice()
    {
        $invoice = new org_openpsa_invoices_invoice();
        $invoice->customer = $_POST['org_openpsa_invoices_invoice_customer'];
        $invoice->invoiceNumber = org_openpsa_invoices_invoice::generate_invoice_number();
        $invoice->owner = $_MIDGARD['user'];
        $invoice->due = ($this->_config->get('default_due_days') * 3600 * 24) + time();
        $invoice->description = '';
        $invoice->sum = 0;
        foreach ($_POST['org_openpsa_invoices_invoice_tasks'] as $task_id => $invoiceable)
        {
            if ($invoiceable)
            {
                $task = $this->_request_data['tasks'][$task_id];
                $invoiceable_hours = 0;
                foreach ($this->_request_data['hour_reports'][$_POST['org_openpsa_invoices_invoice_customer']][$task_id] as $hour_report)
                {
                    $invoiceable_hours += $hour_report->hours;
                }
                $invoice->description .= "{$task->title}: {$invoiceable_hours}\n";
                $invoice->sum += $_POST['org_openpsa_invoices_invoice_tasks_price'][$task_id];
            }
        }

        if ($invoice->create())
        {
            // Generate "Send invoice" task
            $invoice_sender_guid = $this->_config->get('invoice_sender');
            if (!empty($invoice_sender_guid))
            {
                $invoice->generate_invoicing_task($invoice_sender_guid);
            }

            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('invoice "%s" created'), $invoice->invoiceNumber), 'ok');

            $hours_marked = 0;

            // Connect invoice to the tasks involved
            foreach ($_POST['org_openpsa_invoices_invoice_tasks'] as $task_id => $invoiceable)
            {
                if ($invoiceable)
                {
                    $relation = org_openpsa_relatedto_handler::create_relatedto($invoice, 'org.openpsa.invoices', $this->_request_data['tasks'][$task_id], 'org.openpsa.projects');

                    // Mark the hour reports invoiced
                    foreach ($this->_request_data['hour_reports'][$invoice->customer][$task_id] as $hour_report)
                    {
                        $invoice_member = new org_openpsa_invoices_invoice_hour();
                        $invoice_member->hourReport = $hour_report->id;
                        $invoice_member->invoice = $invoice->id;
                        if ($invoice_member->create())
                        {
                            $hours_marked += $hour_report->hours;
                        }
                    }
                }
            }
            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('marked %s hours as invoiced'), $hours_marked), 'ok');

            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $_MIDCOM->relocate("{$prefix}invoice/edit/{$invoice->guid}.html");
            // This will exit
        }
        else
        {
            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), $this->_request_data['l10n']->get('failed to create invoice, reason ') . mgd_errstr(), 'error');
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_uninvoiced($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_invoices_invoice');

        $this->_component_data['active_leaf'] = $this->_topic->id.':from_projects';

        $this->_request_data['hour_reports'] = array();
        $this->_request_data['tasks'] = array();
        $this->_request_data['customers'] = array();

        // List invoiceable uninvoiced hours for finished tasks
        $qb_hours = org_openpsa_projects_hour_report::new_query_builder();
        $qb_hours->add_constraint('invoiceable', '=', 1);
        $qb_hours->add_constraint('invoiced', '=', 0);
        $qb_hours->add_constraint('task.status', '>=', ORG_OPENPSA_TASKSTATUS_COMPLETED);
        $hour_reports = $qb_hours->execute();
        foreach ($hour_reports as $hour_report)
        {
            $task_id = $hour_report->task;
            if (!array_key_exists($hour_report->task, $this->_request_data['tasks']))
            {
                 $this->_request_data['tasks'][$task_id] = new org_openpsa_projects_task($task_id);
            }

            if ($this->_request_data['tasks'][$task_id]->customer)
            {
                $customer_id = $this->_request_data['tasks'][$task_id]->customer;
                if (!array_key_exists($customer_id, $this->_request_data['customers']))
                {
                     $this->_request_data['customers'][$customer_id] = new org_openpsa_contacts_group($customer_id);
                }
            }
            else
            {
                $customer_id = 'no';
            }

            if (!array_key_exists($customer_id, $this->_request_data['hour_reports']))
            {
                $this->_request_data['hour_reports'][$customer_id] = array();
            }

            if (!array_key_exists($task_id, $this->_request_data['hour_reports'][$customer_id]))
            {
                $this->_request_data['hour_reports'][$customer_id][$task_id] = array();
            }

            $this->_request_data['hour_reports'][$customer_id][$task_id][] = $hour_report;
        }

        // Check if we're sending an invoice here
        if (array_key_exists('org_openpsa_invoices_invoice', $_POST))
        {
            $this->_generate_invoice();
        }

        return true;
    }

    function _show_uninvoiced($handler_id, &$data)
    {

        // Locate Contacts node for linking
        $this->_request_data['projects_node'] = midcom_helper_find_node_by_component('org.openpsa.projects');

        midcom_show_style('show-projects-header');
        foreach ($this->_request_data['hour_reports'] as $customer => $tasks)
        {
            $this->_request_data['customer'] = $customer;
            $this->_request_data['invoice_unapproved'] = true;
            if ($customer == 'no')
            {
                $this->_request_data['customer_label'] = $this->_request_data['l10n']->get('no customer');
                $this->_request_data['disabled'] = ' disabled="disabled"';
            }
            else
            {
                $this->_request_data['customer_label'] = $this->_request_data['customers'][$customer]->official;
                $this->_request_data['disabled'] = '';
                $invoice_param = $this->_request_data['customers'][$customer]->parameter('org.openpsa.projects', 'require_hour_report_approval');
                if ($invoice_param == 1)
                {
                    $this->_request_data['invoice_unapproved'] = false;
                }
            }
            midcom_show_style('show-projects-customer-header');

            foreach ($tasks as $task => $hour_reports)
            {
                $this->_request_data['task'] = $this->_request_data['tasks'][$task];
                $this->_request_data['hour_reports'] = $hour_reports;
                $this->_request_data['invoiceable_hours'] = 0;
                $this->_request_data['approved_hours'] = 0;
                foreach ($hour_reports as $hour_report)
                {
                    if (   $this->_request_data['invoice_unapproved']
                        || $hour_report->approved)
                    {
                        $this->_request_data['invoiceable_hours'] += $hour_report->hours;
                    }
                }

                if ($this->_config->get('default_hourly_price'))
                {
                    $this->_request_data['default_price'] = $this->_request_data['invoiceable_hours'] * $this->_config->get('default_hourly_price');
                }
                else
                {
                    $this->_request_data['default_price'] = '';
                }
                midcom_show_style('show-projects-customer-task');
            }
            midcom_show_style('show-projects-customer-footer');
        }
        midcom_show_style('show-projects-footer');
    }
}

?>