<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$featured = $data['featured_item'];

echo "<li>{$featured->metadata->score} ";
echo "<a href=\"move_up/{$featured->guid}.html\" title=\"{$data['l10n']->get('move up')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/stock_up.png\" alt=\"up\"/></a>";
echo "<a href=\"move_down/{$featured->guid}.html\" title=\"{$data['l10n']->get('move down')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/stock_down.png\" alt=\"down\"/></a>";
echo $featured->objectLocation . " ";
echo "<a href=\"edit/{$featured->guid}.html\" title=\"{$data['l10n']->get('edit')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/edit.png\" alt=\"edit\"/></a> ";
echo "<a href=\"delete/{$featured->guid}.html\" title=\"{$data['l10n']->get('delete')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/trash.png\" alt=\"delete\"/></a></li>";

?>
