<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>Index</h1>
<p>TBD: something usefull</p>

<?php
echo "Trying to DL {$data['dl_prefix']}application/list/my";
$_MIDCOM->dynamic_load($data['dl_prefix'] . 'application/list/my');
?>