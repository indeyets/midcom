<?php

$session = new midcom_service_session();
$msg = $session->get("processing_msg");

?>

<div style="background-color: white; border: 1px solid red; padding: 5px;">&(msg:h);</div>