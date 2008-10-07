<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['view_hour_report'];
echo "<tr>\n";
echo "    <td><a href=\"{$prefix}hours/edit/{$data['hour_report']->guid}/\">" . strftime('%x', $data['hour_report']->date) . "</a></td>\n";
echo "    <td>{$view['hours']}</td>\n";
echo "    <td>{$view['invoiceable']}</td>\n";
echo "    <td>{$view['person']}</td>\n";
if ($data['mode'] != 'simple')
{
    echo "    <td>{$view['task']}</td>\n";
}
echo "    <td>{$view['description']}</td>\n";
echo "</tr>\n";
?>