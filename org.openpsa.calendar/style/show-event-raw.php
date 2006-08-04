<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

if ($view['event']->can_do('org.openpsa.calendar:read'))
{

    echo "<div class=\"description\">{$view['event']->description}</div>";

    $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view['event']->guid}/in/normal"); 
}
?>
