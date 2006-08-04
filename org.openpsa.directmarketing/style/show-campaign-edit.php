<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['campaign_dm'];
$nap = new midcom_helper_nav();
/*
$node = $nap->get_node($nap->get_current_node());
$contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
*/
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
<div class="sidebar">
    <div class="area">
    </div>
</div>
