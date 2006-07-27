<?php
global $view; 
global $view_id;
global $midcom; 
global $view_title;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("event"); ?></h2>

<?php midcom_show_style("admin_viewrecord"); ?>