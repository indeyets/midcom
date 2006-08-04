<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');

$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<li>
    <a href="#" onclick="<?php echo org_openpsa_calendar_interface::calendar_editevent_js($view['event']->guid, $node); ?>">
    <?php echo date('H:i', $view['event']->start); ?>:  
    <?php echo $view['event']->title; ?></a>
    <div class="location"><?php echo $view['event']->location; ?></div>
</li>