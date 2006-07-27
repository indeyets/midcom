<?php
$prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "attachment/";
global $view_l10n;
global $view_l10n_midcom;
?>

<h1 class="aish1"><?php echo $view_l10n->get("create attachment"); ?></h1>

<form method="post" action="&(prefix);createok" enctype="multipart/form-data">

<div class="form_description"><?php echo $view_l10n->get("file"); ?>:</div>
<div class="form_field"><input name="f_file" type="file" class="fileselector"></div>

<div class="form_description"><?php echo $view_l10n->get("short description"); ?> (<?php echo $view_l10n_midcom->get("opt"); ?>):</div>
<div class="form_field"><input name="f_title" type="text" maxlength="255" value="" class="shorttext"></div>

<div class="form_description"><?php echo $view_l10n->get("new filename"); ?> (<?php echo $view_l10n_midcom->get("opt"); ?>):</div>
<div class="form_field"><input name="f_filename" type="text" maxlength="255" value="" class="shorttext"></div>

<div class="form_toolbar">
    <input type="submit" name="f_submit" value="<?php echo $view_l10n_midcom->get("upload"); ?>">
    <input type="submit" name="f_cancel" value="<?php echo $view_l10n_midcom->get("cancel"); ?>">
</div>

</form>
