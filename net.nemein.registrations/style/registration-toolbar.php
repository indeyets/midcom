<?php
// Required request keys:
// edit_url, delete_url

$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$toolbar = new midcom_helper_toolbar();

if ($data['edit_url'])
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => $data['edit_url'],
        MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('edit'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true,
    ));
}
if ($data['delete_url'])
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => $data['delete_url'],
        MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('delete'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
        MIDCOM_TOOLBAR_ENABLED => true,
    ));
}

echo $toolbar->render();
?>