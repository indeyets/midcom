<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('edit featured'); ?></h2>

<?php $data['controller']->display_form (); ?>