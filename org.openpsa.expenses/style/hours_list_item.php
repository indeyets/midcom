<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$report = $data['hour_report'];
$reporters =& $data['reporters'];
$invoiceable = ($report->invoiceable) ? 'stock_mark' : 'cancel';

echo "<tr class=\"{$data['class']}\">\n";
echo "    <td><a href=\"{$prefix}hours/edit/{$report->guid}/\">" . strftime('%x', $report->date) . "</a></td>\n";
echo "    <td class=\"numeric\">{$report->hours}</td>\n";
echo "    <td><img alt=\"{$invoiceable}\" src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/{$invoiceable}.png\"/></td>\n";
echo "    <td>{$reporters[$report->person]}</td>\n";
if ($data['mode'] != 'simple')
{
    echo "    <td>{$tasks[$report->task]}</td>\n";
}
echo "    <td>{$report->description}</td>\n";
echo "</tr>\n";
?>