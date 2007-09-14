<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$device =& $data['device'];
$application =& $data['application'];
switch ($application->state)
{
    default:
    case ORG_MAEMO_DEVCODES_APPLICATION_PENDING:
        $state_html = "<span class='pending'>{$data['states_readable'][ORG_MAEMO_DEVCODES_APPLICATION_PENDING]}</span>"; 
        break;
    case ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED:
        $state_html = "<span class='accepted'>{$data['states_readable'][ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED]}</span>"; 
        break;
    case ORG_MAEMO_DEVCODES_APPLICATION_REJECTED:
        $state_html = "<span class='rejected'>{$data['states_readable'][ORG_MAEMO_DEVCODES_APPLICATION_REJECTED]}</span>";
        break;
}
?>
    <li>
        <a href="&(data['prefix']);application/&(application.guid);">&(device.title);</a> (&(state_html:h);)
    </li>
