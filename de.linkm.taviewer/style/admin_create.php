<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$data = $view['datamanager']->get_array();
?>

<h2><?php echo $view['l10n']->get('create article'); ?>: <?php echo htmlspecialchars($data['title']); ?></h2>

<?php $view['datamanager']->display_form (); ?>
