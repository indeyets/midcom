<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$item =& $data['item'];
$view = $data['datamanager']->get_content_html();
?>
<ul id="cc_kaktus_todo_list_&(item.up);">
    <li>
        &(view['title']:h);
    </li>
</ul>
