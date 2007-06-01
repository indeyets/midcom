<?php
global $view_title;
global $view;
global $view_id;

$data = $view->get_array();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("Edit bookmark"); ?></h2>

<?php $view->display_form(); ?>
