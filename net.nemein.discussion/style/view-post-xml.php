<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$post =& $data['post'];
$view =& $data['view_post'];

$mapper = new midcom_helper_xml_objectmapper();
echo $mapper->object2data($post);
?>