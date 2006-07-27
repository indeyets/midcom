<?php
global $view_l10n;
global $view_l10n_midcom;
global $view_auth;
global $view;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
</table>
<!-- index-incidenttype-end &(view["name"]); -->
<p><a href="&(prefix);create/type/&(view["id"]);.html"><?php echo $view_l10n->get("create new incident"); ?></a></p>