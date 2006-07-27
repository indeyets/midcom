<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: entries, type_list, mode, total_count, total_pages, page
//     next_page, next_page_url, previous_page, previoux_page_url, new_search_url, last_search_url
?>
<h2><?php $data['l10n']->show('search result'); ?></h2>

<ul>