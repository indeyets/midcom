<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->enable_live_mode();
$_MIDCOM->header('Content-Type: text/plain');
$exporter = midcom_helper_filesync_exporter::create('snippet');
$exporter->export();
echo "Export to {$exporter->root_dir} completed\n";
?>