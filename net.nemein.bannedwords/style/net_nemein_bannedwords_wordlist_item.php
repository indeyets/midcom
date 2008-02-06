<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$banned = $data['banned_object'];

echo "<li>{$banned->bannedWord} ({$banned->description})";
echo "<a href=\"net.nemein.bannedwords/edit/{$banned->guid}.html\" title=\"{$data['l10n']->get('edit')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/edit.png\" alt=\"edit\"/></a> ";
echo "<a href=\"net.nemein.bannedwords/confirmdelete/{$banned->guid}.html\" title=\"{$data['l10n']->get('delete')}\">
    <img style=\"border: 0px;\" src=\"/midcom-static/stock-icons/16x16/trash.png\" alt=\"delete\"/></a></li>";
?>