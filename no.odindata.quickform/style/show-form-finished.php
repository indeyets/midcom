<?php
// Bind the view data, remember the reference assignment:

$request_data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<p>
<?php 
    echo $request_data['config']->get("end_message");
?>
</p>
