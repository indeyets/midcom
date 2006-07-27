<?php 
global $view_config;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h3><?php echo $GLOBALS["view_l10n"]->get("File Browser"); ?></h3>

<form name="pl_olga_files_createform" method="POST" action="&(prefix);edit/">
<p><?php echo $GLOBALS["view_l10n"]->get("enter root"); ?>:<br>
<input name="pl_olga_files_root_path" value="<?php echo $view_config->get("root_path")?>"></p>
<p><input type="submit" name="pl_olga_files_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>" />
</p></form>