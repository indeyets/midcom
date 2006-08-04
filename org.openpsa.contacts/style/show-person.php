<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $view_data['person_dm']->display_view(); ?>
</div>
<div class="sidebar">
    <?php 
    $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."person/".$view_data['person']->guid."/groups/");
    midcom_show_style("show-person-account"); 
    
    // Try to find campaigns component
    $campaigns_node = midcom_helper_find_node_by_component('org.openpsa.directmarketing');
    if ($campaigns_node)
    {
        $_MIDCOM->dynamic_load($campaigns_node[MIDCOM_NAV_RELATIVEURL]."campaign/list/".$view_data['person']->guid);
    }
    
    $dbe_serviceid = $view_data['person']->parameter('org.openpsa.dbe', 'serviceID');
    if ($dbe_serviceid)
    {
        $synchronized = $view_data['person']->parameter('org.openpsa.dbe', 'synchronized');
            
        $class = "dbe";
        $sync_label = sprintf($view_data['l10n']->get('last synchronized %s'), strftime('%x %X', $synchronized));
        if (!$synchronized)
        {
            $class = "dbe-unsynchronized";
            $sync_label = $view_data['l10n']->get('never synchronized');
        }
        
        echo "<div class=\"area {$class}\">\n";
        echo "  <h2>".$view_data['l10n']->get('digital business ecosystem')."</h2>\n";
        echo "  <dl>\n";
        echo "    <dt>".$view_data['l10n']->get('dbe service id')."</dt><dd>{$dbe_serviceid}</dd>\n";
        echo "    <dt>".$view_data['l10n']->get('synchronization')."</dt><dd>{$sync_label}</li>\n";        
        echo "  </ul>\n";
        echo "</div>\n";
    }
    ?>
</div>
