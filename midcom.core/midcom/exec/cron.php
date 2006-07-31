<?php 
header('Content-Type: text/plain'); 

require(MIDCOM_ROOT . '/midcom/services/cron.php');

$cron = new midcom_services_cron();

// The sudo call is temporary until HTTP Basic Auth works.
$_MIDCOM->auth->request_sudo('midcom.services.cron');
$cron->execute();
$_MIDCOM->auth->drop_sudo();

?>