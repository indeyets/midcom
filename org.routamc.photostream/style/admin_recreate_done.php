<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$node =& $data['photostream_node'];
?>
</ol>
<?php echo '<p>' . $data['l10n']->get('done recreating derived images') . ", <a href='{$node[MIDCOM_NAV_FULLURL]}'>" . $data['l10n']->get('return to photostream') . "</a></p>\n";