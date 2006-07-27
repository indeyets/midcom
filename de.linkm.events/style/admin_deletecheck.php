<?php
global $midcom; 
global $view_title;
global $view;
global $view_id;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("delete event"); ?></h2>

<form action="&(prefix);/delete/&(view_id);.html" method="POST">

<?php midcom_show_style("admin_viewrecord"); ?>

<div class="form_toolbar">
  <input type="submit" name="de_linkm_events_deleteok" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?>" />
  <input type="submit" name="de_linkm_events_deletecancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>" />
</div>

</form>
