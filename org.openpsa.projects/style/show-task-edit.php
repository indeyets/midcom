<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['task_dm'];
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
