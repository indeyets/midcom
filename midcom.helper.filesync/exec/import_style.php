<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->enable_live_mode();
$_MIDCOM->header('Content-Type: text/plain');
$exporter = midcom_helper_filesync_importer::create('style');
$exporter->import();
echo "Target: {$exporter->root_dir}\n";
?>