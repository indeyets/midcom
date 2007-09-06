<?php
// Available request keys: controller, indexmode, schema, schemadb
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$title = sprintf
(
    $data['l10n_midcom']->get('create %s'),
    $data['l10n']->get("{$data['link_type']} item")
);
?>

<h2><?php echo $title; ?>: <?php echo $data['topic']->extra; ?></h2>

<?php
$data['controller']->display_form();
?>