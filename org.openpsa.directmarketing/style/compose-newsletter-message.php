<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
echo $view_data['message_array']['content'];

$newsticker_node = midcom_helper_find_node_by_component('de.linkm.newsticker');
if ($newsticker_node)
{
    /*
    echo "newsticker_node:<pre>\n";
    print_r($newsticker_node);
    echo "</pre>\n";
    */
    //echo "{$newsticker_node[MIDCOM_NAV_RELATIVEURL]}latest/{$view_data['message_array']['newsitems']}";
    $_MIDCOM->dynamic_load("{$newsticker_node[MIDCOM_NAV_RELATIVEURL]}latest/{$view_data['message_array']['newsitems']}");
}
?>