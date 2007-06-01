<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(data['folder_name']);: <?php echo $data['view_title']; ?></h1>

<?php 
$data['datamanager']->display_view(); 
?>