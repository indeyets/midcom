<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

if ($data['event']->can_do('org.openpsa.calendar:read'))
{

    echo "<div class=\"description\">{$data['event']->description}</div>";

    $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$data['event']->guid}/in/normal");
}
?>