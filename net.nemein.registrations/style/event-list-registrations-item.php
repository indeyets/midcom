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
            <tr>
                <td><a href="&(data['registration_url']);">&(registrar.name);</a></td>
                <td><?php echo strftime("%x %X", $registration->metadata->created); ?></td>
<?php
$event_dm =& $data['event_dm'];
if (   !isset($event_dm->types['auto_approve'])
    || !$event_dm->types['auto_approve']->value)
{
    /* approved checkbox */
    echo "                <td><input type='checkbox' name='net_nemein_registrations_process[approved][{$registration->guid}]' value=1 ";
    if ($data['approved'])
    {
        echo "checked=\"checked\" title='{$approved_text}' disabled=\"disabled\"";
    }
    echo "/></td>\n";
    /* /approved checkbox */
}
if ($data['config']->get('pricing_plugin'))
{
    echo "                <td>{$registration->reference}</td>\n";
    echo sprintf("                <td>%0.2f&euro;</td>\n", $registration->price);
    /* paid checkbox */
    echo "            <td><input type='checkbox' name='net_nemein_registrations_process[paid][{$registration->guid}]' value=1";
    if ($registration->is_paid())
    {
        echo  ' checked="checked" disabled="disabled"';
    }
    echo "/></td>\n";
    /* /paid checkbox */
}
?>
            </tr>
