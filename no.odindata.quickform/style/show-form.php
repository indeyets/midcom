<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');


if (($form_description = $data['form_description']) != '')
{
    echo $form_description;
}
echo "<h1>{$data['datamanager']->errstr}</h1>\n";
$data['datamanager']->display_form(); 
?>
