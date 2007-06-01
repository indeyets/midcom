<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<h1><?php echo $data['l10n']->get('sending the message failed'); ?></h1>
<p>
    <?php echo $data['l10n']->get('there was an error while sending the message'); ?>
</p>
