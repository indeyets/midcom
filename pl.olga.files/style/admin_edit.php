<?php
global $view;
global $view_id;
global $midcom;
$data = $view->get_array();
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h3><?php echo $GLOBALS["view_l10n"]->get("edit article"); ?>: <?php echo $data["title"]; ?></h3>

<?php $view->display_form(); ?>