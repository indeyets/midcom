<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $data['controller']->display_form(); ?>
</div>
<div class="sidebar">
    <?php
    $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL] . "person/{$data['person']->guid}/groups/");
    midcom_show_style("show-person-account");
    ?>
</div>