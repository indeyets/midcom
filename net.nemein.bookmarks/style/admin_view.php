<?php
global $view_title;
global $view;
global $view_id;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo htmlspecialchars($view["title"]); ?></h2>

<?php 
$view->display_view();
?>
