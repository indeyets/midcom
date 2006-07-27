<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>

<p style="font-size: bold;">
    <?php echo sprintf($view['l10n']->get('%d user(s) have been found.'), $view['result_count']); ?>
</p>

<ul>