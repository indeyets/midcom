<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: entries, category, category_name, mode, total_count, total_pages, page
//     next_page, next_page_url, previous_page, previoux_page_url
?>
<h2><?php echo "{$data['topic']->extra}: {$data['category_name']}"; ?></h2>

<ul>