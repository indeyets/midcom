<?php
global $view;
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>