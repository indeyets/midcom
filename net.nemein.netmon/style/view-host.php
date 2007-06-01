<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$host =& $data['host'];
$host_view =& $data['view_host'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(host_view['title']);</h2>

<?php $data['datamanager']->display_view(); ?>
