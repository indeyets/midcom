<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

switch ($view_data['action']['type'])
{
    case 'noaction':
        echo $view_data['l10n']->get('no action');
        break;
    case 'event':
        $datelabel = strftime('%x %X', $view_data['action']['time']);
        echo "<a href=\"{$view_data['salesproject_url']}#{$view_data['action']['obj']->guid}\" class=\"event\">{$datelabel}: {$view_data['action']['obj']->title}</a>";
        break;
    case 'task':
        $datelabel = strftime('%x', $view_data['action']['time']);
        echo "<a href=\"{$view_data['salesproject_url']}#{$view_data['action']['obj']->guid}\" class=\"task\">{$datelabel}: {$view_data['action']['obj']->title}</a>";
        break;
}
?>
