<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['entry_dm']->get_content_html();
?>
<li><a href="&(data['detail_url']);">&(view['firstname']); &(view['lastname']);</a></li>