<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['title']);</h2>
<?php
if (empty($data['applications']))
{
    midcom_show_style('list-my-applications-noresults');
}
else
{
    midcom_show_style('list-my-applications-header');
    foreach ($data['applications'] as $application)
    {
        $data['device'] =& org_maemo_devcodes_device_dba::get_cached($application->device);
        $data['application'] =& $application;
        midcom_show_style('list-my-applications-item');
        unset($data['device'], $data['application']);
    }
    midcom_show_style('list-my-applications-footer');
}

if (empty($data['applicable']))
{
    midcom_show_style('list-my-applicable-noresults');
}
else
{
    midcom_show_style('list-my-applicable-header');
    foreach ($data['applicable'] as $device)
    {
        $data['device'] =& $device;
        midcom_show_style('list-my-applicable-item');
        unset($data['device']);
    }
    midcom_show_style('list-my-applicable-footer');
}

?>
