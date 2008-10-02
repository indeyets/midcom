<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: edit.php,v 1.3 2006/06/01 15:28:20 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Invoice edit/view handler
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_handler_edit extends midcom_baseclasses_components_handler
{
    var $_datamanager = null;

    function __construct()
    {
        parent::__construct();
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    function _initialize_datamanager($schemadb_snippet)
    {
        // Initialize the datamanager with the schema
        $this->_datamanager = new midcom_helper_datamanager($schemadb_snippet);

        if (!$this->_datamanager) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
    }

    function _populate_schema_contacts_for_customer($customer)
    {
        // We know the customer company, present contact as a select widget
        $persons_array = array();
        $member_qb = midcom_db_member::new_query_builder();
        $member_qb->add_constraint('gid', '=', $customer->id);
        $members = $member_qb->execute();
        foreach ($members as $member)
        {
            $person = new midcom_db_person($member->uid);
            $persons_array[$person->id] = $person->rname;
        }
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'customerContact', 'widget', 'select', 'default', false);
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'customerContact', 'widget_select_choices', $persons_array, 'default', false);

        // And display the organization too
        $organization_array = Array();
        $organization_array[$customer->id] = $customer->official;
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'customer', 'widget', 'select', 'default', false);
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'customer', 'widget_select_choices', $organization_array, 'default', false);
    }

    function _modify_schema()
    {
        debug_add("schema before \n===\n" . sprint_r($this->_datamanager->_layoutdb['default']) . "===\n");

        // Set default due date
        $due_date = ($this->_config->get('default_due_days') * 3600 * 24) + time();
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'due', 'default', $due_date, 'default', false);

        // Generate invoice number
        // TODO: Check that a default hasn't already been set
        org_openpsa_helpers_schema_modifier($this->_datamanager, 'invoiceNumber', 'default', org_openpsa_invoices_invoice::generate_invoice_number(), 'default', false);

        // Make VAT field a select
        $vat_array = explode(',', $this->_config->get('vat_percentages'));
        if (   is_array($vat_array)
            && count($vat_array) > 0)
        {
            $vat_values = array();
            foreach ($vat_array as $vat)
            {
                $vat_values[$vat] = "{$vat}%";
            }
            org_openpsa_helpers_schema_modifier($this->_datamanager, 'vat', 'widget', 'select', 'default', false);
            org_openpsa_helpers_schema_modifier($this->_datamanager, 'vat', 'widget_select_choices', $vat_values, 'default', false);
        }

        if (array_key_exists('invoice', $this->_request_data))
        {
            if ($this->_request_data['invoice']->customerContact)
            {
                // List customer contact's groups
                $organizations = array();
                $member_qb = midcom_db_member::new_query_builder();
                $member_qb->add_constraint('uid', '=', $this->_request_data['invoice']->customerContact);
                $memberships = $member_qb->execute();
                foreach ($memberships as $member)
                {
                    $organization = new org_openpsa_contacts_group($member->gid);
                    $organizations[$organization->id] = $organization->official;
                }

                //Fill the customer field to DM
                org_openpsa_helpers_schema_modifier($this->_datamanager, 'customer', 'widget', 'select', 'default', false);
                org_openpsa_helpers_schema_modifier($this->_datamanager, 'customer', 'widget_select_choices', $organizations, 'default', false);
            }
            elseif ($this->_request_data['invoice']->customer)
            {
                $customer = new org_openpsa_contacts_group($this->_request_data['invoice']->customer);
                $this->_populate_schema_contacts_for_customer($customer);
            }

            if ($this->_request_data['invoice']->sent)
            {
                org_openpsa_helpers_schema_modifier($this->_datamanager, 'sent', 'hidden', false, 'default', false);
            }

            if ($this->_request_data['invoice']->paid)
            {
                org_openpsa_helpers_schema_modifier($this->_datamanager, 'paid', 'hidden', false, 'default', false);
            }
        }
        else
        {
            if (array_key_exists('customer', $this->_request_data))
            {
                $this->_populate_schema_contacts_for_customer($this->_request_data['customer']);
            }
            else
            {
                // We don't know company, present customer contact as contactchooser and hide customer field
                org_openpsa_helpers_schema_modifier($this->_datamanager, 'customer', 'hidden', true, 'default', false);
            }
        }
        debug_add("schema after \n===\n" . sprint_r($this->_datamanager->_layoutdb['default']) . "===\n");
    }

    function _load_invoice($identifier)
    {
        if (!isset($this->_datamanager))
        {
            $this->_initialize_datamanager($this->_config->get('schemadb'));
        }

        $this->_request_data['invoice'] = new org_openpsa_invoices_invoice($identifier);

        if (!is_object($this->_request_data['invoice']))
        {
            return false;
        }

        $this->_modify_schema();

        // Load the project to datamanager
        if (!$this->_datamanager->init($this->_request_data['invoice']))
        {
            return false;
        }

        return $this->_request_data['invoice'];
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            'success' => false,
            'storage' => null,
        );
        $invoice = new org_openpsa_invoices_invoice();
        $stat = $invoice->create();
        if ($stat)
        {
            $this->_request_data['invoice'] = new org_openpsa_invoices_invoice($invoice->id);
            $result['storage'] =& $this->_request_data['invoice'];
            $result['success'] = true;
            return $result;
        }
        return null;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_mark_sent($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }

        $_MIDCOM->auth->require_valid_user();

        $this->_request_data['invoice'] = $this->_load_invoice($args[0]);
        if (!$this->_request_data['invoice'])
        {
            return false;
        }

        $this->_request_data['invoice']->require_do('midgard:update');

        if (!$this->_request_data['invoice']->sent)
        {
            $this->_request_data['invoice']->sent = time();
            $this->_request_data['invoice']->update();

            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('marked invoice "%s" sent'), $this->_request_data['invoice']->invoiceNumber), 'ok');

            // Close "Send invoice" task
            $qb = org_openpsa_relatedto_relatedto::new_query_builder();
            $qb->add_constraint('toGuid', '=', $this->_request_data['invoice']->guid);
            $qb->add_constraint('fromComponent', '=', 'org.openpsa.projects');
            $qb->add_constraint('fromClass', '=', 'org_openpsa_projects_task');
            $qb->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
            $links = $qb->execute();
            foreach ($links as $link)
            {
                $task = new org_openpsa_projects_task($link->fromGuid);
                if ($task)
                {
                    if ($task->complete())
                    {
                        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('marked task "%s" finished'), $task->title), 'ok');
                    }
                }
            }

        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->relocate("{$prefix}invoice/{$this->_request_data['invoice']->guid}.html");
        // This will exit
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_mark_paid($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }

        $_MIDCOM->auth->require_valid_user();

        $this->_request_data['invoice'] = $this->_load_invoice($args[0]);
        if (!$this->_request_data['invoice'])
        {
            return false;
        }

        $this->_request_data['invoice']->require_do('midgard:update');

        if (!$this->_request_data['invoice']->paid)
        {
            $this->_request_data['invoice']->paid = time();
            $this->_request_data['invoice']->update();

            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('marked invoice "%s" paid'), $this->_request_data['invoice']->invoiceNumber), 'ok');
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->relocate("{$prefix}invoice/{$this->_request_data['invoice']->guid}.html");
        // This will exit
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['invoice'] = $this->_load_invoice($args[0]);
        if (!$this->_request_data['invoice'])
        {
            return false;
        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('invoice') . ' ' . $this->_request_data['invoice']->invoiceNumber);
        $this->_update_breadcrumb_line();

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "invoice/edit/{$this->_request_data['invoice']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['invoice']),
            )
        );

        if (!$this->_request_data['invoice']->sent)
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "invoice/mark_sent/{$this->_request_data['invoice']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('mark sent'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-send.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['invoice']),
                )
            );
        }
        elseif (!$this->_request_data['invoice']->paid)
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "invoice/mark_paid/{$this->_request_data['invoice']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('mark paid'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['invoice']),
                )
            );
        }

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "invoice/delete/{$this->_request_data['invoice']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:delete', $this->_request_data['invoice']),
            )
        );

        $this->_view_toolbar->bind_to($data['invoice']);

        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/midcom.helper.datamanager/columned.css",
            )
        );
        return true;
    }

    function _count_invoice_hours()
    {
        $qb = org_openpsa_invoices_invoice_hour::new_query_builder();
        $qb->add_constraint('invoice', '=', $this->_request_data['invoice']->id);
        $hour_links = $qb->execute();
        if (!is_array($hour_links))
        {
            return false;
        }
        $_MIDCOM->componentloader->load('org.openpsa.projects');

        $this->_request_data['sorted_reports'] = array
        (
            'reports' => array(),
            'approved' => array
            (
                'hours' => 0,
                'reports' => array(),
            ),
            'not_approved' => array
            (
                'hours' => 0,
                'reports' => array(),
            ),
            'tasks' => array(),
            // TODO other sorts ?
        );
        foreach ($hour_links as $link)
        {
            $report = new org_openpsa_projects_hour_report($link->hourReport);
            if (!$report)
            {
                // Could not fetch report for some reason
                continue;
            }
            $this->_request_data['sorted_reports']['reports'][$report->guid] = $report;
            switch (true)
            {
                case ($report->is_approved):
                    $sort =& $this->_request_data['sorted_reports']['approved'];
                    break;
                case (!$report->is_approved):
                    $sort =& $this->_request_data['sorted_reports']['not_approved'];
                    break;
                default:
                    // Could not figure correct sort key (switch is also a for statement so must continue two levels
                    continue 2;
                    break;
            }
            if (!array_key_exists($report->task, $this->_request_data['sorted_reports']['tasks']))
            {
                $this->_request_data['sorted_reports']['tasks'][$report->task] = array
                (
                    'hours' => 0,
                    'reports' => array(),
                );
            }
            $sort2 =& $this->_request_data['sorted_reports']['tasks'][$report->task];
            $sort['hours'] += $report->hours;
            $sort2['hours'] += $report->hours;
            // PHP5-TODO: Must be copy-by-value
            $sort['reports'][] =& $this->_request_data['sorted_reports']['reports'][$report->guid];
            $sort2['reports'][] =& $this->_request_data['sorted_reports']['reports'][$report->guid];
        }
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        $data['invoice_dm']  = $this->_datamanager;
        $this->_count_invoice_hours();
        /*
        // List hour reports invoiced in this invoice
        $data['invoice_hours'] = array();
        $hours_qb = org_openpsa_invoices_invoice_hour::new_query_builder();
        $hours_qb->add_constraint('invoice', '=', $data['invoice']->id);
        //TODO: 1.8 $hours_qb->add_order('hourReport.task');
        //TODO: 1.8 $hours_qb->add_order('hourReport.date');
        $hours = $hours_qb->execute();
        foreach ($hours as $invoice_hour)
        {
            $hour_report = new org_openpsa_projects_hour_report($invoice_hour->hourReport);
            if (!array_key_exists($hour_report->task, $data['invoice_hours']))
            {
                $data['invoice_hours'][$hour_report->task] = array();
            }
            $data['invoice_hours'][$hour_report->task][] = $hour_report;
        }
        */

        midcom_show_style('show-invoice');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['invoice'] = $this->_load_invoice($args[0]);
        $this->_request_data['invoice']->require_do('midgard:update');

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('invoice') . ' ' . $this->_request_data['invoice']->invoiceNumber);
        $this->_update_breadcrumb_line();

        switch ($this->_datamanager->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                return true;
                // This will break;

            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "invoice/" . $this->_request_data['invoice']->guid);
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
                // This will break;
        }

        $this->_view_toolbar->bind_to($this->_request_data['invoice']);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['invoice_dm']  = $this->_datamanager;
        midcom_show_style('show-invoice-edit');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_invoices_invoice');

        if (   $handler_id == 'invoice_new'
            && count($args) == 1)
        {
            // We're creating invoice for chosen company
            $this->_request_data['customer'] = new org_openpsa_contacts_group($args[0]);
            if (!$this->_request_data['customer'])
            {
                return false;
            }
        }

        if (!isset($this->_datamanager))
        {
            $this->_initialize_datamanager($this->_config->get('schemadb'));
        }

        $this->_modify_schema();

        if (!$this->_datamanager->init_creation_mode('default', $this, '_creation_dm_callback'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'default'.");
            // This will exit
        }

        switch ($this->_datamanager->process_form())
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");

                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.invoices'), sprintf($this->_request_data['l10n']->get('invoice %s created'), $this->_request_data['invoice']->invoiceNumber), 'ok');

                // Generate "Send invoice" task
                $invoice_sender_guid = $this->_config->get('invoice_sender');
                if (!empty($invoice_sender_guid))
                {
                    $this->_request_data['invoice']->generate_invoicing_task($invoice_sender_guid);
                }

                // Relocate to main view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}invoice/edit/{$this->_request_data['invoice']->guid}.html");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('create invoice'));
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_request_data['l10n']->get('create invoice'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['invoice_dm']  = $this->_datamanager;
        midcom_show_style('show-invoice-new');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_request_data['l10n']->get('invoice') . ' ' . $this->_request_data['invoice']->invoiceNumber,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

}

?>