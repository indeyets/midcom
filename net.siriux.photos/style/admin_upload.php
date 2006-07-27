<?php
global $view_title;
global $view_status;
global $view_upload_dm;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$have_gzip = ! is_null($GLOBALS['midcom_config']['utility_gzip']); 
$have_tar = ! is_null($GLOBALS['midcom_config']['utility_tar']);
$have_unzip = ! is_null($GLOBALS['midcom_config']['utility_unzip']);
$have_jhead = ! is_null($GLOBALS['midcom_config']['utility_jhead']);
$have_phpexif = function_exists("read_exif_data");

$post_max = sscanf(ini_get("post_max_size"), "%u");
$upload_max = sscanf(ini_get("upload_max_filesize"), "%u");
$max_upload_size = min($post_max[0], $upload_max[0]);
?>

<?php if ($view_status) { ?>
  <h2><?php echo $GLOBALS["view_l10n"]->get("upload status"); ?></h2>
  &(view_status:h);
<?php } ?>

<h2><?php echo $GLOBALS["view_l10n"]->get("upload photos"); ?></h2>

<?php $view_upload_dm->display_form(); ?>

<p><?php echo $GLOBALS["view_l10n"]->get("upload can take time"); ?></p>

<ul>
<li>
  <strong><?echo $GLOBALS["view_l10n"]->get("available exif parsers")?>:</strong>
  <?php if ($have_jhead) { ?> JHead<?php } ?>
  <?php if ($have_phpexif) { ?> PHP<?php } ?>
</li>
<li>
  <strong><?echo $GLOBALS["view_l10n"]->get("supported archive formats")?>:</strong>
  <?php if ($have_tar && $have_gzip) { ?> .tar.gz .tgz<?php } ?>
  <?php if ($have_unzip) { ?> .zip<?php } ?>
</li>
<li>
  <strong><?echo $GLOBALS["view_l10n"]->get("upload size limit")?>:</strong> &(max_upload_size); MB
</li>
