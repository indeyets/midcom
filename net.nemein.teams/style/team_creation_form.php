<?php
/**
 * This is the styleelement I use to show the initialization
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('create a team'); ?></h1>

<?php $data['controller']->display_form (); ?>
