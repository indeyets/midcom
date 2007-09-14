<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$application =& $data['application'];
if (!empty($application->applicant))
{
    $applicant =& org_openpsa_contacts_person::get_cached($application->applicant);
}
?>
        <tr>
            <td><!-- TODO: Link to profile -->&(applicant.name);</td>
            <td><a href="&(data['prefix']);application/&(application.guid);.html">&(application.summary);</td>
            <td><?php echo $data['states_readable'][$application->state]; ?></td>
        </tr>
