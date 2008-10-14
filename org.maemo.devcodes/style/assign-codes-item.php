<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$application =& $data['application'];
$applicant =& org_openpsa_contacts_person_dba::get_cached($application->applicant);
?>
            <tr>
                <td>
                    <select name="org_maemo_devcodes_assign_action[&(application.guid);]">
                        
<?php
if (org_maemo_devcodes_application_dba::can_apply($application->device, $application->applicant, false))
{
    foreach ($data['actions'] as $action => $title)
    {
        $sel = '';
        if (   $action === 'assign'
            && $application->state === ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED)
        {
            $sel = ' selected';
        }
        echo "                        <option value='{$action}'{$sel}>{$title}</option>\n";
    }
}
else
{
    // can_apply status revoked since application creation, only real option left is to reject...
    echo "                        <option value='noop'>{$data['actions']['noop']}</option>\n";
    echo "                        <option value='reject' selected>{$data['actions']['reject']}</option>\n";
}
?>                        
                    </select>
                </td>
                <td><a target="_blank" href="&(data['prefix']);application/&(application.guid);.html">&(application.summary);</a></td>
                <td><?php echo $data['states_readable'][$application->state]; ?></td>
                <td>&(applicant.name);</td>
<?php
if ($data['display_country'])
{
    if (empty($applicant->country))
    {
        $country = $data['l10n']->get('not set');
    }
    else
    {
        $country = $applicant->country;
    }
    echo "                <td>{$country}</td>\n";
}
?>
            </tr>
