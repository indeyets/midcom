<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');
// if you need to set a spesific value to the schema before showing it:
// $data['form']->set_value('comment', 'Insert your comment here');
echo $data['form']->description( );

echo "<h1>" .$data['form']->error( ) . "</h1>\n";
$data['form']->display_form(); 
?>
