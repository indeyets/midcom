<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
echo "\$data['composed_mail_bodies']:<pre>\n";
ob_start();
print_r($data['composed_mail_bodies']);
$r = ob_get_contents();
ob_end_clean();
echo htmlentities($r, ENT_QUOTES, 'UTF-8');
echo "</pre>\n";
?>
