<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<p>
    <?php echo $data['l10n']->get('no exhibitions have been held yet'); ?>
</p>