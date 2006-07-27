<?php
// Required request keys:
// edit_url, delete_url

$data =& $_MIDCOM->get_custom_context_data('request_data');
$toolbar = new midcom_helper_toolbar();

if ($data['create_url'])
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => $data['create_url'],
        MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('create an event'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        MIDCOM_TOOLBAR_ENABLED => true,
    ));
}

if ($data['list_all_url'])
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => $data['list_all_url'],
        MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('list all events'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
        MIDCOM_TOOLBAR_ENABLED => true,
    ));
}

echo $toolbar->render();
?>