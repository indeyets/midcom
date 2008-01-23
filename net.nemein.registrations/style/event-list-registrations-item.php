<?php
// Available request keys:
// event, view_url, edit_url, delete_url
// registrations, registration, registration_url, approved

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$registration =& $data['registration'];
$registrar =& $data['registrar'];
if ($data['approved'])
{
    $date = strftime("%x %X", $registration->get_parameter('net.nemein.registrations', 'approved'));
    $user =& $_MIDCOM->auth->get_user($registration->get_parameter('net.nemein.registrations', 'approver'));
    if ($user)
    {
        $approved_text = sprintf($data['l10n']->get('approved by %s on %s'), $user->name, $date);
    }
    else
    {
        $approved_text = sprintf($data['l10n']->get('approved on %s'), $date);
    }
}
else
{
    $approved_text = $data['l10n_midcom']->get('unapproved');
}
?>
<li>
    <a href="&(data['registration_url']);">&(registrar.name);</a>, &(approved_text);
</li>