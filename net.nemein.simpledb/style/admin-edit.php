<?php
global $view, $view_id, $view_title, $view_topic;
$data = $view->get_array();
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n_midcom"]->get("edit"); ?> &(view_title);: <?echo htmlspecialchars($data["title"]); ?></h2>

<?php
$view->display_form(); 
?>