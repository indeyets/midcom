<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->enable_live_mode();

$exporter = midcom_helper_filesync_exporter::create('structure');
$exporter->export();
?>