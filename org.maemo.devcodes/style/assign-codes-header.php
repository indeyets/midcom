<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$device =& $data['device'];
?>
<h2>&(data['title']);</h2>
<form method="post" action="&(data['prefix']);code/assign/process/">
<input type="hidden" name="org_maemo_devcodes_assign_device" value="&(device.guid);" />
<input type="hidden" name="org_maemo_devcodes_assign_area" value="&(data['area']);" />
    <table>
        <thead>
            <tr>
                <th><?php echo $data['l10n']->get('operation'); ?></th>
                <th><?php echo $data['l10n']->get('summary'); ?></th>
                <th><?php echo $data['l10n']->get('state'); ?></th>
                <th><?php echo $data['l10n']->get('applicant'); ?></th>
<?php
if ($data['display_country'])
{
    echo '            <th>' . $data['l10n_midcom']->get('country') . "</th>\n";
}
?>
            </tr>
        </thead>
        <tbody>
