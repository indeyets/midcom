<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php 
    $GLOBALS["midcom"]->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."project/list/"); 
    ?>
</div>
<div class="sidebar">
    <?php 
    $GLOBALS["midcom"]->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."task/list/"); 
    ?>
</div>