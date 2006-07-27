<?php
$_MIDCOM->auth->require_valid_user();

$user = $_MIDCOM->auth->user->get_storage();

$plazes = org_routamc_positioning_importer::create('plazes');
$coordinates = $plazes->get_plazes_location($user);

if ($coordinates)
{
    echo sprintf('According to Plazes your position is %s', org_routamc_positioning_utils::pretty_print_coordinates($coordinates['latitude'], $coordinates['longitude']));
}
else
{
    echo "Failed to get position, last error is {$plazes->error}";
}
?>