<?php
$_MIDCOM->auth->require_valid_user();
$handler = new net_nemein_opendeploydumper();
//$handler->mode = 'multiple';
echo "<p>Time: " . time() . "<p>\n";
echo "<p>API version: {$handler->mgd_api}</p>\n";
$article =  new midcom_baseclasses_database_article('9efe7ff6a36c7e84796047b967bf875f');
//$handler->_check_approves = true;
$handler->updated($article);
//$handler->deleted($article);
echo "<p>object_url: {$handler->_object_url}</p>\n"; 
echo "<p>object rendered <tt>\n" . nl2br(htmlentities($handler->_object_html)) . "\n</tt></p>\n";


?>