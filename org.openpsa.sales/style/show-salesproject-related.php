<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

//TODO: Configure whether to show in/both and reverse vs normal sorting ?
$_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view_data['salesproject']->guid}/both/normal"); 
?>
</div>