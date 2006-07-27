<?php
// Available request keys: categories, id, title, key, category_url, description

$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<li><a href="&(data['category_url']);">&(data['description']);</a></li>