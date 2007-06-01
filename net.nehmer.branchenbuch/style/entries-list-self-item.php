<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['entry_dm']->get_content_html();
$branche_name = $data['branche']->get_full_name();
$update_icon_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/edit.png';
$delete_icon_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/trash.png';
?>
<li>
    <a href="&(data['detail_url']);">&(view['firstname']); &(view['lastname']);</a>
    (<a href="&(data['branche_url']);">&(branche_name);</a>)
<?php if ($data['update_url'] !== null) { ?>
    <a href="&(data['update_url']);"><img src="&(update_icon_url);" /></a>
<?php } if ($data['delete_url'] !== null) { ?>
    <a href="&(data['delete_url']);"><img src="&(delete_icon_url);" /></a>
<?php } ?>
</li>