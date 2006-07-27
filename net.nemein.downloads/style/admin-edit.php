<?php
global $view;
global $view_id;
global $view_title;
global $view_topic;
global $midcom;
    
$data = $view->get_array();
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(view_title);: <?php echo htmlspecialchars($data["title"]); ?></h2>

<?php
$view->display_form();
?>