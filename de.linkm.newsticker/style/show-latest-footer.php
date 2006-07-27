<?php
$i18n =& $GLOBALS["midcom"]->get_service("i18n");
$l10n =& $i18n->get_l10n("de.linkm.newsticker");
$l10n_midcom =& $i18n->get_l10n("midcom");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

if ($GLOBALS["view_config"]->get("pp_enable")) {
?>
<p style="font-size: smaller;"><a href="&(prefix);add_entry.html"><?echo $l10n->get("add a new entry"); ?></a></p>
<?php } ?>