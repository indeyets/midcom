<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$entry =& $data['entry'];

// Available request data: entry, entries, datamanager, type_list, mode, view_url, total_count, total_pages, page
//     next_page, next_page_url, previous_page, previoux_page_url
?>
<li>
    <a href="&(data['view_url']);">&(entry.title);: </a><br />
    &(entry.abstract);
</li>