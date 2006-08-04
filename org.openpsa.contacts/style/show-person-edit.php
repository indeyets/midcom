<?php
global $view;
//$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
<div class="sidebar">
    <?php $GLOBALS["midcom"]->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."person/".$view_data['person']->guid()."/groups/"); ?>
</div>