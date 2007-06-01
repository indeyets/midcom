<?php
$_MIDCOM->auth->require_valid_user();

$user = $_MIDCOM->auth->user->get_storage();

$html = org_routamc_positioning_importer::create('html');
$coordinates = $html->get_icbm_location($user);

if ($coordinates)
{
    echo sprintf('According to ICBM URL your position is %s', org_routamc_positioning_utils::microformat_location($coordinates['latitude'], $coordinates['longitude']));
}
else
{
    echo "Failed to get position, last error is {$html->error}";
}
?>