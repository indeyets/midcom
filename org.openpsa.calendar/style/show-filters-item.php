<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

// Display the member
$contact = new org_openpsa_contactwidget($data['person']);
$contact->show_groups = false;

$checked = '';
if ($data['subscribed'])
{
    $checked = ' checked="checked"';
}
$contact->prefix_html = "<input style=\"float: right;\"{$checked} type=\"checkbox\" class=\"org_openpsa_calendar_filters_person\" id=\"org_openpsa_calendar_filters_{$data['person']->guid}\" title=\"" . $data['l10n']->get('show calendar') . "\" value=\"{$data['person']->guid}\" />";

$contact->show();
?>