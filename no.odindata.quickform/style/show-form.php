<?php
// Bind the view data, remember the reference assignment:

$request_data =& $_MIDCOM->get_custom_context_data('request_data');


if (($form_description = $request_data['config']->get("form_description")) != '') {
    echo $form_description;
}
echo "<h1>" . $request_data['datamanager']->errstr . "</h1>";
$request_data['datamanager']->display_form(); 
?>

<br/><br/><br/>
