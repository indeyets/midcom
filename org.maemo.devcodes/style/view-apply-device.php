<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['title']);</h2>

<p><?php echo $data['l10n']->get('type below what you would do with the device'); ?></p>

<?php $data['controller']->display_form (); ?>