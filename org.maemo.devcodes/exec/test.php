<?php
$_MIDCOM->auth->require_valid_user();

/*
echo "\$_MIDGARD<pre>\n";
print_r($_MIDGARD);
echo "</pre>\n";
*/

$open_devprogs = org_maemo_devcodes_device_dba::list_open();
echo "\$open_devprogs<pre>\n";
print_r($open_devprogs);
echo "</pre>\n";

$all_devprogs = org_maemo_devcodes_device_dba::list_all();
echo "\$all_devprogs<pre>\n";
print_r($all_devprogs);
echo "</pre>\n";

foreach($all_devprogs as $guid => $title)
{
    $device = org_maemo_devcodes_device_dba::get_cached($guid);
    $deps = (int)$device->has_dependencies();
    echo "{$title} has_dependencies returned {$deps}<br>\n";
}

$applicable_devprogs = org_maemo_devcodes_device_dba::list_applicable();
echo "\$applicable_devprogs<pre>\n";
print_r($applicable_devprogs);
echo "</pre>\n";



/*
$applicable_devprogs = org_maemo_devcodes_application_dba::list_applicable_devices();
echo "\$applicable_devprogs<pre>\n";
print_r($applicable_devprogs);
echo "</pre>\n";
*/

?>