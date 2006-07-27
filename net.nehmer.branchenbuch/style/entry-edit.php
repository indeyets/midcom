<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['entry_controller']->datamanager->get_content_html();
?>
<h2><?php echo $data['topic']->extra; ?>: <?php echo $data['branche']->get_full_name(); ?></h2>
<h3>&(view['firstname']); &(view['lastname']);</h3>

<?php $data['entry_controller']->display_form(); ?>