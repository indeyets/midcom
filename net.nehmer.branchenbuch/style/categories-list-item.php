<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$category =& $data['category'];
?>
<li>
    <a href="&(category['listurl']);">
        &(category['fullname']); (&(category['entrycount']);)
    </a>
</li>