<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view =& $data['datamanager']->get_content_html();
?>
<h1><?php echo $data['person']->name; ?></h1>
<?php
$data['datamanager']->display_view();
?>
