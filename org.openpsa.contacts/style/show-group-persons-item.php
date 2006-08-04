<?php
// TODO: cache this!
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');

// Display the member
$contact = new org_openpsa_contactwidget($view_data['person']);
$contact->link = "{$node[MIDCOM_NAV_FULLURL]}person/{$view_data['person']->guid}/";
$contact->show_groups = false;

if ($_MIDCOM->auth->can_do('midgard:update', $view_data['member']))
{
    $contact->extra_html = "<li>
        <input id=\"editable_title_{$view_data['person']->guid}_ajaxDefault\" value=\"".$view_data['l10n']->get('<title>')."\" type=\"hidden\" />
        <input id=\"editable_title_{$view_data['person']->guid}_ajaxUrl\" value=\"{$node[MIDCOM_NAV_FULLURL]}group/{$view_data['group']->guid}/update_member_title/\" type=\"hidden\" />
        <input id=\"editable_title_{$view_data['person']->guid}\" name=\"member_title[{$view_data['member']->id}]\" class=\"ajax_editable\" style=\"width: 80%;\" onFocus=\"ooAjaxFocus(this)\" onBlur=\"ooAjaxBlur(this)\" value=\"{$view_data['member_title']}\" />
        </li>\n";
}
else
{
    $contact->extra_html = "<li>{$view_data['member_title']}</li>\n";
}

$contact->show();
?>