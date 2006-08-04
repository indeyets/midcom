<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php 
    $view['event_dm']->display_view();
    
    if ($view['event']->can_do('org.openpsa.calendar:read'))
    {
        echo "<div style=\"clear: both;\"></div>\n";    
        $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view['event']->guid}/both/normal"); 
    }
    ?>
</div>
