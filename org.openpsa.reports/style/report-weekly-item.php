<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$hour =& $view_data['current_row']['hour'];
$task =& $view_data['current_row']['task'];
$report =& $view_data['report'];
$person =& $view_data['current_row']['person'];
$query_data =& $view_data['query_data'];
$group =& $view_data['current_group'];
$weekly_data =& $report['raw_results']['weekly_report_data'];
$weekly_data_group =& $group['weekly_report_data'];

$view_data['current_row']['customer'] = false;
//echo "DEBUG: task->customer: {$task->customer}<br>\n";
if ($task->customer)
{
    $view_data['current_row']['customer'] =& org_openpsa_reports_projects_handler::_get_cache('groups', $task->customer, $view_data);
    // echo "DEBUG: view_data['current_row']['customer'] <pre>\n" . sprint_r($view_data['current_row']['customer']) . "</pre><br>\n";
}

if ($hour->invoiceable)
{
    $total =& $weekly_data['invoiceable_total'];
    $customers =& $weekly_data['invoiceable_customers'];
    $total_by_customer =& $weekly_data['invoiceable_total_by_customer'];
    $group_total =& $weekly_data_group['invoiceable_total'];
    $group_customers =& $weekly_data_group['invoiceable_customers'];
    $group_total_by_customer =& $weekly_data_group['invoiceable_total_by_customer'];
}
else
{
    $total =& $weekly_data['uninvoiceable_total'];
    $customers =& $weekly_data['uninvoiceable_customers'];
    $total_by_customer =& $weekly_data['uninvoiceable_total_by_customer'];
    $group_total =& $weekly_data_group['uninvoiceable_total'];
    $group_customers =& $weekly_data_group['uninvoiceable_customers'];
    $group_total_by_customer =& $weekly_data_group['uninvoiceable_total_by_customer'];
}

$total += $hour->hours;
$group_total += $hour->hours;

if ($view_data['current_row']['customer'])
{
    $customers[$task->customer] =& $view_data['current_row']['customer'];
    $group_customers[$task->customer] =& $view_data['current_row']['customer'];
    
    if (!array_key_exists($task->customer, $total_by_customer))
    {
        $total_by_customer[$task->customer] = 0;
    }
    $total_by_customer[$task->customer] += $hour->hours;
    
    if (!array_key_exists($task->customer, $group_total_by_customer))
    {
        $group_total_by_customer[$task->customer] = 0;
    }
    $group_total_by_customer[$task->customer] += $hour->hours;
}

?>
