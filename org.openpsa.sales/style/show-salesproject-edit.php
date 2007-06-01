<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['salesproject_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
<div class="sidebar">
</div>