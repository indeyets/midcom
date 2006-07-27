<?php
global $view; 
global $view_descriptions;
global $view_id;
global $midcom; 
global $view_title;
global $view_layout;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<?php $view_layout->display_view(); ?>
