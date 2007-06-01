<?php
// Query the needed data
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_person = $data['person'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

// Display the contact
$contact = new org_openpsa_contactwidget($view_person);
$contact->link = "{$node[MIDCOM_NAV_FULLURL]}person/{$view_person->guid}/";
$contact->show();
?>