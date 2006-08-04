<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');

// Display the member
$contact = new org_openpsa_contactwidget($view_data['person']);
$contact->show_groups = false;

$checked = '';
if ($view_data['subscribed'])
{
    $checked = ' checked="checked"';
}
$contact->prefix_html = "<input style=\"float: right;\"{$checked} type=\"checkbox\" class=\"org_openpsa_calendar_filters_person\" id=\"org_openpsa_calendar_filters_{$view_data['person']->guid}\" title=\"" . $view_data['l10n']->get('show calendar') . "\" value=\"{$view_data['person']->guid}\" />";

$contact->show();
?>