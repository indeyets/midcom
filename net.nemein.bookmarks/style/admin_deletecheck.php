<?php
global $view_title;
global $view;
global $view_id;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("Delete bookmark"); ?>: &(view["title"]);</h2>

<?php midcom_show_style("admin_viewrecord"); ?>

<form action="&(prefix);/delete/&(view_id);.html" method="post">
  <input type="submit" name="net_nemein_bookmarks_deleteok" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?>" />
  <input type="submit" name="net_nemein_bookmarks_deletecancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>" />
</form>
