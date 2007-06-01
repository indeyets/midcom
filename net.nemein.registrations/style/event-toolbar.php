<?php
// Required request keys:
// edit_url, delete_url, list_registrations_url, register_url, registration_allowed, registration_url,
// registration_open, open_url, close_url

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$toolbar = new midcom_helper_toolbar();

if ($data['registration_open'])
{
    if (   $_MIDCOM->auth->user
        && ! $data['registration_allowed'])
    {
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => $data['register_url'],
            MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('you do not have the permissions to register for this event.'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
            MIDCOM_TOOLBAR_ENABLED => false,
        ));
    }
    else if ($data['register_url'])
    {
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => $data['register_url'],
            MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('register for this event'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
            MIDCOM_TOOLBAR_ENABLED => true,
        ));
    }
    else
    {
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => $data['registration_url'],
            MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('you have already registered for this event.'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
            MIDCOM_TOOLBAR_ENABLED => true,
        ));
    }
} else if ($data['registration_url'])
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => $data['registration_url'],
        MIDCOM_TOOLBAR_LABEL => $data['l10n']->get('view your registration'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
        MIDCOM_TOOLBAR_ENABLED => true,
    ));
}

echo $toolbar->render();
?>