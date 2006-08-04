<?php
$view =& $GLOBALS['midcom']->get_custom_context_data('request_data');
?>
<div class="area">
    <h2><?php echo $view['l10n']->get("contacts"); ?></h2>
    <?php $view['members_qb']->show_pages(); ?>
    <dl>