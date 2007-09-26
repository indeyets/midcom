<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<li>
<?php
echo "<a href=\"manage/team/{$data['team']->groupguid}\">
    {$data['team_group']->name}</a>";
?>
<?php
echo " :: <a href=\"" . $prefix . "manage/delete/" 
. $data['team']->guid . "\">" . $data['l10n_midcom']->get('delete') . "</a>";
?>
</li>

