<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$hash = $data['hash'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$register_link = $prefix . "register_invitation/" . $hash;
$message = $data['user_message'];
?>

Test email...

&(message);


&(register_link);




