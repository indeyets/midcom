<?php
global $view_layouts;
global $view_schema;
global $view_topic;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);


if ($_MIDCOM->auth->can_do('midcom:component_config', $view_topic))
{
?>
<form name="net_nemein_simpledb_schemaform" method="POST" action="&(prefix);">

<h3><?php echo $GLOBALS["view_l10n"]->get("set database schema"); ?></h3>

<?php

if (count($view_layouts) > 0) {
    
?><p><?php echo $GLOBALS["view_l10n"]->get("select database schema"); ?><br>
<select name="net_nemein_simpledb_setschema"><?php

foreach ($view_layouts as $layout => $desc)
{ ?><option value="&(layout);">&(desc);</option><?php }

?></select></p>
<p><input type="submit" name="net_nemein_simpledb_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("select"); ?>">
</p><?php

}

?>

</form>

<?php } ?>