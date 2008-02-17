<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
//If we need to do any specific mangling to the report data etc, we do it here.
$query =& $data['query_data'];
$report =& $data['report'];
if (!is_array($report))
{
    $report = array();
}
if (   !array_key_exists('title', $report)
    || empty($report['title']))
{
    $report['title'] = sprintf($data['l10n']->get('invoice report %s - %s'), strftime('%x', $data['start']), strftime('%x', $data['end']));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
    <head>
        <title>OpenPsa - &(report['title']);</title>
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/common.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.invoices/invoices.css" />
        <script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/table2csv.js"></script>
    </head>
    <body>