<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$monthtime = mktime(01, 01, 00, $data['event_month'], 01, date('Y'));
?>
<h3><?php echo strftime("%B",$monthtime); ?></h3>