<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: entries, type_list, mode, total_count, total_pages, page
//     next_page, next_page_url, previous_page, previoux_page_url, new_search_url, last_search_url
?>
</ul>

<p><?php echo sprintf($data['l10n']->get('found %d entries.'), $data['total_count']); ?></p>

<p>
<?php if ($data['previous_page'] !== null) { ?>
<a href="&(data['previous_page_url']);"><?php $data['l10n_midcom']->show('previous page'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php
}
echo sprintf($data['l10n_midcom']->get('page %d of %d'), $data['page'], $data['total_pages']);

if ($data['next_page'] !== null) {
?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="&(data['next_page_url']);"><?php $data['l10n_midcom']->show('next page'); ?></a>
<?php } ?>
</p>