<?php
$title = sprintf
(
    $data['l10n_midcom']->get('edit %s'),
    $data['l10n']->get("{$data['link_type']} item")
);
?>

<h2><?php echo $data['topic']->extra; ?>: <?php echo $title; ?></h2>

<?php
$data['controller']->display_form();
?>