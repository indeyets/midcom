<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $_MIDCOM->i18n->get_string('edit banned', 'net.nemein.bannedwords'); ?></h2>

<?php $data['controller']->display_form (); ?>

