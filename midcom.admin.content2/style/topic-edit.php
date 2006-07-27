<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['title']);</h1>
<?
$data['datamanager']->display_form();

//print_r($data['datamanager']->schemadb);


