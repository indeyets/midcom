<?php
global $view, $view_id, $view_title, $view_topic;
$data = $view->get_array();
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(view_title);: <?echo htmlspecialchars($data["title"]); ?></h2>

<?php
// Use datamanager to display the record
$view->display_view ();
?>