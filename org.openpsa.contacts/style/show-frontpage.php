<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php
    $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."buddylist");
    ?>
</div>
<div class="sidebar">
    <?php
    midcom_show_style("search-form-simple");
    ?>
</div>