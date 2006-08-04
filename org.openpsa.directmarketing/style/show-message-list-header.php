<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="area">
    <h2><?php echo $view_data['l10n']->get('messages'); ?></h2>
    <?php  $view_data['qbpager']->show_pages(); ?>
    <ul class="comments">