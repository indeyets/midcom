<?php
// Available request keys:
// events, event, view_url

$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
?>

<li><a href="&(data['view_url']);">&(event.title);</a> <?php
if ($event->is_open())
{
    echo '(' . $data['l10n']->get('open') . ')';
}
else
{
    echo '(' . $data['l10n']->get('closed') . ')';
}
?></li>