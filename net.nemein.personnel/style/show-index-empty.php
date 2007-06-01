<?php
// Available Request keys: persons

// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['topic']->extra; ?></h1>

<?php midcom_show_style('index-alpha-bar'); ?>

<p><?php $data['l10n']->show('no persons found.'); ?></p>