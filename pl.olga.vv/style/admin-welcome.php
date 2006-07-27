<?php
global $view;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
if($view["allow_create_by_uri"]=="true") $checked1="checked";else checked1="";
if($view["antispam"]=="true") $checked2="checked";else checked2="";
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("views & votes"); ?></h2>


<form name="pl_olga_vv_editform" method="POST" action="&(prefix);/edit/">

<p><b><?php echo $GLOBALS["view_l10n"]->get("setup"); ?></b></p>

<p><?php echo $GLOBALS["view_l10n"]->get("allow create by uri"); ?> 
<input type="checkbox" name="pl_olga_vv_allow_create_by_uri" value="1" &(checked1);><br>
<p><?php echo $GLOBALS["view_l10n"]->get("activate antispam"); ?> 
<input type="checkbox" name="pl_olga_vv_antispam" value="1" &(checked2);><br>
</p>
<p><input type="submit" name="pl_olga_vv_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("next"); ?>">
</p></form>