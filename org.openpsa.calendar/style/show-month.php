<?php
$view =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div id="org_openpsa_calendar_calendarwidget"></div>
<div class="main wide">
    <div class="area">
        <h2><?php echo strftime("%B %Y", $view['selected_time']); ?></h2>
        <?php $view['calendar']->show(); ?>
    </div>
</div>