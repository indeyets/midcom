<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="pending_approve">
    <h3><?php echo $view_data['l10n']->get('tasks pending approval'); ?></h3>
    <dl>
