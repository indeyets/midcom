<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
//If we need to do some mangling we do it here
$group =& $view_data['current_group'];
$group['weekly_report_data'] = array();
$weekly_data_group =& $group['weekly_report_data'];
$weekly_data_group['invoiceable_total'] = 0;
$weekly_data_group['uninvoiceable_total'] = 0;
$weekly_data_group['invoiceable_customers'] = array(); // Keyed by group id
$weekly_data_group['uninvoiceable_customers'] = array(); // Keyed by group id
$weekly_data_group['invoiceable_total_by_customer'] = array(); // Keyed by group id
$weekly_data_group['uninvoiceable_total_by_customer'] = array(); // Keyed by group id

?>
