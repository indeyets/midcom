<?php
global $view_l10n;
global $view_l10n_midcom;
global $view_auth;
global $view_datamanager;
global $view; // Incident event object

$incident_type = $GLOBALS["view_incident_type"];

$data = $view_datamanager->get_array();
$title = $view_l10n->get("view incident");

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(title);</h2>

<p>
  <a href="&(prefix);"><?echo $view_l10n_midcom->get("back"); ?></a>
<?php if ($view_auth->can_write()) { ?>
 - <a href="&(prefix);edit/&(view.id);.html"><?echo $view_l10n_midcom->get("edit"); ?></a>
<?php } ?>
</p>

<?php $view_datamanager->display_view(); ?>

<p>
  <a href="&(prefix);"><?echo $view_l10n_midcom->get("back"); ?></a>
<?php if ($view_auth->can_write()) { ?>
 - <a href="&(prefix);edit/&(view.id);.html"><?echo $view_l10n_midcom->get("edit"); ?></a>
<?php } ?>
</p>