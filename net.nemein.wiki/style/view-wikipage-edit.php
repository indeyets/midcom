<?php 
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1></h1>

<?php $request_data['controller']->display_form (); ?>