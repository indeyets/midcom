<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."campaign/list/"); ?>
</div>
<div class="sidebar">
    <div class="area">
        <!-- TODO: List latest messages -->
    </div>
</div>