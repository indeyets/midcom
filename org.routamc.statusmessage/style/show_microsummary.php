<?php
$metadata = midcom_helper_metadata::retrieve($data['message']);
$author_user = $_MIDCOM->auth->get_user($metadata->get('author'));
$author = $author_user->get_storage();

$status_line = $author->name;

if ($GLOBALS['midcom_config']['positioning_enable'])
{
    $_MIDCOM->load_library('org.routamc.positioning');
    $user_position = new org_routamc_positioning_person($author);
    $coordinates = $user_position->get_coordinates();
    $pretty_coordinates = org_routamc_positioning_utils::pretty_print_location($coordinates['latitude'], $coordinates['longitude']);
    
    $status_line .= ", in {$pretty_coordinates}";
}

$status_line .= " {$data['message']->status}";
?>
&(status_line:h);