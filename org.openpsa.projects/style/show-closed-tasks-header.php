<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="completed">
    <h3><?php echo $view_data['l10n']->get('closed tasks'); ?></h3>
    <dl>
