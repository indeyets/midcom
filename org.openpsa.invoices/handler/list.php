<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.3 2006/06/14 15:01:43 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * invoice list handler
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_handler_list extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
      // Locate Contacts node for linking
      $this->_request_data['contacts_node'] = midcom_helper_find_node_by_component('org.openpsa.contacts');
    }

    function _process_invoice_list($invoices)
    {
        $this->_request_data['invoices'] = Array();
        $this->_request_data['totals']['overdue'] = 0;
        $this->_request_data['totals']['totals'] = 0;

        foreach ($invoices as $invoice)
        {
            $this->_request_data['totals']['totals'] += $invoice->sum;

            if (   $invoice->paid == 0
                && $invoice->due < time())
            {
                $this->_request_data['totals']['overdue'] += $invoice->sum;
            }

            $this->_request_data['invoices'][] = $invoice;
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_dashboard($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'invoice/new/',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create invoice'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_invoices_invoice'),
            )
        );

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_dashboard($handler_id, &$data)
    {
        midcom_show_style('show-dashboard-header');
        $this->_request_data['header-size'] = 2;

        $this->_show_unsent($handler_id, $data);
        $this->_show_overdue($handler_id, $data);
        $this->_show_open($handler_id, $data);
        $this->_show_recent($handler_id, $data);
        midcom_show_style('show-dashboard-footer');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_unsent($handler_id, &$data)
    {
        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_constraint('sent', '=', 0);
        $qb->add_order('due');
        $qb->add_order('invoiceNumber');
        $invoices = $qb->execute();
        $this->_process_invoice_list($invoices);

        $this->_request_data['list_label'] = $this->_request_data['l10n']->get('unsent invoices');
        $this->_request_data['list_type'] = 'unsent';
        $this->_request_data['next_marker'] = 'sent';

        $this->_show_invoice_list();
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_overdue($handler_id, &$data)
    {
        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_constraint('sent', '>', 0);
        $qb->add_constraint('paid', '=', 0);
        $qb->add_constraint('due', '<=', time());
        $qb->add_order('due');
        $qb->add_order('invoiceNumber');
        $invoices = $qb->execute();
        $this->_process_invoice_list($invoices);

        $this->_request_data['list_label'] = $this->_request_data['l10n']->get('overdue invoices');
        $this->_request_data['list_type'] = 'overdue';
        $this->_request_data['next_marker'] = 'paid';

        $this->_show_invoice_list();
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_open($handler_id, &$data)
    {
        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_constraint('sent', '>', 0);
        $qb->add_constraint('paid', '=', 0);
        $qb->add_constraint('due', '>', time());
        $qb->add_order('due');
        $qb->add_order('invoiceNumber');
        $invoices = $qb->execute();
        $this->_process_invoice_list($invoices);

        $this->_request_data['list_label'] = $this->_request_data['l10n']->get('open invoices');
        $this->_request_data['list_type'] = 'open';
        $this->_request_data['next_marker'] = 'paid';

        $this->_show_invoice_list();
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_recent($handler_id, &$data)
    {
        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_constraint('paid', '>', 0);
        $qb->add_order('paid', 'DESC');
        $qb->set_limit(6);
        $invoices = $qb->execute_unchecked();
        $this->_process_invoice_list($invoices);

        $this->_request_data['list_label'] = $this->_request_data['l10n']->get('recently paid invoices');
        $this->_request_data['list_type'] = 'paid';

        $this->_show_invoice_list();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_customer($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (count($args) != 1)
        {
            return false;
        }

        // We're creating invoice for chosen company
        $this->_request_data['customer'] = new org_openpsa_contacts_group($args[0]);
        if (!$this->_request_data['customer'])
        {
            return false;
        }

        $this->_request_data['invoices'] = Array();
        $this->_request_data['totals'] = Array();
        $this->_request_data['totals']['overdue'] = 0;
        $this->_request_data['totals']['totals'] = 0;

        $this->_node_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "invoice/new/{$this->_request_data['customer']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create invoice'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_invoices_invoice'),
            )
        );

        $qb = org_openpsa_invoices_invoice::new_query_builder();
        $qb->add_constraint('customer', '=', $this->_request_data['customer']->id);

        if ($handler_id == 'list_customer_open')
        {
            $qb->add_constraint('paid', '=', 0);
            $qb->add_order('due');
            $this->_request_data['list_label'] = sprintf($this->_request_data['l10n']->get('open invoices for customer %s'), $this->_request_data['customer']->official);

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "list/customer/all/{$this->_request_data['customer']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('all invoices'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
            $this->_request_data['list_type'] = 'open';
        }
        else
        {
            $qb->add_order('sent');
            $this->_request_data['list_label'] = sprintf($this->_request_data['l10n']->get('all invoices for customer %s'), $this->_request_data['customer']->official);

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "list/customer/{$this->_request_data['customer']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('open invoices'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
            $this->_request_data['list_type'] = 'all';
        }

        $invoices = $qb->execute();
        $this->_process_invoice_list($invoices);

        $_MIDCOM->set_pagetitle($this->_request_data['list_label']);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_customer($handler_id, &$data)
    {
        $this->_show_invoice_list();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_deliverable($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (count($args) != 1)
        {
            return false;
        }

        // We're displaying invoices of a specific deliverable
        $this->_request_data['deliverable'] = new org_openpsa_sales_salesproject_deliverable($args[0]);
        if (!$this->_request_data['deliverable'])
        {
            return false;
        }
        $this->_request_data['salesproject'] = new org_openpsa_sales_salesproject($this->_request_data['deliverable']->salesproject);

        $this->_request_data['list_label'] = sprintf($this->_request_data['l10n']->get('all invoices for deliverable %s in sales project %s'), $this->_request_data['deliverable']->title, $this->_request_data['salesproject']->title);

        $this->_request_data['invoices'] = Array();
        $this->_request_data['totals'] = Array();
        $this->_request_data['totals']['overdue'] = 0;
        $this->_request_data['totals']['totals'] = 0;
        $this->_request_data['list_type'] = 'all';

        $relation_qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $relation_qb->add_constraint('toGuid', '=', $this->_request_data['deliverable']->guid);
        $relation_qb->add_constraint('fromComponent', '=', 'org.openpsa.invoices');
        $relation_qb->add_constraint('fromClass', '=', 'org_openpsa_invoices_invoice');
        $relations = $relation_qb->execute();
        $invoices = Array();
        foreach ($relations as $relation)
        {
            $invoice = new org_openpsa_invoices_invoice($relation->fromGuid);
            if ($invoice)
            {
                $invoices[] = $invoice;
            }
        }

        $this->_process_invoice_list($invoices);

        $_MIDCOM->set_pagetitle($this->_request_data['list_label']);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_deliverable($handler_id, &$data)
    {
        $this->_request_data['header-size'] = 4;
        $this->_show_invoice_list();
    }

    function _show_invoice_list()
    {
        if (count($this->_request_data['invoices']) > 0)
        {
            midcom_show_style('show-list-header');
            $this->_request_data['even'] = false;
            foreach ($this->_request_data['invoices'] as $invoice)
            {
                $this->_request_data['invoice'] =& $invoice;
                $this->_request_data['customer'] = new org_openpsa_contacts_group($invoice->customer);
                midcom_show_style('show-list-item');

                if ($this->_request_data['even'])
                {
                    $this->_request_data['even'] = false;
                }
                else
                {
                    $this->_request_data['even'] = true;
                }
            }
            midcom_show_style('show-list-footer');
        }
    }


}

?>