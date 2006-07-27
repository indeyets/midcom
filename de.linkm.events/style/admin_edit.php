<?php
global $view;
global $view_id;
global $view_title;
global $midcom;

$data = $view->get_array();
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("edit event"); ?></h2>

<?php $view->display_form(); ?>
