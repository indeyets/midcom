<?php
global $view;
global $view_mgr;
global $view_id;
global $midcom;
$data = $view;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h3><?php echo $GLOBALS["view_l10n"]->get("edit article"); ?>: <?php echo htmlentities($data["title"]); ?></h3>

<div class="toolbar">
  <a href="&(prefix);edit/&(view_id);"><?php echo $GLOBALS["view_l10n_midcom"]->get("edit"); ?></a>
  <a href="&(prefix);delete/&(view_id);"><?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?></a>
</div>

<?php $view_mgr->display_view (); ?>