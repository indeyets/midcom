<?php
// TODO: cache this!
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//$data =& $_MIDCOM->get_custom_context_data('request_data');

// Display the member
$contact = new org_openpsa_contactwidget($data['person']);
$contact->link = "{$node[MIDCOM_NAV_FULLURL]}person/{$data['person']->guid}/";
$contact->show_groups = false;

if ($_MIDCOM->auth->can_do('midgard:update', $data['member']))
{
    $contact->extra_html = "<li>
        <input id=\"editable_title_{$data['person']->guid}_ajaxDefault\" value=\"".$data['l10n']->get('<title>')."\" type=\"hidden\" />
        <input id=\"editable_title_{$data['person']->guid}_ajaxUrl\" value=\"{$node[MIDCOM_NAV_FULLURL]}group/{$data['group']->guid}/update_member_title/\" type=\"hidden\" />
        <input id=\"editable_title_{$data['person']->guid}\" name=\"member_title[{$data['member']->id}]\" class=\"ajax_editable\" style=\"width: 80%;\" onFocus=\"ooAjaxFocus(this)\" onBlur=\"ooAjaxBlur(this)\" value=\"{$data['member_title']}\" />
        </li>\n";
}
else
{
    $contact->extra_html = "<li>{$data['member_title']}</li>\n";
}

$contact->show();
?>