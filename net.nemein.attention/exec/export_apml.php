<?php
$_MIDCOM->auth->require_valid_user();

$exporter = net_nemein_attention_exporter::create('apml');
$person = $_MIDCOM->auth->user->get_storage();

if (isset($_GET['profile']))
{
    // Export only a given profile
    $exporter->export($person, $_GET['profile']);
}
else
{
    // Export all user's APML data
    $exporter->export($person);
}
?>