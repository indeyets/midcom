<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['topic']->extra; ?>: <?php echo $data['branche']->get_full_name(); ?></h2>

<ul>