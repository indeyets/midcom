<?php
global $view, $view_id, $view_title, $view_topic;
    
$data = $view->get_array();
?>
<h2>&(view_title);: <?echo htmlspecialchars($data["title"]); ?></h2>

<?php
$view->display_form(); 
?>