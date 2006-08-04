<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="main">
    <?php $view_data['group_dm']->display_form(); ?>
</div>
