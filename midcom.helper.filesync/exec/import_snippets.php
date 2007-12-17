<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->enable_live_mode();
$_MIDCOM->header('Content-Type: text/plain');
$importer = midcom_helper_filesync_importer::create('snippet');
$importer->import();
echo "Import from {$importer->root_dir} completed\n";
?>