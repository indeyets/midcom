<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

// Available request data: entry, controller, type_list, type_config, type, mode, view_url
?>

<h2><?php $data['l10n']->show("edit {$data['mode']}"); ?></h2>

<?php $data['controller']->display_form(); ?>

<p><a href="&(data['view_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>