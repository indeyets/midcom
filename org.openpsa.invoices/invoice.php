<?php
/**
 * @package org.openpsa.invoices
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: invoice.php,v 1.3 2006/06/13 10:50:52 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Midcom wrapped base class, keep logic here
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_invoice extends __org_openpsa_invoices_invoice
{
    function org_openpsa_invoices_invoice($id = null)
    {
        return parent::__org_openpsa_invoices_invoice($id);
    }

    function get_invoice_class()
    {
        if ($this->sent == 0)
        {
            return 'unsent';
        }
        elseif ($this->paid > 0)
        {
            return 'paid';
        }
        elseif ($this->due < time())
        {
            return 'overdue';
        }
        return 'open';
    }

    function generate_invoice_number()
    {
        // TODO: Make configurable
        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_order('metadata.created', 'DESC');
        $qb->set_limit(1);
        $last_invoice = $qb->execute_unchecked();
        if (count($last_invoice) == 0)
        {
            $previous = 0;
        }
        else
        {
            $previous = $last_invoice[0]->id;
        }

        return sprintf('#%06d', $previous + 1);
    }

    /**
     * Generate "Send invoice" task
     */
    function generate_invoicing_task($invoicer)
    {
        $invoice_sender = new midcom_db_person($invoicer);
        if ($invoice_sender)
        {
            $task = new org_openpsa_projects_task();
            $task->resources[$invoice_sender->id] = true;
            $task->manager = $_MIDGARD['user'];
            // TODO: Connect the customer as the contact?
            $task->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_TASK;
            $task->title = sprintf($_MIDCOM->i18n->get_string('send invoice %s', 'org.openpsa.invoices'), $this->invoiceNumber);
            // TODO: Store link to invoice into description
            $task->end = time() + 24 * 3600;
            if ($task->create())
            {
                $relation = org_openpsa_relatedto_handler::create_relatedto($task, 'org.openpsa.projects', $this, 'org.openpsa.invoices');
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.openpsa.invoices', 'org.openpsa.invoices'), sprintf($_MIDCOM->i18n->get_string('created "%s" task to %s', 'org.openpsa.invoices'), $task->title, $invoice_sender->name), 'ok');
            }
        }
    }
}
?>