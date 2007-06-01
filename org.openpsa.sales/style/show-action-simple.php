<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

switch ($data['action']['type'])
{
    case 'noaction':
        echo $data['l10n']->get('no action');
        break;
    case 'event':
        $datelabel = strftime('%x %X', $data['action']['time']);
        echo "<a href=\"{$data['salesproject_url']}#{$data['action']['obj']->guid}\" class=\"event\">{$datelabel}: {$data['action']['obj']->title}</a>";
        break;
    case 'task':
        $datelabel = strftime('%x', $data['action']['time']);
        echo "<a href=\"{$data['salesproject_url']}#{$data['action']['obj']->guid}\" class=\"task\">{$datelabel}: {$data['action']['obj']->title}</a>";
        break;
}
?>