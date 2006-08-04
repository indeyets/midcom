<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="main">
    <div class="area">
        <h2><?php echo $view_data['l10n']->get('org.openpsa.reports'); ?></h2>
        <p>In fact normal user should not reach this page normally, they will be redirected
        to their default report subpage, this is here for testing for now.</p>
    </div>
</div>
<div class="sidebar">
    <div class="area">
        <h2><?php echo $view_data['l10n_midcom']->get("instructions"); ?></h2> 
        <p><?php echo $view_data['l10n']->get("instructions for frontpage"); ?></p>
    </div>
</div>