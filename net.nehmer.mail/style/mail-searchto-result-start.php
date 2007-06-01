<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<p style="font-size: bold;">
    <?php echo sprintf($data['l10n']->get('%d user(s) have been found.'), $data['result_count']); ?>
</p>

<ul>