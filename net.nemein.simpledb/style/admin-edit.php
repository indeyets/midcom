<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_array();
?>

<h1><?php $data['view_title']; ?></h1>

<?php $data['datamanager']->display_form(); ?>